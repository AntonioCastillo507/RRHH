<?php
/** header.php - Encabezado común de todas las páginas */
if (!defined('APP_RUTA')) { define('APP_RUTA', 'index.php'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iTECH Contrataciones | Sistema de RRHH</title>
<link rel="stylesheet" href="css/binance.css">
</head>
<body>
<header class="bn-navbar">
    <div class="bn-logo">iTECH <span>Contrataciones</span></div>
    <nav>
        <a href="<?php echo APP_RUTA; ?>?p=inicio" class="<?php echo ($pagina ?? '') === 'inicio' ? 'activo' : ''; ?>">Inicio</a>
        <a href="<?php echo APP_RUTA; ?>?p=formulario" class="<?php echo ($pagina ?? '') === 'formulario' ? 'activo' : ''; ?>">Registrar Colaborador</a>
        <a href="<?php echo APP_RUTA; ?>?p=reporte" class="<?php echo ($pagina ?? '') === 'reporte' ? 'activo' : ''; ?>">Reporte</a>
    </nav>
</header>
<main class="bn-container">