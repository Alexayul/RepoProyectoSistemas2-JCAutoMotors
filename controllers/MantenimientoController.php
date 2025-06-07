<?php
require_once '../config/conexion.php';
require_once __DIR__ . '/../models/EmpleadoModel.php';

class MantenimientoController {
    private $conn;
    private $empleadoModel;

    public function __construct($conexion) {
        $this->conn = $conexion;
        $this->empleadoModel = new EmpleadoModel($conexion);
    }

    public function getIdEmpleado($id_usuario) {
        return $this->empleadoModel->getEmpleadoByUsuario($id_usuario);
    }

    public function getClientes() {
        $query = "SELECT p._id, CONCAT(p.nombre, ' ', p.apellido) as nombre_completo 
                  FROM PERSONA p 
                  INNER JOIN CLIENTE c ON p._id = c._id 
                  ORDER BY p.nombre";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMotocicletas() {
        try {
            $query = "SELECT M._id, CONCAT(MM.marca, ' ', MM.modelo) as modelo, M.color 
                      FROM MOTOCICLETA M
                      INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
                      WHERE M.cantidad > 0";
            
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting motorcycles: " . $e->getMessage());
            return [];
        }
    }

    public function getMantenimientosEmpleado($id_empleado) {
        $query = "SELECT m._id, m.fecha, m.tipo, m.observaciones, m.costo, m.es_gratuito,
                         CONCAT(p.nombre, ' ', p.apellido) as nombre_cliente,
                         CONCAT(mm.marca, ' ', mm.modelo) as modelo_motocicleta
                  FROM MANTENIMIENTO m
                  INNER JOIN CLIENTE c ON m.id_cliente = c._id
                  INNER JOIN PERSONA p ON c._id = p._id
                  INNER JOIN MOTOCICLETA moto ON m.id_motocicleta = moto._id
                  INNER JOIN MODELO_MOTO mm ON moto.id_modelo = mm._id
                  WHERE m.id_empleado = :id_empleado
                  ORDER BY m.fecha DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting maintenance records: " . $e->getMessage());
            return [];
        }
    }

public function filtrarMantenimientos($mantenimientos, $filtros) {
    // Validar que $mantenimientos sea un array
    if (!is_array($mantenimientos)) {
        return [];
    }

    // Si no hay filtros, devolver todos los mantenimientos
    if (empty($filtros)) {
        return $mantenimientos;
    }

    // Filtrar mantenimientos
    $resultado = array_filter($mantenimientos, function($mantenimiento) use ($filtros) {
        // Filtro por fecha desde
        if (!empty($filtros['fecha_desde']) && 
            (!isset($mantenimiento['fecha']) || 
             strtotime($mantenimiento['fecha']) < strtotime($filtros['fecha_desde']))) {
            return false;
        }

        // Filtro por fecha hasta
        if (!empty($filtros['fecha_hasta']) && 
            (!isset($mantenimiento['fecha']) || 
             strtotime($mantenimiento['fecha']) > strtotime($filtros['fecha_hasta']))) {
            return false;
        }

        // Filtro por tipo de mantenimiento
        if (!empty($filtros['tipo']) && 
            (!isset($mantenimiento['tipo']) || 
             $mantenimiento['tipo'] !== $filtros['tipo'])) {
            return false;
        }

        // Filtro por cliente
        if (!empty($filtros['cliente'])) {
            $cliente_id = $filtros['cliente'];
            
            // Verificar si existe información del cliente
            if (!isset($mantenimiento['nombre_cliente'])) {
                return false;
            }
            
            // Comparación exacta del ID de cliente
            if (isset($mantenimiento['_id']) && 
                $mantenimiento['_id'] == $cliente_id) {
                return true;
            }
            
            // Comparación por nombre completo
            $nombre_cliente = strtolower($mantenimiento['nombre_cliente']);
            $cliente_seleccionado = $this->getClienteById($cliente_id);
            
            if ($cliente_seleccionado) {
                $nombre_completo_seleccionado = strtolower(
                    trim($cliente_seleccionado['nombre'] . ' ' . $cliente_seleccionado['apellido'])
                );
                
                // Comparación exacta del nombre completo
                if ($nombre_cliente === $nombre_completo_seleccionado) {
                    return true;
                }
            }
            
            return false;
        }

        return true;
    });

    // Registro de depuración
    error_log("Filtros aplicados: " . print_r($filtros, true));
    error_log("Número de mantenimientos antes del filtro: " . count($mantenimientos));
    error_log("Número de mantenimientos después del filtro: " . count($resultado));

    return $resultado;
}

