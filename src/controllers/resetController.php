<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$usuarioId = $_SESSION['usuarioId'];

try {
    $pdo->beginTransaction();

    $stmt1 = $pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?");
    $stmt1->execute([$usuarioId]);

    $stmt2 = $pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?");
    $stmt2->execute([$usuarioId]);

    $stmt3 = $pdo->prepare("UPDATE usuario SET monedasActuales = 0, clicsSucios = 0 WHERE usuarioId = ?");
    $stmt3->execute([$usuarioId]);

    $pdo->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al reiniciar la partida']);
}
?>
