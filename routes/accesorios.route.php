<?php
session_start();
require_once '../config/conexion.php';
require_once '../controllers/AccesoriosController.php';

// Debug: Imprimir información de sesión
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    echo "Sesión no iniciada. Redirigiendo...";
    header('Location: login.php');
    exit;
}

// Debug: Imprimir información de usuario
var_dump($_SESSION['user']);

try {
    // Verificar conexión a la base de datos
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }

    // Crear instancia del controlador
    $controller = new AccesoriosController($conn, $_SESSION['user']);
    
    // Ejecutar acción principal
    $controller->index();
} catch (Exception $e) {
    die("Error global: " . $e->getMessage());
}
