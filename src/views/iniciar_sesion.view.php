<?php
/**
 * Vista con el formulario de inicio de sesión.
 * Solo contiene el HTML y algo de PHP para mostrar errores si los hay.
 */
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

    <form id="main-form" method="POST" action="iniciar_sesion.php" enctype="multipart/form-data">
        <input type="email" name="email" id="email" placeholder="tu@correo.com" required><br>
        <input type="password" name="password" id="password" placeholder="Tu contraseña" required><br>
        <button type="submit">Entrar</button>
        
        <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <input type="file" name="partida_json" accept=".json" id="file-import" style="display: none;" onchange="submitImport()">
        <button type="button" onclick="document.getElementById('file-import').click();" style="background-color: #8D6E63; margin-bottom: 20px;">Cargar Partida (.json)</button>
    </form>

    <script>
        function submitImport() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const fileInput = document.getElementById('file-import');
            
            if (!email || !password) {
                alert('Por favor, introduce tu email y contraseña arriba para poder cargar la partida.');
                fileInput.value = '';
                return;
            }
            
            const form = document.getElementById('main-form');
            form.action = 'controllers/controladorImportacion.php';
            form.submit();
        }
    </script>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</body>
</html>
