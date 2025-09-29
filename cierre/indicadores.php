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
$tutorSeleccionado = $_POST['strTutores'] ?? '';
$tipoClaseSeleccionado = $_POST['intTipoClase'] ?? '';
$VehiculoSeleccionado = $_POST['strPlaca'] ?? '';

if ($Tipo === 'Tutor' || $Tipo === 'Supervisor') {
    $dataCierres = @file_get_contents(URL_CIERRES . '?strTutor=' . urlencode($strTutor));
} else {
    $dataCierres = @file_get_contents(URL_CIERRES);
}

$datos = json_decode($dataCierres, true);
if ($datos === null) {
    $datos = [];
}


$dataTipoClases = @file_get_contents(URL_TIPO_CLASES);
$tipos = json_decode($dataTipoClases, true);
if ($tipos === null) {
    $tipos = [];
}

$dataTutores = @file_get_contents(URL_TUTORES);
$tutores = json_decode($dataTutores, true);
if ($tutores === null) {
    $tutores = [];
}

$dataVehiculos = @file_get_contents(URL_VEHICULOS);
$Vehiculos = json_decode($dataVehiculos, true);
if ($Vehiculos === null) {
    $Vehiculos = [];
}

// Datos para tabla agrupada por Vehiculo y clase
$datosPorVehiculoClase = [];
$datosPorTutorClase = [];
$horasPorClase = [];
$totalGeneralHoras = 0;

$resultado = array_filter($datos, function ($item) use ($fechaSeleccionada, $tipoClaseSeleccionado, $tutorSeleccionado, $VehiculoSeleccionado) {

      $fechaConvertida = '';
    if (!empty($item['dteFecha'])) {
        $fechaObj = new DateTime($item['dteFecha']);
        $fechaConvertida = $fechaObj->format('Y-m-d');
    }

    // Convertir en Cadena Vacia en caso que venga null ya que es un array de la libreria select2
    $tutor = $item['strTutor'] ?? '';

    return $fechaConvertida == $fechaSeleccionada
        && ($tipoClaseSeleccionado === '' || $item['intTipoClase'] == $tipoClaseSeleccionado)
        && ($tutorSeleccionado === '' || trim($tutor) === trim($tutorSeleccionado))
        && ($VehiculoSeleccionado === '' || trim($item['strVehiculo']) === trim($VehiculoSeleccionado));
});


$datosFiltrados = []; // <-- inicializamos el array

if (!empty($resultado)) {
    foreach ($resultado as $fila) {
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
            'tutor' => $tutor,
            'clase' => $clase,
            'vehiculo' => $vehiculo,
            'horas' => $totalHoras
        ];
    }
}


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

                <div class="col-md-3 mt-2">
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($fechaSeleccionada); ?>" required>
                </div>

                <?php if ($Tipo === 'Administrador'): ?>

                    <div class="col-md-3 mt-2">
                        <select name="strTutores" class="form-control form-control-sm select-tutores" id="strTutores" placeholder="Tutores">
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
                <div class="col-md-2 mt-2 ">
                    <button type="submit" class="btn btn-outline-info btn-sm ">Filtrar</button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportarExcel()">Exportar</button>
                </div>

            </div>
        </form>

        <!-- Tabla y gráfico -->
        <div class="card shadow mt-4">
            <div class="card-header bg-light text-secondary">
                <h5 class="mb-0 small">
                    <i class="fas fa-user-clock mr-2 text-success "></i>Cantidad de Clases Por Instructor
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($datosPorTutorClase)): ?>
                    <canvas id="graficoHorasTutor" height="100"></canvas>
                    <div class="table-responsive mb-3 mt-4">
                        <table id="tablaClases" class="table table-bordered table-sm text-center small">
                            <thead class="thead-light">
                                <tr>
                                    <th>Instructor</th>
                                    <th>Tipo Clase</th>
                                    <th>Vehiculo</th>
                                    <th>Cantidad Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosFiltrados as $fila): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fila['tutor']) ?></td>
                                        <td><?= htmlspecialchars($fila['clase']) ?></td>
                                        <td><?= htmlspecialchars($fila['vehiculo']) ?></td>
                                        <td><?= formatearHoras($fila['horas']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="3" class="text-right">TOTAL</td>
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
    </div>
</main>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

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
        $('.select-vehiculo').select2({
            placeholder: "Ninguno",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () {
                    return "No existe";
                },
                removeAllItems: function () {
                    return "Eliminar";
                },
                removeItem: function () {
                    return "Eliminar";
                }
            }
        });
    });

    $(document).ready(function () {
        $('.select-tutores').select2({
            placeholder: "Ninguno",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () {
                    return "No existe";
                },
                removeAllItems: function () {
                    return "Eliminar";
                },
                removeItem: function () {
                    return "Eliminar";
                }
            }
        });
    });

    function exportarExcel() {

        //validar que haya datos tablaClases
        const tabla = document.getElementById('tablaClases');
        if (!tabla || tabla.tBodies[0].rows.length === 0) {
            alertify.alert('Exportar a Excel', 'No hay datos para exportar.');
            return;
        }

        alertify.confirm(
            'Exportar a Excel',
            '¿Está seguro de que desea exportar los datos a Excel?',
            function () {
                // Si presiona "Aceptar"
                const params = new URLSearchParams({
                    fecha: '<?= $fechaSeleccionada ?>',
                    strTutores: '<?= $tutorSeleccionado ?>',
                    intTipoClase: '<?= $tipoClaseSeleccionado ?>',
                    strPlaca: '<?= $VehiculoSeleccionado ?>'
                });

                //mostrar datos  params por consola js
                console.log(params);


               window.open('../exportar.php?' + params.toString(), '_blank');
            },
            () => {
                // Si presiona "Cancelar" (opcional)
                //alertify.error('Exportación cancelada');
            }
        ).set('labels', { ok: 'Sí', cancel: 'No' });
    }




</script>