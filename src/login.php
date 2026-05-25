<?php
/**
 * Archivo que procesa el inicio de sesión.
 * Recibe los datos por POST y delega la comprobación al AuthManager.
 * Si todo va bien, redirige al juego. Si no, muestra error en la vista.
 */

require_once 'db.php';
require_once 'models/AuthManager.php';

$auth = new AuthManager($pdo);
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];


    if ($auth->iniciarSesion($email, $pass)) {

        header("Location: index.php");
        exit();
    } else {

        $error = "Email o contraseña incorrectos.";
    }
}

require_once 'views/login.view.php';