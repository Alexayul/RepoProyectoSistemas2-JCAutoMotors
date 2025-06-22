<?php
class MantenimientosAdminModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerMantenimientos($filtros = []) {
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
            WHERE 1=1";

        if (!empty($filtros['id_cliente'])) {
            $sql .= " AND c._id = :id_cliente";
        }
        if (!empty($filtros['id_empleado'])) {
            $sql .= " AND e._id = :id_empleado";
        }
        if (!empty($filtros['id_motocicleta'])) {
            $sql .= " AND moto._id = :id_motocicleta";
        }
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND m.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " ORDER BY m.fecha DESC";

        $stmt = $this->conn->prepare($sql);

        if (!empty($filtros['id_cliente'])) {
            $stmt->bindParam(':id_cliente', $filtros['id_cliente'], PDO::PARAM_INT);
        }
        if (!empty($filtros['id_empleado'])) {
            $stmt->bindParam(':id_empleado', $filtros['id_empleado'], PDO::PARAM_INT);
        }
        if (!empty($filtros['id_motocicleta'])) {
            $stmt->bindParam(':id_motocicleta', $filtros['id_motocicleta'], PDO::PARAM_INT);
        }
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $stmt->bindParam(':fecha_inicio', $filtros['fecha_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $filtros['fecha_fin'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClientes() {
        $sql = "SELECT c._id, CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
                FROM CLIENTE c
                INNER JOIN PERSONA p ON c._id = p._id
                ORDER BY p.nombre";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEmpleados() {
        $sql = "SELECT e._id, CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
                FROM EMPLEADO e
                INNER JOIN PERSONA p ON e._id = p._id
                ORDER BY p.nombre";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}