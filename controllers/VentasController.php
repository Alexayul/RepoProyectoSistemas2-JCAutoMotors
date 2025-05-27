<?php
require_once '../models/VentaModel.php';
require_once '../models/ClienteModel.php';
require_once '../models/ProductoModel.php';
require_once '../models/EmpleadoModel.php';

/**
 * Controlador para la gestión de ventas
 */
class VentasController {
    private $ventaModel;
    private $clienteModel;
    private $productoModel;
    private $empleadoModel;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->ventaModel = new VentaModel($conn);
        $this->clienteModel = new ClienteModel($conn);
        $this->productoModel = new ProductoModel($conn);
        $this->empleadoModel = new EmpleadoModel($conn);
    }
    
    /**
     * Obtiene el ID del empleado basado en el usuario logueado
     */
    public function getIdEmpleado($id_usuario) {
        return $this->empleadoModel->getEmpleadoByUsuario($id_usuario);
    }
    
    /**
     * Obtiene las ventas de un empleado específico
     */
    public function getVentasEmpleado($id_empleado) {
        return $this->ventaModel->getVentasByEmpleado($id_empleado);
    }
    
    /**
     * Obtiene todos los clientes
     */
    public function getClientes() {
        return $this->clienteModel->getAllClientes();
    }
    
    /**
     * Obtiene todos los productos disponibles
     */
    public function getProductos() {
        return $this->productoModel->getProductosDisponibles();
    }
    
    /**
     * Filtra las ventas según los criterios proporcionados
     */
    public function filtrarVentas($ventas, $filtros) {
        return $this->ventaModel->filtrarVentas($ventas, $filtros);
    }
    
    /**
     * Obtiene la información detallada de una venta
     */
    public function getDetalleVenta($id_venta) {
        $venta = $this->ventaModel->getVentaInfo($id_venta);
        $productos = $this->ventaModel->getVentaDetalles($id_venta);
        
        return [
            'venta' => $venta,
            'productos' => $productos
        ];
    }
    
    /**
     * Completa una venta pendiente
     */
    public function completarVenta($id_venta) {
        try {
            $this->ventaModel->completarVenta($id_venta);
            return [
                'success' => true,
                'message' => 'Venta completada exitosamente'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Crea una nueva venta
     */
    public function crearVenta($post_data, $id_empleado) {
        try {
            // Datos generales de la venta
            $id_cliente = $post_data['cliente'];
            $tipo_pago = $post_data['tipo_pago'];
            $adelanto = floatval($post_data['adelanto']);
            
            // Calcular monto total
            $monto_total = 0;
            $productos_venta = [];
            
            foreach ($post_data['productos'] as $producto_id) {
                $tipo = $post_data['tipo_producto'][$producto_id];
                $cantidad = intval($post_data['cantidad'][$producto_id]);
                $precio = floatval($post_data['precio'][$producto_id]);
                
                $subtotal = $precio * $cantidad;
                $monto_total += $subtotal;
                
                $productos_venta[] = [
                    'id' => $producto_id,
                    'tipo' => $tipo,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal
                ];
            }
            
            $estado = ($adelanto >= $monto_total) ? 'Completada' : 'Pendiente';
            $saldo_pendiente = $monto_total - $adelanto;
            
            $datos_venta = [
                'id_cliente' => $id_cliente,
                'id_empleado' => $id_empleado,
                'tipo_pago' => $tipo_pago,
                'monto_total' => $monto_total,
                'adelanto' => $adelanto,
                'saldo_pendiente' => $saldo_pendiente,
                'estado' => $estado
            ];
            
            // Crear la venta en la base de datos
            $this->ventaModel->crearVenta($datos_venta, $productos_venta);
            
            return [
                'success' => true,
                'message' => 'Venta registrada exitosamente!'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ];
        }
    }
}