<?php
session_start();
require_once 'config/conexion.php';
require_once 'controllers/HomeController.php';

// Crear instancia del controlador
$homeController = new HomeController($conn);

// Obtener motos más vendidas
$motosMasVendidas = $homeController->getTopSellingMotorcycles();

// Función de color (puedes mantenerla como una función global o método del controlador)
function getColorCode($colorName) {
    global $homeController;
    return $homeController->getColorCode($colorName);
}

// Resto del código de la vista permanece igual
include 'pages/home/index.php';
