<?php
class GestorPartida
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function exportarPartida($usuarioId)
    {
        $stmtUser = $this->pdo->prepare("SELECT email, nombreCafeteria, monedasActuales, monedasHistoricas, puntosLegado FROM usuario WHERE usuarioId = ?");
        $stmtUser->execute([$usuarioId]);
        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $stmtConejos = $this->pdo->prepare("SELECT c.nombre, COUNT(*) as cantidad 
                                      FROM usuario_conejo uc 
                                      JOIN conejo c ON uc.conejoId = c.conejoId 
                                      WHERE uc.usuarioId = ? 
                                      GROUP BY c.conejoId, c.nombre");
        $stmtConejos->execute([$usuarioId]);
        $conejos = $stmtConejos->fetchAll(PDO::FETCH_ASSOC);

        $stmtUtensilios = $this->pdo->prepare("SELECT u.nombre 
                                         FROM usuario_utensilio uu 
                                         JOIN utensilio u ON uu.utensilioId = u.utensilioId 
                                         WHERE uu.usuarioId = ?");
        $stmtUtensilios->execute([$usuarioId]);
        $utensilios = $stmtUtensilios->fetchAll(PDO::FETCH_ASSOC);

        return [
            'fecha_exportacion' => date('Y-m-d H:i:s'),
            'jugador' => $usuario,
            'inventario_conejos' => $conejos,
            'inventario_utensilios' => $utensilios
        ];
    }

    public function importarPartida($usuarioId, $data)
    {
        try {
            //Usamos beginTransaction porque vamos a borrar sus conejos actuales y a meter los nuevos.
            $this->pdo->beginTransaction();

            // Actualizamos sus datos
            $jugador = $data['jugador'];
            $stmtUpdateUser = $this->pdo->prepare("UPDATE usuario SET monedasActuales = ?, monedasHistoricas = ?, puntosLegado = ?, nombreCafeteria = ? WHERE usuarioId = ?");
            $stmtUpdateUser->execute([
                $jugador['monedasActuales'] ?? 0,
                $jugador['monedasHistoricas'] ?? 0,
                $jugador['puntosLegado'] ?? 0,
                $jugador['nombreCafeteria'] ?? 'Cafetería',
                $usuarioId
            ]);

            // Borramos su granja actual entera
            $this->pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?")->execute([$usuarioId]);
            $this->pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?")->execute([$usuarioId]);

            // Le rellenamos la granja con los datos del JSON
            if (!empty($data['inventario_conejos'])) {
                $stmtConejoId = $this->pdo->prepare("SELECT conejoId FROM conejo WHERE nombre = ?");
                $stmtInsertConejo = $this->pdo->prepare("INSERT INTO usuario_conejo (usuarioId, conejoId) VALUES (?, ?)");

                foreach ($data['inventario_conejos'] as $conejoGuardado) {
                    $stmtConejoId->execute([$conejoGuardado['nombre']]);
                    $conejoId = $stmtConejoId->fetchColumn();
                    if ($conejoId) {
                        for ($i = 0; $i < $conejoGuardado['cantidad']; $i++) {
                            $stmtInsertConejo->execute([$usuarioId, $conejoId]);
                        }
                    }
                }
            }

            if (!empty($data['inventario_utensilios'])) {
                $stmtUtensilioId = $this->pdo->prepare("SELECT utensilioId FROM utensilio WHERE nombre = ?");
                $stmtInsertUtensilio = $this->pdo->prepare("INSERT INTO usuario_utensilio (usuarioId, utensilioId) VALUES (?, ?)");

                foreach ($data['inventario_utensilios'] as $utensilioGuardado) {
                    $stmtUtensilioId->execute([$utensilioGuardado['nombre']]);
                    $utensilioId = $stmtUtensilioId->fetchColumn();
                    if ($utensilioId) {
                        $stmtInsertUtensilio->execute([$usuarioId, $utensilioId]);
                    }
                }
            }

            // Todo ha ido bien, guardamos los cambios definitivamente
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Si cualquier cosa explota no cobramos nada
            $this->pdo->rollBack();
            error_log("Error de importación JSON: " . $e->getMessage());
            return false;
        }
    }
}
