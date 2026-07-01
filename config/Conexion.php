<?php
/**
 * Conexion.php
 * Clase de conexión y acceso a datos (PDO), basada en el ejemplo
 * proporcionado (myConexionEjemplo.php) y adaptada al proyecto.
 */
class Conexion
{
    private static $host = 'localhost';
    private static $dbname = 'parcialdb33';
    private static $user = 'root';
    private static $pass = '';

    private $conexion;

    public function __construct()
    {
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
        try {
            $this->conexion = new PDO($dsn, self::$user, self::$pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
        }
    }

    public function getConexion()
    {
        return $this->conexion;
    }

    public function disconnect()
    {
        $this->conexion = null;
    }

    /** INSERT seguro usando bindings con nombre */
    public function insertar($tabla, array $datos)
    {
        $columnas = implode(', ', array_keys($datos));
        $marcadores = ':' . implode(', :', array_keys($datos));
        $sql = "INSERT INTO $tabla ($columnas) VALUES ($marcadores)";
        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($datos as $campo => $valor) {
                $stmt->bindValue(":$campo", $valor);
            }
            $stmt->execute();
            return $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en INSERT: ' . $e->getMessage());
            return false;
        }
    }

    /** UPDATE seguro usando bindings con nombre */
    public function actualizar($tabla, array $datos, array $condiciones)
    {
        $set = [];
        foreach (array_keys($datos) as $campo) {
            $set[] = "$campo = :$campo";
        }
        $where = [];
        foreach (array_keys($condiciones) as $campo) {
            $where[] = "$campo = :cond_$campo";
        }
        $sql = "UPDATE $tabla SET " . implode(', ', $set) . ' WHERE ' . implode(' AND ', $where);
        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($datos as $campo => $valor) {
                $stmt->bindValue(":$campo", $valor);
            }
            foreach ($condiciones as $campo => $valor) {
                $stmt->bindValue(":cond_$campo", $valor);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UPDATE: ' . $e->getMessage());
            return false;
        }
    }

    /** Devuelve varias filas como arreglo asociativo */
    public function consultar($sql, array $parametros = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en consulta: ' . $e->getMessage());
            return [];
        }
    }

    /** Devuelve una sola fila como arreglo asociativo */
    public function consultarUno($sql, array $parametros = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log('Error en consulta: ' . $e->getMessage());
            return null;
        }
    }
}