// Método auxiliar para obtener información del cliente
private function getClienteById($cliente_id) {
    try {
        $query = "SELECT p.nombre, p.apellido 
                  FROM PERSONA p
                  INNER JOIN CLIENTE c ON p._id = c._id
                  WHERE p._id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener cliente: " . $e->getMessage());
        return null;
    }
}

    public function crearMantenimiento(array $datos, int $id_empleado): array {
        try {
            $this->conn->beginTransaction();

            // Validación de mantenimiento gratuito previo
            $sqlVerificarGratuito = "SELECT COUNT(*) as mantenimientos_gratuitos 
                                     FROM MANTENIMIENTO 
                                     WHERE id_cliente = :cliente 
                                     AND es_gratuito = 1";
            $stmtVerificar = $this->conn->prepare($sqlVerificarGratuito);
            $stmtVerificar->execute([':cliente' => $datos['cliente']]);
            $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

            // Si ya tiene un mantenimiento gratuito y intenta hacer otro
            if ($resultado['mantenimientos_gratuitos'] > 0 && 
                (isset($datos['es_gratuito']) && $datos['es_gratuito'] == '1')) {
                return [
                    'success' => false,
                    'message' => 'El cliente ya ha utilizado su mantenimiento gratuito anteriormente.',
                    'error_code' => 'MANTENIMIENTO_GRATUITO_PREVIO'
                ];
            }

            // Debug: Registrar datos recibidos
            error_log("Datos recibidos para mantenimiento: " . print_r($datos, true));
            error_log("ID Empleado: " . $id_empleado);

            // Validación mejorada de datos
            $camposRequeridos = ['cliente', 'motocicleta', 'tipo'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    throw new Exception("El campo $campo es requerido");
                }
            }

            // Validación de costo más flexible
            if (!isset($datos['costo_bs']) || 
                (!is_numeric($datos['costo_bs']) && $datos['costo_bs'] !== '')) {
                throw new Exception("El costo debe ser un valor numérico");
            }

            // Asignación de variables con validación
            $id_cliente = (int) $datos['cliente'];
            $id_motocicleta = (int) $datos['motocicleta'];
            $tipo = trim($datos['tipo']);
            $observaciones = trim($datos['observaciones'] ?? '');
            
            // Manejar costo de manera más flexible
            $costo = isset($datos['costo_bs']) ? (float) $datos['costo_bs'] : 0;
            $costo = max(0, $costo); // Asegurar que el costo no sea negativo

            $es_gratuito = isset($datos['es_gratuito']) && $datos['es_gratuito'] == '1';

            // Si es gratuito, forzar costo a 0
            if ($es_gratuito) {
                $costo = 0;
            }

            // Verificar existencia de registros
            $this->verificarExistenciaRegistros($id_cliente, $id_motocicleta, $id_empleado);

            // Insertar mantenimiento
            $sql = "INSERT INTO MANTENIMIENTO (
                id_motocicleta, id_cliente, id_empleado, fecha, 
                tipo, observaciones, costo, es_gratuito
            ) VALUES (
                :motocicleta, :cliente, :empleado, NOW(),
                :tipo, :observaciones, :costo, :gratuito
            )";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':motocicleta' => $id_motocicleta,
                ':cliente' => $id_cliente,
                ':empleado' => $id_empleado,
                ':tipo' => $tipo,
                ':observaciones' => $observaciones,
                ':costo' => $costo,
                ':gratuito' => $es_gratuito ? 1 : 0
            ]);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Mantenimiento registrado exitosamente'
            ];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error PDO en crearMantenimiento: " . $e->getMessage());
            error_log("Código de error: " . $e->getCode());
            error_log("Consulta SQL: " . ($e->errorInfo[2] ?? 'No disponible'));
            
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'error_info' => $e->errorInfo
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error General: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function verificarExistenciaRegistros($id_cliente, $id_motocicleta, $id_empleado) {
        // Verificar cliente
        $stmt = $this->conn->prepare("SELECT 1 FROM CLIENTE WHERE _id = ?");
        $stmt->execute([$id_cliente]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("El cliente seleccionado no existe");
        }

        // Verificar motocicleta
        $stmt = $this->conn->prepare("SELECT 1 FROM MOTOCICLETA WHERE _id = ?");
        $stmt->execute([$id_motocicleta]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("La motocicleta seleccionada no existe");
        }

        // Verificar empleado
        $stmt = $this->conn->prepare("SELECT 1 FROM EMPLEADO WHERE _id = ?");
        $stmt->execute([$id_empleado]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("El empleado no existe");
        }
    }

    public function checkMantenimientoGratuito($cliente_id) {
        try {
            $query = "SELECT COUNT(*) as mantenimientos_gratuitos 
                      FROM MANTENIMIENTO 
                      WHERE id_cliente = :cliente_id 
                      AND es_gratuito = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'tiene_mantenimiento_gratuito' => $resultado['mantenimientos_gratuitos'] > 0
            ];
        } catch (PDOException $e) {
            error_log("Error al verificar mantenimiento gratuito: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al verificar mantenimiento gratuito'
            ];
        }
    }
    public function obtenerDetalleMantenimiento($mantenimiento_id) {
    try {
        $query = "
            SELECT 
                m._id AS id,
                m.fecha,
                m.observaciones AS descripcion,
                m.costo AS costo_bs,
                m.es_gratuito,
                p_cli.nombre AS cliente_nombre,
                p_cli.apellido AS cliente_apellido,
                p_cli.documento_identidad AS cliente_cedula,
                modelo.modelo AS moto_modelo,
                modelo.marca AS moto_marca,
                p_emp.nombre AS empleado_nombre,
                p_emp.apellido AS empleado_apellido,
                m.tipo AS tipo_mantenimiento
            FROM 
                MANTENIMIENTO m
            LEFT JOIN 
                CLIENTE cli ON m.id_cliente = cli._id
            LEFT JOIN 
                PERSONA p_cli ON cli._id = p_cli._id
            LEFT JOIN 
                MOTOCICLETA moto ON m.id_motocicleta = moto._id
            LEFT JOIN 
                MODELO_MOTO modelo ON moto.id_modelo = modelo._id
            LEFT JOIN 
                EMPLEADO emp ON m.id_empleado = emp._id
            LEFT JOIN 
                PERSONA p_emp ON emp._id = p_emp._id
            WHERE 
                m._id = :id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $mantenimiento_id, PDO::PARAM_INT);
        $stmt->execute();

        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$detalle) {
            return [
                'success' => false,
                'message' => 'Mantenimiento no encontrado'
            ];
        }

        // Formatear fecha
        $detalle['fecha_formateada'] = date('d/m/Y H:i', strtotime($detalle['fecha']));
        
        // Formatear costo
        $detalle['costo_formateado'] = number_format($detalle['costo_bs'], 2, ',', '.');

        // Combinar modelo y marca de moto
        $detalle['moto_modelo_completo'] = trim($detalle['moto_marca'] . ' ' . $detalle['moto_modelo']);

        return [
            'success' => true,
            'detalle' => $detalle
        ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ];
        }
    }

}
