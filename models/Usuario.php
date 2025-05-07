<?php
class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerPorUsuario($usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM USUARIO WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
