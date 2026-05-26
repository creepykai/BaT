<?php
/**
 * Comprueba si el usuario está logueado.
 * Se incluye al principio de los archivos protegidos para redirigir al login si no hay sesión.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioId'])) {
    header("Location: iniciar_sesion.php");
    exit();
}
?>
