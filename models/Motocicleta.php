<?php
class Motocicleta {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerMotocicletas($brandFilter = '', $modelFilter = '', $ccFilter = '') {
    try {
        $query = "
        SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
               M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
        FROM MOTOCICLETA M
        INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
        WHERE 1=1
        ";

        $params = [];

        if (!empty($brandFilter)) {
            $query .= " AND MM.marca LIKE :marca";
            $params[':marca'] = "%{$brandFilter}%";
        }

        if (!empty($modelFilter)) {
            $query .= " AND MM.modelo LIKE :modelo";
            $params[':modelo'] = "%{$modelFilter}%";
        }

        if (!empty($ccFilter) && $ccFilter > 0) {
            $query .= " AND MM.cilindrada = :cilindrada";
            $params[':cilindrada'] = $ccFilter;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        // Asegurar que siempre se devuelva un array
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultados ?: [];

    } catch (PDOException $e) {
        error_log('Error en obtenerMotocicletas: ' . $e->getMessage());
        // En caso de error, devolver un array vacío
        return [];
    }
}


    public function calcularTotalMotocicletas($motocicletas) {
        return array_sum(array_column($motocicletas, 'cantidad'));
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
}
