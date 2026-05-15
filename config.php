<?php

//Definicion de variables de entorno Produccion
define('URL_LOGIN_TUTOR', 'https://api.masmedellin.com/api-cea/loginTutor');
define('URL_CIERRES', 'https://api.masmedellin.com/api-cea/cierres');
define('URL_ACT_PASSWORD_TUTOR', 'https://api.masmedellin.com/api-cea/update-password-tutor');
define('URL_INSERTAR_CIERRE', 'https://api.masmedellin.com/api-cea/insertar-cierre');
define('URL_TIPO_CLASES', 'https://api.masmedellin.com/api-cea/tipo-clases');
define('URL_VEHICULOS', 'https://api.masmedellin.com/api-cea/vehiculos');
define('URL_EDITAR_CIERRE', 'https://api.masmedellin.com/api-cea/editar-cierre');
define('URL_CIERRE_ID', 'https://api.masmedellin.com/api-cea/cierre/');
define('URL_ELIMINAR_CIERRE', 'https://api.masmedellin.com/api-cea/eliminar-cierre/');
define('URL_TUTORES', 'https://api.masmedellin.com/api-cea/tutores');
define('URL_INDICADORES', 'https://api.masmedellin.com/api-cea/indicadores');

define('URL_INICIO_CEA', 'https://www.ceamasconduccion.com');
define('BASE_URL', '/AppCierre/');
date_default_timezone_set('America/Bogota');

// ── Logging ───────────────────────────────────────────
define('LOG_FILE', __DIR__ . '/logs/app.log');

if (!is_dir(__DIR__ . '/logs')) {
    @mkdir(__DIR__ . '/logs', 0755, true);
}

// Captura warnings, notices, etc.
function _appErrorHandler($errno, $errstr, $errfile, $errline) {
    $tipos = array(
        E_ERROR         => 'ERROR',
        E_WARNING       => 'WARNING',
        E_NOTICE        => 'NOTICE',
        E_USER_ERROR    => 'ERROR',
        E_USER_WARNING  => 'WARNING',
        E_USER_NOTICE   => 'NOTICE',
    );
    if (defined('E_DEPRECATED'))       $tipos[E_DEPRECATED]      = 'DEPRECATED';
    if (defined('E_USER_DEPRECATED'))  $tipos[E_USER_DEPRECATED] = 'DEPRECATED';

    $tipo = isset($tipos[$errno]) ? $tipos[$errno] : "ERR({$errno})";
    $ts   = date('Y-m-d H:i:s');
    $page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
    @file_put_contents(LOG_FILE,
        "[{$ts}] [{$tipo}] [{$page}] {$errstr} en {$errfile}:{$errline}" . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
    return false; // permite que PHP siga su manejo normal
}

// Captura errores fatales que set_error_handler no puede atrapar
function _appShutdownHandler() {
    $err = error_get_last();
    if (!$err) return;
    $fatales = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($err['type'], $fatales)) return;
    $ts   = date('Y-m-d H:i:s');
    $page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
    @file_put_contents(LOG_FILE,
        "[{$ts}] [FATAL] [{$page}] {$err['message']} en {$err['file']}:{$err['line']}" . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

set_error_handler('_appErrorHandler');
register_shutdown_function('_appShutdownHandler');
error_reporting(E_ALL);

function logApp($level, $message, $ctx = array()) {
    $ts    = date('Y-m-d H:i:s');
    $extra = $ctx ? ' | ' . json_encode($ctx) : '';
    $page  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
    @file_put_contents(LOG_FILE,
        "[{$ts}] [{$level}] [{$page}] {$message}{$extra}" . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

// Contexto SSL compartido
$contextNoSSL = stream_context_create(array(
    'ssl' => array('verify_peer' => false, 'verify_peer_name' => false),
));

function apiGet($url) {
    global $contextNoSSL;
    $result = @file_get_contents($url, false, $contextNoSSL);
    if ($result === false) {
        $err = error_get_last();
        logApp('ERROR', "Fallo API: {$url}", array('error' => isset($err['message']) ? $err['message'] : 'desconocido'));
        return '';
    }
    return $result;
}
