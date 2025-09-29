<?php
require 'vendor/autoload.php'; // Autoload de Composer
require_once('config.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Obtener parámetros enviados
$fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');
$tutorSeleccionado = $_GET['strTutores'] ?? '';
$tipoClaseSeleccionado = $_GET['intTipoClase'] ?? '';
$VehiculoSeleccionado = $_GET['strPlaca'] ?? '';

// Cargar datos
$dataClases = @file_get_contents(URL_CIERRES);
$datos = json_decode($dataClases, true);
if ($datos === null) {
    $datos = [];
}

// Filtrar datos según los parámetros
$resultado = array_filter($datos, function ($item) use ($fechaSeleccionada, $tipoClaseSeleccionado, $tutorSeleccionado, $VehiculoSeleccionado) {
    $fechaConvertida = date("Y-m-d", strtotime($item['dteFecha']));
    return $fechaConvertida == $fechaSeleccionada
        && ($tipoClaseSeleccionado === '' || $item['intTipoClase'] == $tipoClaseSeleccionado)
        && ($tutorSeleccionado === '' || $item['strTutor'] === $tutorSeleccionado)
        && ($VehiculoSeleccionado === '' || $item['strVehiculo'] === $VehiculoSeleccionado);
});

// Crear archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'Instructor');
$sheet->setCellValue('B1', 'Tipo Clase');
$sheet->setCellValue('C1', 'Vehiculo');
$sheet->setCellValue('D1', 'Cantidad Horas');

// Aplicar estilo a los encabezados (fila 1)
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F81BD'] // Azul
    ]
];
$sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

// Ajustar ancho de columnas automáticamente
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Datos
$row = 2;
foreach ($resultado as $fila) {
    $tutor = $fila['nombreTutor'];
    $clase = $fila['nombreClase'] ?? 'Clase no especificada';
    $vehiculo = $fila['strVehiculo'] ?? 'Vehiculo no especificado';

    $horas = is_numeric($fila['intCantHoras']) ? (int) $fila['intCantHoras'] : 0;
    $minutos = is_numeric($fila['intCantMinutos']) ? (int) $fila['intCantMinutos'] : 0;
    $totalHoras = $horas + ($minutos / 60);

    $sheet->setCellValue("A$row", $tutor);
    $sheet->setCellValue("B$row", $clase);
    $sheet->setCellValue("C$row", $vehiculo);
    $sheet->setCellValue("D$row", round($totalHoras, 2));

    // Ejemplo: colorear la columna D (horas) de toda la tabla
    $sheet->getStyle("D$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA'); // Verde claro

    $row++;
}

// Descargar archivo
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="clases.xlsx"');
$writer->save('php://output');
exit;
