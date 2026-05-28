<?php
session_start();
require_once '../db.php';
require_once '../models/MotorJuego.php';

if (!isset($_SESSION['usuarioId'])) { 
    echo json_encode(["status" => "error", "message" => "No autenticado"]);
    exit(); 
}

$usuarioId = $_SESSION['usuarioId'];
$game = new MotorJuego($pdo);

$resultado = $game->limpiarCafeteria($usuarioId);

if ($resultado['exito']) {
    echo json_encode([
        "status" => "success", 
        "new_balance" => $resultado['nuevoSaldo'],
        "clics_sucios" => 0
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "No tienes suficientes monedas para limpiar (Cuesta " . MotorJuego::COSTO_LIMPIEZA . ")."
    ]);
}
?>
