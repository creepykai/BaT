<?php
/**
 * Procesa los clics manuales enviados desde el frontend (JS).
 * Actualiza la cantidad de monedas, incrementa la suciedad y comprueba los logros.
 */
session_start();
require_once '../db.php';
require_once '../models/MotorJuego.php';
require_once '../models/GestorLogros.php';

if (!isset($_SESSION['usuarioId'])) { 
    exit(); 
}

$game = new MotorJuego($pdo);
$achievements = new GestorLogros($pdo);
$usuarioId = $_SESSION['usuarioId'];

$input = json_decode(file_get_contents('php://input'), true);

$cantidadClics = isset($input['clics']) ? (int)$input['clics'] : 1;
if ($cantidadClics <= 0) $cantidadClics = 1;

$valorDelClic = $game->obtenerValorClic($usuarioId);
$clicsSucios = $game->obtenerClicsSucios($usuarioId);
$nuevoSaldo = $game->procesarClic($usuarioId, $cantidadClics);

$logrosDesbloqueados = $achievements->chequearLogros($usuarioId);

$stmt = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$clicsActuales = $stmt->fetchColumn();

echo json_encode([
    "status" => "success",
    "new_balance" => $nuevoSaldo,
    "cantidad_ganada" => $valorDelClic * $cantidadClics,
    "clics_sucios" => $clicsActuales,
    "logros_nuevos" => $logrosDesbloqueados
]);