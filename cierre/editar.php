<?php
require_once(__DIR__ . '/../config.php');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['strTipo'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['strTipo'] === 'Tutor') {
    header('Location: error.php');
    exit;
}

if (isset($_SESSION['strTutor'])) {

    $strTutor = $_SESSION['strTutor'];
    $nombreTutor = $_SESSION['strNombres'];
    $strPermiso = $_SESSION['strTipo'];

    // Obtener ID de clase desde GET
    $idCierre = isset($_GET['id']) ? $_GET['id'] : null;
    echo "<script>console.log('ID Cierre: " . htmlspecialchars($idCierre) . "');</script>";

    if (!$idCierre) {
        header('Location: lista.php');
        exit();
    }

    // Obtener datos de clase desde la API
    $dataCierre = @file_get_contents(URL_CIERRE_ID . $idCierre);
    $cierre = $dataCierre !== false ? json_decode($dataCierre, true) : null;

    //mostrar en consola el valor de $cierre
    echo "<script> console.log('Cierre Data: " . json_encode($cierre) . "');</script>";

    if ($cierre !== null) {

        $cierreTutor = trim($cierre['strTutor'] ?? '');
        $usuarioTutor = trim($strTutor ?? '');

        if ($strPermiso === 'Tutor') {
            header('Location:error.php');
            exit();
        }

        // 🚦 Si es Supervisor → solo puede editar sus cierres
        if ($strPermiso === 'Supervisor' && $cierreTutor !== $usuarioTutor) {
            header('Location: error.php');
            exit();
        }

          // ✅ Si pasa la validación, continúa el procesamiento
        $fecha = new DateTime($cierre['dteFecha']); // interpreta correctamente la fecha

        $fechaFormateadaCierre = $fecha->format('Y-m-d');
        $strTutorCierre = $cierre['strTutor']; // Asegurar que strTutor esté definido
        $strVehiculoCierre = $cierre['strVehiculo'] ?? ''; // Asegurar que strVehiculo esté definido
        $strTipoClaseCierre = $cierre['intTipoClase'] ?? ''; // Asegurar que intTipoClase esté definido       
        $tmeHoraInicioCierre = $cierre['tmeHoraInicio'] ?? '';
        $tmeHoraFinCierre = $cierre['tmeHoraFin'] ?? '';
        $strObservaciones = $cierre['strObservaciones'] ?? '';

        // Formatear hora de inicio y fin
        $horaInicioCierre = date('h:i A', strtotime($tmeHoraInicioCierre));
        $dtInicio = new DateTime($tmeHoraInicioCierre);

        // Extraer hora, minuto y AM/PM correctamente
        $horaInicio = $dtInicio->format('g');      // Hora en 12h sin cero inicial
        $minutoInicio = $dtInicio->format('i');    // Minuto con cero inicial
        $ampmInicio = $dtInicio->format('A');      // AM o PM

        //formatear hora de fin
        $horaFinCierre = date('h:i A', strtotime($tmeHoraFinCierre));
        $dtFin = new DateTime($tmeHoraFinCierre);

        // Extraer hora, minuto y AM/PM correctamente
        $horaFin = $dtFin->format('g');      // Hora en 12h sin cero inicial
        $minutoFin = $dtFin->format('i');    // Minuto con cero inicial
        $ampmFin = $dtFin->format('A');      // AM o PM

        $intCantHoras = $cierre['intCantHoras'] ?? 0; // Asegurar que intCantHoras esté definido
        $intCantMin = $cierre['intCantMinutos'] ?? 0; // Asegurar que intCantMin esté definido

        // Extraer horas y minutos de la hora de fin

        // muestrame horaInicio po console
        echo "<script>console.log('Hora Inicio: " . htmlspecialchars($horaFin) . "');</script>";


    } else {
        $cierre = null;
    }


    if (!$cierre) {
        echo "<script>alertify.error('Cierre no encontrada'); window.location.href='cierres.php';</script>";
        exit();
    }

    // Obtener tipos y vehículos
    $dataTipoClases = @file_get_contents(URL_TIPO_CLASES);
    $tipos = $dataTipoClases !== false ? json_decode($dataTipoClases, true) : [];

    $dataVehiculos = @file_get_contents(URL_VEHICULOS);
    $vehiculos = $dataVehiculos !== false ? json_decode($dataVehiculos, true) : [];

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

        .extra-small {
            display: flex;
        }
      

        .letra-pequena {
            font-size: 10px !important;
            border: none !important;
            color: #006e25ff !important;
        }

        .small-textarea {
            font-size: 0.8rem;
            /* Ajusta el tamaño según quieras */
        }
    </style>

    <main>
        <div class="container text-secondary ">
            <div class="card mt-3">
                <div class="card-header"><i class="fa-solid fa-edit"></i> Editar Cierre</div>
                <div class="card-body">

                    <form id="formEditarCierre" name="formEditarCierre" class="mb-3">
                        <input type="hidden" name="intIDCierre" id="intIDCierre" value="<?= htmlspecialchars($idCierre) ?>">
                        <input type="hidden" name="strTutor" id="strTutor" value="<?= htmlspecialchars($strTutorCierre) ?>">

                        <div class="form-row small">

                            <div class="col-md-3">
                                <label for="dteFecha">Fecha</label>
                                <input type="date" class="form-control form-control-sm" id="dteFecha" name="dteFecha"
                                    required value="<?= $fechaFormateadaCierre ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="intTipoClase">Tipo Clase</label>
                                <select name="intTipoClase" class="form-control form-control-sm " id="intTipoClase"
                                    required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= $tipo['idTipo'] ?>" <?= $tipo['idTipo'] == $strTipoClaseCierre ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo['strDescripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="vehiculo">Vehículo</label>
                                <select id="vehiculo" name="vehiculo" class="form-control form-control-sm " required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <option value="<?= htmlspecialchars($vehiculo['strPlaca']) ?>"
                                            <?= trim(strtoupper($vehiculo['strPlaca'])) === trim(strtoupper($strVehiculoCierre)) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($vehiculo['strPlaca']) ?>
                                        </option>

                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row mt-3 small">

                            <div class="col-md-3 d-flex justify-content-center align-items-center">
                                <div class="text-center w-100 text-secondary ">
                                    <i class="fa-regular fa-calendar text-success"></i> Horario
                                </div>
                            </div>

                            <!-- Hora Inicio -->
                            <div class="col-md-3">
                                <label for="horaInicio">Inicio</label>
                                <div class="form-group d-flex align-items-center">
                                    <select class="form-control form-control-sm mr-1" id="horaInicio" required>
                                        <?php for ($h = 1; $h <= 12; $h++): ?>
                                            <option value="<?= $h ?>" <?= ($horaInicio == $h) ? 'selected' : '' ?>>
                                                <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>

                                    <input type="number" class="form-control form-control-sm mr-1" id="minInicio" min="0"
                                        max="59" value="<?= $minutoInicio ?>" required>

                                    <select class="form-control form-control-sm" id="ampmInicio" required>
                                        <option value="AM" <?= $ampmInicio === 'AM' ? 'selected' : '' ?>>AM</option>
                                        <option value="PM" <?= $ampmInicio === 'PM' ? 'selected' : '' ?>>PM</option>
                                    </select>
                                </div>
                            </div>


                            <!-- Hora Fin -->

                            <div class="col-md-3">
                                <label for="horaFin">Fin</label>
                                <div class="form-group d-flex align-items-center">
                                    <select class="form-control form-control-sm mr-1" id="horaFin" required>
                                        <?php for ($h = 1; $h <= 12; $h++): ?>
                                            <option value="<?= $h ?>" <?= ($horaFin == $h) ? 'selected' : '' ?>>
                                                <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>

                                    <input type="number" class="form-control form-control-sm mr-1" id="minFin" min="00"
                                        max="59" value="<?= $minutoFin ?>" required>

                                    <select class="form-control form-control-sm" id="ampmFin" required>
                                        <option value="AM" <?= $ampmFin === 'AM' ? 'selected' : '' ?>>AM</option>
                                        <option value="PM" <?= $ampmFin === 'PM' ? 'selected' : '' ?>>PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Duración</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control form-control-sm mr-2" name="intCantHoras"
                                        id="intCantHoras" placeholder="Horas" readonly value="<?= $intCantHoras ?>">
                                    <input type="text" class="form-control form-control-sm" name="intCantMin"
                                        id="intCantMin" placeholder="Minutos" readonly value="<?= $intCantMin ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-row mt-2 small">
                            <div class="col-md-3 d-flex justify-content-center align-items-center">
                                <div class="text-center w-100 text-secondary ">

                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="observaciones">
                                        <i class="fa-solid fa-comment"></i> Novedad
                                        <span class="letra-pequena">( Maximo 200 caracteres )</span>
                                    </label>
                                    <textarea id="strObservaciones" name="strObservaciones"
                                        class="form-control small-textarea" rows="2"
                                        maxlength="200"><?= $strObservaciones ? htmlspecialchars(trim($strObservaciones)) : '' ?></textarea>
                                </div>
                            </div>

                        </div>

                        <div class="form-group d-flex justify-content-end mt-2">
                            <a href="lista.php" class="btn btn-sm btn-outline-secondary mt-2 mr-2">Atrás</a>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-2">Guardar Cambios</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar Select2 sin jQuery
            const selectVehiculo = document.querySelector('.select-vehiculo');
            if (selectVehiculo && typeof Select2 !== 'undefined') {
                new Select2(selectVehiculo, {
                    placeholder: "Seleccione",
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: () => "No existe",
                        removeAllItems: () => "Eliminar",
                        removeItem: () => "Eliminar"
                    }
                });
            }

            // Asociar eventos de cambio para calcular horas
            const camposHora = ['horaInicio', 'minInicio', 'ampmInicio', 'horaFin', 'minFin', 'ampmFin'];
            camposHora.forEach(id => {
                document.getElementById(id).addEventListener('change', calcularHoras);
            });

            // Validar y enviar formulario
            document.getElementById('formEditarCierre').addEventListener('submit', function (e) {
                e.preventDefault();

                if (!document.getElementById('dteFecha').value) {
                    alertify.error('Seleccione la Fecha');
                    return;
                }

                if (!document.getElementById('intTipoClase').value) {
                    alertify.error('Seleccione el tipo de clase');
                    return;
                }

                if (!document.getElementById('vehiculo').value) {
                    alertify.error('Seleccione el vehículo');
                    return;
                }

                if (!document.getElementById('intCantHoras').value) {
                    alertify.error('Debe seleccionar la cantidad en horas');
                    return;
                }
                if (!document.getElementById('strObservaciones').value) {
                    alertify.error('Ingrese la Novedad');
                    document.getElementById('strObservaciones').focus();
                    return;
                }

                alertify.confirm(
                    'AVISO',
                    '¿ Desea guardar cambios ?',
                    () => enviarDatos(),
                    () => { }
                ).set('labels', { ok: 'Guardar', cancel: 'Cancelar' });
            });
        });


        // Enviar datos del formulario
        function enviarDatos() {

            //el valor id
            const intIDCierre = document.getElementById('intIDCierre').value;
            const dteFecha = document.getElementById('dteFecha').value;
            const intTipoClase = parseInt(document.getElementById('intTipoClase').value);
            const strVehiculo = document.getElementById('vehiculo').value.trim();;
            const strObservaciones = document.getElementById('strObservaciones').value.trim();

            const tmeHoraInicio = convertirHoraToTimeStr(
                document.getElementById('horaInicio').value,
                document.getElementById('minInicio').value,
                document.getElementById('ampmInicio').value
            );

            const tmeHoraFin = convertirHoraToTimeStr(
                document.getElementById('horaFin').value,
                document.getElementById('minFin').value,
                document.getElementById('ampmFin').value
            );

            const intCantHoras = parseFloat(document.getElementById('intCantHoras').value);
            const intCantMinutos = parseFloat(document.getElementById('intCantMin').value);

            const formData = {
                intIDCierre,
                dteFecha,
                intTipoClase,
                strVehiculo,
                tmeHoraInicio,
                tmeHoraFin,
                intCantHoras,
                intCantMinutos,
                strObservaciones
            };

            const URL_EDITAR_CIERRE = "<?php echo URL_EDITAR_CIERRE; ?>";
            fetch(URL_EDITAR_CIERRE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            })
                .then(response => {
                    if (!response.ok) throw new Error('Error al insertar cierre');
                    return response.text();
                })
                .then(data => {
                    alertify.success(data);
                    //carga a cierres.php
                    setTimeout(() => {
                        window.location.href = 'lista.php';
                    }, 1000);


                })
                .catch(error => {
                    alertify.error('Error al insertar el cierre');
                    console.error(error);
                });
        }

        // Convierte hora, minutos y AM/PM a minutos totales
        function parseToMinutes(hour, minutes, ampm) {
            hour = parseInt(hour);
            minutes = parseInt(minutes);

            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;

            return hour * 60 + minutes;
        }

        // Calcular duración entre dos horas
        function calcularHoras() {
            const hInicio = document.getElementById('horaInicio').value;
            const mInicio = document.getElementById('minInicio').value;
            const ampmInicio = document.getElementById('ampmInicio').value;

            const hFin = document.getElementById('horaFin').value;
            const mFin = document.getElementById('minFin').value;
            const ampmFin = document.getElementById('ampmFin').value;

            const minInicio = parseToMinutes(hInicio, mInicio, ampmInicio);
            const minFin = parseToMinutes(hFin, mFin, ampmFin);

            let totalMin = minFin - minInicio;

            if (totalMin < 60) {
                document.getElementById('intCantHoras').value = '';
                document.getElementById('intCantMin').value = '';
                alertify.error('Hora final debe ser al menos una hora después de la inicial.');
                return;
            }

            const horas = Math.floor(totalMin / 60);
            const minutos = totalMin % 60;

            document.getElementById('intCantHoras').value = horas;
            document.getElementById('intCantMin').value = minutos;
        }

        // Convierte hora y minutos con AM/PM a formato HH:mm:ss
        function convertirHoraToTimeStr(hour, minutes, ampm) {
            hour = parseInt(hour);
            minutes = parseInt(minutes);

            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;

            return `${hour.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
        }




    </script>




    <?php
    include '../plantilla/pie.php';
} else {
    header('Location: index.php');
    exit();
}
?>