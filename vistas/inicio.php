<?php
/** inicio.php - Página de inicio */
require_once __DIR__ . '/../modelos/ColaboradorModelo.php';
$modelo = new ColaboradorModelo();
$stats = $modelo->estadisticas();
?>
<section class="bn-hero">
    <h1>Sistema de Gestión de <span>Recursos Humanos</span></h1>
    <p>Registra colaboradores, administra sus perfiles laborales, promociones y bajas,
       y consulta el reporte con verificación de integridad firmada digitalmente (OpenSSL).</p>
</section>

<div class="bn-stats">
    <div class="bn-stat">
        <div class="num"><?php echo $stats['total']; ?></div>
        <div class="lbl">Colaboradores Registrados</div>
    </div>
    <div class="bn-stat">
        <div class="num"><?php echo $stats['activos']; ?></div>
        <div class="lbl">Activos</div>
    </div>
    <div class="bn-stat">
        <div class="num"><?php echo $stats['inactivos']; ?></div>
        <div class="lbl">Inactivos / De Baja</div>
    </div>
</div>

<div class="bn-grid">
    <div class="bn-card">
        <span class="bn-icon">📝</span>
        <h3>Registrar Colaborador</h3>
        <p>Captura los datos personales, de contacto y el primer perfil laboral del colaborador.</p>
        <a class="bn-btn" href="index.php?p=formulario">Ir al formulario</a>
    </div>
    <div class="bn-card">
        <span class="bn-icon">📊</span>
        <h3>Reporte General</h3>
        <p>Consulta todos los colaboradores, su cargo actual, historial y estado de integridad de los datos.</p>
        <a class="bn-btn secundario" href="index.php?p=reporte">Ver reporte</a>
    </div>
    <div class="bn-card">
        <span class="bn-icon">📈</span>
        <h3>Promociones</h3>
        <p>Desde el reporte puedes asignar un nuevo cargo a un colaborador activo (promoción automática).</p>
        <a class="bn-btn secundario" href="index.php?p=reporte">Gestionar</a>
    </div>
</div>