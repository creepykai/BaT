<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/MotorJuego.php';

$game = new MotorJuego($pdo);
$usuarioId = $_SESSION['usuarioId'];

$datosUsuario = $game->obtenerDatosUsuario($usuarioId);

if (!$datosUsuario) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit();
}

$clicsSucios = $game->obtenerClicsSucios($usuarioId);
$ppsActual = $game->obtenerProduccionTotal($usuarioId);
$valorClic = $game->obtenerValorClic($usuarioId);

$estado = [
    "status" => "success",
    "monedasActuales" => (float)$datosUsuario['monedasActuales'],
    "monedasHistoricas" => (float)$datosUsuario['monedasHistoricas'],
    "puntosLegado" => (int)$datosUsuario['puntosLegado'],
    "clicsSucios" => (float)$clicsSucios,
    "pps" => (float)$ppsActual,
    "valorClic" => (float)$valorClic
];

header('Content-Type: application/json');
echo json_encode($estado);
?>
