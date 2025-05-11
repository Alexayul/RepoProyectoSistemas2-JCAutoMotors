<?php
/**
 * Modelo para la gestión de productos (motocicletas y accesorios)
 */
class ProductoModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obtiene todos los productos disponibles para la venta
     */
    public function getProductosDisponibles() {
        $stmtProductos = $this->conn->query("CALL sp_obtener_productos_disponibles()");
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        $stmtProductos->closeCursor();
        return $productos;
    }
}