<?php
/** reporte.php - Reporte general de colaboradores */
require_once __DIR__ . '/../modelos/ColaboradorModelo.php';
$modelo = new ColaboradorModelo();
$filas = $modelo->listarReporte();

$exito = $_SESSION['exito'] ?? null;
unset($_SESSION['exito']);
?>
<div class="bn-panel">
    <h2 style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <span>Reporte <span style="color:#F0B90B">General</span> de Colaboradores</span>
        <a href="exportar_excel.php" class="bn-btn pequeno">⬇ Exportar a Excel</a>
    </h2>

    <?php if ($exito): ?>
        <div class="bn-alert exito"><?php echo htmlspecialchars($exito); ?></div>
    <?php endif; ?>

    <?php if (empty($filas)): ?>
        <p style="color:#848E9C;">Todavía no hay colaboradores registrados. <a href="index.php?p=formulario">Registra el primero</a>.</p>
    <?php else: ?>
    <div class="bn-table-wrap">
        <table class="bn-table">
            <thead>
                <tr>
                    <th>Cód.</th>
                    <th>Colaborador</th>
                    <th>Identidad</th>
                    <th>Sangre</th>
                    <th>Sexo</th>
                    <th>Ruta</th>
                    <th>Contacto</th>
                    <th>Puesto Actual</th>
                    <th>Planilla</th>
                    <th>Salario</th>
                    <th>Estado</th>
                    <th>Integridad</th>
                    <th>Historial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filas as $f): ?>
                    <?php $integridad = $modelo->integridadValida($f); ?>
                    <tr>
                        <td>#<?php echo (int)$f['id_colaborador']; ?></td>
                        <td><?php echo htmlspecialchars($f['nombre'] . ' ' . $f['apellido']); ?><br>
                            <small style="color:#848E9C;"><?php echo (int)$f['edad']; ?> años · <?php echo htmlspecialchars($f['nacionalidad']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($f['identidad']); ?></td>
                        <td><?php echo htmlspecialchars(trim($f['tipo_sangre'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($f['sexo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($f['ruta'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($f['correo']); ?><br>
                            <small style="color:#848E9C;"><?php echo htmlspecialchars($f['celular']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($f['puesto_actual'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($f['planilla_actual'] ?? '—'); ?></td>
                        <td><?php echo isset($f['salario']) ? '$' . number_format((float)$f['salario'], 2) : '—'; ?></td>
                        <td>
                            <?php if ((int)$f['empleado_activo'] === 1): ?>
                                <span class="bn-badge verde">Activo</span>
                            <?php else: ?>
                                <span class="bn-badge roja">Inactivo</span>
                                <?php if (!empty($f['motivo'])): ?><br><small style="color:#848E9C;"><?php echo htmlspecialchars($f['motivo']); ?></small><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($integridad === null): ?>
                                <span class="bn-badge gris">N/A</span>
                            <?php elseif ($integridad === true): ?>
                                <span class="bn-badge verde">✔ Íntegro</span>
                            <?php else: ?>
                                <span class="bn-badge roja">✖ Alterado</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:220px;white-space:normal;color:#848E9C;font-size:12px;">
                            <?php echo htmlspecialchars($f['historial_cargos'] ?? '—'); ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ((int)$f['empleado_activo'] === 1): ?>
                                <a class="bn-btn pequeno secundario" href="index.php?p=promocion&amp;id=<?php echo (int)$f['id_colaborador']; ?>">Promover</a>
                                <a class="bn-btn pequeno peligro" href="index.php?p=baja&amp;id=<?php echo (int)$f['id_colaborador']; ?>">Baja</a>
                            <?php else: ?>
                                <span class="bn-badge gris">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>