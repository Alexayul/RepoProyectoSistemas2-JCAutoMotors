<?php
class CreditoModel {
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
   public function obtenerCreditosDirectos($filtros = []) {
    // Consulta base
    $query = "SELECT c._id, CONCAT(p.nombre, ' ', p.apellido) as nombre,
             p.documento_identidad, p.telefono, p.email,
             v._id as id_venta, v.fecha_venta, v.monto_total, 
             v.adelanto, v.saldo_pendiente, v.estado,
             (SELECT COUNT(*) FROM PAGOS_PROGRAMADOS pp WHERE pp.id_venta = v._id) as total_pagos,
             (SELECT COUNT(*) FROM PAGOS_PROGRAMADOS pp WHERE pp.id_venta = v._id AND pp.estado = 'Completada') as pagos_realizados
             FROM CLIENTE c
             JOIN PERSONA p ON c._id = p._id
             JOIN VENTA v ON c._id = v.id_cliente
             WHERE v.tipo_pago = 'Crédito Directo' AND v.estado = 'Pendiente'";
    
    // Parámetros para la consulta preparada
    $params = [];
    
    // Filtro por cliente (nombre, documento o teléfono)
    if (!empty($filtros['cliente'])) {
        $query .= " AND (p.nombre LIKE :cliente OR p.apellido LIKE :cliente 
                   OR p.documento_identidad LIKE :cliente OR p.telefono LIKE :cliente)";
        $params[':cliente'] = '%' . $filtros['cliente'] . '%';
    }
    
    // Filtro por fecha de venta
    if (!empty($filtros['fecha_venta'])) {
        $hoy = date('Y-m-d');
        
        switch ($filtros['fecha_venta']) {
            case 'hoy':
                $query .= " AND DATE(v.fecha_venta) = :fecha_venta";
                $params[':fecha_venta'] = $hoy;
                break;
            case 'semana':
                $query .= " AND YEARWEEK(v.fecha_venta, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'mes':
                $query .= " AND MONTH(v.fecha_venta) = MONTH(CURRENT_DATE()) 
                           AND YEAR(v.fecha_venta) = YEAR(CURRENT_DATE())";
                break;
            case 'rango':
                if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                    $query .= " AND DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                break;
        }
    }
    
    // Filtro por saldo pendiente
    if (!empty($filtros['saldo'])) {
        switch ($filtros['saldo']) {
            case '0-500':
                $query .= " AND v.saldo_pendiente BETWEEN 0 AND 500";
                break;
            case '500-1000':
                $query .= " AND v.saldo_pendiente BETWEEN 500 AND 1000";
                break;
            case '1000+':
                $query .= " AND v.saldo_pendiente > 1000";
                break;
        }
    }
    
    // Filtro por días de atraso
    if (!empty($filtros['atraso'])) {
        $hoy = date('Y-m-d');
        
        // Subconsulta para contar pagos atrasados por venta
        $query .= " AND EXISTS (
            SELECT 1 FROM PAGOS_PROGRAMADOS pp 
            WHERE pp.id_venta = v._id 
            AND pp.estado IN ('Pendiente', 'Atrasado')
            AND pp.fecha_pago < :hoy";
        
        switch ($filtros['atraso']) {
            case 'al-dia':
                $query .= " AND DATEDIFF(:hoy, pp.fecha_pago) = 0";
                break;
            case '1-7':
                $query .= " AND DATEDIFF(:hoy, pp.fecha_pago) BETWEEN 1 AND 7";
                break;
            case '7+':
                $query .= " AND DATEDIFF(:hoy, pp.fecha_pago) > 7";
                break;
        }
        
        $query .= ")";
        $params[':hoy'] = $hoy;
    }
    
    // Orden por defecto
    $query .= " ORDER BY v.fecha_venta DESC";
    
    // Preparar y ejecutar la consulta
    $stmt = $this->conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function obtenerPagosProgramados($idVenta) {
        $query = "SELECT * FROM PAGOS_PROGRAMADOS 
                 WHERE id_venta = :id_venta
                 ORDER BY fecha_pago ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular mora en tiempo real para cuotas 'Atrasado' o 'Pendiente'
        $hoy = new DateTime();
        foreach ($pagos as &$pago) {
            if (
                ($pago['estado'] == 'Atrasado' || $pago['estado'] == 'Pendiente')
                && isset($pago['fecha_pago'])
            ) {
                $fechaPago = new DateTime($pago['fecha_pago']);
                if ($hoy > $fechaPago) {
                    $diasAtraso = $fechaPago->diff($hoy)->days;
                    $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
                    $pago['monto_mora'] = $mora;
                    
                    // Actualizar la mora en la base de datos si es diferente
                    if (abs($mora - floatval($pago['monto_mora'] ?? 0)) > 0.01) {
                        $this->actualizarMora($pago['id_pago'], $mora);
                    }
                } else {
                    $pago['monto_mora'] = 0;
                }
            } else {
                if (!isset($pago['monto_mora'])) $pago['monto_mora'] = 0;
            }
        }
        return $pagos;
    }

