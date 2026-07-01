<?php
/**
 * Firma.php
 * Firma digital para proteger la integridad de los datos sensibles
 * del perfil laboral: salario, código de empleado, tipo de empleado
 * (planilla), ocupación y fecha de inicio.
 *
 * Modo principal: firma asimétrica RSA-2048 con OpenSSL
 * (openssl_pkey_new + openssl_sign / openssl_verify).
 *
 * Modo de respaldo: en muchos WampServer de Windows, openssl_pkey_new()
 * falla porque PHP no encuentra el archivo "openssl.cnf" necesario para
 * generar llaves RSA (error típico: "no such file"). Cuando esto ocurre,
 * esta clase cambia automáticamente a un esquema con OpenSSL basado en
 * una llave secreta aleatoria (openssl_random_pseudo_bytes) y una huella
 * digital (openssl_digest), que no depende de ningún archivo de
 * configuración y funciona en cualquier instalación.
 *
 * En ambos casos el resultado es el mismo para el resto del sistema:
 * Firma::firmarDatos() / Firma::verificarDatos().
 */
class Firma
{
    private static function carpetaLlaves()
    {
        return __DIR__ . '/../keys';
    }

    private static function rutaLlavePrivada() { return self::carpetaLlaves() . '/private.pem'; }
    private static function rutaLlavePublica() { return self::carpetaLlaves() . '/public.pem'; }
    private static function rutaSecreto()      { return self::carpetaLlaves() . '/secret.key'; }
    private static function rutaModo()         { return self::carpetaLlaves() . '/modo.txt'; }

