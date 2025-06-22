<?php
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../models/Usuario.php';

class ClienteController {
    private $model;

    public function __construct($db) {
        $this->model = new ClienteModel($db);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function get($id) {
        return $this->model->getById($id);
    }

    public function store($data) {
        $clienteData = $data['cliente'];
        $personaData = $data['persona'];
        $usuario = $data['usuario'];
        $password = $data['password'];

        // Validar contraseña ANTES de guardar
        $password_validation = $this->isSecurePassword($password);
        if ($password_validation !== true) {
            $_SESSION['registro_errors'] = $password_validation;
            $_SESSION['form_data'] = $data;
            header("Location: ../pages/clientesA.php");
            exit;
        }

        // Verificar si el usuario ya existe usando el modelo Usuario
        $usuarioModel = new Usuario($this->model->getDb());
        if ($usuarioModel->verificarUsuarioExistente($usuario)) {
            $_SESSION['registro_errors'] = ['El usuario ya existe'];
            $_SESSION['form_data'] = $data;
            header("Location: ../pages/clientesA.php");
            exit;
        }

        // Verificar si el documento de identidad ya está registrado
        if ($this->model->existeDocumento($personaData['documento_identidad'])) {
            $_SESSION['mensaje_error'] = "El documento de identidad ya está registrado.";
            header("Location: ../pages/clientesA.php");
            exit;
        }

        // Verificar si el correo electrónico ya está registrado
        if ($this->model->existeEmail($personaData['email'])) {
            $_SESSION['mensaje_error'] = "El correo electrónico ya está registrado.";
            header("Location: ../pages/clientesA.php");
            exit;
        }

        // Procesar imágenes
        // Procesar croquis
        if (isset($_FILES['croquis_domicilio']) && $_FILES['croquis_domicilio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['croquis_domicilio'] = file_get_contents($_FILES['croquis_domicilio']['tmp_name']);
        } else {
            $clienteData['croquis_domicilio'] = null;
        }
        
        // Procesar factura
        if (isset($_FILES['factura_servicio']) && $_FILES['factura_servicio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['factura_servicio'] = file_get_contents($_FILES['factura_servicio']['tmp_name']);
        } else {
            $clienteData['factura_servicio'] = null;
        }
        
        // Crear persona y cliente
        $persona_id = $this->model->create($personaData, $clienteData);

        // Crear usuario
        $usuarioModel = new Usuario($this->model->getDb());
        $passwordHash = hash('sha256', $password);
        $usuarioModel->registrarUsuario(
            $personaData['nombre'],
            $personaData['apellido'],
            $personaData['email'],
            $usuario,
            $passwordHash,
            $persona_id // Nuevo parámetro para usar el id_persona ya creado
        );

        return $persona_id;
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

    public function update($id, $data) {
        $clienteData = $data['cliente'];
        $personaData = $data['persona'];
        $usuario = $data['usuario'];
        $password = $data['password'] ?? '';

        // Validar contraseña solo si se proporciona
        if (!empty($password)) {
            $password_validation = $this->isSecurePassword($password);
            if ($password_validation !== true) {
                // Puedes manejar los errores como prefieras, por ejemplo:
                $_SESSION['registro_errors'] = $password_validation;
                $_SESSION['form_data'] = $data;
                header("Location: ../pages/clientesA.php");
                exit;
            }
            $passwordHash = hash('sha256', $password);
            $usuarioModel = new Usuario($this->model->getDb());
            $usuarioModel->actualizarUsuario($id, $usuario, $passwordHash);
        } else {
            $usuarioModel = new Usuario($this->model->getDb());
            $usuarioModel->actualizarUsuario($id, $usuario);
        }

        // Procesar imágenes
        // Procesar croquis (solo si se subió uno nuevo)
        if (isset($_FILES['croquis_domicilio']) && $_FILES['croquis_domicilio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['croquis_domicilio'] = file_get_contents($_FILES['croquis_domicilio']['tmp_name']);
        } else {
            // Mantener el existente si no se subió uno nuevo
            $current = $this->model->getById($id);
            $clienteData['croquis_domicilio'] = $current['croquis_domicilio'] ?? null;
        }
        
        // Procesar factura (solo si se subió una nueva)
        if (isset($_FILES['factura_servicio']) && $_FILES['factura_servicio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['factura_servicio'] = file_get_contents($_FILES['factura_servicio']['tmp_name']);
        } else {
            // Mantener la existente si no se subió una nueva
            $current = $this->model->getById($id);
            $clienteData['factura_servicio'] = $current['factura_servicio'] ?? null;
        }
        
        // Actualizar persona y cliente
        $this->model->update($id, $personaData, $clienteData);

        // Actualizar usuario y contraseña si corresponde
        $usuarioModel = new Usuario($this->model->getDb());
        if (!empty($password)) {
            $passwordHash = hash('sha256', $password);
            $usuarioModel->actualizarUsuario($id, $usuario, $passwordHash);
        } else {
            $usuarioModel->actualizarUsuario($id, $usuario);
        }

        return true;
    }

    public function existeDocumento($documento, $excluir_id = null) {
        return $this->model->existeDocumento($documento, $excluir_id);
    }
    public function existeEmail($email, $excluir_id = null) {
        return $this->model->existeEmail($email, $excluir_id);
    }
}