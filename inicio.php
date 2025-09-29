<?php
require_once('config.php');
session_start();

// Verificar si el usuario está autenticado
if (isset($_SESSION['strTutor'])) {

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

        /* HERO (portada) */
        .hero {
    position: relative;
    height: 100vh;
    background: url("./img/fondoCierre2.png") center/cover no-repeat fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
}

        /* Capa oscura para resaltar el texto */
        .hero::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(25, 87, 43, 0.3);
        }

        /* Texto encima de la capa */
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            padding: 0 15px;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 250;
            margin-bottom: 5px;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .hero .btn {
            padding: 12px 20px;
            font-size: 1.1rem;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>

    <main>

        <!-- HERO PORTADA -->
        <section class="hero">
            <div class="hero-content">
                <h1>Sistema de Cierres Diarios</h1>
                <p>Gestione clases, exámenes e indicadores de forma rápida y centralizada.</p>
                <a  href="<?php echo BASE_URL; ?>cierre/lista.php" class="btn btn-outline-dark shadow">Ir a Cierres</a>
            </div>
        </section>




        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



    </main>
    <?php include './plantilla/pie.php'; ?>



    </body>

    </html>
    <?php


} else {
    // Si el usuario no está autenticado, redirigir al login
    header('Location: index.php');

    exit();
}
?>