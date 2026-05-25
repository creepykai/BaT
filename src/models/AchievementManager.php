<?php
/**
 * Clase encargada de gestionar los logros del jugador.
 * Comprueba las estadísticas y desbloquea los logros que se hayan cumplido.
 */
class AchievementManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function chequearLogros($usuarioId) {
        $logrosDesbloqueadosHoy = [];

        $sql = "SELECT * FROM logro WHERE logroId NOT IN (SELECT logroId FROM usuario_logro WHERE usuarioId = ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $logrosPendientes = $stmt->fetchAll();

        if (empty($logrosPendientes)) {
            return $logrosDesbloqueadosHoy;
        }

        $stmtStats = $this->pdo->prepare("SELECT monedasHistoricas FROM usuario WHERE usuarioId = ?");
        $stmtStats->execute([$usuarioId]);
        $monedasHistoricas = $stmtStats->fetchColumn();

        $stmtConejos = $this->pdo->prepare("SELECT SUM(cantidad) as total_conejos FROM usuario_conejo WHERE usuarioId = ?");
        $stmtConejos->execute([$usuarioId]);
        $totalConejos = $stmtConejos->fetchColumn() ?: 0;

        foreach ($logrosPendientes as $logro) {
            $cumplido = false;

            switch ($logro['tipoCondicion']) {
                case 'clics':
                case 'monedas_totales':
                    if ($monedasHistoricas >= $logro['valorCondicion']) {
                        $cumplido = true;
                    }
                    break;
                case 'cantidad_conejos':
                    if ($totalConejos >= $logro['valorCondicion']) {
                        $cumplido = true;
                    }
                    break;
            }

            if ($cumplido) {
                $stmtInsert = $this->pdo->prepare("INSERT INTO usuario_logro (usuarioId, logroId) VALUES (?, ?)");
                $stmtInsert->execute([$usuarioId, $logro['logroId']]);
                
                $logrosDesbloqueadosHoy[] = $logro;
            }
        }

        return $logrosDesbloqueadosHoy;
    }
}
?>
