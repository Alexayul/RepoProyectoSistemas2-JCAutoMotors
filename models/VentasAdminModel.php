<?php
class VentasAdminModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerDatosUsuario($user_id) {
        $stmt = $this->conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                                      FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                                      WHERE p._id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Procesar la imagen si existe
        if (!empty($userData['foto'])) {
            $userData['foto'] = 'data:image/jpeg;base64,' . base64_encode($userData['foto']);
        } else {
            $userData['foto'] = 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png';
        }

        return $userData;
    }

    public function obtenerClientes() {
        $stmt = $this->conn->query("CALL sp_obtener_clientes()");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerVentas() {
        $stmt = $this->conn->query("CALL sp_obtener_ventas_todo()");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductosDisponibles() {
        $stmt = $this->conn->query("CALL sp_obtener_productos_disponibles()");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $productos;
    }

    public function obtenerDetalleVenta($id_venta) {
        $stmtVenta = $this->conn->prepare("CALL sp_obtener_info_venta(:id_venta)");
        $stmtVenta->execute(['id_venta' => $id_venta]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
        $stmtVenta->closeCursor();

        $stmtProductos = $this->conn->prepare("CALL sp_obtener_detalles_venta(:id_venta)");
        $stmtProductos->execute(['id_venta' => $id_venta]);
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        $stmtProductos->closeCursor();

        return ['venta' => $venta, 'productos' => $productos];
    }

    public function completarVenta($id_venta) {
        $stmt = $this->conn->prepare("CALL sp_completar_venta(:id_venta)");
        $stmt->execute(['id_venta' => $id_venta]);
        $stmt->closeCursor();
    }

    public function registrarVenta($data, $user_id) {
        try {
            $this->conn->beginTransaction();

            $stmtVenta = $this->conn->prepare("CALL sp_insertar_venta_completa(
                :cliente, :empleado, :tipo_pago, :monto, :adelanto, :saldo, :estado, @id_venta)");
            $stmtVenta->execute([
                'cliente' => $data['cliente'],
                'empleado' => $user_id,
                'tipo_pago' => $data['tipo_pago'],
                'monto' => $data['monto_total'],
                'adelanto' => $data['adelanto'],
                'saldo' => $data['saldo_pendiente'],
                'estado' => $data['estado']
            ]);
            $stmtVenta->closeCursor();

            $stmt = $this->conn->query("SELECT @id_venta as id_venta");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $id_venta = $result['id_venta'];

            if (!$id_venta) {
                throw new Exception("No se pudo obtener el ID de la venta.");
            }

            foreach ($data['productos'] as $producto) {
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

                $procedure = $producto['tipo'] === 'motocicleta' ? "CALL sp_actualizar_inventario_moto(:id, :cantidad, @resultado)" : "CALL sp_actualizar_inventario_accesorio(:id, :cantidad, @resultado)";
                $stmtUpdate = $this->conn->prepare($procedure);
                $stmtUpdate->execute([
                    'id' => $producto['id'],
                    'cantidad' => $producto['cantidad']
                ]);
                $stmtUpdate->closeCursor();

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
            throw new Exception("Error al registrar la venta: " . $e->getMessage());
        }
    }
}