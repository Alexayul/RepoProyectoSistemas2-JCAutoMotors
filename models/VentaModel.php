<?php
/**
 * Modelo para la gestión de ventas
 */
class VentaModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obtiene todas las ventas de un empleado específico
     */
    public function getVentasByEmpleado($id_empleado) {
        $stmtVentas = $this->conn->prepare("CALL sp_obtener_ventas_empleado(:id_empleado)");
        $stmtVentas->execute(['id_empleado' => $id_empleado]);
        $ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);
        $stmtVentas->closeCursor();
        return $ventas;
    }
    
    /**
     * Obtiene la información detallada de una venta específica
     */
    public function getVentaInfo($id_venta) {
        $stmtVenta = $this->conn->prepare("CALL sp_obtener_info_venta(:id_venta)");
        $stmtVenta->execute(['id_venta' => $id_venta]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
        $stmtVenta->closeCursor();
        return $venta;
    }
    
    /**
     * Obtiene los productos incluidos en una venta específica
     */
    public function getVentaDetalles($id_venta) {
        $stmtProductos = $this->conn->prepare("CALL sp_obtener_detalles_venta(:id_venta)");
        $stmtProductos->execute(['id_venta' => $id_venta]);
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        $stmtProductos->closeCursor();
        return $productos;
    }
    
    /**
     * Completa una venta pendiente
     */
    public function completarVenta($id_venta) {
        $stmtUpdate = $this->conn->prepare("CALL sp_completar_venta(:id_venta)");
        $stmtUpdate->execute(['id_venta' => $id_venta]);
        $stmtUpdate->closeCursor();
        return true;
    }
    
    /**
     * Crea una nueva venta con todos sus detalles
     */
    public function crearVenta($datos_venta, $productos_venta) {
        try {
            $this->conn->beginTransaction();
            
            // Insertar la venta principal
            $stmtVenta = $this->conn->prepare("CALL sp_insertar_venta_completa(
                :cliente, :empleado, :tipo_pago, :monto, :adelanto, :saldo, :estado, @id_venta)");
            $stmtVenta->execute([
                'cliente' => $datos_venta['id_cliente'],
                'empleado' => $datos_venta['id_empleado'],
                'tipo_pago' => $datos_venta['tipo_pago'],
                'monto' => $datos_venta['monto_total'],
                'adelanto' => $datos_venta['adelanto'],
                'saldo' => $datos_venta['saldo_pendiente'],
                'estado' => $datos_venta['estado']
            ]);
            $stmtVenta->closeCursor();
            
            // Obtener el ID de la venta generada
            $stmt = $this->conn->query("SELECT @id_venta as id_venta");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $id_venta = $result['id_venta'];
            
            // Insertar los detalles de la venta
            foreach ($productos_venta as $producto) {
                // Insertar detalle
                $stmtDetalle = $this->conn->prepare("CALL sp_insertar_detalle_venta(
                    :venta, :producto, :tipo, :precio, :cantidad, :subtotal)");
                $stmtDetalle->execute([
                    'venta' => $id_venta,
                    'producto' => $producto['id'],
                    'tipo' => $producto['tipo'],
                    'precio' => $producto['precio'],
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal']
                ]);
                $stmtDetalle->closeCursor();
                
                // Actualizar inventario
                if ($producto['tipo'] === 'motocicleta') {
                    $stmtUpdate = $this->conn->prepare("CALL sp_actualizar_inventario_moto(:id, :cantidad, @resultado)");
                } else {
                    $stmtUpdate = $this->conn->prepare("CALL sp_actualizar_inventario_accesorio(:id, :cantidad, @resultado)");
                }
                
                $stmtUpdate->execute([
                    'id' => $producto['id'],
                    'cantidad' => $producto['cantidad']
                ]);
                $stmtUpdate->closeCursor();
                
                // Verificar resultado
                $stmtResult = $this->conn->query("SELECT @resultado as resultado");
                $result = $stmtResult->fetch(PDO::FETCH_ASSOC);
                $stmtResult->closeCursor();
                
                if ($result['resultado'] === 0) {
                    throw new Exception("No hay suficiente stock para el producto ID: {$producto['id']}");
                }
            }
            
            $this->conn->commit();
            return $id_venta;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Filtra las ventas según los criterios proporcionados
     */
    public function filtrarVentas($ventas, $filtros) {
        return array_filter($ventas, function($v) use ($filtros) {
            // Filtro por fecha desde
            if ($filtros['fecha_desde'] && strtotime($v['fecha_venta']) < strtotime($filtros['fecha_desde'])) {
                return false;
            }
            // Filtro por fecha hasta
            if ($filtros['fecha_hasta'] && strtotime($v['fecha_venta']) > strtotime($filtros['fecha_hasta'])) {
                return false;
            }
            
            // Filtro por estado
            if ($filtros['estado'] && $v['estado'] !== $filtros['estado']) {
                return false;
            }
            
            // Filtro por tipo de pago
            if ($filtros['tipo_pago'] && $v['tipo_pago'] !== $filtros['tipo_pago']) {
                return false;
            }
            
            return true;
        });
    }
}