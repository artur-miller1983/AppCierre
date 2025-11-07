<!-- ./plantilla/pie.php -->

<style>
    footer {
        background-color: #31493bff;
        /* Verde oscuro elegante */
        color: #ddd;
        padding: 30px 0 20px;
        font-size: 14px;
        padding: 10px;
    }

    footer h5 {
        font-size: 16px;
        margin-bottom: 15px;
        color: #fff;
        font-weight: 600;
    }

    footer a {
        color: #ddd;
        text-decoration: none;
    }

    footer a:hover {
        color: #fff;
        text-decoration: underline;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 15px;
        margin-top: 20px;
        font-size: 13px;
        color: #bbb;
    }
</style>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6  mt-3">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="mb-0">&copy; Mas Conduccion</h5>
                </div>
                <p class="mb-2  small">
                    Sistema de Cierres Diarios del CEA.
                    Gestiona clases, exámenes e indicadores de forma rápida y centralizada.
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <p>&nbsp;</p>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>cierre/nuevo.php"><i class="fa-solid fa-plus"></i> Nuevo</a></li>
                    <li><a href="<?php echo BASE_URL; ?>cierre/lista.php"><i class="fa-solid fa-bars"></i> Cierres</a></li>
                    <li><a href="<?php echo BASE_URL; ?>cierre/indicadores.php"><i class="fa-solid fa-chart-line"></i> Indicadores</a></li>
                    <li><a id="cerrarsesion" href="cerrar.php"><i class="fa-solid fa-power-off"></i> Cerrar sesion</a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Línea inferior -->
        <div class="footer-bottom text-center mb-3">
            &copy; 2025 • ceamasconduccion. Todos los derechos reservados.
        </div>
    </div>
</footer>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#cerrarsesion").on("click", function (e) {
            e.preventDefault(); // evitar que vaya de una vez al cerrar.php

            alertify.confirm(
                "Confirmación",
                "¿Deseas cerrar la sesión?",
                function () {
                    // si confirma
                    window.location.href = "./cerrar.php";
                },
                function () {
                    // si cancela
                    //alertify.error("Acción cancelada");
                }
            ).set({ labels: { ok: "Sí", cancel: "No" }, padding: true });
        });
    });
</script>

<!-- jQuery (siempre primero) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>


<!-- Dependencias para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<!-- Dependencias de exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>


<!-- Alertify-->
<script src="./librerias/alertify/alertify.min.js"></script>

<!-- Select2-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

