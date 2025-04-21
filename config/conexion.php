<?php
// Configuración de la base de datos
$host = "b4tbtxmwwzuudshpuohy-mysql.services.clever-cloud.com";
$dbname = "b4tbtxmwwzuudshpuohy";
$username = "ulbdcz4pcdollulm";
$password = "LHxsOFWkoDWRo4xFENP9";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuraciones adicionales
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false); // Crucial para BLOBs
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}