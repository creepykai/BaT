<?php
//Archivo que recibe los datos de la copia de seguridad y restaura la partida
session_start();
require_once '../db.php';
require_once '../models/GestorAutenticacion.php';
require_once '../models/GestorPartida.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['partida_json'])) {
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalido");
    }
    $pass = $_POST['password'] ?? '';
    
    $auth = new GestorAutenticacion($pdo);
    if (!$auth->iniciarSesion($email, $pass)) {
        die("Credenciales incorrectas.");
    }
    
$usuarioId = $_SESSION['usuarioId'];
    // Abrimos el archivo temporal que acaba de subir el usuario y lo leemos
    $jsonContent = file_get_contents($_FILES['partida_json']['tmp_name']);
    $data = json_decode($jsonContent, true);
    
    if (!$data || !isset($data['jugador'])) {
        die("Archivo de guardado inválido.");
    }
    
    $gestorPartida = new GestorPartida($pdo);

    if ($gestorPartida->importarPartida($usuarioId, $data)) {
        header("Location: ../index.php");
        exit();
    } else {
        die("Error: El archivo de guardado está corrupto o es inválido.");
    }
}
?>
