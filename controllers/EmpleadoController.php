<?php
require_once '../config/conexion.php';

class EmpleadoController {
    private $conn;
    private $usuario;

    public function __construct($conexion, $usuario) {
        $this->conn = $conexion;
        $this->usuario = $usuario;
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

    public function obtenerMotocicletas($brandFilter = '') {
        try {
            $query = "
            SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
                   M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
            FROM MOTOCICLETA M
            INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
            ";
            
            if ($brandFilter) {
                $query .= " WHERE MM.marca = :marca";
            }

            $stmt = $this->conn->prepare($query);

            if ($brandFilter) {
                $stmt->bindParam(':marca', $brandFilter, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error al cargar los datos: " . $e->getMessage());
        }
    }

    public function procesarVista() {
        // Verificar si el usuario está logueado
        if (!$this->usuario) {
            header('Location: login.php');
            exit;
        }

        // Inicializar filtros
        $brandFilter = isset($_POST['brand']) ? trim($_POST['brand']) : '';
        $modelFilter = isset($_POST['model']) ? trim($_POST['model']) : '';
        $ccFilter = isset($_POST['cc']) ? (int)$_POST['cc'] : '';

        try {
            // Obtener motocicletas con filtros
            $queryMotos = "
            SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
                   M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
            FROM MOTOCICLETA M
            INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
            WHERE 1=1
            ";

            $params = [];

            if (!empty($brandFilter)) {
                $queryMotos .= " AND MM.marca LIKE :marca";
                $params[':marca'] = "%$brandFilter%";
            }
            if (!empty($modelFilter)) {
                $queryMotos .= " AND MM.modelo LIKE :modelo";
                $params[':modelo'] = "%$modelFilter%";
            }
            if (!empty($ccFilter) && $ccFilter > 0) {
                $queryMotos .= " AND MM.cilindrada = :cilindrada";
                $params[':cilindrada'] = $ccFilter;
            }

            $stmtMotos = $this->conn->prepare($queryMotos);
            $stmtMotos->execute($params);
            $motocicletas = $stmtMotos->fetchAll(PDO::FETCH_ASSOC);

            // Calcular totales
            $totalMotos = array_sum(array_column($motocicletas, 'cantidad'));
            $modelosUnicos = count(array_unique(array_column($motocicletas, 'modelo')));
            $marcasUnicas = count(array_unique(array_column($motocicletas, 'marca')));

            // Preparar datos para la vista
            $datos = [
                'usuario_logueado' => $this->usuario,
                'motocicletas' => $motocicletas,
                'totalMotos' => $totalMotos,
                'modelosUnicos' => $modelosUnicos,
                'marcasUnicas' => $marcasUnicas,
                'brandFilter' => $brandFilter,
                'modelFilter' => $modelFilter,
                'ccFilter' => $ccFilter
            ];

            // Incluir la vista
            extract($datos);
            require '../pages/empleado.php';

        } catch (Exception $e) {
            error_log("Error al cargar los datos: " . $e->getMessage());
            die("<div class='alert alert-danger'>Error al cargar los datos. Por favor, intente nuevamente.</div>");
        }
    }
}
