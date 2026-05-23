<?php
session_start();
require_once '../db.php';
require_once '../models/GameEngine.php';
require_once '../models/AchievementManager.php';

if (!isset($_SESSION['usuarioId'])) { 
    exit(); 
}

$game = new GameEngine($pdo);
$achievements = new AchievementManager($pdo);
$usuarioId = $_SESSION['usuarioId'];

$valorDelClic = $game->getClickValue($usuarioId);
$nuevoSaldo = $game->procesarClic($usuarioId);

$logrosDesbloqueados = $achievements->chequearLogros($usuarioId);

$stmt = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$clicsActuales = $stmt->fetchColumn();

echo json_encode([
    "status" => "success",
    "new_balance" => $nuevoSaldo,
    "cantidad_ganada" => $valorDelClic,
    "clics_sucios" => $clicsActuales,
    "logros_nuevos" => $logrosDesbloqueados
]);