    /** Busca un openssl.cnf válido en las rutas típicas de WampServer/XAMPP */
    private static function localizarOpensslCnf()
    {
        $candidatos = [];

        if (getenv('OPENSSL_CONF')) {
            $candidatos[] = getenv('OPENSSL_CONF');
        }

        $iniActivo = php_ini_loaded_file();
        if ($iniActivo) {
            $dirIni = dirname($iniActivo);
            $candidatos[] = $dirIni . '/extras/ssl/openssl.cnf';
            $candidatos[] = $dirIni . '/extras/openssl.cnf';
            $candidatos[] = $dirIni . '/../extras/ssl/openssl.cnf';

            // Busca en carpetas hermanas dentro de .../bin/php/phpX.X.X/
            $carpetaPhpBase = dirname($dirIni);
            if (is_dir($carpetaPhpBase)) {
                foreach ((glob($carpetaPhpBase . '/*', GLOB_ONLYDIR) ?: []) as $carpeta) {
                    $candidatos[] = $carpeta . '/extras/ssl/openssl.cnf';
                }
            }
        }

        foreach ($candidatos as $c) {
            if ($c && @file_exists($c)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Prepara el mecanismo de firma (una sola vez). Devuelve true si
     * quedó listo (en modo RSA o en modo de respaldo).
     */
    public static function generarLlaves()
    {
        $dir = self::carpetaLlaves();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        // Ya se configuró en una ejecución anterior de setup.php
        if (file_exists(self::rutaModo())) {
            return true;
        }

        // ---------- Intento 1: RSA-2048 con OpenSSL (ideal) ----------
        if (function_exists('openssl_pkey_new')) {
            $cnf = self::localizarOpensslCnf();
            if ($cnf) {
                putenv('OPENSSL_CONF=' . $cnf);
            }
            $config = [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];
            if ($cnf) {
                $config['config'] = $cnf;
            }

            $recurso = @openssl_pkey_new($config);
            if ($recurso !== false) {
                $llavePrivadaPem = '';
                @openssl_pkey_export($recurso, $llavePrivadaPem);
                $detalles = @openssl_pkey_get_details($recurso);
                if ($llavePrivadaPem !== '' && $detalles && isset($detalles['key'])) {
                    file_put_contents(self::rutaLlavePrivada(), $llavePrivadaPem);
                    file_put_contents(self::rutaLlavePublica(), $detalles['key']);
                    file_put_contents(self::rutaModo(), 'RSA');
                    return true;
                }
            }
        }

        // ---------- Intento 2 (respaldo): llave secreta + huella OpenSSL ----------
        // No depende de openssl.cnf. Se activa automáticamente cuando el
        // servidor no puede generar llaves RSA (muy común en WampServer/Windows).
        if (function_exists('openssl_random_pseudo_bytes')) {
            $secreto = openssl_random_pseudo_bytes(32);
        } elseif (function_exists('random_bytes')) {
            $secreto = random_bytes(32);
        } else {
            $secreto = uniqid('', true) . microtime();
        }
        file_put_contents(self::rutaSecreto(), base64_encode($secreto));
        file_put_contents(self::rutaModo(), 'HMAC');
        return true;
    }

    private static function modoActivo()
    {
        self::generarLlaves();
        if (file_exists(self::rutaModo())) {
            $m = trim((string)file_get_contents(self::rutaModo()));
            if ($m === 'RSA' || $m === 'HMAC') {
                return $m;
            }
        }
        return 'HMAC';
    }

    /** Devuelve una descripción legible del mecanismo activo (para el instalador) */
    public static function modoInfo()
    {
        return self::modoActivo() === 'RSA'
            ? 'firma digital RSA-2048 (OpenSSL)'
            : 'huella de integridad OpenSSL (modo de respaldo, sin necesidad de openssl.cnf)';
    }

    /** Arma la cadena canónica a partir de los datos sensibles */
    private static function cadenaCanonica(array $datos)
    {
        ksort($datos);
        $partes = [];
        foreach ($datos as $llave => $valor) {
            $partes[] = $llave . '=' . $valor;
        }
        return implode('|', $partes);
    }

    /** Firma los datos sensibles. Devuelve una cadena "RSA:..." o "HMAC:..." */
    public static function firmarDatos(array $datos)
    {
        $cadena = self::cadenaCanonica($datos);
        $modo = self::modoActivo();

        if ($modo === 'RSA') {
            $llavePrivada = @openssl_pkey_get_private((string)@file_get_contents(self::rutaLlavePrivada()));
            if ($llavePrivada !== false) {
                $firmaBinaria = '';
                if (openssl_sign($cadena, $firmaBinaria, $llavePrivada, OPENSSL_ALGO_SHA256)) {
                    return 'RSA:' . base64_encode($firmaBinaria);
                }
            }
        }

        $secreto = base64_decode((string)@file_get_contents(self::rutaSecreto()));
        $huella = openssl_digest($secreto . '|' . $cadena, 'sha256');
        return 'HMAC:' . $huella;
    }

    /** Verifica que los datos actuales coincidan con la firma guardada */
    public static function verificarDatos(array $datos, $firmaGuardada)
    {
        if (empty($firmaGuardada)) {
            return false;
        }
        self::generarLlaves();
        $cadena = self::cadenaCanonica($datos);

        if (strpos($firmaGuardada, 'RSA:') === 0) {
            if (!file_exists(self::rutaLlavePublica())) {
                return false;
            }
            $firmaBinaria = base64_decode(substr($firmaGuardada, 4), true);
            if ($firmaBinaria === false) {
                return false;
            }
            $llavePublica = @openssl_pkey_get_public((string)file_get_contents(self::rutaLlavePublica()));
            if ($llavePublica === false) {
                return false;
            }
            return openssl_verify($cadena, $firmaBinaria, $llavePublica, OPENSSL_ALGO_SHA256) === 1;
        }

        if (strpos($firmaGuardada, 'HMAC:') === 0) {
            $huellaGuardada = substr($firmaGuardada, 5);
            $secreto = base64_decode((string)@file_get_contents(self::rutaSecreto()));
            $huellaCalculada = openssl_digest($secreto . '|' . $cadena, 'sha256');
            if ($huellaCalculada === false) {
                return false;
            }
            return hash_equals($huellaCalculada, $huellaGuardada);
        }

        return false;
    }
}