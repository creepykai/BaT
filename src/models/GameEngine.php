<?php
/**
 * Contiene la lógica matemática del juego.
 * Aquí se calcula cuánto vale cada clic o cuántas monedas se generan por segundo.
 */
class GameEngine {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getMultiplicadorLegado($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT puntosLegado FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $puntosLegado = $stmt->fetchColumn() ?: 0;
        
        return 1 + ($puntosLegado * 0.05);
    }

    public function getClickValue($usuarioId) {
        $sql = "SELECT SUM(u.valorExtraClic) as total 
                FROM usuario_utensilio uu
                JOIN utensilio u ON uu.utensilioId = u.utensilioId
                WHERE uu.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $valorUtensilios = $stmt->fetchColumn() ?: 0;

        $produccionPasiva = $this->getProduccionTotal($usuarioId);

        $valorClic = 1 + $valorUtensilios + ($produccionPasiva * 0.05);

        return $valorClic;
    }

    public function procesarClic($usuarioId, $cantidadClics = 1) {
        $valorClic = $this->getClickValue($usuarioId);
        $gananciaTotal = $valorClic * $cantidadClics;

        $stmt = $this->pdo->prepare("
            UPDATE usuario 
            SET monedasActuales = monedasActuales + ?, 
                monedasHistoricas = monedasHistoricas + ?,
                clicsSucios = clicsSucios + ?
            WHERE usuarioId = ?
        ");
        $stmt->execute([$gananciaTotal, $gananciaTotal, $cantidadClics, $usuarioId]);

        $stmtSaldo = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
        $stmtSaldo->execute([$usuarioId]);
        
        return $stmtSaldo->fetchColumn();
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
        $totalConejos = $stmt->fetchColumn() ?: 0;

        $sqlU = "SELECT SUM(u.produccionPasivaExtra) as total 
                 FROM usuario_utensilio uu
                 JOIN utensilio u ON uu.utensilioId = u.utensilioId
                 WHERE uu.usuarioId = ?";
        $stmtU = $this->pdo->prepare($sqlU);
        $stmtU->execute([$usuarioId]);
        $totalUtensilios = $stmtU->fetchColumn() ?: 0;

        $total = $totalConejos + $totalUtensilios;

        $multiplicador = $this->getMultiplicadorLegado($usuarioId);
        $total = $total * $multiplicador;

        if ($suciedad >= 50) {
            $total = $total / 2;
        }

        return $total;
    }
}
?>