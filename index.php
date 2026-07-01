<?php
/**
 * index.php
 * Controlador frontal (router) del sistema. Agrupa las vistas
 * disponibles y las carga según el parámetro ?p=
 */
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

define('APP_RUTA', 'index.php');

$paginasValidas = ['inicio', 'formulario', 'reporte', 'promocion', 'baja'];
$pagina = $_GET['p'] ?? 'inicio';
if (!in_array($pagina, $paginasValidas, true)) {
    $pagina = 'inicio';
}

require __DIR__ . '/vistas/header.php';
require __DIR__ . '/vistas/' . $pagina . '.php';
require __DIR__ . '/vistas/footer.php';