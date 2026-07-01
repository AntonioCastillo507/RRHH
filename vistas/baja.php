<?php
/** baja.php - Dar de baja a un colaborador */
require_once __DIR__ . '/../modelos/ColaboradorModelo.php';
$modelo = new ColaboradorModelo();

$idColaborador = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$colaborador = $modelo->obtenerColaborador($idColaborador);
$motivos = $modelo->catalogoMotivos();

$errores = $_SESSION['errores'] ?? [];
unset($_SESSION['errores']);
?>
<div class="bn-panel">
    <h2>Dar de <span>Baja</span> a Colaborador</h2>

    <?php if (!$colaborador): ?>
        <div class="bn-alert error">No se encontró el colaborador indicado.</div>
        <a href="index.php?p=reporte" class="bn-btn secundario">Volver al reporte</a>
    <?php else: ?>
        <p style="color:#848E9C;">
            Colaborador: <strong style="color:#EAECEF;"><?php echo htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']); ?></strong>
            (Código #<?php echo (int)$colaborador['id_colaborador']; ?>)
        </p>

        <?php if (!empty($errores)): ?>
            <div class="bn-alert error">
                <ul><?php foreach ($errores as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="dar_baja.php" method="POST">
            <input type="hidden" name="id_colaborador" value="<?php echo (int)$colaborador['id_colaborador']; ?>">
            <div class="bn-form-grid">
                <div class="bn-field">
                    <label>Motivo de la Baja</label>
                    <select name="id_motivo" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($motivos as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="bn-field">
                    <label>Fecha de Baja</label>
                    <input type="date" name="fecha_fin" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="bn-acciones">
                <button type="submit" class="bn-btn peligro">Confirmar Baja</button>
                <a href="index.php?p=reporte" class="bn-btn secundario">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</div>