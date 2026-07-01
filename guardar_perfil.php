<?php
/**
 * guardar_perfil.php (Controlador)
 * Procesa una promoción: crea un nuevo perfil laboral para un
 * colaborador existente y desactiva el cargo anterior.
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
$datosPerfil = Sanitizacion::sanitizarPerfilLaboral($_POST);
$errores = Validacion::validarPerfilLaboral($datosPerfil);

$modelo = new ColaboradorModelo();
$colaborador = $idColaborador > 0 ? $modelo->obtenerColaborador($idColaborador) : null;

if (!$colaborador) {
    $errores[] = 'El colaborador indicado no existe.';
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header('Location: index.php?p=promocion&id=' . $idColaborador);
    exit;
}

try {
    $modelo->crearPerfilLaboral($idColaborador, $datosPerfil);
    $_SESSION['exito'] = 'Se registró la promoción correctamente. El cargo anterior fue desactivado.';
    header('Location: index.php?p=reporte');
    exit;
} catch (Exception $e) {
    $_SESSION['errores'] = ['Ocurrió un error al guardar la promoción: ' . $e->getMessage()];
    header('Location: index.php?p=promocion&id=' . $idColaborador);
    exit;
}