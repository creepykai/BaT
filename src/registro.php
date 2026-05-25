<?php
/**
 * Controlador que procesa el registro de nuevos usuarios.
 * Recibe el formulario POST, valida el email y delega en el AuthManager.
 */
require_once 'db.php';
require_once 'models/AuthManager.php';

$auth = new AuthManager($pdo);
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido. ");
    }

    if ($auth->registrarUsuario($email, $pass)) {
        header("Location: login.php");
        exit();
    } else {
        $mensaje = "<p style='color: red;'>Error: El email ya existe o hay un problema técnico.</p>";
    }
}

require_once 'views/registro.view.php';