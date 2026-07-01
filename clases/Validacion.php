<?php
/**
 * Validacion.php
 * Clase con métodos estáticos para validar los datos del lado del servidor.
 */
class Validacion
{
    public static function esVacio($valor)
    {
        return trim((string)$valor) === '';
    }

    public static function esCorreoValido($correo)
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function esCelularValido($celular)
    {
        return preg_match('/^6[0-9]{7}$/', (string)$celular) === 1;
    }

    public static function esIdentidadValida($identidad)
    {
        return preg_match('/^[0-9A-Za-z\-]{5,20}$/', (string)$identidad) === 1;
    }

    public static function esEdadValida($edad)
    {
        return is_numeric($edad) && $edad >= 18 && $edad <= 75;
    }

    public static function esEnteroPositivo($valor)
    {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false && (int)$valor > 0;
    }

    public static function esSalarioValido($salario)
    {
        return is_numeric($salario) && (float)$salario > 0 && (float)$salario < 100000;
    }

    public static function esFechaValida($fecha)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string)$fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    /**
     * Valida los datos del colaborador (Paso 1 del formulario).
     * Devuelve un arreglo de mensajes de error (vacío si todo está bien).
     */
    public static function validarColaborador(array $datos)
    {
        $errores = [];

        if (self::esVacio($datos['identidad'] ?? '')) {
            $errores[] = 'La identidad es obligatoria.';
        } elseif (!self::esIdentidadValida($datos['identidad'])) {
            $errores[] = 'La identidad no tiene un formato válido (5 a 20 caracteres alfanuméricos).';
        }

        if (self::esVacio($datos['nombre'] ?? '')) {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (self::esVacio($datos['apellido'] ?? '')) {
            $errores[] = 'El apellido es obligatorio.';
        }
        if (!self::esEdadValida($datos['edad'] ?? '')) {
            $errores[] = 'La edad debe estar entre 18 y 75 años.';
        }
        if (!self::esEnteroPositivo($datos['id_tiposangre'] ?? '')) {
            $errores[] = 'Seleccione un tipo de sangre.';
        }
        if (!self::esEnteroPositivo($datos['id_sexo'] ?? '')) {
            $errores[] = 'Seleccione el sexo.';
        }
        if (self::esVacio($datos['nacionalidad'] ?? '')) {
            $errores[] = 'La nacionalidad es obligatoria.';
        }
        if (!self::esEnteroPositivo($datos['id_ruta'] ?? '')) {
            $errores[] = 'Seleccione una ruta.';
        }
        if (self::esVacio($datos['correo'] ?? '') || !self::esCorreoValido($datos['correo'])) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if (self::esVacio($datos['celular'] ?? '') || !self::esCelularValido($datos['celular'])) {
            $errores[] = 'El celular debe tener 8 dígitos y comenzar con 6 (ej. 61234567).';
        }

        return $errores;
    }

    /**
     * Valida los datos del perfil laboral (Paso 2 del formulario / promoción).
     */
    public static function validarPerfilLaboral(array $datos)
    {
        $errores = [];

        if (!self::esEnteroPositivo($datos['id_ocupacion'] ?? '')) {
            $errores[] = 'Seleccione un puesto/ocupación.';
        }
        if (!self::esEnteroPositivo($datos['id_tipoempleado'] ?? '')) {
            $errores[] = 'Seleccione el tipo de planilla.';
        }
        if (!self::esSalarioValido($datos['salario'] ?? '')) {
            $errores[] = 'El salario debe ser un número positivo válido.';
        }
        if (!self::esFechaValida($datos['fecha_inicio'] ?? '')) {
            $errores[] = 'La fecha de inicio no es válida (formato AAAA-MM-DD).';
        }

        return $errores;
    }

    public static function validarBaja(array $datos)
    {
        $errores = [];
        if (!self::esEnteroPositivo($datos['id_motivo'] ?? '')) {
            $errores[] = 'Seleccione el motivo de la baja.';
        }
        if (!self::esFechaValida($datos['fecha_fin'] ?? '')) {
            $errores[] = 'La fecha de baja no es válida (formato AAAA-MM-DD).';
        }
        return $errores;
    }
}