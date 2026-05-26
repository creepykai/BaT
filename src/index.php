<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/MotorJuego.php';

$game = new MotorJuego($pdo);
$usuarioId = $_SESSION['usuarioId'];

// 1. Obtener datos generales del usuario
$datosUsuario = $game->obtenerDatosUsuario($usuarioId);

$puntosLegado = $datosUsuario['puntosLegado'] ?? 0;
$monedasHistoricas = $datosUsuario['monedasHistoricas'] ?? 0;
$hojasPendientes = floor($monedasHistoricas / 10000);
$monedasParaSiguiente = 10000 - ($monedasHistoricas % 10000);

// 2. Obtener estado del juego (Conejos y Producción)
$misConejos = $game->obtenerConejosUsuario($usuarioId);
$produccionPorSegundo = $game->obtenerProduccionTotal($usuarioId);
$valorClicActual = $game->obtenerValorClic($usuarioId);

// 3. Evaluar el nivel de suciedad
$clicsActuales = $game->obtenerClicsSucios($usuarioId);
$estaSucio = ($clicsActuales >= 50);

// 4. Obtener el progreso de los logros
$listaLogros = $game->obtenerLogrosUsuario($usuarioId);

// 5. Cargar la vista pasándole todas las variables
require_once 'views/home.view.php';