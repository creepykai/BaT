<?php
//Pruebas unitarias para la validación y desbloqueo de logros del usuario.
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/GestorLogros.php';

class GestorLogrosTest extends BaseTestCase {
    private $gestor;

    protected function setUp(): void {
        parent::setUp();
        $this->gestor = new GestorLogros($this->pdo);
        
        $this->pdo->exec("INSERT INTO logro (logroId, nombre, descripcion, tipoCondicion, valorCondicion) 
                          VALUES (1, 'Primer Paso', 'Consigue 10 monedas', 'monedas_totales', 10)");
        $this->pdo->exec("INSERT INTO logro (logroId, nombre, descripcion, tipoCondicion, valorCondicion) 
                          VALUES (2, 'Granjero', 'Consigue 2 conejos', 'cantidad_conejos', 2)");
    }

    public function test_chequearLogros_sin_cumplir_condiciones() {
        $logrosNuevos = $this->gestor->chequearLogros(1);
        $this->assertEmpty($logrosNuevos);
    }

    public function test_chequearLogros_cumple_condicion_monedas() {
        $this->pdo->exec("UPDATE usuario SET monedasHistoricas = 15 WHERE usuarioId = 1");
        
        $logrosNuevos = $this->gestor->chequearLogros(1);
        
        $this->assertCount(1, $logrosNuevos);
        $this->assertEquals('Primer Paso', $logrosNuevos[0]['nombre']);
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM usuario_logro WHERE usuarioId = 1 AND logroId = 1");
        $this->assertEquals(1, $stmt->fetchColumn());
    }

    public function test_chequearLogros_no_repite_logros_ya_conseguidos() {
        $this->pdo->exec("UPDATE usuario SET monedasHistoricas = 15 WHERE usuarioId = 1");
        $this->pdo->exec("INSERT INTO usuario_logro (usuarioId, logroId) VALUES (1, 1)"); 
        
        $logrosNuevos = $this->gestor->chequearLogros(1);
        
        $this->assertEmpty($logrosNuevos);
    }
}
