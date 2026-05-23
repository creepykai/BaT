<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) { 
    echo json_encode(["status" => "error", "message" => "No autenticado"]);
    exit(); 
}

$usuarioId = $_SESSION['usuarioId'];
$costoLimpieza = 20.00;

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$monedas = $stmt->fetchColumn();

if ($monedas >= $costoLimpieza) {
    $stmtUpdate = $pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales - ?, clicsSucios = 0 WHERE usuarioId = ?");
    $stmtUpdate->execute([$costoLimpieza, $usuarioId]);
    
    echo json_encode([
        "status" => "success", 
        "new_balance" => $monedas - $costoLimpieza,
        "clics_sucios" => 0
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "No tienes suficientes monedas para limpiar (Cuesta 20)."
    ]);
}
?>
