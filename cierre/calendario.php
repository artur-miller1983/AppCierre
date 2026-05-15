<?php
require_once(__DIR__ . '/../config.php');
session_start();

if (!isset($_SESSION['strTutor'])) {
    header("Location: ../index.php");
    exit();
}

$strTutor = $_SESSION['strTutor'];
$Tipo = $_SESSION['strTipo'];

// Navegación mes/año
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('m');
$anio = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');

if ($mes < 1) {
    $mes = 12;
    $anio--;
}
if ($mes > 12) {
    $mes = 1;
    $anio++;
}

$contextNoSSL = stream_context_create([
    "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
]);

$dataCierres = ($Tipo === 'Instructor' || $Tipo === 'Auxiliar' || $Tipo === 'Supervisor')
    ? @file_get_contents(URL_CIERRES . '?strTutor=' . urlencode($strTutor), false, $contextNoSSL)
    : @file_get_contents(URL_CIERRES, false, $contextNoSSL);
$cierres = $dataCierres !== false ? json_decode($dataCierres, true) : [];

// Agrupar por día (solo el mes/año activo)
$porDia = [];
foreach ($cierres as $c) {
    if (empty($c['dteFecha']))
        continue;
    $dt = new DateTime($c['dteFecha']);
    if ((int) $dt->format('m') !== $mes || (int) $dt->format('Y') !== $anio)
        continue;
    $dia = (int) $dt->format('d');
    if (!isset($porDia[$dia]))
        $porDia[$dia] = ['cierres' => 0, 'horas' => 0, 'items' => []];
    $porDia[$dia]['cierres']++;
    $porDia[$dia]['horas'] += ($c['intCantHoras'] ?? 0) + (($c['intCantMinutos'] ?? 0) / 60);
    $porDia[$dia]['items'][] = $c;
}

$mesesNombres = [
    '',
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
];

$primerDia = new DateTime("$anio-$mes-01");
$diasEnMes = (int) $primerDia->format('t');
$diaInicio = (int) $primerDia->format('N'); // 1=Lun … 7=Dom
$hoyStr = date('Y-m-d');

// Mes anterior / siguiente para navegación
$antMes = $mes === 1 ? 12 : $mes - 1;
$antAnio = $mes === 1 ? $anio - 1 : $anio;
$sigMes = $mes === 12 ? 1 : $mes + 1;
$sigAnio = $mes === 12 ? $anio + 1 : $anio;

// Todos los días con detalle para el modal (JSON)
$diasJson = json_encode($porDia);

include '../plantilla/cabecera.php';
?>

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

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .cal-header-cell {
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        color: #888;
        padding: 4px 0;
        text-transform: uppercase;
    }

    .cal-cell {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        min-height: 72px;
        padding: 6px 7px 5px;
        cursor: default;
        transition: background .15s, border-color .15s;
        position: relative;
    }

    .cal-cell.tiene-datos {
        cursor: pointer;
        border-color: #b6dfb4;
        background: rgba(30, 163, 26, .04);
    }

    .cal-cell.tiene-datos:hover {
        background: rgba(30, 163, 26, .1);
        border-color: #1ea31a;
    }

    .cal-cell.hoy {
        border-color: #1a73e8 !important;
        background: rgba(26, 115, 232, .06) !important;
    }

    .cal-cell.vacio {
        background: transparent;
        border-color: transparent;
        cursor: default;
    }

    .cal-num {
        font-size: 13px;
        font-weight: 600;
        color: #444;
        line-height: 1;
    }

    .cal-cell.hoy .cal-num {
        color: #1a73e8;
    }

    .cal-badges {
        margin-top: 5px;
    }

    .cal-badge-cierres {
        display: inline-block;
        font-size: 10px;
        background: #1ea31a;
        color: #fff;
        border-radius: 10px;
        padding: 1px 6px;
        margin-right: 2px;
    }

    .cal-badge-horas {
        display: inline-block;
        font-size: 10px;
        background: rgba(26, 115, 232, .15);
        color: #1a73e8;
        border-radius: 10px;
        padding: 1px 6px;
    }

    /* dark mode */
    [data-theme="dark"] .cal-cell {
        border-color: #30363d;
        background: #1c2128;
    }

    [data-theme="dark"] .cal-cell.tiene-datos {
        border-color: #3fb950;
        background: rgba(63, 185, 80, .06);
    }

    [data-theme="dark"] .cal-cell.tiene-datos:hover {
        background: rgba(63, 185, 80, .14);
        border-color: #3fb950;
    }

    [data-theme="dark"] .cal-cell.hoy {
        border-color: #58a6ff !important;
        background: rgba(88, 166, 255, .08) !important;
    }

    [data-theme="dark"] .cal-cell.vacio {
        border-color: transparent;
        background: transparent;
    }

    [data-theme="dark"] .cal-num {
        color: #c9d1d9;
    }

    [data-theme="dark"] .cal-cell.hoy .cal-num {
        color: #58a6ff;
    }

    [data-theme="dark"] .cal-badge-horas {
        background: rgba(88, 166, 255, .15);
        color: #58a6ff;
    }

    [data-theme="dark"] .cal-header-cell {
        color: #8b949e;
    }
</style>

