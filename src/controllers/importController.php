<?php
/**
 * Recibe un archivo JSON y restaura la partida guardada del usuario.
 * Usa transacciones de PDO para evitar corromper la base de datos si el archivo es inválido.
 */
session_start();
require_once '../db.php';
require_once '../models/AuthManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['partida_json'])) {
    
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    $auth = new AuthManager($pdo);
    if (!$auth->iniciarSesion($email, $pass)) {
        die("Error: Credenciales incorrectas.");
    }
    
    $usuarioId = $_SESSION['usuarioId'];

    $jsonContent = file_get_contents($_FILES['partida_json']['tmp_name']);
    $data = json_decode($jsonContent, true);
    
    if (!$data || !isset($data['jugador'])) {
        die("Error: Archivo de guardado inválido.");
    }
    
    try {
        $pdo->beginTransaction();
        
        $jugador = $data['jugador'];
        $stmtUpdateUser = $pdo->prepare("UPDATE usuario SET monedasActuales = ?, monedasHistoricas = ?, puntosLegado = ?, nombreCafeteria = ? WHERE usuarioId = ?");
        $stmtUpdateUser->execute([
            $jugador['monedasActuales'] ?? 0,
            $jugador['monedasHistoricas'] ?? 0,
            $jugador['puntosLegado'] ?? 0,
            $jugador['nombreCafeteria'] ?? 'Cafetería',
            $usuarioId
        ]);
        
        $pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?")->execute([$usuarioId]);
        $pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?")->execute([$usuarioId]);
        
        if (!empty($data['inventario_conejos'])) {
            $stmtConejoId = $pdo->prepare("SELECT conejoId FROM conejo WHERE nombre = ?");
            $stmtInsertConejo = $pdo->prepare("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (?, ?)");
            
            foreach ($data['inventario_conejos'] as $conejoGuardado) {
                $stmtConejoId->execute([$conejoGuardado['nombre']]);
                $conejoId = $stmtConejoId->fetchColumn();
                if ($conejoId) {
                    for ($i = 0; $i < $conejoGuardado['cantidad']; $i++) {
                        $stmtInsertConejo->execute([$usuarioId, $conejoId]);
                    }
                }
            }
        }
        
        if (!empty($data['inventario_utensilios'])) {
            $stmtUtensilioId = $pdo->prepare("SELECT utensilioId FROM utensilio WHERE nombre = ?");
            $stmtInsertUtensilio = $pdo->prepare("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (?, ?)");
            
            foreach ($data['inventario_utensilios'] as $utensilioGuardado) {
                $stmtUtensilioId->execute([$utensilioGuardado['nombre']]);
                $utensilioId = $stmtUtensilioId->fetchColumn();
                if ($utensilioId) {
                    $stmtInsertUtensilio->execute([$usuarioId, $utensilioId]);
                }
            }
        }
        
        $pdo->commit();
        header("Location: ../index.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al importar la partida: " . $e->getMessage());
    }
}
?>
