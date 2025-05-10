<?php
require_once __DIR__ . '/../models/Accesorio.php';

class AccesoriosController {
    private $model;
    private $usuario;
    private $conexion;

    public function __construct($conexion, $usuario) {
        $this->conexion = $conexion;
        $this->usuario = $usuario;
        $this->model = new Accesorio($conexion);
    }

    public function index() {
        // Verificaciones de seguridad
        if (!$this->usuario) {
            die("Error: Usuario no definido");
        }

        if (!$this->conexion) {
            die("Error: Conexión a base de datos no establecida");
        }

        // Inicializar filtros
        $nombreFilter = $_POST['nombre_accesorio'] ?? '';
        $categoriaFilter = $_POST['categoria'] ?? '';

        try {
            // Obtener accesorios
            $accesorios = $this->model->obtenerAccesorios($nombreFilter, $categoriaFilter);
            
            // Calcular total de accesorios
            $totalAccesorios = $this->model->calcularTotalAccesorios($accesorios);

            // Preparar datos para la vista
            $datos = [
                'usuario_logueado' => $this->usuario,
                'accesorios' => $accesorios,
                'totalAccesorios' => $totalAccesorios,
                'nombreFilter' => $nombreFilter,
                'categoriaFilter' => $categoriaFilter
            ];

            // Renderizar vista con datos
            $this->renderView($datos);

        } catch (Exception $e) {
            die("Error al procesar accesorios: " . $e->getMessage());
        }
    }

    private function renderView($datos) {
        // Extraer variables para usar en la vista
        extract($datos);
        
        // Incluir la vista
        require __DIR__ . '/../pages/accesorios.php';
    }
}
