<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/GameEngine.php';

$game = new GameEngine($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("SELECT monedasActuales, monedasHistoricas, puntosLegado, clicsSucios FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit();
}

$ppsActual = $game->getProduccionTotal($usuarioId);
$valorClic = $game->getClickValue($usuarioId);

$estado = [
    "status" => "success",
    "monedasActuales" => (float)$usuario['monedasActuales'],
    "monedasHistoricas" => (float)$usuario['monedasHistoricas'],
    "puntosLegado" => (int)$usuario['puntosLegado'],
    "clicsSucios" => (float)$usuario['clicsSucios'],
    "pps" => (float)$ppsActual,
    "valorClic" => (float)$valorClic
];

header('Content-Type: application/json');
echo json_encode($estado);
?>
