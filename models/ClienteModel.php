<?php
/**
 * Modelo para la gestión de clientes
 */
class ClienteModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obtiene todos los clientes de la base de datos
     */
    public function getAllClientes() {
        $stmtClientes = $this->conn->query("CALL sp_obtener_clientes()");
        $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
        $stmtClientes->closeCursor();
        return $clientes;
    }
}