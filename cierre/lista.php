<?php
require_once(__DIR__ . '/../config.php');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['strTutor'])) {
    header('Location: login.php');
    exit;
}

$strTutor = $_SESSION['strTutor'];
$nombreTutor = $_SESSION['strNombres'];
$Tipo = $_SESSION['strTipo'];



 /* ===================================================
   🌐 BLOQUE ACTUAL CON SSL (ACTIVO)
   =================================================== */
// $dataClases = ($Tipo === 'Instructor' || $Tipo === 'Supervisor')
//     ? @file_get_contents(URL_CIERRES . '?strTutor=' . urlencode($strTutor))
//     : @file_get_contents(URL_CIERRES);
// =================================================== */



$urlCierres = ($Tipo === 'Instructor' || $Tipo === 'Auxiliar' || $Tipo === 'Supervisor')
    ? URL_CIERRES . '?strTutor=' . urlencode($strTutor)
    : URL_CIERRES;
$dataClases = apiGet($urlCierres);
$response = $dataClases !== '' ? json_decode($dataClases, true) : [];

include '../plantilla/cabecera.php';

function obtenerMesEspanol($mes)
{
    $mesesIngles = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $mesesEspanol = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return str_replace($mesesIngles, $mesesEspanol, $mes);
}
?>

<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
    }

    main {
        flex: 1;
    }

    .extra-small {
        font-size: 12px;
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

    /* ---- Cards modo móvil ---- */
    #cardContainer { display: none; }

    @media (max-width: 767px) {
        table#tablaCierres { display: none !important; }
        #cardContainer    { display: block !important; }
    }

    .cierre-card { border-radius: 10px; }
    .cierre-card .card-body { padding: 0.6rem 0.75rem; }

    /* compacto: 2 por fila */
    .cards-2col .cierre-card .card-body { padding: 0.4rem 0.5rem; }
    .cards-2col .cierre-card .card-fecha { font-size: 10px; }
    .cards-2col .cierre-card .card-clase { font-size: 11px; }
    .cards-2col .cierre-card .card-tutor { font-size: 10px; }
    .cards-2col .cierre-card .card-meta  { font-size: 10px; }
    .cards-2col .cierre-card .card-acciones { font-size: 12px; }
</style>

<main>
    <div class="container p-2">

        <div class="row mb-3 mt-2 align-items-center">
            <div class="col">
                <span class="text-secondary">
                    <i class="fa-solid fa-bars"></i> Lista de Cierres
                </span>
            </div>
            <div class="col-auto text-end" id="contenedor-boton"></div>
        </div>

        <!-- Filtros rápidos por fecha -->
        <div class="mb-2">
            <div class="btn-group btn-group-sm" role="group" id="filtros-rapidos">
                <button type="button" class="btn btn-secondary filtro-btn" data-filtro="todos">
                    <i class="fa-solid fa-list mr-1"></i>Todos
                </button>
                <button type="button" class="btn btn-outline-secondary filtro-btn" data-filtro="hoy">
                    <i class="fa-solid fa-calendar-day mr-1"></i>Hoy
                </button>
                <button type="button" class="btn btn-outline-secondary filtro-btn" data-filtro="semana">
                    <i class="fa-solid fa-calendar-week mr-1"></i>Esta semana
                </button>
                <button type="button" class="btn btn-outline-secondary filtro-btn" data-filtro="mes">
                    <i class="fa-solid fa-calendar mr-1"></i>Este mes
                </button>
            </div>
        </div>

        <?php if (!empty($response)): ?>
            <div id="cardContainer"></div>

            <div class="table-responsive mb-3" id="seleccionado">
                <table class="table table-hover " id="tablaCierres">
                    <thead class="text-success bg-light">
                        <tr>
                            <th class="small">Fecha</th>
                            <th class="small">Clase</th>
                            <th class="small">Responsable</th>
                            <th class="small">Vehículo</th>
                            <th class="small">Horario</th>
                            <th class="small">Duración</th>
                            <th class="small"></th>
                        </tr>
                    </thead>
                    <tbody class="">
                        <?php foreach ($response as $data): ?>
                            <tr class="text-secondary">
                               <td class="extra-small"
                                <?php
                                    $fecha = new DateTime($data['dteFecha']);
                                    $orden = $fecha->format('Ymd'); // para ordenar correctamente
                                ?>
                                data-order="<?= $orden ?>">
                                <?= $fecha->format('d/m/Y') ?>
                                </td>

                                <td class="extra-small"><?php echo $data['nombreClase']; ?></td>
                                <td class="extra-small"><?php echo $data['nombreTutor']; ?></td>
                                <td class="extra-small"><?php echo $data['strVehiculo']; ?></td>
                                <td class="extra-small">
                                    <?php
                                    if (!empty($data['tmeHoraInicio']) && !empty($data['tmeHoraFin'])) {
                                        $horaInicio = new DateTime($data['tmeHoraInicio']);
                                        $horaFin = new DateTime($data['tmeHoraFin']);
                                        echo $horaInicio->format('g:i A') . ' / ' . $horaFin->format('g:i A');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td class="small text-center">
                                    <?php echo $data['intCantHoras']?>
                                </td>

                                <td class="extra-small">
                                    <a href="editar.php?id=<?php echo $data['intIDCierre']; ?>" class="mr-1 text-success"
                                        title="Editar Cierre">
                                        <i class="fas fa-edit fa-lg"></i>
                                    </a>
                                    <?php if ($Tipo !== 'Instructor'): ?>
                                        <a href="#" class="text-danger eliminar-cierre"
                                            data-id="<?php echo $data['intIDCierre']; ?>" title="Eliminar Cierre">
                                            <i class="fas fa-trash-alt fa-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header bg-secondary text-white small">0 Resultados</div>
                <div class="card-body">
                    <p class="text-center small">No hay cierres por mostrar.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include '../plantilla/pie.php'; ?>

<script>
    // Eliminar Cierre
    document.addEventListener('click', e => {
        const el = e.target.closest('.eliminar-cierre');
        if (!el) return;
        e.preventDefault();

        const id = el.dataset.id;
        const URL_ELIMINAR_CIERRE = <?php echo json_encode(URL_ELIMINAR_CIERRE); ?>;

        alertify.confirm('AVISO', '¿Desea eliminar el cierre?', () => {
            fetch(URL_ELIMINAR_CIERRE + id, { method: 'DELETE' })
                .then(res => {
                    if (res.ok) {
                        el.closest('tr').remove();
                        alertify.success('Cierre eliminado con éxito');
                    } else {
                        alertify.error('Error al eliminar cierre');
                    }
                })
                .catch(() => alertify.error('Error de conexión'));
        }, () => { }).set('labels', { ok: 'Sí', cancel: 'No' });
    });

    // DataTable
    $(document).ready(function () {
        var tabla = $('#tablaCierres').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay información disponible",
                "info": "_START_ de _END_ Registros",
                "infoEmpty": "0 a 0 de 0 Registros",
                "infoFiltered": "(_MAX_ Filtrados)",
                "lengthMenu": "Mostrar _MENU_",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar  ",
                "zeroRecords": "No se encontraron coincidencias",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar ascendente",
                    "sortDescending": ": activar para ordenar descendente"
                }
            },
            responsive: true,
            order: [[0, 'DESC']],
            pagingType: "full_numbers",
            pageLength: 10,
            lengthMenu: [10, 50],
            columnDefs: [               
                { targets: [6], orderable: false }               
            ],
            dom:
                // fila 1: selector izquierda + buscador derecha
                '<"row mb-2"<"col-sm-6"l><"col-sm-6 text-end"f>>' +
                // fila 2: botones exportación derecha
                '<"row mb-2"<"col-sm-12 text-end"B>>' +
                // tabla
                'rt' +
                // fila 3: info izquierda + paginación derecha
                '<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 text-end"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Exportar',
                    className: 'btn btn-secondary btn-sm',
                    action: function (e, dt, button, config) {
                        var self = this; // guardar contexto
                        var originalAction = $.fn.dataTable.ext.buttons.excelHtml5.action;

                        alertify.confirm(
                            'Confirmación',
                            '¿Desea exportar los datos a Excel?',
                            function () { // OK
                                originalAction.call(self, e, dt, button, config);
                                alertify.success('Exportando...');
                            },
                            function () { // Cancelar

                            }
                        ).set('labels', { ok: 'Guardar', cancel: 'Cancelar' });
                    }
                }
            ]
        });

        tabla.buttons().container().appendTo('#contenedor-boton');

        // Mueve el cardContainer dentro del wrapper de DataTables,
        // justo después de la tabla, para que quede entre controles y paginación
        $('#tablaCierres_wrapper').find('table').after($('#cardContainer'));

        function renderizarCards() {
            var pageLen = tabla.page.len();
            var esCompacto = pageLen >= 50;
            var colClass   = esCompacto ? 'col-6 px-1' : 'col-12';
            var rowClass   = esCompacto ? 'row mx-0 cards-2col' : 'row mx-0';
            var html = '';

            tabla.rows({ page: 'current' }).every(function () {
                var celdas   = $(this.node()).find('td');
                var fecha    = $(celdas[0]).text().trim();
                var clase    = $(celdas[1]).text().trim();
                var tutor    = $(celdas[2]).text().trim();
                var vehiculo = $(celdas[3]).text().trim();
                var horario  = $(celdas[4]).text().trim();
                var duracion = $(celdas[5]).text().trim();
                var acciones = $(celdas[6]).html();

                html += `
                <div class="${colClass} mb-2">
                    <div class="card cierre-card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-secondary card-fecha">${fecha}</span>
                                <div class="card-acciones">${acciones}</div>
                            </div>
                            <div class="font-weight-bold card-clase text-truncate" title="${clase}">${clase}</div>
                            <div class="text-muted card-tutor text-truncate" title="${tutor}">${tutor}</div>
                            <div class="d-flex flex-wrap card-meta mt-1" style="gap:6px">
                                <span><i class="fa-solid fa-car text-secondary mr-1"></i>${vehiculo}</span>
                                <span><i class="fa-solid fa-clock text-secondary mr-1"></i>${horario}</span>
                                <span class="ml-auto font-weight-bold">${duracion}h</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            $('#cardContainer').html('<div class="' + rowClass + '">' + html + '</div>');
        }

        // Dibuja cards en cada actualización de DataTables
        tabla.on('draw', function () {
            renderizarCards();
        });

        // Dibuja la primera vez
        renderizarCards();

        // ── Filtros rápidos por fecha ────────────────────────────
        var rangoFechas = null;
        var hoyBase = new Date(); hoyBase.setHours(0, 0, 0, 0);

        // Función de búsqueda personalizada sobre la columna Fecha (data-order="YYYYMMDD")
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tablaCierres' || !rangoFechas) return true;
            var ordenStr = ($(tabla.row(dataIndex).node()).find('td:first').data('order') + '');
            if (ordenStr.length !== 8) return true;
            var fecha = new Date(
                parseInt(ordenStr.substr(0, 4)),
                parseInt(ordenStr.substr(4, 2)) - 1,
                parseInt(ordenStr.substr(6, 2))
            );
            return fecha >= rangoFechas.inicio && fecha <= rangoFechas.fin;
        });

        function activarFiltroBtn(el) {
            $('.filtro-btn').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $(el).removeClass('btn-outline-secondary').addClass('btn-secondary');
        }

        $('.filtro-btn').on('click', function () {
            activarFiltroBtn(this);
            var tipo = $(this).data('filtro');

            if (tipo === 'todos') {
                rangoFechas = null;
            } else if (tipo === 'hoy') {
                rangoFechas = { inicio: hoyBase, fin: hoyBase };
            } else if (tipo === 'semana') {
                var ini = new Date(hoyBase);
                var dow = ini.getDay() || 7;        // lunes = 1
                ini.setDate(ini.getDate() - dow + 1);
                var fin = new Date(ini);
                fin.setDate(ini.getDate() + 6);
                rangoFechas = { inicio: ini, fin: fin };
            } else if (tipo === 'mes') {
                var ini = new Date(hoyBase.getFullYear(), hoyBase.getMonth(), 1);
                var fin = new Date(hoyBase.getFullYear(), hoyBase.getMonth() + 1, 0);
                rangoFechas = { inicio: ini, fin: fin };
            }

            tabla.draw();
        });
    });

</script>