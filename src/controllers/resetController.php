<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

$usuarioId = $_SESSION['usuarioId'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT monedasHistoricas FROM usuario WHERE usuarioId = ?");
    $stmt->execute([$usuarioId]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $monedasHistoricas = $resultado ? (float)$resultado['monedasHistoricas'] : 0;
    
    $nuevasHojas = floor($monedasHistoricas / 10000);

    $stmtDelC = $pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?");
    $stmtDelC->execute([$usuarioId]);

    $stmtDelU = $pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?");
    $stmtDelU->execute([$usuarioId]);

    $stmtUpdate = $pdo->prepare("UPDATE usuario 
                                 SET monedasActuales = 0, 
                                     monedasHistoricas = 0, 
                                     clicsSucios = 0, 
                                     puntosLegado = puntosLegado + ? 
                                 WHERE usuarioId = ?");
    $stmtUpdate->execute([$nuevasHojas, $usuarioId]);

    $pdo->commit();

    echo json_encode(['status' => 'success']);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
?>
