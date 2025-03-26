<?php
// Configuración de la base de datos
$host = "b4tbtxmwwzuudshpuohy-mysql.services.clever-cloud.com";
$dbname = "b4tbtxmwwzuudshpuohy";
$username = "ulbdcz4pcdollulm";
$password = "LHxsOFWkoDWRo4xFENP9";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}