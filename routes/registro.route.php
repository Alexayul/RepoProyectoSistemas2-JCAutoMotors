<?php
session_start();
require_once '../config/conexion.php';
require_once '../controllers/RegistroController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $registroController = new RegistroController($conn);
    $resultado = $registroController->registrar($_POST);

    if ($resultado['success']) {
        $_SESSION['registro_exitoso'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['registro_errors'] = $resultado['errors'];
        $_SESSION['form_data'] = $resultado['form_data'];
        header("Location: ../pages/registro.php");
        exit;
    }
}
