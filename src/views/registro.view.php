<?php
//Encargado de mostrar el formulario de registro.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Bunnies & Tea</title>
    <style>
        body { font-family: sans-serif; background: #fdf6f0; text-align: center; padding-top: 50px; }
        form { background: white; padding: 20px; display: inline-block; border-radius: 10px; border: 1px solid #ddd; }
        input { margin-bottom: 10px; padding: 8px; width: 200px; }
        button { background: #d4a373; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Únete a la cafetería 🐰☕</h1>
    <?php echo $mensaje; ?>
    
    <form method="POST">
        <input type="email" name="email" required><br>
        <input type="password" name="password" required><br>
        <button type="submit">Registrarse</button>
    </form>
    
    <div style="text-align: center; margin-top: 20px;">
        <p>¿Ya tienes una cuenta? <a href="iniciar_sesion.php" style="color: #8d6e63; font-weight: bold; text-decoration: none;">Inicia sesión aquí ☕</a></p>
    </div>
</body>
</html>
