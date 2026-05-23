<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/ShopManager.php';

$shop = new ShopManager($pdo);
$usuarioId = $_SESSION['usuarioId'];
$mensaje = "";
$logrosDesbloqueados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'models/AchievementManager.php';
    $achievements = new AchievementManager($pdo);
    
    if (isset($_POST['conejoId'])) {
        if ($shop->comprarConejo($usuarioId, $_POST['conejoId'])) {
            $mensaje = "<div class='alert success'>¡Nuevo conejo contratado!</div>";
            $nuevosLogros = $achievements->chequearLogros($usuarioId);
            if (!empty($nuevosLogros)) {
                $logrosDesbloqueados = array_merge($logrosDesbloqueados, $nuevosLogros);
            }
        } else { 
            $mensaje = "<div class='alert error'>No se pudo contratar al conejo.</div>"; 
        }
    }
    
    if (isset($_POST['utensilioId'])) {
        if ($shop->comprarUtensilio($usuarioId, $_POST['utensilioId'])) {
            $mensaje = "<div class='alert success'>¡Utensilio mejorado!</div>";
            $nuevosLogros = $achievements->chequearLogros($usuarioId);
            if (!empty($nuevosLogros)) {
                $logrosDesbloqueados = array_merge($logrosDesbloqueados, $nuevosLogros);
            }
        } else { 
            $mensaje = "<div class='alert error'>No se pudo comprar el utensilio.</div>"; 
        }
    }
}

$catalogoConejos = $shop->getCatalogo($usuarioId);
$catalogoUtensilios = $shop->getUtensiliosCatalogo($usuarioId);

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$misMonedas = $stmt->fetchColumn();

require_once 'views/tienda.view.php';