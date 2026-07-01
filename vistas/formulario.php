<?php
/** formulario.php - Registro de colaborador + perfil laboral inicial */
require_once __DIR__ . '/../modelos/ColaboradorModelo.php';
$modelo = new ColaboradorModelo();

$tiposSangre  = $modelo->catalogoTiposSangre();
$sexos        = $modelo->catalogoSexo();
$rutas        = $modelo->catalogoRutas();
$ocupaciones  = $modelo->catalogoOcupaciones();
$tiposEmpleado = $modelo->catalogoTipoEmpleado();

$errores = $_SESSION['errores'] ?? [];
$viejo   = $_SESSION['viejo'] ?? [];
$exito   = $_SESSION['exito'] ?? null;
unset($_SESSION['errores'], $_SESSION['viejo'], $_SESSION['exito']);

function val($campo, $viejo) {
    return htmlspecialchars($viejo[$campo] ?? '', ENT_QUOTES, 'UTF-8');
}
function sel($campo, $valor, $viejo) {
    return (isset($viejo[$campo]) && (string)$viejo[$campo] === (string)$valor) ? 'selected' : '';
}
?>
<div class="bn-panel">
    <h2>Registrar <span>Colaborador</span></h2>

    <?php if ($exito): ?>
        <div class="bn-alert exito"><?php echo htmlspecialchars($exito); ?></div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="bn-alert error">
            <strong>Se encontraron los siguientes errores:</strong>
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="guardar.php" method="POST" autocomplete="off">
        <h3 style="color:#848E9C;font-size:14px;text-transform:uppercase;letter-spacing:.5px;">Datos del Colaborador</h3>
        <div class="bn-form-grid">
            <div class="bn-field">
                <label>Identidad / Cédula</label>
                <input type="text" name="identidad" value="<?php echo val('identidad', $viejo); ?>" placeholder="8-123-4567" required>
            </div>
            <div class="bn-field">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo val('nombre', $viejo); ?>" required>
            </div>
            <div class="bn-field">
                <label>Apellido</label>
                <input type="text" name="apellido" value="<?php echo val('apellido', $viejo); ?>" required>
            </div>
            <div class="bn-field">
                <label>Edad</label>
                <input type="number" name="edad" min="18" max="75" value="<?php echo val('edad', $viejo); ?>" required>
            </div>
            <div class="bn-field">
                <label>Tipo de Sangre</label>
                <select name="id_tiposangre" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($tiposSangre as $ts): ?>
                        <option value="<?php echo $ts['id']; ?>" <?php echo sel('id_tiposangre', $ts['id'], $viejo); ?>>
                            <?php echo htmlspecialchars(trim($ts['nombre'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="bn-field">
                <label>Sexo</label>
                <select name="id_sexo" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($sexos as $sx): ?>
                        <option value="<?php echo $sx['id']; ?>" <?php echo sel('id_sexo', $sx['id'], $viejo); ?>>
                            <?php echo htmlspecialchars($sx['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="bn-field">
                <label>Nacionalidad</label>
                <input type="text" name="nacionalidad" value="<?php echo htmlspecialchars($viejo['nacionalidad'] ?? 'Panameña', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="bn-field">
                <label>Ruta</label>
                <select name="id_ruta" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($rutas as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo sel('id_ruta', $r['id'], $viejo); ?>>
                            <?php echo htmlspecialchars($r['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 style="color:#848E9C;font-size:14px;text-transform:uppercase;letter-spacing:.5px;margin-top:26px;">Información de Contacto</h3>
        <div class="bn-form-grid">
            <div class="bn-field">
                <label>Correo Electrónico</label>
                <input type="email" name="correo" value="<?php echo val('correo', $viejo); ?>" required>
            </div>
            <div class="bn-field">
                <label>Celular</label>
                <input type="tel" name="celular" value="<?php echo val('celular', $viejo); ?>" placeholder="61234567" pattern="6[0-9]{7}" required>
                <small class="ayuda">8 dígitos, debe iniciar con 6.</small>
            </div>
        </div>

        <h3 style="color:#848E9C;font-size:14px;text-transform:uppercase;letter-spacing:.5px;margin-top:26px;">Perfil Laboral Inicial</h3>
        <div class="bn-form-grid">
            <div class="bn-field">
                <label>Puesto / Ocupación</label>
                <select name="id_ocupacion" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($ocupaciones as $o): ?>
                        <option value="<?php echo $o['id']; ?>" <?php echo sel('id_ocupacion', $o['id'], $viejo); ?>>
                            <?php echo htmlspecialchars($o['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="bn-field">
                <label>Tipo de Planilla</label>
                <select name="id_tipoempleado" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($tiposEmpleado as $te): ?>
                        <option value="<?php echo $te['id']; ?>" <?php echo sel('id_tipoempleado', $te['id'], $viejo); ?>>
                            <?php echo htmlspecialchars($te['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="bn-field">
                <label>Salario</label>
                <input type="number" step="0.01" min="0" name="salario" value="<?php echo val('salario', $viejo); ?>" required>
            </div>
            <div class="bn-field">
                <label>Fecha de Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($viejo['fecha_inicio'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>

        <div class="bn-acciones">
            <button type="submit" class="bn-btn">Guardar Colaborador</button>
            <a href="index.php?p=inicio" class="bn-btn secundario">Cancelar</a>
        </div>
    </form>
</div>