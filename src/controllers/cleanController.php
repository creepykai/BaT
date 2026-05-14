<?php
session_start();
require_once '../db.php';
if (isset($_SESSION['usuarioId'])) {
    $stmt = $pdo->prepare("UPDATE usuario SET clicsSucios = 0 WHERE usuarioId = ?");
    $stmt->execute([$_SESSION['usuarioId']]);
}
