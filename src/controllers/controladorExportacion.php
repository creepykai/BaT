<?php
//Archivo que devuelve los datos de la partida para hacer una copia de seguridad
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    die("No autenticado.");
}

$usuarioId = $_SESSION['usuarioId'];
require_once '../models/GestorPartida.php';
$gestorPartida = new GestorPartida($pdo);

try {
    $datosPartida = $gestorPartida->exportarPartida($usuarioId);

    echo json_encode([
        'status' => 'success',
        'data' => $datosPartida
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al exportar la partida.']);
}
?>
