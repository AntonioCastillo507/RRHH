<?php
/**
 * exportar_excel.php
 * Exporta el reporte de colaboradores a un archivo .xlsx usando
 * PhpOffice/PhpSpreadsheet (composer require phpoffice/phpspreadsheet),
 * tal como se indica en el material "Integrar_Excel_al_Proyecto".
 */
require_once __DIR__ . '/modelos/ColaboradorModelo.php';

$rutaAutoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($rutaAutoload)) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>Falta PhpSpreadsheet</title>
    <link rel="stylesheet" href="css/binance.css"></head><body>
    <main class="bn-container"><div class="bn-panel">
    <h2>Falta instalar <span style="color:#F0B90B">PhpSpreadsheet</span></h2>
    <div class="bn-alert error">Para poder exportar a Excel necesitas instalar la librería una sola vez desde
    una consola (CMD) ubicada dentro de la carpeta del proyecto:</div>
    <pre style="background:#1E2329;padding:16px;border-radius:8px;color:#EAECEF;overflow-x:auto;">composer require phpoffice/phpspreadsheet</pre>
    <p style="color:#848E9C;">Si no tienes Composer instalado, descárgalo de
    <a href="https://getcomposer.org" target="_blank">getcomposer.org</a>. Luego recarga esta página.</p>
    <a class="bn-btn secundario" href="index.php?p=reporte">Volver al reporte</a>
    </div></main></body></html>';
    exit;
}

require_once $rutaAutoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$modelo = new ColaboradorModelo();
$filas = $modelo->listarReporte();

$documento = new Spreadsheet();
$documento->getProperties()
    ->setCreator('iTECH Contrataciones')
    ->setLastModifiedBy('Sistema RRHH')
    ->setTitle('Reporte de Colaboradores')
    ->setDescription('Colaboradores exportados desde MySQL - parcialdb33');

$hoja = $documento->getActiveSheet();
$hoja->setTitle('Colaboradores');

$encabezado = [
    'Código', 'Identidad', 'Nombre', 'Apellido', 'Edad', 'Tipo de Sangre', 'Sexo',
    'Nacionalidad', 'Ruta', 'Correo', 'Celular', 'Puesto Actual', 'Planilla',
    'Salario', 'Fecha Inicio', 'Estado', 'Motivo Baja', 'Historial de Cargos',
];
$hoja->fromArray($encabezado, null, 'A1');

$fila = 2;
foreach ($filas as $reg) {
    $hoja->setCellValue('A' . $fila, $reg['id_colaborador']);
    $hoja->setCellValue('B' . $fila, $reg['identidad']);
    $hoja->setCellValue('C' . $fila, $reg['nombre']);
    $hoja->setCellValue('D' . $fila, $reg['apellido']);
    $hoja->setCellValue('E' . $fila, $reg['edad']);
    $hoja->setCellValue('F' . $fila, trim((string)$reg['tipo_sangre']));
    $hoja->setCellValue('G' . $fila, $reg['sexo']);
    $hoja->setCellValue('H' . $fila, $reg['nacionalidad']);
    $hoja->setCellValue('I' . $fila, $reg['ruta']);
    $hoja->setCellValue('J' . $fila, $reg['correo']);
    $hoja->setCellValue('K' . $fila, $reg['celular']);
    $hoja->setCellValue('L' . $fila, $reg['puesto_actual']);
    $hoja->setCellValue('M' . $fila, $reg['planilla_actual']);
    $hoja->setCellValue('N' . $fila, $reg['salario']);
    $hoja->setCellValue('O' . $fila, $reg['fecha_inicio']);
    $hoja->setCellValue('P' . $fila, ((int)$reg['empleado_activo'] === 1) ? 'Activo' : 'Inactivo');
    $hoja->setCellValue('Q' . $fila, $reg['motivo']);
    $hoja->setCellValue('R' . $fila, $reg['historial_cargos']);
    $fila++;
}

foreach (range('A', 'R') as $columna) {
    $hoja->getColumnDimension($columna)->setAutoSize(true);
}

$nombreArchivo = 'reporte_colaboradores_' . date('Y-m-d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($documento);
$writer->save('php://output');
exit;