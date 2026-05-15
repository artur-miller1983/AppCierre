<?php
require_once('config.php');
session_start();

if (!isset($_SESSION['strTutor'])) {
    header('Location: index.php');
    exit();
}

$strTutor   = $_SESSION['strTutor'];
$nombreTutor = $_SESSION['strNombres'];
$Tipo       = $_SESSION['strTipo'];

$url = ($Tipo === 'Instructor' || $Tipo === 'Auxiliar')
    ? URL_CIERRES . '?strTutor=' . urlencode($strTutor)
    : URL_CIERRES;
$dataCierres = apiGet($url);
$cierres = $dataCierres !== '' ? json_decode($dataCierres, true) : [];

// ── KPIs ──────────────────────────────────────────────
$hoy        = date('Y-m-d');
$mesActual  = date('Y-m');
$cierresHoy = 0;
$horasMes   = 0;
$cierresMes = 0;
$horasPorInstructor = [];
$horasPorVehiculo   = [];
$horasPorDia        = [];

foreach ($cierres as $c) {
    if (empty($c['dteFecha'])) continue;
    $fecha = (new DateTime($c['dteFecha']))->format('Y-m-d');
    $horas = (float)($c['intCantHoras'] ?? 0) + ((float)($c['intCantMinutos'] ?? 0) / 60);

    if ($fecha === $hoy) $cierresHoy++;

    if (substr($fecha, 0, 7) === $mesActual) {
        $horasMes  += $horas;
        $cierresMes++;
        $inst = $c['nombreTutor']  ?? 'N/A';
        $veh  = $c['strVehiculo']  ?? 'N/A';
        $horasPorInstructor[$inst] = ($horasPorInstructor[$inst] ?? 0) + $horas;
        $horasPorVehiculo[$veh]    = ($horasPorVehiculo[$veh]    ?? 0) + $horas;
    }
    $horasPorDia[$fecha] = ($horasPorDia[$fecha] ?? 0) + $horas;
}

arsort($horasPorInstructor);
arsort($horasPorVehiculo);
$_keysInst     = array_keys($horasPorInstructor);
$topInstructor = !empty($_keysInst) ? $_keysInst[0] : '—';
$topInstructorHoras = isset($horasPorInstructor[$topInstructor]) ? $horasPorInstructor[$topInstructor] : 0;
$_keysVeh      = array_keys($horasPorVehiculo);
$topVehiculo   = !empty($_keysVeh) ? $_keysVeh[0] : '—';
$topVehiculoHoras   = isset($horasPorVehiculo[$topVehiculo]) ? $horasPorVehiculo[$topVehiculo] : 0;
unset($_keysInst, $_keysVeh);

// Últimos 8 cierres
usort($cierres, function($a, $b) {
    $fa = isset($a['dteFecha']) ? $a['dteFecha'] : '';
    $fb = isset($b['dteFecha']) ? $b['dteFecha'] : '';
    return strcmp($fb, $fa);
});
$ultimos = array_slice($cierres, 0, 8);

// Sparkline últimos 7 días
$spark = [];
$sparkLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $spark[]       = round($horasPorDia[$d] ?? 0, 1);
    $sparkLabels[] = date('d/m', strtotime("-$i days"));
}

$mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$mesNombre = $mesesNombres[(int)date('n')];

function fmtHoras($h) {
    $h = (float)$h;
    $hh = floor($h);
    $mm = round(($h - $hh) * 60);
    return "{$hh}h {$mm}m";
}

include './plantilla/cabecera.php';
?>

