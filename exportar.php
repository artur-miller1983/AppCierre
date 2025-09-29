<?php
require 'vendor/autoload.php'; // Autoload de Composer
require_once(__DIR__ . '/config.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// ==============================
// 1. Obtener parámetros enviados
// ==============================
$fechaSeleccionada     = $_GET['fecha'] ?? date('Y-m-d');
$tutorSeleccionado     = $_GET['strTutores'] ?? '';
$tipoClaseSeleccionado = $_GET['intTipoClase'] ?? '';
$VehiculoSeleccionado  = $_GET['strPlaca'] ?? '';

// DEBUG: guardar parámetros recibidos
file_put_contents(__DIR__ . "/debug_exportar.log", print_r($_GET, true));

// ==============================
// 2. Cargar datos desde API
// ==============================
$dataClases = @file_get_contents(URL_CIERRES);
$datos = json_decode($dataClases, true);
if ($datos === null) {
    $datos = [];
}

// ==============================
// 3. Filtrar datos
// ==============================
$resultado = array_filter($datos, function ($item) use ($fechaSeleccionada, $tipoClaseSeleccionado, $tutorSeleccionado, $VehiculoSeleccionado) {
    
    $fechaObj = new DateTime($item['dteFecha']);
    $fechaConvertida = $fechaObj->format('Y-m-d');

    return $fechaConvertida == $fechaSeleccionada
        && ($tipoClaseSeleccionado === '' || $item['intTipoClase'] == $tipoClaseSeleccionado)
        && ($tutorSeleccionado === '' || $item['nombreTutor'] === $tutorSeleccionado) // ojo: usar nombreTutor
        && ($VehiculoSeleccionado === '' || $item['strVehiculo'] === $VehiculoSeleccionado);
});

// ==============================
// 4. Crear archivo Excel
// ==============================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'Instructor');
$sheet->setCellValue('B1', 'Tipo Clase');
$sheet->setCellValue('C1', 'Vehículo');
$sheet->setCellValue('D1', 'Cantidad Horas');

// Estilo de encabezados
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

// Ajustar ancho columnas
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ==============================
// 5. Rellenar datos
// ==============================
$row = 2;
foreach ($resultado as $fila) {
    $tutor    = $fila['nombreTutor'];
    $clase    = $fila['nombreClase'] ?? 'Clase no especificada';
    $vehiculo = $fila['strVehiculo'] ?? 'Vehículo no especificado';

    $horas   = is_numeric($fila['intCantHoras']) ? (int)$fila['intCantHoras'] : 0;
    $minutos = is_numeric($fila['intCantMinutos']) ? (int)$fila['intCantMinutos'] : 0;
    $totalHoras = $horas + ($minutos / 60);

    $sheet->setCellValue("A$row", $tutor);
    $sheet->setCellValue("B$row", $clase);
    $sheet->setCellValue("C$row", $vehiculo);
    $sheet->setCellValue("D$row", round($totalHoras, 2));

    // Colorear columna D (horas)
    $sheet->getStyle("D$row")->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E2EFDA'); // Verde claro

    $row++;
}

// ==============================
// 6. Descargar archivo
// ==============================
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="clases.xlsx"');
$writer->save('php://output');
exit;
