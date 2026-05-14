<?php
class ShopManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getCatalogo($usuarioId) {

        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM usuario_conejo uc WHERE uc.conejoId = c.conejoId AND uc.usuarioId = ?) as cantidadPoseida
                FROM conejo c 
                ORDER BY c.costeBase ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $multiplicador = pow(1.15, $row['cantidadPoseida']);
            $row['costeBase'] = round($row['costeBase'] * $multiplicador, 2);
        }

        return $rows;
    }
    public function comprarConejo($usuarioId, $conejoId) {
        try {
            $this->pdo->beginTransaction();


            $stmtC = $this->pdo->prepare("SELECT costeBase FROM conejo WHERE conejoId = ?");
            $stmtC->execute([$conejoId]);
            $conejo = $stmtC->fetch();

            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM usuario_conejo WHERE usuarioId = ? AND conejoId = ?");
            $stmtCheck->execute([$usuarioId, $conejoId]);
            $cantidadActual = $stmtCheck->fetchColumn();

            $multiplicador = pow(1.15, $cantidadActual);
            $costeActual = round($conejo['costeBase'] * $multiplicador, 2);

            $stmtU = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
            $stmtU->execute([$usuarioId]);
            $usuario = $stmtU->fetch();


            if (!$conejo || $usuario['monedasActuales'] < $costeActual) {
                $this->pdo->rollBack();
                return false; 
            }

            $stmtUpdate = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales - ? WHERE usuarioId = ?");
            $stmtUpdate->execute([$costeActual, $usuarioId]);

            $stmtInsert = $this->pdo->prepare("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (?, ?)");
            $stmtInsert->execute([$usuarioId, $conejoId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getUtensiliosCatalogo($usuarioId) {
        $sql = "SELECT u.*, 
                (SELECT COUNT(*) FROM usuario_utensilio uu WHERE uu.utensilioId = u.utensilioId AND uu.usuarioId = ?) as cantidadPoseida
                FROM utensilio u 
                ORDER BY u.costeBase ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    public function comprarUtensilio($usuarioId, $utensilioId) {
        try {
            $this->pdo->beginTransaction();

            $stmtU = $this->pdo->prepare("SELECT costeBase, limiteMax FROM utensilio WHERE utensilioId = ?");
            $stmtU->execute([$utensilioId]);
            $utensilio = $stmtU->fetch();

            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM usuario_utensilio WHERE usuarioId = ? AND utensilioId = ?");
            $stmtCheck->execute([$usuarioId, $utensilioId]);
            $cantidadActual = $stmtCheck->fetchColumn();

            $stmtUser = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
            $stmtUser->execute([$usuarioId]);
            $monedas = $stmtUser->fetchColumn();

            if (!$utensilio || $monedas < $utensilio['costeBase'] || $cantidadActual >= $utensilio['limiteMax']) {
                $this->pdo->rollBack();
                return false;
            }

            $stmtUpdate = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales - ? WHERE usuarioId = ?");
            $stmtUpdate->execute([$utensilio['costeBase'], $usuarioId]);

            $stmtInsert = $this->pdo->prepare("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (?, ?)");
            $stmtInsert->execute([$usuarioId, $utensilioId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}