<?php
class ComprasModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerIdCliente($id_usuario) {
        $query = "SELECT c._id as id_cliente 
                 FROM CLIENTE c
                 JOIN USUARIO u ON c._id = u.id_persona
                 WHERE u._id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerComprasCliente($id_cliente) {
        $query = "SELECT v._id, v.fecha_venta, v.monto_total
                  FROM VENTA v
                  WHERE v.id_cliente = ?
                  ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_cliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalleMotocicletas($id_venta) {
        $query = "SELECT mm.marca, mm.modelo, mm.cilindrada, m.color, 
                 dv.precio_unitario, dv.cantidad, dv.subtotal
                 FROM DETALLE_VENTA dv
                 JOIN MOTOCICLETA m ON dv.id_producto = m._id
                 JOIN MODELO_MOTO mm ON m.id_modelo = mm._id
                 WHERE dv.id_venta = ? AND dv.tipo_producto = 'motocicleta'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_venta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalleAccesorios($id_venta) {
        $query = "SELECT a.nombre, a.descripcion, 
                 dv.precio_unitario, dv.cantidad, dv.subtotal
                 FROM DETALLE_VENTA dv
                 JOIN ACCESORIO a ON dv.id_producto = a._id
                 WHERE dv.id_venta = ? AND dv.tipo_producto = 'accesorio'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_venta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>