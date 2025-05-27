<?php
require_once __DIR__ . '/../models/Usuario.php';

class RegistroController {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    private function sanitizeInput($input) { 
        $input = trim($input); 
        $input = stripslashes($input); 
        $input = htmlspecialchars($input); 
        return $input; 
    }

    private function isSecurePassword($password) {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un carácter especial";
        }
        
        return empty($errors) ? true : $errors;
    }

    public function registrar($datos) {
        $errors = [];
        $form_data = [];

        $required_fields = ['nombre', 'email', 'usuario', 'password', 'confirm_password'];
        foreach ($required_fields as $field) {
            if (empty($datos[$field])) {
                $errors[] = "El campo " . ucfirst(str_replace('_', ' ', $field)) . " es obligatorio";
            } else {
                $form_data[$field] = $this->sanitizeInput($datos[$field]);
            }
        }
        
        if (empty($errors)) {
            // Validaciones adicionales
            if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del correo electrónico no es válido";
            }
            
            if (strlen($form_data['usuario']) < 4) {
                $errors[] = "El nombre de usuario debe tener al menos 4 caracteres";
            }
            
            $password_validation = $this->isSecurePassword($form_data['password']);
            if ($password_validation !== true) {
                $errors = array_merge($errors, $password_validation);
            }
            
            if ($form_data['password'] !== $form_data['confirm_password']) {
                $errors[] = "Las contraseñas no coinciden";
            }

            $usuarioModel = new Usuario($this->db);

            if ($usuarioModel->verificarEmailExistente($form_data['email'])) {
                $errors[] = "El correo electrónico ya está registrado";
            }

            if ($usuarioModel->verificarUsuarioExistente($form_data['usuario'])) {
                $errors[] = "El nombre de usuario ya está en uso";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'form_data' => $form_data];
        } else {
            try {
                $nombre_completo = explode(' ', trim($form_data['nombre']), 2);
                $nombre = $nombre_completo[0];
                $apellido = isset($nombre_completo[1]) ? $nombre_completo[1] : '';
                
                $hashed_password = hash('sha256', $form_data['password']);
                
                $usuarioModel->registrarUsuario($nombre, $apellido, $form_data['email'], $form_data['usuario'], $hashed_password);

                return ['success' => true];
                
            } catch (Exception $e) {
                return ['success' => false, 'errors' => ["Error al registrar el usuario: " . $e->getMessage()], 'form_data' => $form_data];
            }
        }
    }
}
