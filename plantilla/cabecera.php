<?php

// plantilla/cabecera.php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="es">

<style>
    .nav-link.active-custom {
        color: rgba(1, 129, 22, 1) !important;
    }

    nav {
        background-color: rgba(219, 206, 206, 0.20);
        box-shadow: 0 2px 4px rgba(207, 42, 42, 0.1);
    }

    .alertify-notifier .ajs-message.ajs-error {
        background-color: rgb(66, 66, 66) !important;
        color: #ffffff !important;
        font-size: 13px;
        text-align: center;
    }

    .alertify-notifier .ajs-message.ajs-success {
        background-color: rgb(0, 85, 11) !important;
        color: #ffffff !important;
        font-size: 13px;
        text-align: center;
    }

    .alertify-notifier .ajs-message.ajs-warning {
        background-color: rgb(255, 153, 0) !important;
        color: #ffffff !important;
        font-size: 13px;
        text-align: center;
    }

    .alertify-notifier .ajs-message {
        background-color: rgb(0, 85, 21) !important;
        color: #ffffff !important;
        font-size: 13px;
        text-align: center;
    }

    /* personaliza el confirm() solo la cabecera */
    .ajs-header {
        background-color: #31493bff !important;
        color: #ffffff !important;
        font-size: 14px;
        text-align: left;
    }

    .ajs-button {
        font-size: 12px !important;
        /* Cambia 14px por el tamaño que desees */
        border: none !important;
        /* Elimina el borde */
        padding: 5px 10px !important;
        /* Ajusta el padding según sea necesario */

    }
</style>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEA - Cierre</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- DataTables + integración con Bootstrap -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <!-- DataTables Buttons + integración con Bootstrap -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


    <!-- Alertify -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>librerias/alertify/css/alertify.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>librerias/alertify/css/themes/default.css">
    <script src="<?php echo BASE_URL; ?>librerias/alertify/alertify.min.js"></script>

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">





    <!--
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

     DataTables Buttons 
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
-->
    <!-- Dependencias de exportación 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>-->


    <style>
        .dropdown-toggle-no-caret::after {
            display: none !important;
        }

        body {
            background: linear-gradient(to right, rgb(255, 255, 255), rgba(201, 198, 196, 0.3));
        }

        hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid rgb(119, 173, 105);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg  navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>inicio.php">
                <img src="<?php echo BASE_URL; ?>img/LogoCeaSInTitulo.png" width="120" height="40"
                    class="d-inline-block align-top" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <a class="nav-link <?php echo ($current_page == 'nuevo.php') ? 'active-custom' : ''; ?>"
                        href="<?php echo BASE_URL; ?>cierre/nuevo.php">Nuevo </a>
                    <a class="nav-link <?php echo ($current_page == 'lista.php') ? 'active-custom' : ''; ?>"
                        href="<?php echo BASE_URL; ?>cierre/lista.php">Cierres </a>
                    <a class="nav-link <?php echo ($current_page == 'indicadores.php') ? 'active-custom' : ''; ?>"
                        href="<?php echo BASE_URL; ?>cierre/indicadores.php"> Indicadores </a>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="text-right mr-3">
                        <span class="d-block text-uppercase mb-0" style="font-size: 12px; color:#444444;">
                            <i class="fa-solid fa-user text-secondary"></i> <?php echo $_SESSION['strNombres']; ?>
                        </span>
                        <small class="d-block text-success" style="font-size: 12px; ">
                            <?php echo $_SESSION['strTipo']; ?>
                        </small>
                    </div>
                    <a id="btnCerrarSesion" class="nav-link active-custom fa-lg p-0"
                        href="<?php echo BASE_URL; ?>cerrar.php" title="Cerrar sesión">
                        <i class="fa-solid fa-power-off"></i>
                    </a>

                </div>

            </div>
        </div>
    </nav>
    <!-- <hr class="mt-0 bg-dark"> -->
    <hr class="mt-0 mb-0 bg-success">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#btnCerrarSesion").on("click", function (e) {
                e.preventDefault(); // evitar que vaya de una vez al cerrar.php

                alertify.confirm(
                    "Confirmación",
                    "¿Deseas cerrar la sesión?",
                    function () {
                        // si confirma
                        window.location.href = "<?php echo BASE_URL; ?>cerrar.php";
                    },
                    function () {
                        // si cancela
                        //alertify.error("Acción cancelada");
                    }
                ).set({ labels: { ok: "Sí", cancel: "No" }, padding: true });
            });
        });
    </script>