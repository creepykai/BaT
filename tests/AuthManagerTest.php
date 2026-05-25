<?php
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/../src/models/AuthManager.php';

class AuthManagerTest extends BaseTestCase {
    private $authManager;

    protected function setUp(): void {
        parent::setUp();
        $this->authManager = new AuthManager($this->pdo);
    }

    public function test_registrarUsuario_crea_cuenta_con_hash() {
        $email = "nuevo@test.com";
        $password = "123456";
        
        $result = $this->authManager->registrarUsuario($email, $password);
        $this->assertTrue($result);
        
        $stmt = $this->pdo->prepare("SELECT passwordHash FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $hashGuardado = $stmt->fetchColumn();
        
        $this->assertNotEquals($password, $hashGuardado);
        $this->assertTrue(password_verify($password, $hashGuardado));
    }

    public function test_registrarUsuario_email_duplicado_falla() {
        $result = $this->authManager->registrarUsuario('test@test.com', 'password');
        
        $this->assertFalse($result);
    }

    public function test_iniciarSesion_credenciales_incorrectas_falla() {
        $result = $this->authManager->iniciarSesion('test@test.com', 'contraseñamala');
        $this->assertFalse($result);
        
        $this->assertArrayNotHasKey('usuarioId', $_SESSION);
    }

    public function test_iniciarSesion_credenciales_correctas_crea_sesion() {
        $this->authManager->registrarUsuario('login@test.com', 'secreta123');
        
        $result = $this->authManager->iniciarSesion('login@test.com', 'secreta123');
        
        $this->assertTrue($result);
        $this->assertArrayHasKey('usuarioId', $_SESSION);
        
        $stmt = $this->pdo->prepare("SELECT usuarioId FROM usuario WHERE email = ?");
        $stmt->execute(['login@test.com']);
        $idReal = $stmt->fetchColumn();
        
        $this->assertEquals($idReal, $_SESSION['usuarioId']);
    }
}
