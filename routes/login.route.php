<?php
session_start();
require_once '../config/conexion.php';
require_once '../controllers/LoginController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $controller = new LoginController($conn);
    $controller->login($usuario, $password);
}
