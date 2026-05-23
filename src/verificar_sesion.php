<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioId'])) {
    header("Location: login.php");
    exit();
}
?>