<style>
    html, body { height: 100%; margin: 0; }
    body { display: flex; flex-direction: column; min-height: 100vh; }
    main { flex: 1; }

    .kpi-card { border-radius: 12px; border: none; transition: transform .15s, box-shadow .15s; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,.1) !important; }
    .kpi-icon {
        width: 50px; height: 50px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        flex-shrink: 0;
    }
    .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
    .kpi-label { font-size: 11px; color: #888; margin-top: 2px; text-transform: uppercase; letter-spacing: .4px; }
    .kpi-sub   { font-size: 11px; margin-top: 3px; }

    .badge-clase {
        font-size: 10px; font-weight: 500;
        padding: 3px 8px; border-radius: 20px;
        background: rgba(49,73,59,.12); color: #31493b;
    }
    [data-theme="dark"] .badge-clase { background: rgba(63,185,80,.15); color: #3fb950; }

    .recientes td { font-size: 12px; vertical-align: middle; }
    .recientes th { font-size: 11px; }

    /* dark overrides para las kpi cards */
    [data-theme="dark"] .kpi-label { color: #8b949e; }
</style>

<main>
<div class="container mt-3 mb-5">

    <!-- Encabezado -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="mb-0">Bienvenido, <?= htmlspecialchars($nombreTutor) ?></h5>
            <small class="text-muted"><?= $mesNombre . ' ' . date('Y') ?> &mdash; <?= htmlspecialchars($Tipo) ?></small>
        </div>
        <a href="<?= BASE_URL ?>cierre/nuevo.php" class="btn btn-success btn-sm shadow-sm">
            <i class="fa-solid fa-plus mr-1"></i> Nuevo cierre
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">

        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon mr-3" style="background:rgba(30,163,26,.12);color:#1ea31a">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="kpi-value text-success"><?= $cierresHoy ?></div>
                        <div class="kpi-label">Cierres hoy</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon mr-3" style="background:rgba(26,115,232,.1);color:#1a73e8">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div class="kpi-value" style="color:#1a73e8;font-size:1.3rem"><?= fmtHoras($horasMes) ?></div>
                        <div class="kpi-label">Horas en <?= $mesNombre ?></div>
                        <div class="kpi-sub text-muted"><?= $cierresMes ?> cierres</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon mr-3" style="background:rgba(245,158,11,.1);color:#f59e0b">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div style="min-width:0">
                        <div class="kpi-value text-truncate" style="color:#f59e0b;font-size:1rem"
                             title="<?= htmlspecialchars($topInstructor) ?>">
                            <?= htmlspecialchars($topInstructor) ?>
                        </div>
                        <div class="kpi-label">Top</div>
                        <?php if ($topInstructor !== '—'): ?>
                        <div class="kpi-sub text-muted"><?= fmtHoras($topInstructorHoras) ?> este mes</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon mr-3" style="background:rgba(232,62,140,.1);color:#e83e8c">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div>
                        <div class="kpi-value" style="color:#e83e8c"><?= htmlspecialchars($topVehiculo) ?></div>
                        <div class="kpi-label">Vehículo más activo</div>
                        <?php if ($topVehiculo !== '—'): ?>
                        <div class="kpi-sub text-muted"><?= fmtHoras($topVehiculoHoras) ?> este mes</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Gráfico + Tabla recientes -->
    <div class="row">

        <div class="col-md-5 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header small">
                    <i class="fa-solid fa-chart-area text-success mr-1"></i>
                    Horas — últimos 7 días
                </div>
                <div class="card-body">
                    <canvas id="sparkChart" height="160"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-7 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center small">
                    <span><i class="fa-solid fa-list text-secondary mr-1"></i> Últimos cierres</span>
                    <a href="<?= BASE_URL ?>cierre/lista.php"
                       class="btn btn-outline-secondary btn-sm" style="font-size:11px">
                       Ver todos
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($ultimos)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover recientes mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Clase</th>
                                    <th>Responsable</th>
                                    <th class="text-center">Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos as $c): ?>
                                <tr>
                                    <td><?= (new DateTime($c['dteFecha']))->format('d/m/Y') ?></td>
                                    <td>
                                        <span class="badge-clase">
                                            <?= htmlspecialchars($c['nombreClase'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width:130px"
                                        title="<?= htmlspecialchars($c['nombreTutor'] ?? '') ?>">
                                        <?= htmlspecialchars($c['nombreTutor'] ?? '') ?>
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        <?= $c['intCantHoras'] ?? 0 ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center text-muted p-4 mb-0 small">No hay cierres registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<?php include './plantilla/pie.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var gridColor  = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
    var labelColor = isDark ? '#8b949e' : '#666';

    new Chart(document.getElementById('sparkChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($sparkLabels) ?>,
            datasets: [{
                data: <?= json_encode($spark) ?>,
                borderColor: '#1ea31a',
                backgroundColor: 'rgba(30,163,26,.08)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: '#1ea31a',
                fill: true,
                tension: 0.38
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' h' } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: labelColor, callback: v => v + 'h' },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: labelColor },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>

</body>
</html>
