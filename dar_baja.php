<?php
/**
 * dar_baja.php (Controlador)
 * Procesa la baja de un colaborador (Empleado_Activo = 0).
 */
session_start();
require_once __DIR__ . '/clases/Validacion.php';
require_once __DIR__ . '/clases/Sanitizacion.php';
require_once __DIR__ . '/modelos/ColaboradorModelo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?p=reporte');
    exit;
}

$idColaborador = isset($_POST['id_colaborador']) ? (int)$_POST['id_colaborador'] : 0;
$datos = [
    'id_motivo' => Sanitizacion::aEntero($_POST['id_motivo'] ?? ''),
    'fecha_fin' => Sanitizacion::limpiarTexto($_POST['fecha_fin'] ?? ''),
];
$errores = Validacion::validarBaja($datos);

$modelo = new ColaboradorModelo();
$colaborador = $idColaborador > 0 ? $modelo->obtenerColaborador($idColaborador) : null;

if (!$colaborador) {
    $errores[] = 'El colaborador indicado no existe.';
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header('Location: index.php?p=baja&id=' . $idColaborador);
    exit;
}

try {
    $modelo->registrarBaja($idColaborador, $datos['id_motivo'], $datos['fecha_fin']);
    $_SESSION['exito'] = 'El colaborador fue dado de baja correctamente.';
    header('Location: index.php?p=reporte');
    exit;
} catch (Exception $e) {
    $_SESSION['errores'] = ['Ocurrió un error al procesar la baja: ' . $e->getMessage()];
    header('Location: index.php?p=baja&id=' . $idColaborador);
    exit;
}