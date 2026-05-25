<?php
/**
 * Controlador principal de la cafetería.
 * Comprueba que la sesión es válida, recoge los datos del jugador desde la base de datos
 * y por último carga la vista (home.view.php).
 */
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/GameEngine.php';

$game = new GameEngine($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("SELECT email, monedasActuales, nombreCafeteria, puntosLegado, monedasHistoricas FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$datosUsuario = $stmt->fetch();

$puntosLegado = $datosUsuario['puntosLegado'] ?? 0;
$monedasHistoricas = $datosUsuario['monedasHistoricas'] ?? 0;
$hojasPendientes = floor($monedasHistoricas / 10000);
$monedasParaSiguiente = 10000 - ($monedasHistoricas % 10000);

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

$stmtTodosLogros = $pdo->prepare("
    SELECT l.nombre, l.descripcion, ul.fechaDesbloqueo 
    FROM logro l
    LEFT JOIN usuario_logro ul ON l.logroId = ul.logroId AND ul.usuarioId = ?
    ORDER BY l.logroId ASC
");
$stmtTodosLogros->execute([$usuarioId]);
$listaLogros = $stmtTodosLogros->fetchAll();

require_once 'views/home.view.php';