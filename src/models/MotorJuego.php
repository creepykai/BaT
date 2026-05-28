<?php
// Contiene toda la lógica matemática y se encarga de hablar exclusivamente con la base de datos.

class MotorJuego {
    const BONUS_POR_HOJA = 0.05;      
    const BONUS_PASIVA_CLIC = 0.05;   
    const LIMITE_SUCIEDAD = 50;       
    const COSTO_LIMPIEZA = 20.00;

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerMultiplicadorLegado($usuarioId) {
        //Devuelve el bonificador extra por legado
        $stmt = $this->pdo->prepare("SELECT puntosLegado FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $puntosLegado = $stmt->fetchColumn() ?: 0;
        return 1 + ($puntosLegado * self::BONUS_POR_HOJA);
    }

    public function obtenerValorClic($usuarioId) {
        //Devuelve el valor del click sumando lo que aporta cada utensilio y la produccion pasiva
        $sql = "SELECT SUM(u.valorExtraClic) as total 
                FROM usuario_utensilio uu
                JOIN utensilio u ON uu.utensilioId = u.utensilioId
                WHERE uu.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $valorUtensilios = $stmt->fetchColumn() ?: 0;

        $produccionPasiva = $this->obtenerProduccionTotal($usuarioId);

        $valorClic = 1 + $valorUtensilios + ($produccionPasiva * self::BONUS_PASIVA_CLIC);

        return $valorClic;
    }


    public function procesarClic($usuarioId, $cantidadClics = 1) {
        //Actualiza las monedas y la suciedad
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

    public function procesarProduccionPasiva($usuarioId) {
        // Añade la suciedad por paso del tiempo (0.2)
        $stmt = $this->pdo->prepare("UPDATE usuario SET clicsSucios = clicsSucios + 0.2 WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);

        // Obtiene lo generado por conejos y mejoras
        $produccion = $this->obtenerProduccionTotal($usuarioId);

        // Si han generado algo, se lo sumamos a la cuenta
        if ($produccion > 0) {
            $stmt = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales + ?, monedasHistoricas = monedasHistoricas + ? WHERE usuarioId = ?");
            $stmt->execute([$produccion, $produccion, $usuarioId]);
        }
        
        // Devolvemos la producción y el saldo actual en un array
        $stmtSaldo = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
        $stmtSaldo->execute([$usuarioId]);
        
        return [
            'produccion' => $produccion,
            'nuevoSaldo' => $stmtSaldo->fetchColumn()
        ];
    }

    public function obtenerProduccionTotal($usuarioId) {
        //Obtiene la producción total sumando la producción de los conejos y la producción pasiva de los utensilios
        $stmt = $this->pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $suciedad = $stmt->fetchColumn() ?: 0;
        
        //Produccion base de los conejos
        $sql = "SELECT SUM(c.produccionBase) as total 
                FROM usuario_conejo uc
                JOIN conejo c ON uc.conejoId = c.conejoId
                WHERE uc.usuarioId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $totalConejos = $stmt->fetchColumn() ?: 0;

        //Produccion pasiva extra de los utensilios
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

        if ($suciedad >= self::LIMITE_SUCIEDAD) {
            $total = $total / 2;
        }

        return $total;
    }

    public function obtenerDatosUsuario($usuarioId) {
        //Obtiene los datos del usuario
        $stmt = $this->pdo->prepare("SELECT email, monedasActuales, nombreCafeteria, puntosLegado, monedasHistoricas FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(); 
    }

    public function obtenerConejosUsuario($usuarioId) {
        //Devuelve todos los conejos del usuario junto a su cantidad
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
        //Devuelve los clics sucios del usuario
        $stmt = $this->pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function obtenerLogrosUsuario($usuarioId) {
        //Obtiene todos los logros y marca cuáles tiene desbloqueados el usuario actual
        $stmt = $this->pdo->prepare("
            SELECT l.nombre, l.descripcion, ul.fechaDesbloqueo 
            FROM logro l
            LEFT JOIN usuario_logro ul ON l.logroId = ul.logroId AND ul.usuarioId = ?
            ORDER BY l.logroId ASC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }


    public function obtenerEstadisticasConejos($usuarioId) {
        // Devuelve las estadísticas agrupadas para la gráfica circular
        $sql = "SELECT c.nombre, SUM(c.produccionBase) as produccionTotal, COUNT(uc.conejoId) as cantidad 
                FROM usuario_conejo uc
                JOIN conejo c ON uc.conejoId = c.conejoId
                WHERE uc.usuarioId = ?
                GROUP BY c.conejoId, c.nombre";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function limpiarCafeteria($usuarioId) {
        // Comprueba si tiene monedas y resetea la suciedad
        $stmt = $this->pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
        $stmt->execute([$usuarioId]);
        $monedas = $stmt->fetchColumn();

        if ($monedas >= self::COSTO_LIMPIEZA) {
            $stmtUpdate = $this->pdo->prepare("UPDATE usuario SET monedasActuales = monedasActuales - ?, clicsSucios = 0 WHERE usuarioId = ?");
            $stmtUpdate->execute([self::COSTO_LIMPIEZA, $usuarioId]);
            return [
                'exito' => true,
                'nuevoSaldo' => $monedas - self::COSTO_LIMPIEZA
            ];
        }
        return ['exito' => false];
    }

    public function reiniciarPartida($usuarioId) {
        try {
            //Reinicia la partida del usuario sumandole puntos de legado segun sus monedas historicas
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT monedasHistoricas FROM usuario WHERE usuarioId = ?");
            $stmt->execute([$usuarioId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $monedasHistoricas = $resultado ? (float)$resultado['monedasHistoricas'] : 0;
        
            $nuevasHojas = floor($monedasHistoricas / 10000);

            $this->pdo->prepare("DELETE FROM usuario_conejo WHERE usuarioId = ?")->execute([$usuarioId]);
            $this->pdo->prepare("DELETE FROM usuario_utensilio WHERE usuarioId = ?")->execute([$usuarioId]);

        
            $stmtUpdate = $this->pdo->prepare("UPDATE usuario 
                                         SET monedasActuales = 0, 
                                             monedasHistoricas = 0, 
                                             clicsSucios = 0, 
                                             puntosLegado = puntosLegado + ? 
                                         WHERE usuarioId = ?");
            $stmtUpdate->execute([$nuevasHojas, $usuarioId]);

            $this->pdo->commit();
            return ['exito' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'error' => $e->getMessage()];
        }
    }
}
?>