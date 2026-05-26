<?php
/**
 * Contiene la lógica matemática del juego.
 * Aquí se calcula cuánto vale cada clic o cuántas monedas se generan por segundo.
 */
class MotorJuego {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerMultiplicadorLegado($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT puntosLegado FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $puntosLegado = $stmt->fetchColumn() ?: 0;
        
        return 1 + ($puntosLegado * 0.05);
    }

    public function obtenerValorClic($usuarioId) {
        $sql = "SELECT SUM(u.valorExtraClic) as total 
                FROM usuario_utensilio uu
                JOIN utensilio u ON uu.utensilioId = u.utensilioId
                WHERE uu.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $valorUtensilios = $stmt->fetchColumn() ?: 0;

        $produccionPasiva = $this->obtenerProduccionTotal($usuarioId);

        $valorClic = 1 + $valorUtensilios + ($produccionPasiva * 0.05);

        return $valorClic;
    }

    public function procesarClic($usuarioId, $cantidadClics = 1) {
        $valorClic = $this->obtenerValorClic($usuarioId);
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

    public function obtenerProduccionTotal($usuarioId) {
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

        $multiplicador = $this->obtenerMultiplicadorLegado($usuarioId);
        $total = $total * $multiplicador;

        if ($suciedad >= 50) {
            $total = $total / 2;
        }

        return $total;
    }
    public function obtenerDatosUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT email, monedasActuales, nombreCafeteria, puntosLegado, monedasHistoricas FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch();
    }

    public function obtenerConejosUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT c.nombre, COUNT(uc.usuario_conejoId) as total 
            FROM conejo c
            INNER JOIN usuario_conejo uc ON c.conejoId = uc.conejoId
            WHERE uc.usuarioId = ?
            GROUP BY c.conejoId
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    public function obtenerClicsSucios($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function obtenerLogrosUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT l.nombre, l.descripcion, ul.fechaDesbloqueo 
            FROM logro l
            LEFT JOIN usuario_logro ul ON l.logroId = ul.logroId AND ul.usuarioId = ?
            ORDER BY l.logroId ASC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }
}
?>