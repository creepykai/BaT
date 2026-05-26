<?php
/**
 * Archivo que procesa el inicio de sesión.
 * Recibe los datos por POST y delega la comprobación al GestorAutenticacion.
 * Si todo va bien, redirige al juego. Si no, muestra error en la vista.
 */

require_once 'db.php';
require_once 'models/GestorAutenticacion.php';

$auth = new GestorAutenticacion($pdo);
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];


    if ($auth->iniciarSesion($email, $pass)) {

        header("Location: index.php");
        exit();
    } else {

        $error = "Email o contraseña incorrectos.";
    }
}

require_once 'views/iniciar_sesion.view.php';