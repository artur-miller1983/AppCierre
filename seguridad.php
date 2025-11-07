<?php
session_start();

// Tiempo máximo de inactividad en segundos (30 minutos)1800
$tiempoInactividad = 60; 

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempoTranscurrido = time() - $_SESSION['ultimo_acceso'];
    if ($tiempoTranscurrido > $tiempoInactividad) {
        // Cerrar sesión y redirigir
        session_unset();
        session_destroy();
        header("Location: index.php?msg=sesion_expirada");
        exit();
    }
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['strTutor'])) {
    header('Location: index.php');
    exit();
}
