<?php
//Middleware de autenticación, comprueba si el usuario está logueado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioId'])) {
    echo "<script>window.location.href='iniciar_sesion.php';</script>";
    exit();
}
?>
