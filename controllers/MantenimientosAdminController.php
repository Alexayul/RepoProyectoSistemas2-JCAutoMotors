<?php
require_once '../models/MantenimientosAdminModel.php';

class MantenimientosAdminController {
    private $model;

    public function __construct($conn) {
        $this->model = new MantenimientosAdminModel($conn);
    }

    public function obtenerMantenimientos($filtros = []) {
        $mantenimientos = $this->model->obtenerMantenimientos();

        // Filtrado en PHP (puedes hacerlo en SQL si prefieres)
        if (empty($filtros)) return $mantenimientos;

        return array_filter($mantenimientos, function($m) use ($filtros) {
            if (!empty($filtros['fecha_desde']) && strtotime($m['fecha']) < strtotime($filtros['fecha_desde'])) return false;
            if (!empty($filtros['fecha_hasta']) && strtotime($m['fecha']) > strtotime($filtros['fecha_hasta'])) return false;
            if (!empty($filtros['tipo']) && $m['tipo'] !== $filtros['tipo']) return false;
            if (!empty($filtros['cliente']) && $m['id_cliente'] != $filtros['cliente']) return false;
            if (!empty($filtros['empleado']) && $m['id_empleado'] != $filtros['empleado']) return false;
            return true;
        });
    }

    public function obtenerClientes() {
        return $this->model->obtenerClientes();
    }

    public function obtenerEmpleados() {
        return $this->model->obtenerEmpleados();
    }

    public function obtenerDetalleMantenimiento($id) {
        $sql = "SELECT m.*, 
                       CONCAT(p_cli.nombre, ' ', p_cli.apellido) AS nombre_cliente,
                       CONCAT(p_emp.nombre, ' ', p_emp.apellido) AS nombre_empleado,
                       CONCAT(mm.marca, ' ', mm.modelo) AS modelo_motocicleta
                FROM MANTENIMIENTO m
                INNER JOIN CLIENTE c ON m.id_cliente = c._id
                INNER JOIN PERSONA p_cli ON c._id = p_cli._id
                INNER JOIN EMPLEADO e ON m.id_empleado = e._id
                INNER JOIN PERSONA p_emp ON e._id = p_emp._id
                INNER JOIN MOTOCICLETA moto ON m.id_motocicleta = moto._id
                INNER JOIN MODELO_MOTO mm ON moto.id_modelo = mm._id
                WHERE m._id = :id
                LIMIT 1";
        $stmt = $this->model->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($m) {
            return ['success' => true, 'detalle' => $m];
        } else {
            return ['success' => false, 'message' => 'No encontrado'];
        }
    }
}