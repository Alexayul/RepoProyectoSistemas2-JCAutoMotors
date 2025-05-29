<?php
require_once '../config/conexion.php';
require_once '../controllers/CatalogoController.php';

class EmpleadoController {
    private $conn;
    private $usuario;
    private $catalogo;

    public function __construct($conexion, $usuario) {
        $this->conn = $conexion;
        $this->usuario = $usuario;
        $this->catalogo = new CatalogoController($conexion);
    }

    public function procesarVista() {
        if (!$this->usuario) {
            header('Location: login.php');
            exit;
        }

        $brandFilter = isset($_POST['brand']) ? trim($_POST['brand']) : '';
        $modelFilter = isset($_POST['model']) ? trim($_POST['model']) : '';
        $ccFilter = isset($_POST['cc']) ? (int)$_POST['cc'] : '';

        try {
            $motocicletas = $this->catalogo->obtenerMotocicletas($brandFilter, $modelFilter, $ccFilter);

            $totalMotos = array_sum(array_column($motocicletas, 'cantidad'));
            $modelosUnicos = count(array_unique(array_column($motocicletas, 'modelo')));
            $marcasUnicas = count(array_unique(array_column($motocicletas, 'marca')));

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

            extract($datos);
            require '../pages/empleado.php';

        } catch (Exception $e) {
            error_log("Error al cargar los datos: " . $e->getMessage());
            die("<div class='alert alert-danger'>Error al cargar los datos. Por favor, intente nuevamente.</div>");
        }
    }
}
