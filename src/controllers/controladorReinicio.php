<?php
//Encargado del sistema de prestigio reinicia la partida del jugador a cero
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

$usuarioId = $_SESSION['usuarioId'];
require_once '../models/MotorJuego.php';
$game = new MotorJuego($pdo);

$resultado = $game->reiniciarPartida($usuarioId);

if ($resultado['exito']) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $resultado['error'] ?? 'Error al reiniciar']);
}
exit();
?>
