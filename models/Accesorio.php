<?php
class Accesorio {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerAccesorios($nombreFilter = '', $categoriaFilter = '') {
        try {
            $query = "SELECT * FROM ACCESORIO WHERE 1=1";
            $params = [];

            if (!empty($nombreFilter)) {
                $query .= " AND nombre LIKE :nombre";
                $params[':nombre'] = "%{$nombreFilter}%";
            }

            if (!empty($categoriaFilter)) {
                $query .= " AND categoria = :categoria";
                $params[':categoria'] = $categoriaFilter;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en obtenerAccesorios: ' . $e->getMessage());
            throw new Exception("Error al recuperar accesorios: " . $e->getMessage());
        }
    }

    public function calcularTotalAccesorios($accesorios) {
        // Sumar la cantidad de accesorios
        return array_sum(array_column($accesorios, 'cantidad'));
    }
}