    // Nuevo método para actualizar la mora en la base de datos
    private function actualizarMora($idPago, $mora) {
        $query = "UPDATE PAGOS_PROGRAMADOS SET monto_mora = ? WHERE id_pago = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$mora, $idPago]);
    }

    // Método para obtener el total de pagos y total con mora
    public function obtenerTotalesPagos($idVenta) {
        $pagos = $this->obtenerPagosProgramados($idVenta);
        $total = 0;
        $totalConMora = 0;
        
        foreach ($pagos as $pago) {
            $total += floatval($pago['monto']);
            $mora = floatval($pago['monto_mora'] ?? 0);
            $totalConMora += floatval($pago['monto']) + $mora;
        }
        
        return [
            'total' => $total,
            'total_con_mora' => $totalConMora
        ];
    }

    // Método para programar pagos personalizados
    public function programarPagosPersonalizados($idVenta, $fechas_pago, $montos) {
        try {
            $this->conn->beginTransaction();
            
            // Eliminar pagos existentes si los hay
            $deleteQuery = "DELETE FROM PAGOS_PROGRAMADOS WHERE id_venta = ?";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->execute([$idVenta]);

            // Insertar nuevos pagos programados (agregar monto_pagado = 0)
            $insertQuery = "INSERT INTO PAGOS_PROGRAMADOS 
                           (id_venta, fecha_pago, monto, estado, monto_mora, fecha_pagado, monto_pagado) 
                           VALUES (?, ?, ?, 'Pendiente', 0.00, NULL, 0.00)";
            $insertStmt = $this->conn->prepare($insertQuery);

            for ($i = 0; $i < count($fechas_pago); $i++) {
                $insertStmt->execute([
                    $idVenta,
                    $fechas_pago[$i],
                    $montos[$i]
                ]);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function registrarPago($idPago, $montoPagado = null) {
    try {
        $this->conn->beginTransaction();
        
        // Obtener información del pago
        $stmt = $this->conn->prepare("SELECT * FROM PAGOS_PROGRAMADOS WHERE id_pago = ?");
        $stmt->execute([$idPago]);
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pago) {
            throw new Exception("Pago no encontrado");
        }

        $fechaActual = new DateTime();
        $fechaPago = new DateTime($pago['fecha_pago']);
        $mora = 0;

        // Calcular mora si el pago está atrasado (2% por día de atraso)
        if ($fechaActual > $fechaPago && ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado')) {
            $diasAtraso = $fechaPago->diff($fechaActual)->days;
            $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
        }

        // El total a pagar es cuota + mora
        $totalAPagar = floatval($pago['monto']) + floatval($mora);
        
        // Si se proporciona un montoPagado, usarlo (pero no permitir pagos menores al mínimo)
        if ($montoPagado !== null) {
            $totalPagado = max($totalAPagar, floatval($montoPagado));
        } else {
            $totalPagado = $totalAPagar;
        }

        // Actualizar el pago - Asegurarse de guardar el monto_pagado correctamente
        $updateQuery = "UPDATE PAGOS_PROGRAMADOS 
                       SET estado = 'Completada', 
                           monto_mora = ?,
                           fecha_pagado = ?,
                           monto_pagado = ?
                       WHERE id_pago = ?";
        
        $updateStmt = $this->conn->prepare($updateQuery);
        $result = $updateStmt->execute([
            $mora,
            $fechaActual->format('Y-m-d'),
            $totalPagado,  // Aquí se guarda el monto total pagado (cuota + mora)
            $idPago
        ]);

        // Actualizar el saldo pendiente en la venta
        $this->actualizarSaldoPendienteConMora($pago['id_venta']);

        $this->conn->commit();
        return $result;
    } catch (Exception $e) {
        $this->conn->rollback();
        throw $e;
    }
}private function calcularMora($fechaPagoStr, $montoCuota, $estadoActual) {
    $hoy = new DateTime();
    $fechaPago = new DateTime($fechaPagoStr);
    
    if (($estadoActual == 'Pendiente' || $estadoActual == 'Atrasado') && $hoy > $fechaPago) {
        $diasAtraso = $fechaPago->diff($hoy)->days;
        return round($montoCuota * 0.02 * $diasAtraso, 2);
    }
    return 0;
}

    // Nuevo método: descuenta del saldo pendiente la suma de cuotas y moras pagadas
    private function actualizarSaldoPendienteConMora($idVenta) {
        // Calcular el total pagado (cuotas + moras)
        $query = "SELECT 
                    COALESCE(SUM(monto_pagado),0) as total_pagado
                  FROM PAGOS_PROGRAMADOS 
                  WHERE id_venta = ? AND estado = 'Completada'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idVenta]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPagado = $result['total_pagado'];

        // Obtener el monto total original
        $ventaQuery = "SELECT monto_total, adelanto FROM VENTA WHERE _id = ?";
        $ventaStmt = $this->conn->prepare($ventaQuery);
        $ventaStmt->execute([$idVenta]);
        $venta = $ventaStmt->fetch(PDO::FETCH_ASSOC);

        $nuevoSaldo = $venta['monto_total'] - $venta['adelanto'] - $totalPagado;
        
        // Actualizar el saldo pendiente
        $updateQuery = "UPDATE VENTA SET saldo_pendiente = ? WHERE _id = ?";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute([$nuevoSaldo, $idVenta]);
    }

    public function verificarYActualizarEstadoCredito($idVenta) {
        try {
            // Verificar si todos los pagos están completos
            $query = "SELECT COUNT(*) as total_pagos, 
                            SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as pagos_completos
                     FROM PAGOS_PROGRAMADOS 
                     WHERE id_venta = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$idVenta]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total_pagos'] > 0 && $resultado['total_pagos'] == $resultado['pagos_completos']) {
                // Todos los pagos están completos, actualizar estado del crédito
                $updateQuery = "UPDATE VENTA SET estado = 'Completada', saldo_pendiente = 0 WHERE _id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                return $updateStmt->execute([$idVenta]);
            }
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerEstadisticasCredito($idVenta) {
        $query = "SELECT 
                    v.monto_total,
                    v.adelanto,
                    v.saldo_pendiente,
                    v.estado as estado_credito,
                    COUNT(pp.id_pago) as total_pagos,
                    SUM(CASE WHEN pp.estado = 'Completada' THEN 1 ELSE 0 END) as pagos_realizados,
                    SUM(CASE WHEN pp.estado = 'Pendiente' AND pp.fecha_pago < CURDATE() THEN 1 ELSE 0 END) as pagos_atrasados,
                    SUM(CASE WHEN pp.estado = 'Completada' THEN pp.monto ELSE 0 END) as monto_pagado,
                    SUM(pp.monto_mora) as total_mora
                  FROM VENTA v
                  LEFT JOIN PAGOS_PROGRAMADOS pp ON v._id = pp.id_venta
                  WHERE v._id = ?
                  GROUP BY v._id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idVenta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método corregido para marcar como no pagado y calcular mora
    public function marcarNoPagado($idPago) {
        try {
            // Obtener información del pago
            $stmt = $this->conn->prepare("SELECT * FROM PAGOS_PROGRAMADOS WHERE id_pago = ?");
            $stmt->execute([$idPago]);
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pago) return false;

            $hoy = new DateTime();
            $fechaPago = new DateTime($pago['fecha_pago']); // Corregido: usar fecha_pago
            $mora = 0;

            // Calcular mora si la fecha de pago ya pasó
            if ($hoy > $fechaPago) {
                $diasAtraso = $fechaPago->diff($hoy)->days;
                $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
            }

            // Actualizar el estado y la mora
            $query = "UPDATE PAGOS_PROGRAMADOS 
                     SET estado = 'Atrasado', 
                         monto_mora = ? 
                     WHERE id_pago = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$mora, $idPago]);
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en marcarNoPagado: " . $e->getMessage());
            return false;
        }
    }

    // Método para actualizar automáticamente pagos atrasados
    public function actualizarPagosAtrasados() {
        try {
            $hoy = new DateTime();
            $query = "SELECT id_pago, monto, fecha_pago FROM PAGOS_PROGRAMADOS 
                     WHERE estado = 'Pendiente' AND fecha_pago < ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$hoy->format('Y-m-d')]);
            $pagosAtrasados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pagosAtrasados as $pago) {
                $fechaPago = new DateTime($pago['fecha_pago']);
                $diasAtraso = $fechaPago->diff($hoy)->days;
                $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);

                $updateQuery = "UPDATE PAGOS_PROGRAMADOS 
                               SET estado = 'Atrasado', monto_mora = ? 
                               WHERE id_pago = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->execute([$mora, $pago['id_pago']]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error actualizando pagos atrasados: " . $e->getMessage());
            return false;
        }
    }

    // Helper para obtener el total a pagar de una cuota (cuota + mora)
    public function obtenerTotalAPagar($idPago) {
        $stmt = $this->conn->prepare("SELECT monto, monto_mora FROM PAGOS_PROGRAMADOS WHERE id_pago = ?");
        $stmt->execute([$idPago]);
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pago) return 0;
        return floatval($pago['monto']) + floatval($pago['monto_mora']);
    }

    public function obtenerCreditosFiltrados($filtros) {
        $where = [];
        $params = [];

        // Filtro por cliente (nombre, documento, teléfono)
        if (!empty($filtros['cliente'])) {
            $where[] = "(CONCAT(p.nombre, ' ', p.apellido) LIKE ? OR p.documento_identidad LIKE ? OR p.telefono LIKE ?)";
            $params[] = '%' . $filtros['cliente'] . '%';
            $params[] = '%' . $filtros['cliente'] . '%';
            $params[] = '%' . $filtros['cliente'] . '%';
        }

        // Filtro por fecha de venta
        if (!empty($filtros['fecha_venta'])) {
            $hoy = date('Y-m-d');
            if ($filtros['fecha_venta'] === 'hoy') {
                $where[] = "v.fecha_venta = ?";
                $params[] = $hoy;
            } elseif ($filtros['fecha_venta'] === 'semana') {
                $where[] = "YEARWEEK(v.fecha_venta, 1) = YEARWEEK(?, 1)";
                $params[] = $hoy;
            } elseif ($filtros['fecha_venta'] === 'mes') {
                $where[] = "YEAR(v.fecha_venta) = YEAR(?) AND MONTH(v.fecha_venta) = MONTH(?)";
                $params[] = $hoy;
                $params[] = $hoy;
            } elseif ($filtros['fecha_venta'] === 'rango') {
                if (!empty($filtros['fecha_desde'])) {
                    $where[] = "v.fecha_venta >= ?";
                    $params[] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $where[] = "v.fecha_venta <= ?";
                    $params[] = $filtros['fecha_hasta'];
                }
            }
        }

        // Filtro por saldo pendiente
        if (!empty($filtros['saldo'])) {
            if ($filtros['saldo'] === '0-500') {
                $where[] = "v.saldo_pendiente >= 0 AND v.saldo_pendiente <= 500";
            } elseif ($filtros['saldo'] === '500-1000') {
                $where[] = "v.saldo_pendiente > 500 AND v.saldo_pendiente <= 1000";
            } elseif ($filtros['saldo'] === '1000+') {
                $where[] = "v.saldo_pendiente > 1000";
            }
        }

        // Filtro por atraso (requiere subconsulta)
        if (!empty($filtros['atraso'])) {
            if ($filtros['atraso'] === 'al-dia') {
                $where[] = "NOT EXISTS (
                    SELECT 1 FROM PAGOS_PROGRAMADOS pp 
                    WHERE pp.id_venta = v._id 
                      AND pp.estado IN ('Pendiente','Atrasado') 
                      AND pp.fecha_pago < CURDATE()
                )";
            } elseif ($filtros['atraso'] === '1-7') {
                $where[] = "EXISTS (
                    SELECT 1 FROM PAGOS_PROGRAMADOS pp 
                    WHERE pp.id_venta = v._id 
                      AND pp.estado IN ('Pendiente','Atrasado') 
                      AND pp.fecha_pago < CURDATE()
                      AND DATEDIFF(CURDATE(), pp.fecha_pago) BETWEEN 1 AND 7
                )";
            } elseif ($filtros['atraso'] === '7+') {
                $where[] = "EXISTS (
                    SELECT 1 FROM PAGOS_PROGRAMADOS pp 
                    WHERE pp.id_venta = v._id 
                      AND pp.estado IN ('Pendiente','Atrasado') 
                      AND pp.fecha_pago < CURDATE()
                      AND DATEDIFF(CURDATE(), pp.fecha_pago) > 7
                )";
            }
        }

        $sql = "SELECT c._id, CONCAT(p.nombre, ' ', p.apellido) as nombre,
                 p.documento_identidad, p.telefono, p.email,
                 v._id as id_venta, v.fecha_venta, v.monto_total, 
                 v.adelanto, v.saldo_pendiente, v.estado,
                 (SELECT COUNT(*) FROM PAGOS_PROGRAMADOS pp WHERE pp.id_venta = v._id) as total_pagos,
                 (SELECT COUNT(*) FROM PAGOS_PROGRAMADOS pp WHERE pp.id_venta = v._id AND pp.estado = 'Completada') as pagos_realizados
            FROM CLIENTE c
            JOIN PERSONA p ON c._id = p._id
            JOIN VENTA v ON c._id = v.id_cliente
            WHERE v.tipo_pago = 'Crédito Directo' AND v.estado = 'Pendiente'";

        if ($where) {
            $sql .= " AND " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY v.fecha_venta DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>