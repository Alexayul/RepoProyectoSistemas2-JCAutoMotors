<?php
require_once 'config/conexion.php';

class HomeController {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function getTopSellingMotorcycles() {
        try {
            $sql = "SELECT 
                m._id,
                mm.marca,
                mm.modelo,
                mm.imagen,
                m.color,
                m.precio,
                COALESCE(ventas.total_ventas, 0) AS total_ventas
            FROM 
                MOTOCICLETA m
            JOIN 
                MODELO_MOTO mm ON m.id_modelo = mm._id
            LEFT JOIN (
                SELECT 
                    id_producto, 
                    COUNT(*) AS total_ventas
                FROM 
                    DETALLE_VENTA
                WHERE 
                    tipo_producto = 'motocicleta'
                GROUP BY 
                    id_producto
            ) ventas ON m._id = ventas.id_producto
            ORDER BY 
                total_ventas DESC, RAND()
            LIMIT 3";

            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en la consulta: " . $e->getMessage());
            return []; 
        }
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
