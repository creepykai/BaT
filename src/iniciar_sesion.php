<?php
//Archivo que procesa el inicio de sesión.
require_once 'db.php';
require_once 'models/GestorAutenticacion.php';

$auth = new GestorAutenticacion($pdo);
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];


    if ($auth->iniciarSesion($email, $pass)) {

        echo "<script>window.location.href='index.php';</script>";
        exit();
    } else {

        $error = "Email o contraseña incorrectos.";
    }
}

require_once 'views/iniciar_sesion.view.php';