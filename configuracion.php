<?php
require_once('config.php');
session_start();

if (!isset($_SESSION['strTutor'])) {
    header('Location: index.php');
    exit();
}

include './plantilla/cabecera.php';
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
    }
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    main { flex: 1; }

    .tema-option {
        cursor: pointer;
        margin: 0;
        display: block;
        width: 100%;
    }
    .tema-option input[type="radio"] {
        display: none;
    }
    .tema-card {
        border: 2px solid #dee2e6;
        border-radius: 14px;
        padding: 28px 16px 20px;
        text-align: center;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }
    .tema-option input[type="radio"]:checked + .tema-card {
        border-color: #1ea31a;
        box-shadow: 0 0 0 3px rgba(30, 163, 26, 0.18);
        transform: translateY(-3px);
    }
    .tema-card:hover {
        border-color: #aaa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .tema-icon {
        font-size: 2.2rem;
        margin-bottom: 10px;
    }
    .tema-label {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .tema-desc {
        display: block;
        font-size: 12px;
        color: #888;
        margin-top: 5px;
        line-height: 1.4;
    }

    /* Dark mode overrides para esta página */
    [data-theme="dark"] .tema-card {
        background: #1c2128 !important;
        border-color: #30363d !important;
    }
    [data-theme="dark"] .tema-option input[type="radio"]:checked + .tema-card {
        border-color: #3fb950 !important;
        box-shadow: 0 0 0 3px rgba(63, 185, 80, 0.22) !important;
    }
    [data-theme="dark"] .tema-card:hover {
        border-color: #58a6ff !important;
    }
    [data-theme="dark"] .tema-label {
        color: #c9d1d9 !important;
    }
    [data-theme="dark"] .tema-desc {
        color: #8b949e !important;
    }
</style>

<main>
    <div class="container mt-4 mb-5">

        <div class="d-flex align-items-center mb-1">
            <a href="<?php echo BASE_URL; ?>inicio.php" class="text-muted mr-2" style="font-size:13px;">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <span class="text-muted mr-2" style="font-size:13px;">/</span>
            <span style="font-size:13px;">Configuración</span>
        </div>
        <hr>

        <h5 class="mb-4">
            <i class="fa-solid fa-gear mr-1"></i> Configuración
        </h5>

        <!-- Apariencia -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center">
                <i class="fa-solid fa-palette mr-2"></i>
                <strong>Apariencia</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Selecciona el tema visual. Se aplica de inmediato y se mantiene entre sesiones.
                </p>

                <div class="row">

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="tema-option" for="temaClaro">
                            <input type="radio" name="tema" id="temaClaro" value="light">
                            <div class="tema-card">
                                <div class="tema-icon text-warning">
                                    <i class="fa-solid fa-sun"></i>
                                </div>
                                <span class="tema-label">Claro</span>
                                <span class="tema-desc">Fondo blanco, colores claros</span>
                            </div>
                        </label>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="tema-option" for="temaOscuro">
                            <input type="radio" name="tema" id="temaOscuro" value="dark">
                            <div class="tema-card">
                                <div class="tema-icon" style="color:#58a6ff">
                                    <i class="fa-solid fa-moon"></i>
                                </div>
                                <span class="tema-label">Oscuro</span>
                                <span class="tema-desc">Fondo oscuro, reduce la fatiga visual</span>
                            </div>
                        </label>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="tema-option" for="temaSistema">
                            <input type="radio" name="tema" id="temaSistema" value="system">
                            <div class="tema-card">
                                <div class="tema-icon text-secondary">
                                    <i class="fa-solid fa-circle-half-stroke"></i>
                                </div>
                                <span class="tema-label">Sistema</span>
                                <span class="tema-desc">Sigue la preferencia del sistema operativo</span>
                            </div>
                        </label>
                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<?php include './plantilla/pie.php'; ?>

<script>
    // Marca el radio según el tema guardado
    (function () {
        var temaActual = localStorage.getItem('appTheme') || 'light';
        var radio = document.querySelector('input[name="tema"][value="' + temaActual + '"]');
        if (radio) radio.checked = true;
    })();

    // Aplica el tema al cambiar la selección
    document.querySelectorAll('input[name="tema"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var seleccionado = this.value;
            localStorage.setItem('appTheme', seleccionado);
            aplicarTema(seleccionado);
            var nombres = { light: 'Claro', dark: 'Oscuro', system: 'Sistema' };
            alertify.success('Tema cambiado a: <strong>' + nombres[seleccionado] + '</strong>');
        });
    });
</script>

</body>
</html>
