<?php
class GestorTienda {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerMonedas($usuarioId) {
        // Devuelve las monedas actuales del usuario
        $stmt = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function obtenerCatalogo($usuarioId) {
        //Obtenemos el catálogo base con una subconsulta para ver cuántos tiene ya el usuario.
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM usuario_conejo uc WHERE uc.conejoId = c.conejoId AND uc.usuarioId = ?) as cantidadPoseida
                FROM conejo c 
                ORDER BY c.costeBase ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['costeBase'] = ceil(($row['costeBase'] * pow(1.15, $row['cantidadPoseida'])) / 5) * 5;
        }

        return $rows;
    }

    public function comprarConejo($usuarioId, $conejoId) {
        try {
            // Congelamos la base de datos para asegurar que o se hace toda la compra entera
            $this->pdo->beginTransaction();

            $stmtC = $this->pdo->prepare("SELECT costeBase FROM conejo WHERE conejoId = ?");
            $stmtC->execute([$conejoId]);
            $conejo = $stmtC->fetch();

            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM usuario_conejo WHERE usuarioId = ? AND conejoId = ?");
            $stmtCheck->execute([$usuarioId, $conejoId]);
            $cantidadActual = $stmtCheck->fetchColumn();

            //Cada conejo que tienes hace que el siguiente sea un 15% más caro
            //redondeamos el número para que siempre acabe en 0 o 5 (ceil / 5 * 5)
            $costeActual = ceil(($conejo['costeBase'] * pow(1.15, $cantidadActual)) / 5) * 5;

            $stmtU = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
            $stmtU->execute([$usuarioId]);
            $usuario = $stmtU->fetch();

            if (!$conejo || $usuario['monedasActuales'] < $costeActual) {
                // Si no hay dinero cancelamos la transacción
                $this->pdo->rollBack();
                return false; 
            }

            //Cobrar y entregar 
            $stmtUpdate = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales - ? WHERE usuarioId = ?");
            $stmtUpdate->execute([$costeActual, $usuarioId]);

            $stmtInsert = $this->pdo->prepare("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (?, ?)");
            $stmtInsert->execute([$usuarioId, $conejoId]);

            //Todo ha ido bien, guardamos los cambios definitivamente
            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            //Si cualquier cosa explota no cobramos nada
            $this->pdo->rollBack();
            return false;
        }
    }

    public function obtenerCatalogoUtensilios($usuarioId) {
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
?>