<?php
/**
 * Calcula y añade las monedas que generan los conejos automáticamente.
 * El frontend hace una petición a este archivo cada segundo.
 */
session_start();
require_once '../db.php';
require_once '../models/MotorJuego.php';
require_once '../models/GestorLogros.php';

if (!isset($_SESSION['usuarioId'])) { exit(); }

$game = new MotorJuego($pdo);
$achievements = new GestorLogros($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("UPDATE usuario SET clicsSucios = clicsSucios + 0.2 WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);

$clicsSucios = $game->obtenerClicsSucios($usuarioId);

$produccion = $game->obtenerProduccionTotal($usuarioId);

if ($produccion > 0) {
    $stmt = $pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales + ?, monedasHistoricas = monedasHistoricas + ? WHERE usuarioId = ?");
    $stmt->execute([$produccion, $produccion, $usuarioId]);
}

$logrosDesbloqueados = $achievements->chequearLogros($usuarioId);

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$nuevoSaldo = $stmt->fetchColumn();

$ppsActual = $game->obtenerProduccionTotal($usuarioId);

$stmtS = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmtS->execute([$usuarioId]);
$suciedadActual = $stmtS->fetchColumn();

echo json_encode([
    "status" => "success",
    "produccion_pasiva" => $produccion,
    "new_balance" => $nuevoSaldo,
    "current_pps" => $ppsActual,
    "clics_sucios" => $suciedadActual,
    "logros_nuevos" => $logrosDesbloqueados
]);
