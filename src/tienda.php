<?php
/**
 * Controlador de la tienda.
 * Obtiene el catálogo usando GestorTienda y carga la vista correspondiente.
 * También procesa las peticiones de compra.
 */
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/GestorTienda.php';

$shop = new GestorTienda($pdo);
$usuarioId = $_SESSION['usuarioId'];
$mensaje = "";
$logrosDesbloqueados = [];
$activeTab = 'personal';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'models/GestorLogros.php';
    $achievements = new GestorLogros($pdo);
    
    if (isset($_POST['conejoId'])) {
        $activeTab = 'personal';
        $conejoId = filter_input(INPUT_POST, 'conejoId', FILTER_VALIDATE_INT);
        if ($conejoId && $shop->comprarConejo($usuarioId, $conejoId)) {
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
        $activeTab = 'utensilios';
        $utensilioId = filter_input(INPUT_POST, 'utensilioId', FILTER_VALIDATE_INT);
        if ($utensilioId && $shop->comprarUtensilio($usuarioId, $utensilioId)) {
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

$catalogoConejos = $shop->obtenerCatalogo($usuarioId);
$catalogoUtensilios = $shop->obtenerCatalogoUtensilios($usuarioId);

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$misMonedas = $stmt->fetchColumn();

require_once 'views/tienda.view.php';