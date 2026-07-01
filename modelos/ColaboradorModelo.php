<?php
/**
 * ColaboradorModelo.php
 * Capa de acceso a datos (Modelo) para colaboradores y sus perfiles laborales.
 */
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../clases/Firma.php';

class ColaboradorModelo
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->getConexion();
    }

    public function identidadExiste($identidad)
    {
        $stmt = $this->db->prepare('SELECT id_colaborador FROM colaboradores WHERE identidad = :identidad');
        $stmt->execute([':identidad' => $identidad]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function correoExiste($correo)
    {
        $stmt = $this->db->prepare('SELECT id_colaborador FROM colaboradores WHERE correo = :correo');
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Crea el registro base del colaborador. Devuelve el id_colaborador generado. */
    public function crearColaborador(array $datos)
    {
        $sql = "INSERT INTO colaboradores
                    (identidad, nombre, apellido, edad, id_tiposangre, id_sexo, nacionalidad, id_ruta, correo, celular)
                VALUES
                    (:identidad, :nombre, :apellido, :edad, :id_tiposangre, :id_sexo, :nacionalidad, :id_ruta, :correo, :celular)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':identidad'     => $datos['identidad'],
            ':nombre'        => $datos['nombre'],
            ':apellido'      => $datos['apellido'],
            ':edad'          => $datos['edad'],
            ':id_tiposangre' => $datos['id_tiposangre'],
            ':id_sexo'       => $datos['id_sexo'],
            ':nacionalidad'  => $datos['nacionalidad'],
            ':id_ruta'       => $datos['id_ruta'],
            ':correo'        => $datos['correo'],
            ':celular'       => $datos['celular'],
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Crea un nuevo perfil laboral (cargo) para el colaborador.
     * Lógica de promoción: si ya existe un cargo activo, se desactiva
     * (es_activo = 0, fecha_fin = hoy) antes de crear el nuevo cargo activo.
     * Los datos sensibles se firman digitalmente con OpenSSL.
     */
    public function crearPerfilLaboral($idColaborador, array $datos)
    {
        $stmtActivo = $this->db->prepare(
            'SELECT id_perfil FROM perfiles_laborales WHERE id_colaborador = :id AND es_activo = 1'
        );
        $stmtActivo->execute([':id' => $idColaborador]);
        $activoPrevio = $stmtActivo->fetch(PDO::FETCH_ASSOC);

        if ($activoPrevio) {
            $stmtDesactivar = $this->db->prepare(
                'UPDATE perfiles_laborales SET es_activo = 0, fecha_fin = CURDATE() WHERE id_perfil = :id'
            );
            $stmtDesactivar->execute([':id' => $activoPrevio['id_perfil']]);
        }

        $firma = Firma::firmarDatos([
            'id_colaborador'  => (int)$idColaborador,
            'id_ocupacion'    => (int)$datos['id_ocupacion'],
            'id_tipoempleado' => (int)$datos['id_tipoempleado'],
            'salario'         => number_format((float)$datos['salario'], 2, '.', ''),
            'fecha_inicio'    => $datos['fecha_inicio'],
        ]);

        $sql = "INSERT INTO perfiles_laborales
                    (id_colaborador, id_ocupacion, id_tipoempleado, salario, fecha_inicio, es_activo, firma)
                VALUES
                    (:id_colaborador, :id_ocupacion, :id_tipoempleado, :salario, :fecha_inicio, 1, :firma)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_colaborador'  => $idColaborador,
            ':id_ocupacion'    => $datos['id_ocupacion'],
            ':id_tipoempleado' => $datos['id_tipoempleado'],
            ':salario'         => $datos['salario'],
            ':fecha_inicio'    => $datos['fecha_inicio'],
            ':firma'           => $firma,
        ]);
        return $this->db->lastInsertId();
    }

    /** Da de baja a un colaborador (Empleado_Activo = 0) */
    public function registrarBaja($idColaborador, $idMotivo, $fechaFin)
    {
        $stmt = $this->db->prepare(
            'UPDATE colaboradores SET empleado_activo = 0, fecha_fin = :fecha_fin, id_motivo = :id_motivo WHERE id_colaborador = :id'
        );
        return $stmt->execute([
            ':fecha_fin' => $fechaFin,
            ':id_motivo' => $idMotivo,
            ':id'        => $idColaborador,
        ]);
    }

    public function obtenerColaborador($idColaborador)
    {
        $stmt = $this->db->prepare('SELECT * FROM colaboradores WHERE id_colaborador = :id');
        $stmt->execute([':id' => $idColaborador]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Catálogos para llenar los <select> del formulario */
    public function catalogo($tabla, $colId, $colNombre)
    {
        $sql = "SELECT $colId AS id, $colNombre AS nombre FROM $tabla ORDER BY $colNombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoTiposSangre()
    {
        return $this->db->query('SELECT id, Nombre AS nombre FROM tiposangre ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoSexo()
    {
        return $this->db->query('SELECT id, nombre FROM ` cat_sexo` ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoRutas()
    {
        return $this->db->query('SELECT id, Nombre AS nombre FROM cat_rutas ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoOcupaciones()
    {
        return $this->db->query('SELECT C_OCUP AS id, OCUPACION AS nombre FROM cat_ocupaciones WHERE Activo = 1 ORDER BY OCUPACION ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoTipoEmpleado()
    {
        return $this->db->query('SELECT id, Nombre AS nombre FROM cat_tipoempleado WHERE Activo = 1 ORDER BY Nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoMotivos()
    {
        return $this->db->query('SELECT C_TERMINACION AS id, MOTIVO AS nombre FROM cat_motivos_terminacion ORDER BY MOTIVO ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function colaboradoresActivosSimple()
    {
        return $this->db->query(
            "SELECT id_colaborador, CONCAT(nombre,' ',apellido,' (Cod. ',id_colaborador,')') AS etiqueta
             FROM colaboradores WHERE empleado_activo = 1 ORDER BY nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function estadisticas()
    {
        $total = $this->db->query('SELECT COUNT(*) AS n FROM colaboradores')->fetch(PDO::FETCH_ASSOC);
        $activos = $this->db->query('SELECT COUNT(*) AS n FROM colaboradores WHERE empleado_activo = 1')->fetch(PDO::FETCH_ASSOC);
        $inactivos = $this->db->query('SELECT COUNT(*) AS n FROM colaboradores WHERE empleado_activo = 0')->fetch(PDO::FETCH_ASSOC);
        return [
            'total'     => (int)$total['n'],
            'activos'   => (int)$activos['n'],
            'inactivos' => (int)$inactivos['n'],
        ];
    }

    /**
     * Reporte principal: colaborador + cargo activo + historial de cargos
     * (el historial se muestra separado por comas, tal como lo pide la guía).
     */
    public function listarReporte()
    {
        $sql = "SELECT
                    c.id_colaborador, c.identidad, c.nombre, c.apellido, c.edad,
                    ts.Nombre AS tipo_sangre, sx.nombre AS sexo, c.nacionalidad, r.Nombre AS ruta,
                    c.correo, c.celular, c.empleado_activo, c.fecha_fin, mt.MOTIVO AS motivo,
                    pa.id_perfil, pa.salario, pa.fecha_inicio, pa.firma,
                    pa.id_tipoempleado, pa.id_ocupacion,
                    oa.OCUPACION AS puesto_actual, tea.Nombre AS planilla_actual,
                    (SELECT GROUP_CONCAT(
                                CONCAT(o2.OCUPACION, ' (', p2.fecha_inicio, ' a ', IFNULL(p2.fecha_fin, 'Presente'), ')')
                                ORDER BY p2.fecha_inicio SEPARATOR ', ')
                     FROM perfiles_laborales p2
                     LEFT JOIN cat_ocupaciones o2 ON o2.C_OCUP = p2.id_ocupacion
                     WHERE p2.id_colaborador = c.id_colaborador) AS historial_cargos
                FROM colaboradores c
                LEFT JOIN tiposangre ts ON ts.id = c.id_tiposangre
                LEFT JOIN ` cat_sexo` sx ON sx.id = c.id_sexo
                LEFT JOIN cat_rutas r ON r.id = c.id_ruta
                LEFT JOIN cat_motivos_terminacion mt ON mt.C_TERMINACION = c.id_motivo
                LEFT JOIN perfiles_laborales pa ON pa.id_colaborador = c.id_colaborador AND pa.es_activo = 1
                LEFT JOIN cat_ocupaciones oa ON oa.C_OCUP = pa.id_ocupacion
                LEFT JOIN cat_tipoempleado tea ON tea.id = pa.id_tipoempleado
                ORDER BY c.id_colaborador DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Verifica la integridad (firma) de un renglón del reporte */
    public function integridadValida(array $fila)
    {
        if (empty($fila['firma']) || empty($fila['id_perfil'])) {
            return null; // sin cargo activo, no hay nada que verificar
        }
        return Firma::verificarDatos([
            'id_colaborador'  => (int)$fila['id_colaborador'],
            'id_ocupacion'    => (int)$fila['id_ocupacion'],
            'id_tipoempleado' => (int)$fila['id_tipoempleado'],
            'salario'         => number_format((float)$fila['salario'], 2, '.', ''),
            'fecha_inicio'    => $fila['fecha_inicio'],
        ], $fila['firma']);
    }
}