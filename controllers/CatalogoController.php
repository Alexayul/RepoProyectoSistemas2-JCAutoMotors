<?php
require_once '../config/conexion.php';
include '../models/CatalogoModel.php';

class CatalogoController {
    private $model;
    private $conn; 

    public function __construct($conexion) {
        $this->conn = $conexion; 
        $this->model = new CatalogoModel($conexion);
    }

    // Método para manejar todas las acciones
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'agregar':
                        $this->agregarMoto();
                        break;
                    case 'editar':
                        $this->editarMoto();
                        break;
                    case 'obtener':
                        $this->obtenerMoto();
                        break;
                }
            }
        }
    }
 public function obtenerDatosUsuario($user_id) {
        return $this->model->obtenerDatosUsuario($user_id);
    }
    public function getColorCode($colorName) {
        $colorMap = [
            'Rojo' => '#dc3545',
            'Azul' => '#0d6efd',
            'Negro' => '#000000',
            'Blanco' => '#ffffff',
            'Verde' => '#28a745',
            'Amarillo' => '#ffc107',
            'Gris' => '#6c757d',
            'Naranja' => '#fd7e14',
            'Morado' => '#6f42c1',
            'Rosado' => '#e83e8c',
            'Negro Mate' => '#0a0a0a',
            'Turquesa' => '#40e0d0', 
            'Blanco combinado' => '#f8f9fa',
        ];
        
        return $colorMap[$colorName] ?? '#6c757d'; 
    }

    public function obtenerMotocicletas($brandFilter = '', $modelFilter = '', $ccFilter = '') {
        try {
            $query = "
            SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
              M.color, M.precio, M.fecha_ingreso, M.cantidad 
            FROM MOTOCICLETA M
            INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
            WHERE M.cantidad >= 0
            ";

            $params = [];

            if (!empty($brandFilter)) {
                $query .= " AND MM.marca LIKE :marca";
                $params[':marca'] = "%$brandFilter%";
            }
            if (!empty($modelFilter)) {
                $query .= " AND MM.modelo LIKE :modelo";
                $params[':modelo'] = "%$modelFilter%";
            }
            if (!empty($ccFilter) && $ccFilter > 0) {
                $query .= " AND MM.cilindrada = :cilindrada";
                $params[':cilindrada'] = $ccFilter;
            }

            // Elimina el punto y coma final de la consulta antes de preparar
            $query = rtrim($query, "; \n\t");

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error al cargar los datos: " . $e->getMessage());
        }
    } // This closing brace was missing
    
 private function agregarMoto() {
        session_start();

        try {
            // Procesar la imagen
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
            }

            // Verificar si el modelo ya existe
            $modeloExistente = $this->model->buscarModelo(
                $_POST['marca'], 
                $_POST['modelo'], 
                $_POST['cilindrada']
            );

            if (!$modeloExistente) {
                // Crear nuevo modelo
                $idModelo = $this->model->crearModelo(
                    $_POST['marca'], 
                    $_POST['modelo'], 
                    $_POST['cilindrada'], 
                    $imagen
                );
            } else {
                $idModelo = $modeloExistente['_id'];
                // Actualizar imagen si se proporcionó una nueva
                if ($imagen) {
                    $this->model->actualizarImagenModelo($idModelo, $imagen);
                }
            }

            // Crear la motocicleta
            $this->model->crearMotocicleta(
                $idModelo,
                $_POST['color'],
                $_POST['precio'],
                $_POST['fecha_ingreso'],
                $_POST['cantidad']
            );

            $_SESSION['success'] = 'Motocicleta agregada exitosamente';
            // Redirigir según el origen del formulario
            if (isset($_POST['origen']) && $_POST['origen'] === 'catalogoA') {
                header('Location: catalogoA.php');
            } else {
                header('Location: empleado.php');
            }
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al agregar la motocicleta: ' . $e->getMessage();
            if (isset($_POST['origen']) && $_POST['origen'] === 'catalogoA') {
                header('Location: catalogoA.php');
            } else {
                header('Location: empleado.php');
            }
            exit;
        }
    }

    private function editarMoto() {
        session_start();

        try {
            // Procesar la imagen si se subió una nueva
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
            }

            // Obtener la moto existente
            $motoExistente = $this->model->obtenerMotocicletaPorId($_POST['moto_id']);
            $modeloExistente = $this->model->obtenerModeloPorId($motoExistente['id_modelo']);

            // Verificar si los datos del modelo han cambiado
            $necesitaActualizarModelo = 
                $modeloExistente['marca'] !== $_POST['marca'] ||
                $modeloExistente['modelo'] !== $_POST['modelo'] ||
                $modeloExistente['cilindrada'] != $_POST['cilindrada'];

            if ($necesitaActualizarModelo) {
                // Buscar si ya existe un modelo con los nuevos datos
                $nuevoModelo = $this->model->buscarModelo(
                    $_POST['marca'], 
                    $_POST['modelo'], 
                    $_POST['cilindrada']
                );

                if ($nuevoModelo) {
                    $idModelo = $nuevoModelo['_id'];
                } else {
                    // Crear un nuevo modelo
                    $idModelo = $this->model->crearModelo(
                        $_POST['marca'], 
                        $_POST['modelo'], 
                        $_POST['cilindrada'], 
                        $imagen ?: $modeloExistente['imagen']
                    );
                }
            } else {
                $idModelo = $modeloExistente['_id'];
                // Actualizar la imagen del modelo si se proporcionó una nueva
                if ($imagen) {
                    $this->model->actualizarImagenModelo($idModelo, $imagen);
                }
            }

            // Actualizar la motocicleta
            $this->model->actualizarMotocicleta(
                $_POST['moto_id'],
                $idModelo,
                $_POST['color'],
                $_POST['precio'],
                $_POST['fecha_ingreso'],
                $_POST['cantidad']
            );

            $_SESSION['success'] = 'Motocicleta actualizada exitosamente';
            if (isset($_POST['origen']) && $_POST['origen'] === 'catalogoA') {
                header('Location: catalogoA.php');
            } else {
                header('Location: empleado.php');
            }
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar la motocicleta: ' . $e->getMessage();
            if (isset($_POST['origen']) && $_POST['origen'] === 'catalogoA') {
                header('Location: catalogoA.php');
            } else {
                header('Location: empleado.php');
            }
            exit;
        }
    }

    private function obtenerMoto() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_POST['moto_id'])) {
                throw new Exception('ID de moto no proporcionado');
            }

            $motoData = $this->model->obtenerDetallesCompletosMoto($_POST['moto_id']);

            if (!$motoData) {
                throw new Exception('Moto no encontrada');
            }

            echo json_encode([
                'success' => true,
                'data' => $motoData
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function obtenerMarcas() {
        return $this->model->obtenerMarcas();
    }

    public function obtenerMotocicletasC($brandFilter = '', $modelFilter = '', $ccFilter = '') {
    try {
        $query = "
        SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
          M.color, M.precio, M.fecha_ingreso, M.cantidad 
        FROM MOTOCICLETA M
        INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
        WHERE M.cantidad > 0";  // Eliminé el punto y coma aquí

        $params = [];

        if (!empty($brandFilter)) {
            $query .= " AND MM.marca LIKE :marca";
            $params[':marca'] = "%$brandFilter%";
        }
        if (!empty($modelFilter)) {
            $query .= " AND MM.modelo LIKE :modelo";
            $params[':modelo'] = "%$modelFilter%";
        }
        if (!empty($ccFilter) && $ccFilter > 0) {
            $query .= " AND MM.cilindrada = :cilindrada";
            $params[':cilindrada'] = $ccFilter;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        throw new Exception("Error al cargar los datos: " . $e->getMessage());
    }
}
}