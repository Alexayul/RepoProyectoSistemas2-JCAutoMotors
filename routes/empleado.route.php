<?php
session_start();
require_once '../config/conexion.php';
require_once '../controllers/EmpleadoController.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

try {
    // Crear instancia del controlador
    $controller = new EmpleadoController($conn, $_SESSION['user']);
    
    // Procesar vista
    $controller->procesarVista();

} catch (Exception $e) {
    // Manejar errores
    $_SESSION['empleado_error'] = $e->getMessage();
    header("Location: empleado.php");
    exit();
}
