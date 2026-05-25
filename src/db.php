<?php
/**
 * Conexión a la base de datos usando PDO.
 * Utilizamos PDO para poder hacer consultas preparadas y evitar inyección SQL.
 * Al usar require_once en otros archivos, reutilizamos la conexión.
 */
$host = 'db';
$db   = 'bunniesAndTea';
$user = 'user_bunnies';
$pass = 'user_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     throw new PDOException($e->getMessage(), (int)$e->getCode());
}