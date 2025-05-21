<?php
$DEFAULT_AVATAR = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJzNC40NzcgMTAgMTAgMTAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnptMCAyYzQuNDE4IDAgOCAzLjU4MiA4IDhzLTMuNTgyIDgtOCA4LTgtMy41ODItOC04IDMuNTgyLTggOC04eiIvPjxwYXRoIGQ9Ik0xMiAzYy0yLjIxIDAtNCAxLjc5LTQgNHMxLjc5IDQgNCA0IDQtMS43OSA0LTRzLTEuNzktNC00LTR6bTAgN2MtMy4zMTMgMC02IDIuNjg3LTYgNnYxaDEydi0xYzAtMy4zMTMtMi42ODctNi02LTZ6Ii8+PC9zdmc+');

require_once '../config/conexion.php';

class AdminController {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerEstadisticas() {
        $stats = [];
        try {
            // Total motocicletas disponibles
            $stmt = $this->conn->query("SELECT SUM(cantidad) FROM MOTOCICLETA WHERE estado = 'Disponible'");
            $stats['motocicletas'] = $stmt->fetchColumn() ?? 0;

            // Ventas del mes
            $stmt = $this->conn->query("SELECT COUNT(*) as count, SUM(v.monto_total) as total 
                             FROM VENTA v
                             WHERE MONTH(v.fecha_venta) = MONTH(CURRENT_DATE())");
            $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['ventas_count'] = $ventas['count'];
            $stats['ventas_total'] = $ventas['total'] ?? 0;

            // Mantenimientos del mes
            $stmt = $this->conn->query("SELECT COUNT(*) as count, SUM(m.costo) as total 
                             FROM MANTENIMIENTO m
                             WHERE MONTH(m.fecha) = MONTH(CURRENT_DATE())");
            $mantenimientos = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['mantenimientos_count'] = $mantenimientos['count'];
            $stats['mantenimientos_total'] = $mantenimientos['total'] ?? 0;

            // Accesorios en stock
            $stmt = $this->conn->query("SELECT SUM(cantidad) FROM ACCESORIO WHERE estado = 'Disponible'");
            $stats['accesorios'] = $stmt->fetchColumn() ?? 0;

            return $stats;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerUltimasVentas() {
        try {
            $stmt = $this->conn->query("SELECT v._id, v.fecha_venta, v.monto_total, p.nombre as cliente
                             FROM VENTA v
                             JOIN CLIENTE c ON v.id_cliente = c._id
                             JOIN PERSONA p ON c._id = p._id
                             ORDER BY v.fecha_venta DESC LIMIT 5");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUltimasVentas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerVentasMensuales() {
        try {
            $stmt = $this->conn->query("SELECT MONTH(fecha_venta) as mes, SUM(monto_total) as total 
                             FROM VENTA 
                             WHERE YEAR(fecha_venta) = YEAR(CURRENT_DATE())
                             GROUP BY MONTH(fecha_venta)");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Datos de ejemplo si hay error
            return [
                ['mes' => 1, 'total' => 120000],
                ['mes' => 2, 'total' => 150000],
                ['mes' => 3, 'total' => 180000],
                ['mes' => 4, 'total' => 160000],
                ['mes' => 5, 'total' => 200000],
                ['mes' => 6, 'total' => 220000]
            ];
        }
    }

    public function obtenerMantenimientosMensuales() {
        try {
            $stmt = $this->conn->query("SELECT MONTH(fecha) as mes, SUM(costo) as total 
                             FROM MANTENIMIENTO 
                             WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
                             GROUP BY MONTH(fecha)");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Datos de ejemplo si hay error
            $ventas = $this->obtenerVentasMensuales();
            $mantenimientos = [];
            foreach ($ventas as $venta) {
                $mantenimientos[] = [
                    'mes' => $venta['mes'],
                    'total' => $venta['total'] * 0.3
                ];
            }
            return $mantenimientos;
        }
    }

    public function obtenerDatosUsuario($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                                 FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                                 WHERE p._id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userData['foto'] = !empty($userData['foto']) ? 
                'data:image/jpeg;base64,'.base64_encode($userData['foto']) : 
                DEFAULT_AVATAR;
            
            return $userData;
        } catch (PDOException $e) {
            error_log("Error en obtenerDatosUsuario: " . $e->getMessage());
            return [
                'nombre' => 'Usuario',
                'apellido' => '',
                'foto' => DEFAULT_AVATAR,
                'cargo' => 'No especificado'
            ];
        }
    }
}
