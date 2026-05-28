<?php
class GestorAutenticacion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function registrarUsuario($email, $password)
    {
        //Guarda el usuario con una contraseña segura
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuario (email, passwordHash) VALUES (?, ?)");
            return $stmt->execute([$email, $passwordHash]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function iniciarSesion($email, $password)
    {
        //Busca el usuario por email y verifica la contraseña
        $stmt = $this->pdo->prepare("SELECT usuarioId, passwordHash FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['passwordHash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);

            $_SESSION['usuarioId'] = $user['usuarioId'];
            return true;
        }
        return false;
    }
}
