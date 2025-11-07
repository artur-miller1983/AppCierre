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



 /* ===================================================
   🌐 BLOQUE ACTUAL SIN SSL (ACTIVO)
   =================================================== */
$contextNoSSL = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
]);   
$dataClases = ($Tipo === 'Instructor' || $Tipo === 'Supervisor')
    ? @file_get_contents(URL_CIERRES . '?strTutor=' . urlencode($strTutor), false, $contextNoSSL)
    : @file_get_contents(URL_CIERRES, false, $contextNoSSL);

    // =================================================== */
    

$response = $dataClases !== false ? json_decode($dataClases, true) : [];

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

        <?php if (!empty($response)): ?>
            <div class="table-responsive mb-3" id="seleccionado">
                <table class="table table-hover " id="tablaCierres">
                    <thead class="text-success bg-light">
                        <tr>
                            <th class="small">Fecha</th>
                            <th class="small">Clase</th>
                            <th class="small">Tutor</th>
                            <th class="small">Vehículo</th>
                            <th class="small">Horario</th>
                            <th class="small">Duración</th>
                            <th class="small"></th>
                        </tr>
                    </thead>
                    <tbody class="">
                        <?php foreach ($response as $data): ?>
                            <tr class="text-secondary">
                                <td class="extra-small">
                                    <?php
                                    $fecha = new DateTime($data['dteFecha']); // interpreta la fecha
                                    $mesEspanol = obtenerMesEspanol($fecha->format('F'));
                                    echo $fecha->format('d') . '/' . $mesEspanol . '/' . $fecha->format('Y');
                                    ?>
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

                                <td class="extra-small">
                                    <?php echo $data['intCantHoras'] . "Hr / " . $data['intCantMinutos'] . 'Mn'; ?>
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
                    "first": "<<",
                    "last": ">>",
                    "next": ">",
                    "previous": "<"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar ascendente",
                    "sortDescending": ": activar para ordenar descendente"
                }
            },
            responsive: true,
            order: [[0, 'desc']],
            pagingType: "first_last_numbers",
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
    });

</script>