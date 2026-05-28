<?php
session_start();
require_once '../db.php';
require_once '../models/MotorJuego.php';
require_once '../models/GestorLogros.php';

if (!isset($_SESSION['usuarioId'])) { exit(); }

$game = new MotorJuego($pdo);
$achievements = new GestorLogros($pdo);
$usuarioId = $_SESSION['usuarioId'];

$resultado = $game->procesarProduccionPasiva($usuarioId);
$produccion = $resultado['produccion'];
$nuevoSaldo = $resultado['nuevoSaldo'];

$logrosDesbloqueados = $achievements->chequearLogros($usuarioId);

$ppsActual = $game->obtenerProduccionTotal($usuarioId);
$suciedadActual = $game->obtenerClicsSucios($usuarioId);

echo json_encode([
    "status" => "success",
    "produccion_pasiva" => $produccion,
    "new_balance" => $nuevoSaldo,
    "current_pps" => $ppsActual,
    "clics_sucios" => $suciedadActual,
    "logros_nuevos" => $logrosDesbloqueados
]);
