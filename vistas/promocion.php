<?php
/** promocion.php - Asignar un nuevo cargo (promoción) a un colaborador existente */
require_once __DIR__ . '/../modelos/ColaboradorModelo.php';
$modelo = new ColaboradorModelo();

$idColaborador = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$colaborador = $modelo->obtenerColaborador($idColaborador);

$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['errores']);

$ocupaciones   = $modelo->catalogoOcupaciones();
$tiposEmpleado = $modelo->catalogoTipoEmpleado();
?>
<div class="bn-panel">
    <h2>Nuevo Cargo <span>(Promoción)</span></h2>

    <?php if (!$colaborador): ?>
        <div class="bn-alert error">No se encontró el colaborador indicado.</div>
        <a href="index.php?p=reporte" class="bn-btn secundario">Volver al reporte</a>
    <?php else: ?>
        <p style="color:#848E9C;">
            Colaborador: <strong style="color:#EAECEF;"><?php echo htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']); ?></strong>
            (Código #<?php echo (int)$colaborador['id_colaborador']; ?>).
            Al guardar, el cargo activo anterior (si existe) se desactivará automáticamente y este nuevo cargo pasará a ser el activo.
        </p>

        <?php if (!empty($errores)): ?>
            <div class="bn-alert error">
                <ul><?php foreach ($errores as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="guardar_perfil.php" method="POST">
            <input type="hidden" name="id_colaborador" value="<?php echo (int)$colaborador['id_colaborador']; ?>">
            <div class="bn-form-grid">
                <div class="bn-field">
                    <label>Puesto / Ocupación</label>
                    <select name="id_ocupacion" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($ocupaciones as $o): ?>
                            <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="bn-field">
                    <label>Tipo de Planilla</label>
                    <select name="id_tipoempleado" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tiposEmpleado as $te): ?>
                            <option value="<?php echo $te['id']; ?>"><?php echo htmlspecialchars($te['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="bn-field">
                    <label>Nuevo Salario</label>
                    <input type="number" step="0.01" min="0" name="salario" required>
                </div>
                <div class="bn-field">
                    <label>Fecha de Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="bn-acciones">
                <button type="submit" class="bn-btn">Guardar Promoción</button>
                <a href="index.php?p=reporte" class="bn-btn secundario">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</div>