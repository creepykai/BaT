<?php
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase {
    protected $pdo;

    protected function setUp(): void {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE usuario (
                usuarioId INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE,
                passwordHash TEXT,
                monedasActuales REAL DEFAULT 0,
                monedasHistoricas REAL DEFAULT 0,
                clicsSucios INTEGER DEFAULT 0,
                puntosLegado INTEGER DEFAULT 0,
                nombreCafeteria TEXT DEFAULT 'Cafetería'
            );

            CREATE TABLE conejo (
                conejoId INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT,
                produccionBase REAL,
                costeBase REAL DEFAULT 0
            );

            CREATE TABLE utensilio (
                utensilioId INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT,
                valorExtraClic REAL DEFAULT 0,
                produccionPasivaExtra REAL DEFAULT 0,
                costeBase REAL DEFAULT 0,
                limiteMax INTEGER DEFAULT 1
            );

            CREATE TABLE usuario_conejo (
                usuarioId INTEGER,
                conejoId INTEGER,
                cantidad INTEGER DEFAULT 1
            );

            CREATE TABLE usuario_utensilio (
                usuarioId INTEGER,
                utensilioId INTEGER
            );
        ");

        $this->pdo->exec("INSERT INTO usuario (usuarioId, email, passwordHash, monedasActuales, monedasHistoricas, clicsSucios, puntosLegado) VALUES (1, 'test@test.com', 'hash', 0, 0, 0, 0)");
    }

    protected function tearDown(): void {
        $this->pdo = null;
        parent::tearDown();
    }
}
