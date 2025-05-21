<?php
/**
 * Modelo para la gestión de empleados
 */
class EmpleadoModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obtiene el ID del empleado basado en el ID de usuario
     */
    public function getEmpleadoByUsuario($id_usuario) {
        try {
            // Intenta con la relación directa
            $stmt = $this->conn->prepare("SELECT e._id as id_empleado 
                                         FROM EMPLEADO e
                                         INNER JOIN USUARIO u ON e._id = u._id
                                         WHERE u._id = :id_usuario");
            
            $stmt->execute(['id_usuario' => $id_usuario]);
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($empleado) {
                return $empleado['id_empleado'];
            }
            
            // Si no funciona, intenta con la relación a través de PERSONA
            $stmt = $this->conn->prepare("SELECT e._id as id_empleado 
                                         FROM EMPLEADO e
                                         INNER JOIN PERSONA p ON e._id = p._id
                                         INNER JOIN USUARIO u ON p._id = u.id_persona
                                         WHERE u._id = :id_usuario");
            
            $stmt->execute(['id_usuario' => $id_usuario]);
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$empleado) {
                throw new Exception("No se encontró un empleado asociado con este usuario.");
            }
            
            return $empleado['id_empleado'];
            
        } catch (PDOException $e) {
            throw new Exception("Error de base de datos: " . $e->getMessage());
        }
    }
}