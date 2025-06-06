<?php
class MantenimientoController {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function getIdEmpleado($id_usuario) {
        // Obtener ID de emp
    }

    public function getClientes() {
        // Obtener lista de clientes
    }

    public function getMotocicletas() {
        // lista de moto
    }

    public function getMantenimientosEmpleado($id_empleado) {
        // Obtener mantenimientos realizados por el empleado
        // Implementar lógica de mantenimientos gratuitos
    }

    public function filtrarMantenimientos($mantenimientos, $filtros) {
        // Filtrar mantenimientos según criterios
    }

    public function crearMantenimiento($datos, $id_empleado) {
        // Lógica para crear mantenimiento
        
        $id_cliente = $datos['cliente'];
        $id_motocicleta = $datos['motocicleta'];
        
        // Verificar si existe una venta previa de esta moto al cliente
        $query_venta = "SELECT fecha_venta FROM VENTA 
                        WHERE id_cliente = ? AND id_motocicleta = ? 
                        AND fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        
        $stmt_venta = $this->conn->prepare($query_venta);
        $stmt_venta->bind_param("ii", $id_cliente, $id_motocicleta);
        $stmt_venta->execute();
        $result_venta = $stmt_venta->get_result();
        
        $es_gratuito = false;
        
        if ($result_venta->num_rows > 0) {
            // Verificar si ya se usó el mantenimiento gratuito
            $query_mantenimiento = "SELECT COUNT(*) as total_gratuitos FROM MANTENIMIENTO 
                                    WHERE id_cliente = ? AND id_motocicleta = ? 
                                    AND es_gratuito = 1";
            
            $stmt_mantenimiento = $this->conn->prepare($query_mantenimiento);
            $stmt_mantenimiento->bind_param("ii", $id_cliente, $id_motocicleta);
            $stmt_mantenimiento->execute();
            $result_mantenimiento = $stmt_mantenimiento->get_result()->fetch_assoc();
            
            // Si no ha usado el mantenimiento gratuito, marcarlo como gratuito
            if ($result_mantenimiento['total_gratuitos'] == 0) {
                $es_gratuito = true;
                $datos['costo'] = 0; // Costo cero para mantenimiento gratuito
            }
        }
        
        // Insertar mantenimiento
        $query_insert = "INSERT INTO MANTENIMIENTO 
                        (id_motocicleta, id_cliente, id_empleado, fecha, tipo, observaciones, costo, es_gratuito) 
                        VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)";
        
        $stmt_insert = $this->conn->prepare($query_insert);
        $stmt_insert->bind_param(
            "iiissdi", 
            $datos['motocicleta'], 
            $datos['cliente'], 
            $id_empleado, 
            $datos['tipo'], 
            $datos['observaciones'], 
            $datos['costo'], 
            $es_gratuito
        );
        
        if ($stmt_insert->execute()) {
            return [
                'success' => true, 
                'message' => $es_gratuito 
                    ? 'Mantenimiento registrado gratuitamente' 
                    : 'Mantenimiento registrado con éxito'
            ];
        } else {
            return [
                'success' => false, 
                'message' => 'Error al registrar el mantenimiento'
            ];
        }
    }
}
