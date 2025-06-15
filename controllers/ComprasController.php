<?php
require_once '../config/conexion.php';  // Asegúrate que este archivo existe y define la clase Conexion
require_once '../models/ComprasModel.php';

class ComprasController {
     private $model;

    public function __construct($conn = null) {
        // Si no se pasa una conexión, usa la conexión global $conn
        if ($conn === null) {
            global $conn; // Usa la conexión creada en conexion.php
        }
        $this->model = new ComprasModel($conn);
    }

    public function mostrarCompras() {
        session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: ../pages/login.php');
            exit();
        }

        $id_usuario = $_SESSION['user']['id'];
        $cliente_data = $this->model->obtenerIdCliente($id_usuario);

        if (!$cliente_data) {
            header('Location: ../pages/login.php');
            exit();
        }

        $id_cliente = $cliente_data['id_cliente'];
        $compras = $this->model->obtenerComprasCliente($id_cliente);

        foreach ($compras as &$compra) {
            $compra['motos'] = $this->model->obtenerDetalleMotocicletas($compra['_id']);
            $compra['accesorios'] = $this->model->obtenerDetalleAccesorios($compra['_id']);
        }
        unset($compra);

        return [
            'compras' => $compras ? $compras : []
        ];
    }
}
?>