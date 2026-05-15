<?php
require_once('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $strTutor    = $_POST["strTutor"];
    $strPassword = $_POST["strPassword"];

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode(array('strTutor' => $strTutor, 'strPassword' => $strPassword))
        ),
        'ssl' => array(
            'verify_peer'      => false,
            'verify_peer_name' => false
        )
    );

    $context  = stream_context_create($options);
    $response = @file_get_contents(URL_LOGIN_TUTOR, false, $context);

    if ($response === false) {
        $status_line = isset($http_response_header[0]) ? $http_response_header[0] : '';
        $mensajeError = (strpos($status_line, "401") !== false)
            ? "Usuario o contraseña incorrecta"
            : "No se pudo conectar con el servidor";
    } else {
        $responseData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($responseData) && !empty($responseData)) {
            $_SESSION['strTutor']   = $responseData['strTutor'];
            $_SESSION['strNombres'] = $responseData['strNombres'];
            $_SESSION['strTipo']    = $responseData['strTipo'];
            header("Location: inicio.php");
            exit();
        } else {
            $mensajeError = "Usuario o contraseña incorrecta";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CEA - Cierre</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            background-image: url("./img/fondoCierre2.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-color: rgba(15, 60, 30, 0.45);
            z-index: 0;
        }

        .login-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 20px;
            padding: 2.2rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
        }

        .login-logo {
            display: block;
            margin: 0 auto 0.4rem;
            max-width: 180px;
            height: auto;
        }

        .login-subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            margin-bottom: 1.6rem;
        }

        .login-card label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .login-card .form-control {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            border-radius: 10px;
            font-size: 0.95rem;
            padding: 0.55rem 0.85rem;
        }

        .login-card .form-control::placeholder { color: rgba(255,255,255,0.5); }
        .login-card .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.15);
        }

        .btn-ingresar {
            width: 100%;
            background: rgba(30, 163, 26, 0.85);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            padding: 0.6rem;
            letter-spacing: 0.4px;
            transition: background 0.2s;
        }
        .btn-ingresar:hover { background: rgba(20, 130, 18, 0.95); color: #fff; }

        .home-link {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
            z-index: 2;
        }
        .home-link:hover { color: #fff; }

        .alert-login {
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 0.5rem 0.9rem;
            margin-bottom: 1rem;
        }

        /* móvil pequeño */
        @media (max-width: 400px) {
            .login-card { padding: 1.6rem 1.2rem; }
            .login-logo { max-width: 150px; }
        }
    </style>

    <script>
        history.pushState(null, null, location.href);
        window.onpopstate = function() { history.go(1); };
    </script>
</head>
<body>

    <a href="<?php echo URL_INICIO_CEA; ?>" class="home-link" title="Ir al sitio web">
        <i class="fas fa-home"></i>
    </a>

    <div class="login-wrap">
        <div class="login-card">

            <img src="./img/LogoCeaSInTitulo.png" class="login-logo" alt="CEA Logo">
            <p class="login-subtitle">Cierre Diario</p>

            <?php if (!empty($mensajeError)): ?>
            <div class="alert alert-danger alert-login" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?php echo htmlspecialchars($mensajeError); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group mb-3">
                    <label for="strTutor">Usuario</label>
                    <input type="text" id="strTutor" name="strTutor"
                           class="form-control" autocomplete="username"
                           placeholder="Identificación del responsable" required>
                </div>
                <div class="form-group mb-4">
                    <label for="strPassword">Contraseña</label>
                    <input type="password" id="strPassword" name="strPassword"
                           class="form-control" autocomplete="current-password"
                           placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-ingresar">
                    <i class="fas fa-sign-in-alt mr-1"></i> Acceso
                </button>
            </form>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
