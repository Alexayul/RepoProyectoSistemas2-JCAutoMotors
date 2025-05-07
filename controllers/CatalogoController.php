<?php
require_once '../config/conexion.php';

class CatalogoController {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
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

    public function obtenerMarcas() {
        try {
            $brandStmt = $this->conn->prepare("SELECT DISTINCT marca FROM MODELO_MOTO");
            $brandStmt->execute();
            return $brandStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener marcas: " . $e->getMessage());
        }
    }
}
