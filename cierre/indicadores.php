<?php
require_once(__DIR__ . '/../config.php');
session_start();

if (!isset($_SESSION['strTutor'])) {
    header("Location: ./index.php");
    exit();
}

$strTutor = $_SESSION['strTutor'];
$nombreTutor = $_SESSION['strNombres'];
$Tipo = $_SESSION['strTipo'];


$fechaSeleccionada = $_POST['fecha'] ?? date('Y-m-d');
$fechaSeleccionadaFin = $_POST['fechaFin'] ?? date('Y-m-d');
$tutorSeleccionado = $_POST['strTutores'] ?? '';
$tipoClaseSeleccionado = $_POST['intTipoClase'] ?? '';
$VehiculoSeleccionado = $_POST['strPlaca'] ?? '';
$EstadoSeleccionado = $_POST['strEstado'] ?? '';

//*************************************************** */
// 🔹 Contexto Con SSL
//************************************************ */

// if ($Tipo === 'Instructor' || $Tipo === 'Supervisor') {
//     $dataCierres = @file_get_contents(URL_CIERRES . '?strTutor=' . urlencode($strTutor));
// } else {
//     $dataCierres = @file_get_contents(URL_CIERRES);
// }

// $datos = json_decode($dataCierres, true);
// if ($datos === null) {
//     $datos = [];
// }

// $dataTipoClases = @file_get_contents(URL_TIPO_CLASES);
// $tipos = json_decode($dataTipoClases, true);
// if ($tipos === null) {
//     $tipos = [];
// }

// $dataTutores = @file_get_contents(URL_TUTORES);
// $tutores = json_decode($dataTutores, true);
// if ($tutores === null) {
//     $tutores = [];
// }

// $dataVehiculos = @file_get_contents(URL_VEHICULOS);
// $Vehiculos = json_decode($dataVehiculos, true);
// if ($Vehiculos === null) {
//     $Vehiculos = [];
// }
//************************************************* */


//*************************************************** */
// 🔹 Contexto sin SSL
//*************************************************** */
$urlCierres = ($Tipo === 'Instructor' || $Tipo === 'Auxiliar' || $Tipo === 'Supervisor')
    ? URL_CIERRES . '?strTutor=' . urlencode($strTutor)
    : URL_CIERRES;
$dataCierres = apiGet($urlCierres);
$datos = $dataCierres !== '' ? json_decode($dataCierres, true) : [];

$dataTipoClases = apiGet(URL_TIPO_CLASES);
$tipos = $dataTipoClases !== '' ? json_decode($dataTipoClases, true) : [];

$dataTutores = apiGet(URL_TUTORES);
$tutores = $dataTutores !== '' ? json_decode($dataTutores, true) : [];

$dataVehiculos = apiGet(URL_VEHICULOS);
$Vehiculos = $dataVehiculos !== '' ? json_decode($dataVehiculos, true) : [];
//*************************************************** */



// Datos para tabla agrupada por Vehiculo y clase
$datosPorVehiculoClase = [];
$datosPorTutorClase = [];
$horasPorClase = [];
$totalGeneralHoras = 0;

$resultado = array_filter($datos, function ($item) use ($fechaSeleccionada, $fechaSeleccionadaFin, $tipoClaseSeleccionado, $tutorSeleccionado, $VehiculoSeleccionado) {

    if (empty($item['dteFecha'])) {
        return false;
    }

    $fechaObj = new DateTime($item['dteFecha']);
    $fechaConvertida = $fechaObj->format('Y-m-d');

    // Convertir tutor
    $tutor = $item['strTutor'] ?? '';

    return
        ($fechaConvertida >= $fechaSeleccionada && $fechaConvertida <= $fechaSeleccionadaFin)
        && ($tipoClaseSeleccionado === '' || $item['intTipoClase'] == $tipoClaseSeleccionado)
        && ($tutorSeleccionado === '' || trim($tutor) === trim($tutorSeleccionado))
        && ($VehiculoSeleccionado === '' || trim($item['strVehiculo']) === trim($VehiculoSeleccionado));
});


$datosFiltrados = []; // <-- inicializamos el array

