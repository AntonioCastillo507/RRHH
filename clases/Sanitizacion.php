<?php
/**
 * Sanitizacion.php
 * Clase con métodos estáticos para limpiar/normalizar los datos
 * antes de guardarlos en la base de datos.
 */
class Sanitizacion
{
    public static function limpiarTexto($valor)
    {
        return trim(strip_tags((string)$valor));
    }

    /** Convierte a Formato Título: "juan perez" -> "Juan Perez" */
    public static function aTitulo($valor)
    {
        $limpio = self::limpiarTexto($valor);
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($limpio, MB_CASE_TITLE, 'UTF-8');
        }
        return ucwords(strtolower($limpio));
    }

    public static function aEntero($valor)
    {
        $limpio = preg_replace('/[^0-9\-]/', '', (string)$valor);
        return $limpio === '' || $limpio === '-' ? 0 : (int)$limpio;
    }

    public static function aDecimal($valor)
    {
        $limpio = preg_replace('/[^0-9.\-]/', '', (string)$valor);
        return $limpio === '' || $limpio === '-' ? 0.0 : (float)$limpio;
    }

    public static function aCorreo($valor)
    {
        $valor = trim((string)$valor);
        return preg_replace('/[^A-Za-z0-9@._\-\+]/', '', $valor);
    }

    public static function soloNumeros($valor)
    {
        return preg_replace('/[^0-9]/', '', (string)$valor);
    }

    /** Sanitiza el bloque de datos del colaborador */
    public static function sanitizarColaborador(array $datos)
    {
        return [
            'identidad'     => self::limpiarTexto($datos['identidad'] ?? ''),
            'nombre'        => self::aTitulo($datos['nombre'] ?? ''),
            'apellido'      => self::aTitulo($datos['apellido'] ?? ''),
            'edad'          => self::aEntero($datos['edad'] ?? 0),
            'id_tiposangre' => self::aEntero($datos['id_tiposangre'] ?? 0),
            'id_sexo'       => self::aEntero($datos['id_sexo'] ?? 0),
            'nacionalidad'  => self::aTitulo($datos['nacionalidad'] ?? ''),
            'id_ruta'       => self::aEntero($datos['id_ruta'] ?? 0),
            'correo'        => self::aCorreo($datos['correo'] ?? ''),
            'celular'       => self::soloNumeros($datos['celular'] ?? ''),
        ];
    }

    /** Sanitiza el bloque de datos del perfil laboral */
    public static function sanitizarPerfilLaboral(array $datos)
    {
        return [
            'id_ocupacion'    => self::aEntero($datos['id_ocupacion'] ?? 0),
            'id_tipoempleado' => self::aEntero($datos['id_tipoempleado'] ?? 0),
            'salario'         => self::aDecimal($datos['salario'] ?? 0),
            'fecha_inicio'    => self::limpiarTexto($datos['fecha_inicio'] ?? ''),
        ];
    }
}