<main>
    <div class="container mt-3 mb-5">

        <!-- Breadcrumb -->
        <div class="d-flex align-items-center mb-1">
            <a href="<?= BASE_URL ?>inicio.php" class="text-muted mr-2" style="font-size:13px">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <span class="text-muted mr-2" style="font-size:13px">/</span>
            <span style="font-size:13px">Calendario</span>
        </div>
        <hr>

        <!-- Navegación mes -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="?mes=<?= $antMes ?>&anio=<?= $antAnio ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h5 class="mb-0 font-weight-bold">
                <?= $mesesNombres[$mes] ?> <?= $anio ?>
            </h5>
            <a href="?mes=<?= $sigMes ?>&anio=<?= $sigAnio ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <!-- Resumen del mes -->
        <?php
        $totalCierresMes = array_sum(array_column($porDia, 'cierres'));
        $totalHorasMes = array_sum(array_column($porDia, 'horas'));
        $diasActivos = count($porDia);
        ?>
        <div class="row mb-3">
            <div class="col-4 text-center">
                <div class="card shadow-sm py-2">
                    <div style="font-size:1.4rem;font-weight:700;color:#1ea31a"><?= $totalCierresMes ?></div>
                    <div style="font-size:11px;color:#888">Cierres</div>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="card shadow-sm py-2">
                    <div style="font-size:1.4rem;font-weight:700;color:#1a73e8">
                        <?= floor($totalHorasMes) ?>h
                    </div>
                    <div style="font-size:11px;color:#888">Horas</div>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="card shadow-sm py-2">
                    <div style="font-size:1.4rem;font-weight:700;color:#f59e0b"><?= $diasActivos ?></div>
                    <div style="font-size:11px;color:#888">Días activos</div>
                </div>
            </div>
        </div>

        <!-- Calendario -->
        <div class="card shadow-sm p-3">
            <div class="cal-grid mb-1">
                <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $d): ?>
                    <div class="cal-header-cell"><?= $d ?></div>
                <?php endforeach; ?>
            </div>

            <div class="cal-grid">
                <?php
                // Celdas vacías antes del primer día
                for ($v = 1; $v < $diaInicio; $v++):
                    ?>
                    <div class="cal-cell vacio"></div>
                <?php endfor; ?>

                <?php for ($dia = 1; $dia <= $diasEnMes; $dia++):
                    $fechaDia = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                    $info = $porDia[$dia] ?? null;
                    $esHoy = ($fechaDia === $hoyStr);
                    $clases = 'cal-cell';
                    if ($esHoy)
                        $clases .= ' hoy';
                    if ($info)
                        $clases .= ' tiene-datos';
                    ?>
                    <div class="<?= $clases ?>" <?= $info ? "onclick=\"abrirDia($dia)\"" : '' ?>>
                        <div class="cal-num"><?= $dia ?></div>
                        <?php if ($info): ?>
                            <div class="cal-badges mt-1">
                                <span class="cal-badge-cierres"><?= $info['cierres'] ?></span>
                                <span class="cal-badge-horas"><?= floor($info['horas']) ?>h</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="mt-2 d-flex" style="gap:14px;font-size:11px;color:#888">
                <span><span class="cal-badge-cierres">n</span> cierres del día</span>
                <span><span class="cal-badge-horas">nh</span> horas</span>
                <span style="border:1.5px solid #1a73e8;border-radius:6px;padding:0 5px">hoy</span>
            </div>
        </div>

    </div>
</main>

<!-- Modal detalle día -->
<div class="modal fade" id="modalDia" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalDiaTitulo"></h6>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-2" id="modalDiaCuerpo"></div>
        </div>
    </div>
</div>

<?php include '../plantilla/pie.php'; ?>

<script>
    var diasData = <?= $diasJson ?>;
    var mesTitulo = '<?= $mesesNombres[$mes] . ' ' . $anio ?>';

    function fmtH(h) {
        h = parseFloat(h) || 0;
        return Math.floor(h) + 'h ' + Math.round((h % 1) * 60) + 'm';
    }

    function abrirDia(dia) {
        var info = diasData[dia];
        if (!info) return;

        document.getElementById('modalDiaTitulo').textContent =
            dia + ' de ' + mesTitulo + ' — ' + info.cierres + ' cierre(s), ' + fmtH(info.horas);

        var html = '<div class="table-responsive"><table class="table table-sm mb-0" style="font-size:12px">'
            + '<thead class="thead-light"><tr>'
            + '<th>Responsable</th><th>Clase</th><th>Vehículo</th><th>Horario</th><th class="text-center">Horas</th>'
            + '</tr></thead><tbody>';

        function extraerHora(val) {
            if (!val) return null;
            var m = String(val).match(/(\d{1,2}):(\d{2})/);
            return m ? m[1].padStart(2, '0') + ':' + m[2] : null;
        }

        info.items.forEach(function (c) {
            var inicio = extraerHora(c.tmeHoraInicio);
            var fin    = extraerHora(c.tmeHoraFin);
            var horario = (inicio && fin) ? inicio + ' / ' + fin : '—';

            html += '<tr>'
                + '<td>' + (c.nombreTutor || '—') + '</td>'
                + '<td>' + (c.nombreClase || '—') + '</td>'
                + '<td>' + (c.strVehiculo || '—') + '</td>'
                + '<td>' + horario + '</td>'
                + '<td class="text-center font-weight-bold">' + (c.intCantHoras || 0) + '</td>'
                + '</tr>';
        });

        html += '</tbody></table></div>';
        document.getElementById('modalDiaCuerpo').innerHTML = html;

        $('#modalDia').modal('show');
    }
</script>

</body>

</html>