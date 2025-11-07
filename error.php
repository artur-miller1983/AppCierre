<?php
require_once('config.php');
session_start();

// Definir variable si no está definida
if (isset($_SESSION['strTutor'])) {
    $strTutor = $_SESSION['strTutor'];
    $nombreTutor = $_SESSION['strNombres'];
} else {
    header('Location: index.php');
    exit();
}

include './plantilla/cabecera.php';
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

    .error-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding: 2rem;
    }

    .error-container img {
        width: 100px;
        margin-bottom: 1rem;
    }
</style>

<main>
    <div class="container">
        <div class="error-container">

        <i class="fa-solid fa-triangle-exclamation fa-3x text-success mb-3"> 404</i>
            <h3 class="">¡Oops! Algo salió mal</h3>
            <p>No se pudo completar la operación. Por favor, intenta nuevamente.</p>
     
            <a  href="<?php echo BASE_URL; ?>inicio.php"  class="btn btn-outline-success mt-3 btn-sm"><i class="fas fa-home"></i> Inicio</a>
        </div>
    </div>
</main>

<?php
include './plantilla/pie.php';
?>