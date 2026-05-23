<?php
session_start();
require_once '../db.php';
require_once '../models/GameEngine.php';
require_once '../models/AchievementManager.php';

if (!isset($_SESSION['usuarioId'])) { exit(); }

$game = new GameEngine($pdo);
$achievements = new AchievementManager($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("UPDATE usuario SET clicsSucios = clicsSucios + 0.2 WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);

$produccion = $game->getProduccionTotal($usuarioId);

if ($produccion > 0) {
    $stmt = $pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales + ?, monedasHistoricas = monedasHistoricas + ? WHERE usuarioId = ?");
    $stmt->execute([$produccion, $produccion, $usuarioId]);
}

$logrosDesbloqueados = $achievements->chequearLogros($usuarioId);

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$nuevoSaldo = $stmt->fetchColumn();

$ppsActual = $game->getProduccionTotal($usuarioId);

$stmtS = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmtS->execute([$usuarioId]);
$suciedadActual = $stmtS->fetchColumn();

echo json_encode([
    "status" => "success",
    "new_balance" => $nuevoSaldo,
    "current_pps" => $ppsActual,
    "clics_sucios" => $suciedadActual,
    "logros_nuevos" => $logrosDesbloqueados
]);