if (!empty($resultado)) {
    foreach ($resultado as $fila) {
        $fecha = $fila['dteFecha'] ?? 'Fecha no especificada';
        $tutor = $fila['nombreTutor'];
        $clase = $fila['nombreClase'] ?? 'Clase no especificada';
        $vehiculo = $fila['strVehiculo'] ?? 'Vehiculo no especificado';

        $horas = is_numeric($fila['intCantHoras']) ? (int) $fila['intCantHoras'] : 0;
        $minutos = is_numeric($fila['intCantMinutos']) ? (int) $fila['intCantMinutos'] : 0;
        $totalHoras = $horas + ($minutos / 60);

        // --- Agrupar por Tutor → Clase
        if (!isset($datosPorTutorClase[$tutor])) {
            $datosPorTutorClase[$tutor] = [];
        }
        if (!isset($datosPorTutorClase[$tutor][$clase])) {
            $datosPorTutorClase[$tutor][$clase] = 0;
        }
        $datosPorTutorClase[$tutor][$clase] += $totalHoras;

        // --- Agrupar por Vehiculo → Clase
        if (!isset($datosPorVehiculoClase[$vehiculo])) {
            $datosPorVehiculoClase[$vehiculo] = [];
        }
        if (!isset($datosPorVehiculoClase[$vehiculo][$clase])) {
            $datosPorVehiculoClase[$vehiculo][$clase] = 0;
        }
        $datosPorVehiculoClase[$vehiculo][$clase] += $totalHoras;

        // --- Total general
        $totalGeneralHoras += $totalHoras;

        // --- Para gráfico: horas por clase
        if (!isset($horasPorClase[$clase])) {
            $horasPorClase[$clase] = 0;
        }
        $horasPorClase[$clase] += $totalHoras;

        $datosFiltrados[] = [
            'fecha' => $fecha,
            'tutor' => $tutor,
            'clase' => $clase,
            'vehiculo' => $vehiculo,
            'horas' => $totalHoras
        ];
    }
}


// ── Comparativo período anterior ─────────────────────────────────────────────
$dias = max(1, (new DateTime($fechaSeleccionada))->diff(new DateTime($fechaSeleccionadaFin))->days + 1);
$fechaAntIni = date('Y-m-d', strtotime($fechaSeleccionada . " -{$dias} days"));
$fechaAntFin = date('Y-m-d', strtotime($fechaSeleccionadaFin . " -{$dias} days"));

$horasAnterior   = 0;
$cierresAnterior = 0;
foreach ($datos as $item) {
    if (empty($item['dteFecha'])) continue;
    $f = (new DateTime($item['dteFecha']))->format('Y-m-d');
    if ($f >= $fechaAntIni && $f <= $fechaAntFin) {
        $horasAnterior += ($item['intCantHoras'] ?? 0) + (($item['intCantMinutos'] ?? 0) / 60);
        $cierresAnterior++;
    }
}
$totalCierresActual = count($datosFiltrados);
$varHoras   = $horasAnterior   > 0 ? (($totalGeneralHoras   - $horasAnterior)   / $horasAnterior   * 100) : null;
$varCierres = $cierresAnterior > 0 ? (($totalCierresActual  - $cierresAnterior) / $cierresAnterior * 100) : null;

// ── Ranking instructor ────────────────────────────────────────────────────────
$rankInstructor = [];
foreach ($datosFiltrados as $f) {
    $t = $f['tutor'];
    if (!isset($rankInstructor[$t])) $rankInstructor[$t] = ['cierres' => 0, 'horas' => 0];
    $rankInstructor[$t]['cierres']++;
    $rankInstructor[$t]['horas'] += $f['horas'];
}
uasort($rankInstructor, function($a, $b) { return $b['horas'] > $a['horas'] ? 1 : ($b['horas'] < $a['horas'] ? -1 : 0); });
$maxHorasInst = max(array_column($rankInstructor, 'horas') ?: [1]);

