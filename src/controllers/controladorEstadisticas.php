<?php
//Archivo que devuelve los datos de los conejos que tiene cada usuario para representar la gráfica circular
session_start();
require_once '../db.php';
require_once '../models/MotorJuego.php';

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$usuarioId = $_SESSION['usuarioId'];
$game = new MotorJuego($pdo);

try {
    $datos = $game->obtenerEstadisticasConejos($usuarioId);

    echo json_encode([
        'status' => 'success',
        'data' => $datos
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener estadísticas']);
}
?>
