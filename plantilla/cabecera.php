<?php

// plantilla/cabecera.php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="es">

<!-- Aplica el tema antes de que el navegador pinte cualquier cosa (evita parpadeo) -->
<script>
(function () {
    var t = localStorage.getItem('appTheme') || 'light';
    if (t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
})();
</script>

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
        background-color: rgba(133, 48, 48, 1) !important;
        color: #ffffff !important;
        font-size: 13px;
        text-align: center;
    }

    .alertify-notifier .ajs-message {
        background-color: rgba(43, 49, 45, 1) !important;
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

        /* ===== MODO OSCURO ===== */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0d1117 0%, #161b22 100%) !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] nav,
        [data-theme="dark"] .navbar {
            background-color: #161b22 !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.5) !important;
        }
        [data-theme="dark"] .nav-link {
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .navbar-toggler {
            border-color: #30363d !important;
        }
        [data-theme="dark"] .navbar-toggler-icon {
            filter: invert(1);
        }
        [data-theme="dark"] .card {
            background-color: #1c2128 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .card-header {
            background-color: #21262d !important;
            border-bottom-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .card-footer {
            background-color: #21262d !important;
            border-top-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .table {
            color: #c9d1d9 !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .table th,
        [data-theme="dark"] .table td {
            border-color: #30363d !important;
        }
        [data-theme="dark"] .table thead th {
            background-color: #21262d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255,255,255,0.04) !important;
        }
        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(56,139,253,0.08) !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .form-control {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .form-control:focus {
            background-color: #161b22 !important;
            border-color: #58a6ff !important;
            color: #c9d1d9 !important;
            box-shadow: 0 0 0 0.2rem rgba(88,166,255,0.2) !important;
        }
        [data-theme="dark"] .form-control::placeholder { color: #484f58 !important; }
        [data-theme="dark"] .form-control:disabled,
        [data-theme="dark"] .form-control[readonly] {
            background-color: #161b22 !important;
            color: #6e7681 !important;
        }
        [data-theme="dark"] .custom-select {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] label { color: #c9d1d9 !important; }
        [data-theme="dark"] .input-group-text {
            background-color: #21262d !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #8b949e transparent transparent transparent !important;
        }
        [data-theme="dark"] .select2-dropdown {
            background-color: #1c2128 !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .select2-search--dropdown .select2-search__field {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .select2-container--default .select2-results__option {
            color: #c9d1d9 !important;
            background-color: transparent !important;
        }
        [data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected],
        [data-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #21262d !important;
            color: #58a6ff !important;
        }
        [data-theme="dark"] .dataTables_wrapper { color: #c9d1d9 !important; }
        [data-theme="dark"] .dataTables_wrapper .dataTables_filter label,
        [data-theme="dark"] .dataTables_wrapper .dataTables_length label,
        [data-theme="dark"] .dataTables_wrapper .dataTables_info { color: #c9d1d9 !important; }
        [data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
        [data-theme="dark"] .dataTables_wrapper .dataTables_length select {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .dt-buttons .btn {
            background-color: #21262d !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .dt-buttons .btn:hover { background-color: #30363d !important; }
        [data-theme="dark"] .page-link,
        [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .page-link {
            background-color: #1c2128 !important;
            border-color: #30363d !important;
            color: #ffffff !important;
        }
        [data-theme="dark"] .page-link:hover,
        [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .page-link:hover {
            background-color: #21262d !important;
            color: #ffffff !important;
        }
        [data-theme="dark"] .page-item.active .page-link {
            background-color: #1f6feb !important;
            border-color: #1f6feb !important;
            color: #fff !important;
        }
        [data-theme="dark"] .page-item.disabled .page-link {
            background-color: #161b22 !important;
            border-color: #30363d !important;
            color: #484f58 !important;
        }
        [data-theme="dark"] .modal-content {
            background-color: #1c2128 !important;
            border-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .modal-header { border-bottom-color: #30363d !important; }
        [data-theme="dark"] .modal-footer { border-top-color: #30363d !important; }
        [data-theme="dark"] .close { color: #c9d1d9 !important; text-shadow: none !important; }
        [data-theme="dark"] .dropdown-menu {
            background-color: #1c2128 !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .dropdown-item { color: #c9d1d9 !important; }
        [data-theme="dark"] .dropdown-item:hover {
            background-color: #21262d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .dropdown-divider { border-top-color: #30363d !important; }
        [data-theme="dark"] .text-dark { color: #c9d1d9 !important; }
        [data-theme="dark"] .text-muted { color: #8b949e !important; }
        [data-theme="dark"] .text-secondary { color: #8b949e !important; }
        [data-theme="dark"] hr { border-top-color: #30363d !important; }
        [data-theme="dark"] .badge-secondary { background-color: #484f58 !important; }
        [data-theme="dark"] .alert-info {
            background-color: #0c2d6b !important;
            border-color: #1f4eb8 !important;
            color: #79c0ff !important;
        }
        [data-theme="dark"] .alert-warning {
            background-color: #4d2e00 !important;
            border-color: #9b6200 !important;
            color: #e3b341 !important;
        }
        [data-theme="dark"] .alert-danger {
            background-color: #4d0d0d !important;
            border-color: #a01010 !important;
            color: #f97583 !important;
        }
        [data-theme="dark"] .alert-success {
            background-color: #0d2d1a !important;
            border-color: #155724 !important;
            color: #3fb950 !important;
        }
        [data-theme="dark"] .btn-outline-secondary {
            color: #8b949e !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .btn-outline-secondary:hover {
            background-color: #30363d !important;
            color: #c9d1d9 !important;
        }
        [data-theme="dark"] .btn-outline-dark {
            color: #c9d1d9 !important;
            border-color: #30363d !important;
        }
        [data-theme="dark"] .btn-outline-dark:hover {
            background-color: #30363d !important;
            color: #c9d1d9 !important;
        }

        /* Transición suave al cambiar tema */
        body, nav, .navbar, .card, .card-header, .card-body, .card-footer,
        .table, .form-control, .modal-content, .page-link, .dropdown-menu,
        .input-group-text {
            transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease !important;
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
                    <a class="nav-link <?php echo ($current_page == 'calendario.php') ? 'active-custom' : ''; ?>"
                        href="<?php echo BASE_URL; ?>cierre/calendario.php"> Calendario </a>
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
                    <a class="nav-link p-0 mr-3 <?php echo ($current_page == 'configuracion.php') ? 'active-custom' : 'text-secondary'; ?>"
                        href="<?php echo BASE_URL; ?>configuracion.php" title="Configuración">
                        <i class="fa-solid fa-gear fa-lg"></i>
                    </a>
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
        // Función global para cambiar el tema desde cualquier página
        window.aplicarTema = function (tema) {
            if (tema === 'dark' || (tema === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        };

        // Escucha cambios del sistema cuando el tema es "system"
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if ((localStorage.getItem('appTheme') || 'light') === 'system') {
                window.aplicarTema('system');
            }
        });

        // Usar addEventListener nativo para que no dependa de qué instancia
        // de jQuery esté activa (pie.php recarga jQuery varias veces)
        document.getElementById('btnCerrarSesion').addEventListener('click', function (e) {
            e.preventDefault();
            alertify.confirm(
                "Confirmación",
                "¿Deseas cerrar la sesión?",
                function () {
                    window.location.href = "<?php echo BASE_URL; ?>cerrar.php";
                },
                function () {}
            ).set({ labels: { ok: "Sí", cancel: "No" }, padding: true });
        });
    </script>