// ── Resumen vehículos ─────────────────────────────────────────────────────────
$rankVehiculo = [];
foreach ($datosFiltrados as $f) {
    $v = $f['vehiculo'];
    if (!isset($rankVehiculo[$v])) $rankVehiculo[$v] = ['cierres' => 0, 'horas' => 0, 'ultima' => ''];
    $rankVehiculo[$v]['cierres']++;
    $rankVehiculo[$v]['horas'] += $f['horas'];
    if ($f['fecha'] > $rankVehiculo[$v]['ultima']) $rankVehiculo[$v]['ultima'] = $f['fecha'];
}
uasort($rankVehiculo, function($a, $b) { return $b['horas'] > $a['horas'] ? 1 : ($b['horas'] < $a['horas'] ? -1 : 0); });

// Formato horas
function formatearHoras($decimal)
{
    $decimal = is_numeric($decimal) ? $decimal : 0;
    $horas = floor($decimal);
    $minutos = round(($decimal - $horas) * 60);
    return "{$horas}h {$minutos}m";
}
?>

<?php include '../plantilla/cabecera.php'; ?>

<style>
    html,
    body {
        height: 100%;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1;
    }

    .extra-small {
        display: flex;
    }

    table.dataTable,
    table.dataTable thead th {
        border: 1px solid rgb(236, 238, 236) !important;
        /* gris claro */
    }

    /* //TITULOD E LAS CABECERAS */
    table.dataTable {
        font-size: 14px;
    }

    table.dataTable thead th,
    table.dataTable tbody td {
        padding: 5px 5px !important;
    }

    /* --- Paginación estilo Bootstrap Outline Success (compacto) --- */
    .dataTables_wrapper .dataTables_paginate .page-link {
        background-color: #eecd9046 !important;
        color: #814b4bff !important;
        border: 1px solid #d4d8d5ff !important;
        border-radius: 4px !important;
        margin: 0 2px !important;
        padding: 2px 6px !important;
        /* más pequeños */
        font-size: 13px !important;
        /* texto más chico */
        line-height: 1.2 !important;
    }

    /* Hover */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #decce0ff !important;
        color: #31493bff !important;
    }

    /* Activo */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #1ea31ae5 !important;
        color: #fff !important;
        border: 1px solid #d7e6dbff !important;
    }

    /* Quitar focus feo */
    .dataTables_wrapper .dataTables_paginate .page-link:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    [data-theme="dark"] tfoot tr.bg-light {
        background-color: #1c2128 !important;
        color: #c9d1d9 !important;
    }
</style>

