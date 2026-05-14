<?php

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entrar - Bunnies & Tea</title>
    <style>
        body { font-family: sans-serif; background: #fdf6f0; text-align: center; padding-top: 50px; }
        form { background: white; padding: 20px; display: inline-block; border-radius: 10px; border: 1px solid #ddd; }
        input { margin-bottom: 10px; padding: 8px; width: 200px; }
        button { background: #d4a373; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Entrar a la Cafetería 🐰☕</h1>
    
    <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="tu@correo.com" required><br>
        <input type="password" name="password" placeholder="Tu contraseña" required><br>
        <button type="submit">Entrar</button>
    </form>
    
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</body>
</html>