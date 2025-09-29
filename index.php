<?php
require_once('config.php');
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Verificar si el formulario se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $strTutor = $_POST["strTutor"];
    $strPassword = $_POST["strPassword"];

    $url = URL_LOGIN_TUTOR;
    $data = array(
        "strTutor" => $strTutor,
        "strPassword" => $strPassword
    );

    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);

    try {
        ini_set('display_errors', 0);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $status_line = $http_response_header[0] ?? '';
            if (strpos($status_line, "401") !== false) {
                $mensajeError = "Usuario o contraseña incorrecta";
            } else {
                $mensajeError = "Error en la solicitud a la API";
            }
        } else {
            $responseData = json_decode($response, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($responseData) && !empty($responseData)) {
                
                // Login correcto, guardar datos en sesión
                $_SESSION['strTutor'] = $responseData['strTutor'];
                $_SESSION['strNombres'] = $responseData['strNombres'];
                $_SESSION['strTipo'] = $responseData['strTipo'];

                // Redirigir a la página de inicio
                header("Location: inicio.php");
                exit();
            } else {
                $mensajeError = "Respuesta inválida de la API";
            }
        }


    } catch (\Throwable $th) {
        //throw $th;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>CEA Cierre</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css"
        integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css">

    <style>
        body {
            background-image: url("./img/fondoCierre2.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;   
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(25, 87, 43, 0.2); /* Negro al 50% */
            z-index: -1; /* queda detrás del contenido */
        }
        .container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background-color: rgba(60, 121, 83, 0.1);
            border: none;
            border-radius: 19px;
            padding: 30px;
            margin-top: -50px;
        }

        .card-title {
            text-align: center;
        }

        .card-subtitle {
            display: block;
            margin-top: 0px;
            color: white;   
            font-size: 14px;
            padding-top: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }


    </style>

    <script>
        // Deshabilitar el botón de retroceso del navegador
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</head>

<body>
    <div class="container">

        <div class="card shadow-lg" style="width: 350px;">
            <!-- ICONO HOME PARA LA PAGINA CEA -->
            <div class="text-right">
                <a href="<?php echo URL_INICIO_CEA; ?>" class="btn btn-default">
                    <i class="fas fa-home fa-1x"></i>
                </a>
            </div>

            <a class="text-center">
                <img src="./img/LogoCeaSInTitulo.png" width="195" height="65" class="d-inline-block r">
            </a>
            <h2 class="card-title"><span class="card-subtitle">Cierre Diario</span></h2>

            <?php if (isset($errores["general"])) { ?>
                <p><?php echo $errores["general"]; ?></p>
            <?php } ?>

            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <div class="form-group">
                    <label for="strTutor" class="text-white" >Instructor:</label>
                    <input type="text" id="strTutor" name="strTutor" class="form-control form-control-sm " required>
                    <?php if (isset($errores["strTutor"])) { ?>
                        <p><?php echo $errores["strTutor"]; ?></p>
                    <?php } ?>
                </div>

                <div class="form-group">
                    <label for="strPassword" class="text-white"  >Contraseña:</label>
                    <input type="password" id="strPassword" name="strPassword" class="form-control form-control-sm" required>
                    <?php if (isset($errores["strPassword"])) { ?>
                        <p><?php echo $errores["strPassword"]; ?></p>
                    <?php } ?>
                </div>

                <input type="submit" value="Acceso" class="btn btn-outline-dark btn-block">
            </form>
        </div>


        
    </div>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>


</body>

</html>