<main>


    <div class="container my-2">
        <div class="row mt-3 mb-3">
            <div class="col-md-8 d-flex justify-content-between">
                <span class="text-secondary ">
                    <i class="fa-solid fa-chart-line"></i>
                    Indicadores
                </span>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" class="mb-4">
            <div class="form-group row">

                <div class="col-md-2 mt-2">
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($fechaSeleccionada); ?>" >
                </div>

                <div class="col-md-2 mt-2">
                    <input type="date" name="fechaFin" id="fechaFin" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($fechaSeleccionadaFin); ?>" >
                </div>

                <?php if ($Tipo === 'Administrador'): ?>

                    <div class="col-md-2 mt-2">
                        <select name="strTutores" class="form-control form-control-sm select-tutores" id="strTutores"
                            placeholder="Tutores">
                            <option value=""></option>
                            <?php foreach ($tutores as $tutor): ?>
                                <option value="<?= $tutor['strTutor'] ?>" <?= $tutorSeleccionado === $tutor['strTutor'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tutor['strNombreTutor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php endif; ?>

                <div class="col-md-2 mt-2">
                    <select name="intTipoClase" class="form-control form-control-sm" id="intTipoClase">
                        <option value="">Ninguno</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['idTipo'] ?>" <?= $tipoClaseSeleccionado == $tipo['idTipo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['strDescripcion']) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mt-2">
                    <select id="vehiculo" name="strPlaca" class="form-control form-control-sm select-vehiculo">
                        <option value=""></option>
                        <?php foreach ($Vehiculos as $vehiculo): ?>
                            <option value="<?= $vehiculo['strPlaca'] ?>" <?= $VehiculoSeleccionado == $vehiculo['strPlaca'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vehiculo['strPlaca']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>

                <div class="col-md-2 mt-2 d-flex align-items-center justify-content-between">
                    <button type="submit" class="btn btn-outline-info btn-sm">Filtrar</button>
                    <div class="col-auto text-end" id="contenedor-boton"></div>
                </div>

            </div>
        </form>

        <!-- Comparativo período anterior -->
        <?php if (!empty($datosFiltrados)): ?>
        <div class="row mb-2 mt-3">
            <?php
            function varBadge($val) {
                if ($val === null) return '<span class="badge badge-secondary">Sin datos anteriores</span>';
                $cls  = $val >= 0 ? 'success' : 'danger';
                $icon = $val >= 0 ? 'arrow-trend-up' : 'arrow-trend-down';
                return "<span class=\"badge badge-{$cls}\"><i class=\"fa-solid fa-{$icon} mr-1\"></i>"
                     . ($val >= 0 ? '+' : '') . number_format($val, 1) . "% vs período anterior</span>";
            }
            ?>
            <div class="col-6 col-md-3 mb-2">
                <div class="card shadow-sm text-center py-2 px-1">
                    <div style="font-size:11px;color:#888">Horas actuales</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#1ea31a"><?= formatearHoras($totalGeneralHoras) ?></div>
                    <div class="mt-1"><?= varBadge($varHoras) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="card shadow-sm text-center py-2 px-1">
                    <div style="font-size:11px;color:#888">Cierres actuales</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#1a73e8"><?= $totalCierresActual ?></div>
                    <div class="mt-1"><?= varBadge($varCierres) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="card shadow-sm text-center py-2 px-1">
                    <div style="font-size:11px;color:#888">Período anterior</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#888"><?= formatearHoras($horasAnterior) ?></div>
                    <div style="font-size:11px;color:#aaa"><?= $cierresAnterior ?> cierres</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="card shadow-sm text-center py-2 px-1">
                    <div style="font-size:11px;color:#888">Promedio diario</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#f59e0b">
                        <?= $dias > 0 ? formatearHoras($totalGeneralHoras / $dias) : '—' ?>
                    </div>
                    <div style="font-size:11px;color:#aaa">por día del período</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla y gráfico -->
        <div class="card shadow mt-2">
            <div class="card-header bg-light text-secondary d-flex justify-content-between align-items-center">
                <h5 class="mb-0 small">
                    <i class="fas fa-user-clock mr-2 text-success"></i>Cantidad de Clases
                </h5>
                <button id="btnExportarPDF" class="btn btn-outline-danger btn-sm" style="font-size:11px">
                    <i class="fa-solid fa-file-pdf mr-1"></i>PDF
                </button>
            </div>
            <div class="card-body" id="grafico" name="grafico">
                <?php if (!empty($datosPorTutorClase)): ?>
                    <canvas id="graficoHorasTutor" height="100"></canvas>
                    <div class="table-responsive mb-3 mt-4">
                        <table id="tablaClases" class="table table-bordered table-sm text-center small">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Responsable</th>
                                    <th>Tipo Clase</th>
                                    <th>Vehiculo</th>
                                    <th>Cantidad Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosFiltrados as $fila): ?>
                                    <tr>
                                        <td class="small" <?php
                                        $fecha = new DateTime($fila['fecha']);
                                        $orden = $fecha->format('Ymd'); // para ordenar correctamente
                                        ?>
                                        data-order="<?= $orden ?>">
                                            <?= $fecha->format('d/m/Y') ?>
                                        </td>

                                        <td><?= htmlspecialchars($fila['tutor']) ?></td>
                                        <td><?= htmlspecialchars($fila['clase']) ?></td>
                                        <td><?= htmlspecialchars($fila['vehiculo']) ?></td>
                                        <td><?= formatearHoras($fila['horas']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="4" class="text-right">TOTAL</td>
                                    <td><?= formatearHoras($totalGeneralHoras) ?></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                <?php else: ?>
                    <p class="text-center mb-0">No hay datos de horas para la fecha seleccionada.</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Ranking Instructores -->
        <?php if (!empty($rankInstructor)): ?>
        <div class="card shadow mt-3">
            <div class="card-header bg-light text-secondary small">
                <i class="fa-solid fa-ranking-star mr-1 text-warning"></i> Ranking de Instructores
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:13px">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Responsable</th>
                                <th class="text-center">Cierres</th>
                                <th class="text-center">Horas</th>
                                <th style="min-width:120px">Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $pos = 1; foreach ($rankInstructor as $nombre => $datos): ?>
                            <tr>
                                <td>
                                    <?php if ($pos === 1): ?>
                                        <i class="fa-solid fa-trophy text-warning"></i>
                                    <?php elseif ($pos === 2): ?>
                                        <i class="fa-solid fa-medal" style="color:#aaa"></i>
                                    <?php elseif ($pos === 3): ?>
                                        <i class="fa-solid fa-medal" style="color:#cd7f32"></i>
                                    <?php else: ?>
                                        <span class="text-muted"><?= $pos ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($nombre) ?></td>
                                <td class="text-center"><?= $datos['cierres'] ?></td>
                                <td class="text-center font-weight-bold"><?= formatearHoras($datos['horas']) ?></td>
                                <td>
                                    <div class="progress" style="height:8px;border-radius:4px">
                                        <div class="progress-bar bg-success" style="width:<?= $maxHorasInst > 0 ? round($datos['horas']/$maxHorasInst*100) : 0 ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php $pos++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumen Vehículos -->
        <?php if (!empty($rankVehiculo)): ?>
        <div class="card shadow mt-3 mb-4">
            <div class="card-header bg-light text-secondary small">
                <i class="fa-solid fa-car mr-1 text-info"></i> Resumen de Vehículos
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:13px">
                        <thead class="thead-light">
                            <tr>
                                <th>Placa</th>
                                <th class="text-center">Cierres</th>
                                <th class="text-center">Horas totales</th>
                                <th class="text-center">Último uso</th>
                                <th style="min-width:120px">Uso relativo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $maxHorasVeh = max(array_column($rankVehiculo, 'horas') ?: [1]);
                        foreach ($rankVehiculo as $placa => $vd):
                        ?>
                            <tr>
                                <td class="font-weight-bold"><?= htmlspecialchars($placa) ?></td>
                                <td class="text-center"><?= $vd['cierres'] ?></td>
                                <td class="text-center"><?= formatearHoras($vd['horas']) ?></td>
                                <td class="text-center text-muted">
                                    <?= $vd['ultima'] ? (new DateTime($vd['ultima']))->format('d/m/Y') : '—' ?>
                                </td>
                                <td>
                                    <div class="progress" style="height:8px;border-radius:4px">
                                        <div class="progress-bar" style="width:<?= $maxHorasVeh > 0 ? round($vd['horas']/$maxHorasVeh*100) : 0 ?>%;background:#17a2b8"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<!-- PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>

    <?php if (!empty($horasPorClase)): ?>
        const labelsHoras = <?php echo json_encode(array_keys($horasPorClase)); ?>;
        const dataHoras = <?php echo json_encode(array_values($horasPorClase)); ?>;
        const ctxHoras = document.getElementById('graficoHorasTutor').getContext('2d');

        // Colores dinámicos con degradado/3D
        const colores = [
            'rgba(201, 206, 209, 0.9)',
            'rgba(84, 137, 77, 0.9)',
        ];

        new Chart(ctxHoras, {
            type: 'bar',
            data: {
                labels: labelsHoras,
                datasets: [{
                    label: 'Horas',
                    data: dataHoras,
                    backgroundColor: labelsHoras.map((_, i) => colores[i % colores.length]),
                    borderColor: 'rgba(0,0,0,0.3)',
                    borderWidth: 1,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1500, // duración total en ms
                    easing: 'easeOutBounce' // efecto rebote al crecer
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => `${value.toFixed(1)}h`,
                        color: '#00410fff',
                        font: { weight: 'bold' }
                    },
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.parsed.y.toFixed(2)} horas`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Horas' } },
                    x: { title: { display: true, text: 'Clases' } }
                }
            },
            plugins: [{
                // Plugin efecto 3D con sombra
                id: '3d-effect',
                beforeDraw: (chart) => {
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach((dataset, i) => {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((bar) => {
                            const { x, y, base, width } = bar.getProps(['x', 'y', 'base', 'width'], true);
                            ctx.fillStyle = 'rgba(56, 126, 133, 0.15)';
                            ctx.fillRect(x - width / 2 + 5, y + 5, width, base - y);
                        });
                    });
                }
            }, ChartDataLabels]
        });
    <?php endif;
    ?>

</script>

<?php include '../plantilla/pie.php'; ?>
<script>

$(document).ready(function () {

    // inicializar DataTable UNA sola vez
    var tabla = $('#tablaClases').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay información disponible",
            "info": "_START_ de _END_ Registros",
            "infoEmpty": "0 a 0 de 0 Registros",
            "infoFiltered": "(_MAX_ Filtrados)",
            "lengthMenu": "Mostrar _MENU_",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar",
            "zeroRecords": "No se encontraron coincidencias",
            "paginate": {
                "first": "<<",
                "last": ">>",
                "next": ">",
                "previous": "<"
            }
        },
        responsive: true,
        order: [[0, 'desc']],
        pagingType: "first_last_numbers",
        buttons: [{
            extend: 'excelHtml5',
            text: 'Exportar',
            className: 'btn btn-secondary btn-sm',
            action: function (e, dt, button, config) {

                var filas = dt.rows({ search: 'applied' }).count();

                // Si no hay registros
                if (filas === 0) {
                    alertify.message("No hay datos para exportar.");
                    return;
                }

                var self = this;
                var originalAction = $.fn.dataTable.ext.buttons.excelHtml5.action;

                alertify.confirm(
                    'Confirmación',
                    '¿Desea exportar los datos a Excel?',
                    function () {
                        originalAction.call(self, e, dt, button, config);
                        alertify.success('Exportando...');
                    },
                    function () {}
                ).set('labels', { ok: 'Guardar', cancel: 'Cancelar' });

            }
        }]
    });

    tabla.buttons().container().appendTo('#contenedor-boton');

    // ── Exportar PDF ──────────────────────────────────────────────────────────
    $('#btnExportarPDF').on('click', function () {
        var btn = $(this).prop('disabled', true).text('Generando...');
        html2canvas(document.getElementById('grafico'), { scale: 1.5, useCORS: true })
            .then(function (canvas) {
                var { jsPDF } = window.jspdf;
                var pdf = new jsPDF('landscape', 'mm', 'a4');
                var imgW = 280;
                var imgH = canvas.height * imgW / canvas.width;
                pdf.setFontSize(11);
                pdf.text('Indicadores — generado el <?= date('d/m/Y H:i') ?>', 10, 10);
                pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 10, 16, imgW, imgH);
                pdf.save('indicadores_<?= date('Ymd') ?>.pdf');
                btn.prop('disabled', false).html('<i class="fa-solid fa-file-pdf mr-1"></i>PDF');
            });
    });


    // Select2
    $('.select-vehiculo').select2({
        placeholder: "Ninguno",
        allowClear: true,
        width: '100%'
    });

    $('.select-tutores').select2({
        placeholder: "Ninguno",
        allowClear: true,
        width: '100%'
    });


    // Cambio fecha inicio
    $('#fecha').on('change', function () {

        let fechaInicio = $(this).val();

        $('#fechaFin').attr('min', fechaInicio);

        if ($('#fechaFin').val() < fechaInicio) {

            $('#fechaFin').val('');

            $('#grafico').empty();

            tabla.clear().draw();

        }

    });


    // Cambio fecha fin
    $('#fechaFin').on('change', function () {

        let fechaInicio = $('#fecha').val();
        let fechaFin = $(this).val();

        if (fechaFin < fechaInicio) {

            alertify.warning("La fecha final no puede ser menor que la fecha inicial.");

            $(this).val('');

            $('#grafico').empty();

            tabla.clear().draw();

            $(this).focus();

        }

    });


    // Validación al enviar
    $('form').on('submit', function (e) {

        let fechaInicio = $('#fecha').val();
        let fechaFin = $('#fechaFin').val();

        if (!fechaInicio || !fechaFin) {

            e.preventDefault();

            alertify.warning("Por favor, complete ambas fechas.");

            return false;

        }

        if (fechaFin < fechaInicio) {

            e.preventDefault();

            alertify.warning("La fecha de fin no puede ser anterior a la fecha de inicio.");

            $('#grafico').empty();

            tabla.clear().draw();

            $('#fechaFin').focus();

            return false;

        }

    });

});

</script>