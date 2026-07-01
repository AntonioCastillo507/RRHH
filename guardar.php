<?php
/**
 * guardar.php (Controlador)
 * Procesa el registro de un nuevo colaborador junto con su
 * primer perfil laboral. Sanitiza, valida y persiste los datos.
 */
session_start();
require_once __DIR__ . '/clases/Validacion.php';
require_once __DIR__ . '/clases/Sanitizacion.php';
require_once __DIR__ . '/modelos/ColaboradorModelo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?p=formulario');
    exit;
}

$datosColaborador = Sanitizacion::sanitizarColaborador($_POST);
$datosPerfil = Sanitizacion::sanitizarPerfilLaboral($_POST);

$errores = array_merge(
    Validacion::validarColaborador($datosColaborador),
    Validacion::validarPerfilLaboral($datosPerfil)
);

$modelo = new ColaboradorModelo();

if (empty($errores)) {
    if ($modelo->identidadExiste($datosColaborador['identidad'])) {
        $errores[] = 'Ya existe un colaborador registrado con esa identidad.';
    }
    if ($modelo->correoExiste($datosColaborador['correo'])) {
        $errores[] = 'Ya existe un colaborador registrado con ese correo.';
    }
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['viejo'] = $_POST;
    header('Location: index.php?p=formulario');
    exit;
}

try {
    $idColaborador = $modelo->crearColaborador($datosColaborador);
    $modelo->crearPerfilLaboral($idColaborador, $datosPerfil);

    $_SESSION['exito'] = 'Colaborador registrado correctamente con el código #' . $idColaborador . '.';
    header('Location: index.php?p=reporte');
    exit;
} catch (Exception $e) {
    $_SESSION['errores'] = ['Ocurrió un error al guardar: ' . $e->getMessage()];
    $_SESSION['viejo'] = $_POST;
    header('Location: index.php?p=formulario');
    exit;
}