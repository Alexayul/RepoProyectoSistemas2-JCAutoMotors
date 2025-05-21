<?php
require_once '../controllers/VentasController.php';

$controller = new VentasController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_venta'])) {
    $controller->completarVenta();
} elseif (isset($_GET['id_venta']) && isset($_GET['action']) && $_GET['action'] === 'get_details') {
    $controller->obtenerDetallesVenta();
} else {
    $controller->index();
}
?>
