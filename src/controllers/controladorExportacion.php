<?php
/**
 * Genera un archivo JSON descargable con todo el progreso del jugador.
 * Útil para hacer copias de seguridad de la partida en local.
 */
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuarioId'])) {
    die("Error: No autenticado.");
}

$usuarioId = $_SESSION['usuarioId'];

try {
    $stmtUser = $pdo->prepare("SELECT email, nombreCafeteria, monedasActuales, monedasHistoricas, puntosLegado FROM usuario WHERE usuarioId = ?");
    $stmtUser->execute([$usuarioId]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $stmtConejos = $pdo->prepare("SELECT c.nombre, COUNT(uc.usuario_conejoId) as cantidad 
                                  FROM usuario_conejo uc 
                                  JOIN conejo c ON uc.conejoId = c.conejoId 
                                  WHERE uc.usuarioId = ? 
                                  GROUP BY c.conejoId, c.nombre");
    $stmtConejos->execute([$usuarioId]);
    $conejos = $stmtConejos->fetchAll(PDO::FETCH_ASSOC);

    $stmtUtensilios = $pdo->prepare("SELECT u.nombre 
                                     FROM usuario_utensilio uu 
                                     JOIN utensilio u ON uu.utensilioId = u.utensilioId 
                                     WHERE uu.usuarioId = ?");
    $stmtUtensilios->execute([$usuarioId]);
    $utensilios = $stmtUtensilios->fetchAll(PDO::FETCH_ASSOC);

    $datosPartida = [
        'fecha_exportacion' => date('Y-m-d H:i:s'),
        'jugador' => $usuario,
        'inventario_conejos' => $conejos,
        'inventario_utensilios' => $utensilios
    ];

    header('Content-Type: application/json');
    $fecha = date('Y-m-d');
    header('Content-Disposition: attachment; filename="Bunnies_And_Tea_Guardado_' . $fecha . '.json"');
    echo json_encode($datosPartida, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    die("Error al exportar la partida.");
}
?>
