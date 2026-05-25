<?php
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/ShopManager.php';

class ShopManagerTest extends BaseTestCase {
    private $shopManager;

    protected function setUp(): void {
        parent::setUp();
        $this->shopManager = new ShopManager($this->pdo);
    }

    public function test_getCatalogo_calcula_precio_escalado() {
        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase, costeBase) VALUES (1, 'Conejo', 1, 10)");
        
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        
        $catalogo = $this->shopManager->getCatalogo(1);
        
        $this->assertEquals(15, $catalogo[0]['costeBase']);
        $this->assertEquals(2, $catalogo[0]['cantidadPoseida']);
    }

    public function test_comprarConejo_sin_dinero_suficiente_falla() {
        $this->pdo->exec("INSERT INTO conejo (conejoId, costeBase) VALUES (1, 100)");
        $result = $this->shopManager->comprarConejo(1, 1);
        $this->assertFalse($result);
    }

    public function test_comprarConejo_con_dinero_actualiza_inventario() {
        $this->pdo->exec("UPDATE usuario SET monedasActuales = 50 WHERE usuarioId = 1");
        $this->pdo->exec("INSERT INTO conejo (conejoId, costeBase) VALUES (1, 10)");
        
        $result = $this->shopManager->comprarConejo(1, 1);
        
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query("SELECT monedasActuales FROM usuario WHERE usuarioId = 1");
        $this->assertEquals(40, $stmt->fetchColumn());
        
        $stmt2 = $this->pdo->query("SELECT COUNT(*) FROM usuario_conejo WHERE usuarioId = 1 AND conejoId = 1");
        $this->assertEquals(1, $stmt2->fetchColumn());
    }

    public function test_comprarUtensilio_supera_limite_maximo_falla() {
        $this->pdo->exec("UPDATE usuario SET monedasActuales = 9999 WHERE usuarioId = 1");
        $this->pdo->exec("INSERT INTO utensilio (utensilioId, costeBase, limiteMax) VALUES (1, 10, 1)");
        
        $this->assertTrue($this->shopManager->comprarUtensilio(1, 1));
        
        $this->assertFalse($this->shopManager->comprarUtensilio(1, 1));
    }
}
