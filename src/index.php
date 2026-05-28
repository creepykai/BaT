<?php
// En index.php se cargan los datos y luego se muestra la vista home.view.php

require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/MotorJuego.php';

$game = new MotorJuego($pdo);
$usuarioId = $_SESSION['usuarioId'];

$datosUsuario = $game->obtenerDatosUsuario($usuarioId);
$puntosLegado = $datosUsuario['puntosLegado'] ?? 0;
$monedasHistoricas = $datosUsuario['monedasHistoricas'] ?? 0;
$hojasPendientes = floor($monedasHistoricas / 10000);
$monedasParaSiguiente = 10000 - fmod((float)$monedasHistoricas, 10000);

$misConejos = $game->obtenerConejosUsuario($usuarioId);
$produccionPorSegundo = $game->obtenerProduccionTotal($usuarioId);
$valorClicActual = $game->obtenerValorClic($usuarioId);
$clicsActuales = $game->obtenerClicsSucios($usuarioId);
$estaSucio = ($clicsActuales >= 50);
$listaLogros = $game->obtenerLogrosUsuario($usuarioId);

require_once 'views/home.view.php';