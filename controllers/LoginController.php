<?php
require_once __DIR__ . '/../models/Usuario.php';

class LoginController {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function login($usuario, $password) {
        $usuarioModel = new Usuario($this->db);
        $user = $usuarioModel->obtenerPorUsuario($usuario);

        if ($user && hash('sha256', $password) === $user['password']) {
            $this->iniciarSesion($user);
        } else {
            $_SESSION['login_error'] = "Usuario o contraseña incorrectos";
            header("Location: ../pages/login.php");
            exit();
        }
    }

    private function iniciarSesion($user) {
        $_SESSION['user'] = [
            'id' => $user['_id'],
            'usuario' => $user['usuario'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'rol_id' => $user['id_rol'],
            'rol_nombre' => $user['rol_nombre']
        ];

        switch ($user['id_rol']) {
            case 1: header("Location: ../pages/admin.php"); break;
            case 2: header("Location: ../pages/empleado.php"); break;
            case 3: header("Location: ../index.php"); break;
            default: header("Location: ../index.php");
        }
        exit();
    }
}
