<?php
/**
 * Obtiene las estadísticas de progreso y logros desbloqueados del usuario.
 * Estos datos se usan para mostrarlos en el menú de ajustes o estadísticas.
 */
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$usuarioId = $_SESSION['usuarioId'];

try {
    $sql = "SELECT c.nombre, SUM(c.produccionBase) as produccionTotal, COUNT(uc.conejoId) as cantidad 
            FROM usuario_conejo uc
            JOIN conejo c ON uc.conejoId = c.conejoId
            WHERE uc.usuarioId = ?
            GROUP BY c.conejoId, c.nombre";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuarioId]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $datos
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener estadísticas']);
}
?>
