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

    .error-container h1 {
        font-size: 2rem;
        color: #cc0000;
    }

    .error-container p {
        font-size: 1rem;
        color: #555;
        margin-bottom: 1.5rem;
    }

    .error-container a {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        text-decoration: none;
    }

    .error-container a:hover {
        background-color: #0056b3;
    }
</style>

<main>
    <div class="container">
        <div class="error-container">

       <i class="fa-solid fa-face-frown fa-8x text-secondary"></i>

            <h1 class="">¡Oops! Algo salió mal</h1>
            <p>No se pudo completar la operación. Por favor, intenta nuevamente.</p>

            <a href="javascript:history.back()">← Volver atrás</a>
        </div>
    </div>
</main>

<?php
include './plantilla/pie.php';
?>
