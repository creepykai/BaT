<?php
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/GestorTienda.php';

class GestorTiendaTest extends BaseTestCase {
    private $GestorTienda;

    protected function setUp(): void {
        parent::setUp();
        $this->GestorTienda = new GestorTienda($this->pdo);
    }

    public function test_obtenerCatalogo_calcula_precio_escalado() {
        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase, costeBase) VALUES (1, 'Conejo', 1, 10)");
        
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        
        $catalogo = $this->GestorTienda->obtenerCatalogo(1);
        
        $this->assertEquals(15, $catalogo[0]['costeBase']);
        $this->assertEquals(2, $catalogo[0]['cantidadPoseida']);
    }

    public function test_comprarConejo_sin_dinero_suficiente_falla() {
        $this->pdo->exec("INSERT INTO conejo (conejoId, costeBase) VALUES (1, 100)");
        $result = $this->GestorTienda->comprarConejo(1, 1);
        $this->assertFalse($result);
    }

    public function test_comprarConejo_con_dinero_actualiza_inventario() {
        $this->pdo->exec("UPDATE usuario SET monedasActuales = 50 WHERE usuarioId = 1");
        $this->pdo->exec("INSERT INTO conejo (conejoId, costeBase) VALUES (1, 10)");
        
        $result = $this->GestorTienda->comprarConejo(1, 1);
        
        $this->assertTrue($result);
        
        $stmt = $this->pdo->query("SELECT monedasActuales FROM usuario WHERE usuarioId = 1");
        $this->assertEquals(40, $stmt->fetchColumn());
        
        $stmt2 = $this->pdo->query("SELECT COUNT(*) FROM usuario_conejo WHERE usuarioId = 1 AND conejoId = 1");
        $this->assertEquals(1, $stmt2->fetchColumn());
    }

    public function test_comprarUtensilio_supera_limite_maximo_falla() {
        $this->pdo->exec("UPDATE usuario SET monedasActuales = 9999 WHERE usuarioId = 1");
        $this->pdo->exec("INSERT INTO utensilio (utensilioId, costeBase, limiteMax) VALUES (1, 10, 1)");
        
        $this->assertTrue($this->GestorTienda->comprarUtensilio(1, 1));
        
        $this->assertFalse($this->GestorTienda->comprarUtensilio(1, 1));
    }
}
