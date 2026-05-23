<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/GameEngine.php';

$game = new GameEngine($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("SELECT email, monedasActuales, nombreCafeteria, puntosLegado FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$datosUsuario = $stmt->fetch();

$puntosLegado = $datosUsuario['puntosLegado'] ?? 0;

$stmtC = $pdo->prepare("
    SELECT c.nombre, COUNT(uc.usuario_conejoId) as total 
    FROM conejo c
    INNER JOIN usuario_conejo uc ON c.conejoId = uc.conejoId
    WHERE uc.usuarioId = ?
    GROUP BY c.conejoId
");
$stmtC->execute([$usuarioId]);
$misConejos = $stmtC->fetchAll();

$produccionPorSegundo = $game->getProduccionTotal($usuarioId);
$valorClicActual = $game->getClickValue($usuarioId);

$stmtS = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmtS->execute([$usuarioId]);
$clicsActuales = $stmtS->fetchColumn();
$estaSucio = ($clicsActuales >= 50);

require_once 'views/home.view.php';