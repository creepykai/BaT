<?php
class GameEngine {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getClickValue($usuarioId) {
        $valorBase = 1.00;

        $sql = "SELECT SUM(u.valorExtraClic) as extra 
                FROM usuario_utensilio uu
                JOIN utensilio u ON uu.utensilioId = u.utensilioId
                WHERE uu.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $extra = $stmt->fetchColumn() ?: 0;

        return $valorBase + $extra;
    }

    public function procesarClic($usuarioId) {
        $valorClic = $this->getClickValue($usuarioId);
        

        $stmt = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales + ?, clicsSucios = clicsSucios + 1 WHERE usuarioId = ?");
        $stmt->execute([$valorClic, $usuarioId]);

        $stmt = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function getProduccionTotal($usuarioId) {

        $stmt = $this->pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $suciedad = $stmt->fetchColumn() ?: 0;

        $sql = "SELECT SUM(c.produccionBase) as total 
                FROM usuario_conejo uc
                JOIN conejo c ON uc.conejoId = c.conejoId
                WHERE uc.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $resultado = $stmt->fetch();
        $produccionBase = $resultado['total'] ?? 0;


        if ($suciedad >= 50) {
            return $produccionBase * 0.20; 
        }

        return $produccionBase;
    }
}