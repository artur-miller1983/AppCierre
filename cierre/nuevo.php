<?php
require_once(__DIR__ . '/../config.php');
session_start();

// Verificar si el usuario está autenticado
if (isset($_SESSION['strTutor'])) {
   
    $strTutor = $_SESSION['strTutor'];
    $nombreTutor = $_SESSION['strNombres'];
    $strTipo = $_SESSION['strTipo'];
   

    $dataTipoClases = @file_get_contents(URL_TIPO_CLASES);
    if ($dataTipoClases !== false) {
        $tipos = json_decode($dataTipoClases, true);
    } else {
        $tipos = [];
    }

    $dataVehiculos = @file_get_contents(URL_VEHICULOS);
    if ($dataVehiculos !== false) {
        $vehiculos = json_decode($dataVehiculos, true);
    } else {
        $vehiculos = [];
    }

    ?>

  <?php include __DIR__ . '/../plantilla/cabecera.php'; ?>

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

        <div class="container text-secondary ">

            <div class="card mt-3 mb-2">
                <div class="card-header ">
                  <i class="fa-solid fa-plus"></i>  Nuevo Cierre
                </div>
                <div class="card-body ">

                    <form id="formAgregarClase" name="formAgregarClase" class="mb-3">
                        <input type="hidden" id="strTutor" name="strTutor" 
                            value="<?php echo trim($strTutor); ?> " readonly>

                        <div class="form-row">

                            <div class="col-md-3">
                                <label for="dteFecha">Fecha</label>
                                <input type="date" class="form-control form-control-sm" 
                                    id="dteFecha" name="dteFecha"
                                    required value="<?php echo date('Y-m-d'); ?>">
                            </div>


                            <div class="col-md-6">
                                <label for="intTipoClase">Tipo Clase</label>
                                <select name="intTipoClase" class="form-control form-control-sm" id="intTipoClase" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= $tipo['idTipo'] ?>"><?= htmlspecialchars($tipo['strDescripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="vehiculo">Vehículo</label>
                                <select id="vehiculo" name="vehiculo" class="form-control form-control-sm select-vehiculo"
                                    required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <option value="<?= htmlspecialchars($vehiculo['strPlaca']) ?>">
                                            <?= htmlspecialchars($vehiculo['strPlaca']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="form-row mt-3">
                            <div class="col-md-3 d-flex justify-content-center align-items-center">
                                <div class="text-center w-100 text-secondary ">
                                    <i class="fa-regular fa-calendar text-success"></i> Horario
                                </div>
                            </div>

                            <!-- Hora de Inicio -->
                            <div class="col-md-3">
                                <label for="horaInicio">Inicio</label>
                                <div class="form-group d-flex align-items-center">
                                    <select class="form-control form-control-sm mr-1" id="horaInicio" required>
                                        <?php for ($h = 1; $h <= 12; $h++): ?>
                                            <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= $h ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <input type="number" class="form-control form-control-sm mr-1" id="minInicio" min="0"
                                        max="59" value="00" required>
                                    <select class="form-control form-control-sm" id="ampmInicio" required>
                                        <option value="AM" selected>AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Hora de Fin -->
                            <div class="col-md-3">
                                <label for="horaFin">Fin</label>
                                <div class="form-group d-flex align-items-center">
                                    <select class="form-control form-control-sm mr-1" id="horaFin" required>
                                        <?php for ($h = 1; $h <= 12; $h++): ?>
                                            <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= $h ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <input type="number" class="form-control form-control-sm mr-1" id="minFin" min="0"
                                        max="59" value="00" required>
                                    <select class="form-control form-control-sm" id="ampmFin" required>
                                        <option value="AM">AM</option>
                                        <option value="PM" selected>PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>Duración</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control form-control-sm mr-2" name="intCantHoras"
                                        id="intCantHoras" placeholder="Horas" readonly>
                                    <input type="text" class="form-control form-control-sm" name="intCantMin"
                                        id="intCantMin" placeholder="Minutos" readonly>
                                </div>
                            </div>

                        </div>

                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-outline-success mr-2 mt-2">Guardar</button>
                            <button type="button" onclick="limpiarFormulario()" class="btn btn-sm btn-outline-secondary mt-2 ">Limpiar</button>
                            
                        </div>

                </div>
                </form>


            </div>
        </div>

    </main>
   <?php include __DIR__ . '/../plantilla/pie.php'; ?>

    <script>


        $(document).ready(function () {
            $('.select-vehiculo').select2({
                placeholder: "Seleccione",
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

        function parseToMinutes(hour, minutes, ampm) {
            hour = parseInt(hour);
            minutes = parseInt(minutes);

            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;

            return hour * 60 + minutes;
        }

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

            // Dividir totalMin en horas y minutos
            const horas = Math.floor(totalMin / 60);
            const minutos = totalMin % 60;

            // Asignar a los campos separados
            document.getElementById('intCantHoras').value = horas;
            document.getElementById('intCantMin').value = minutos;
        }

        // Detectar cambios y recalcular
        ['horaInicio', 'minInicio', 'ampmInicio', 'horaFin', 'minFin', 'ampmFin'].forEach(id => {
            document.getElementById(id).addEventListener('change', calcularHoras);
        });

        function convertirHoraToTimeStr(hour, minutes, ampm) {
            hour = parseInt(hour);
            minutes = parseInt(minutes);
            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;
            return `${hour.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
        }


        document.getElementById('formAgregarClase').addEventListener('submit', function (e) {
            e.preventDefault();
            if (document.getElementById('dteFecha').value === '') {
                alertify.error('Seleccione la Fecha');
                return;
            }
            // Validar campos primero, si hay errores, se sale
            if (document.getElementById('intTipoClase').value === '') {
                alertify.error('Seleccione el tipo de clase');
                return;
            }
            if (document.getElementById('vehiculo').value === '') {
                alertify.error('Seleccione el vehículo');
                return;
            }
            if (document.getElementById('horaInicio').value === '' || document.getElementById('minInicio').value === '' || document.getElementById('ampmInicio').value === '') {
                alertify.error('Seleccione la hora de inicio');
                return;
            }
            if (document.getElementById('horaFin').value === '' || document.getElementById('minFin').value === '' || document.getElementById('ampmFin').value === '') {
                alertify.error('Seleccione la hora de fin');
                return;
            }
            if (document.getElementById('intCantHoras').value === '') {
                alertify.error('Debe seleccionar la cantidad en horas');
                return;
            }

            // Mostrar confirmación con alertify.confirm
            alertify.confirm(
                'AVISO',
                '¿ Desea guardar el Cierre ?',
                function () {
                    // El usuario aceptó
                    enviarDatos();
                },
                function () {
                    // El usuario canceló, no hacemos nada
                }
            ).set('labels', { ok: 'Guardar', cancel: 'Cancelar' })
        });

        function enviarDatos() {
            // Prepara los datos igual que antes
            const dteFecha = document.getElementById('dteFecha').value;
            const intTipoClase = parseInt(document.getElementById('intTipoClase').value);
            const strTutor = document.getElementById('strTutor').value.trim();
            const strVehiculo = document.getElementById('vehiculo').value.trim();

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

            const intCantHoras = parseInt(document.getElementById('intCantHoras').value);
            const intCantMinutos = parseInt(document.getElementById('intCantMin').value);

            const formData = {
                dteFecha,
                intTipoClase,
                strTutor,
                strVehiculo,
                tmeHoraInicio,
                tmeHoraFin,
                intCantHoras,
                intCantMinutos
            };


            // mostrame por consola js los datos antes de enviar para revisar
            console.log('Datos a enviar:', formData);

            const URL_INSERTAR_CIERRE = "<?php echo URL_INSERTAR_CIERRE; ?>";
            fetch(URL_INSERTAR_CIERRE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            })
                .then((response) => {
                    if (!response.ok) throw new Error('Error al insertar cierre');
                    return response.text();
                })
                .then((data) => {
                    alertify.success(data);
                    limpiarFormulario();
                })
                .catch((error) => {
                    alertify.error('Error al insertar el cierre');
                    console.error(error);
                });
        }

        function limpiarFormulario() {
            // Resetea el formulario HTML (limpia inputs, selects, textareas)
            document.getElementById('formAgregarClase').reset();

            // Si usas Select2 o cualquier plugin, reinícialo, ejemplo para Select2:
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#vehiculo').val(null).trigger('change'); // Esto limpia select2 correctamente
            }
        }

    </script>

    </body>

    </html>
    <?php


} else {
    // Si el usuario no está autenticado, redirigir al login
    header('Location: ./index.php');
    exit();
}
?>