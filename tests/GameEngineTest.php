<?php
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/GameEngine.php';

class GameEngineTest extends BaseTestCase {
    private $gameEngine;

    protected function setUp(): void {
        parent::setUp();
        $this->gameEngine = new GameEngine($this->pdo);
    }

    public function test_getClickValue_usuario_sin_utensilios() {
        $valorClic = $this->gameEngine->getClickValue(1);
        
        $this->assertEquals(1, $valorClic);
    }

    public function test_getClickValue_usuario_con_utensilios() {
        $this->pdo->exec("INSERT INTO utensilio (utensilioId, nombre, valorExtraClic) VALUES (1, 'Espátula', 2)");
        $this->pdo->exec("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (1, 1)");

        $valorClic = $this->gameEngine->getClickValue(1);
        
        $this->assertEquals(3, $valorClic);
    }

    public function test_getProduccionTotal_limpio() {
        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase) VALUES (1, 'Conejo Básico', 10)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId, cantidad) VALUES (1, 1, 1)");

        $this->pdo->exec("INSERT INTO utensilio (utensilioId, nombre, produccionPasivaExtra) VALUES (2, 'Horno', 5)");
        $this->pdo->exec("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (1, 2)");

        $produccion = $this->gameEngine->getProduccionTotal(1);
        
        $this->assertEquals(15, $produccion);
    }

    public function test_getProduccionTotal_suciedad_mayor_a_50() {
        $this->pdo->exec("UPDATE usuario SET clicsSucios = 60 WHERE usuarioId = 1");
        
        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase) VALUES (1, 'Conejo Básico', 10)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId, cantidad) VALUES (1, 1, 1)");

        $produccion = $this->gameEngine->getProduccionTotal(1);
        
        $this->assertEquals(5, $produccion);
    }

    public function test_procesarClic_actualiza_monedas_y_suciedad() {
        $monedasFinales = $this->gameEngine->procesarClic(1, 5);

        $this->assertEquals(5, $monedasFinales);

        $stmt = $this->pdo->query("SELECT monedasActuales, monedasHistoricas, clicsSucios FROM usuario WHERE usuarioId = 1");
        $usuarioBD = $stmt->fetch();

        $this->assertEquals(5, $usuarioBD['monedasActuales']);
        $this->assertEquals(5, $usuarioBD['monedasHistoricas']);
        $this->assertEquals(5, $usuarioBD['clicsSucios']);
    }

    public function test_multiplicador_legado() {
        $this->pdo->exec("UPDATE usuario SET puntosLegado = 2 WHERE usuarioId = 1");

        $multiplicador = $this->gameEngine->getMultiplicadorLegado(1);
        $this->assertEquals(1.10, $multiplicador);

        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase) VALUES (1, 'Conejo Básico', 10)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId, cantidad) VALUES (1, 1, 1)");

        $produccion = $this->gameEngine->getProduccionTotal(1);
        
        $this->assertEquals(11, $produccion);
    }
}
