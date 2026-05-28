<?php
//Pruebas unitarias para la exportación e importación de partidas en formato JSON.
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/GestorPartida.php';

class GestorPartidaTest extends BaseTestCase {
    private $gestor;

    protected function setUp(): void {
        parent::setUp();
        $this->gestor = new GestorPartida($this->pdo);
        
        $this->pdo->exec("INSERT INTO conejo (conejoId, nombre, produccionBase) VALUES (1, 'Conejo Blanco', 10)");
        $this->pdo->exec("INSERT INTO utensilio (utensilioId, nombre) VALUES (1, 'Taza de Té')");
    }

    public function test_exportarPartida_devuelve_estructura_correcta() {
        $this->pdo->exec("UPDATE usuario SET monedasActuales = 100, nombreCafeteria = 'Mi Local' WHERE usuarioId = 1");
        
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        $this->pdo->exec("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (1, 1)");
        
        $this->pdo->exec("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (1, 1)");

        $exportado = $this->gestor->exportarPartida(1);

        $this->assertArrayHasKey('fecha_exportacion', $exportado);
        $this->assertArrayHasKey('jugador', $exportado);
        $this->assertArrayHasKey('inventario_conejos', $exportado);
        $this->assertArrayHasKey('inventario_utensilios', $exportado);

        $this->assertEquals(100, $exportado['jugador']['monedasActuales']);
        $this->assertEquals('Mi Local', $exportado['jugador']['nombreCafeteria']);

        $this->assertCount(1, $exportado['inventario_conejos']);
        $this->assertEquals('Conejo Blanco', $exportado['inventario_conejos'][0]['nombre']);
        $this->assertEquals(2, $exportado['inventario_conejos'][0]['cantidad']);
    }

    public function test_importarPartida_reemplaza_granja_con_exito() {
        $datosImportacion = [
            'jugador' => [
                'monedasActuales' => 500,
                'monedasHistoricas' => 500,
                'puntosLegado' => 1,
                'nombreCafeteria' => 'Cafetería Importada'
            ],
            'inventario_conejos' => [
                ['nombre' => 'Conejo Blanco', 'cantidad' => 3]
            ],
            'inventario_utensilios' => [
                ['nombre' => 'Taza de Té']
            ]
        ];

        $resultado = $this->gestor->importarPartida(1, $datosImportacion);
        
        $this->assertTrue($resultado);

        $stmtUser = $this->pdo->query("SELECT monedasActuales, nombreCafeteria FROM usuario WHERE usuarioId = 1");
        $usuario = $stmtUser->fetch();
        $this->assertEquals(500, $usuario['monedasActuales']);
        $this->assertEquals('Cafetería Importada', $usuario['nombreCafeteria']);

        $stmtConejos = $this->pdo->query("SELECT COUNT(*) FROM usuario_conejo WHERE usuarioId = 1");
        $this->assertEquals(3, $stmtConejos->fetchColumn());

        $stmtUtensilios = $this->pdo->query("SELECT COUNT(*) FROM usuario_utensilio WHERE usuarioId = 1");
        $this->assertEquals(1, $stmtUtensilios->fetchColumn());
    }
}
