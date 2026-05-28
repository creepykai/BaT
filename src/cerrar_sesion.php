<?php
//Cierra la sesión del usuario de forma segura y le redirige a la pantalla de login.
session_start();

$_SESSION = array();
session_destroy();

echo "<script>window.location.href='iniciar_sesion.php';</script>";
exit();