<?php
/**
 * Controlador que procesa el registro de nuevos usuarios.
 * Recibe el formulario POST, valida el email y delega en el GestorAutenticacion.
 */
require_once 'db.php';
require_once 'models/GestorAutenticacion.php';

$auth = new GestorAutenticacion($pdo);
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalido");
    }

    if ($auth->registrarUsuario($email, $pass)) {
        echo "<script>window.location.href='iniciar_sesion.php';</script>";
        exit();
    } else {
        $mensaje = "<p style='color: red;'>Error: El email ya existe o hay un problema técnico.</p>";
    }
}

require_once 'views/registro.view.php';