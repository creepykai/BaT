<?php
session_start();
require_once '../db.php';
require_once '../models/AuthManager.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$auth = new AuthManager($pdo);
if (!$auth->iniciarSesion($email, $password)) {
    header("Location: ../login.php?error=" . urlencode("Email o contraseña incorrectos."));
    exit();
}

$usuarioId = $_SESSION['usuarioId'];
$fileUploaded = isset($_FILES['archivo_partida']) && $_FILES['archivo_partida']['error'] === UPLOAD_ERR_OK;

if (!$fileUploaded) {
    header("Location: ../index.php");
    exit();
}

$pdo->beginTransaction();

try {
    $jsonContent = file_get_contents($_FILES['archivo_partida']['tmp_name']);
    $data = json_decode($jsonContent, true);

    if ($data === null) {
        throw new Exception("Archivo JSON inválido.");
    }

    $stmtDelC = $pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?");
    $stmtDelC->execute([$usuarioId]);

    $stmtDelU = $pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?");
    $stmtDelU->execute([$usuarioId]);

    $jugador = isset($data['jugador']) ? $data['jugador'] : [];
    $monedasActuales = isset($jugador['monedasActuales']) ? (float)$jugador['monedasActuales'] : 0.00;
    $monedasHistoricas = isset($jugador['monedasHistoricas']) ? (float)$jugador['monedasHistoricas'] : 0.00;
    $puntosLegado = isset($jugador['puntosLegado']) ? (int)$jugador['puntosLegado'] : 0;
    $nombreCafeteria = isset($jugador['nombreCafeteria']) ? $jugador['nombreCafeteria'] : '';

    $stmtUpdateUser = $pdo->prepare("UPDATE usuario SET monedasActuales = ?, monedasHistoricas = ?, puntosLegado = ?, nombreCafeteria = ?, clicsSucios = 0 WHERE usuarioId = ?");
    $stmtUpdateUser->execute([$monedasActuales, $monedasHistoricas, $puntosLegado, $nombreCafeteria, $usuarioId]);

    $conejos = isset($data['inventario_conejos']) ? $data['inventario_conejos'] : [];
    $stmtConejoId = $pdo->prepare("SELECT conejoId FROM conejo WHERE nombre = ?");
    $stmtInsertC = $pdo->prepare("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (?, ?)");

    foreach ($conejos as $c) {
        $nombreConejo = isset($c['nombre']) ? $c['nombre'] : '';
        $cantidad = isset($c['cantidad']) ? (int)$c['cantidad'] : 0;

        if ($nombreConejo !== '' && $cantidad > 0) {
            $stmtConejoId->execute([$nombreConejo]);
            $conejoId = $stmtConejoId->fetchColumn();

            if ($conejoId) {
                for ($i = 0; $i < $cantidad; $i++) {
                    $stmtInsertC->execute([$usuarioId, $conejoId]);
                }
            }
        }
    }

    $utensilios = isset($data['inventario_utensilios']) ? $data['inventario_utensilios'] : [];
    $stmtUtensilioId = $pdo->prepare("SELECT utensilioId FROM utensilio WHERE nombre = ?");
    $stmtInsertU = $pdo->prepare("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (?, ?)");

    foreach ($utensilios as $u) {
        $nombreUtensilio = isset($u['nombre']) ? $u['nombre'] : '';

        if ($nombreUtensilio !== '') {
            $stmtUtensilioId->execute([$nombreUtensilio]);
            $utensilioId = $stmtUtensilioId->fetchColumn();

            if ($utensilioId) {
                $stmtInsertU->execute([$usuarioId, $utensilioId]);
            }
        }
    }

    $pdo->commit();
    header("Location: ../index.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: ../login.php?error=" . urlencode("Error al importar la partida: " . $e->getMessage()));
    exit();
}
?>
