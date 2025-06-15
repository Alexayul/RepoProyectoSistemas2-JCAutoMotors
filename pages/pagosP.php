<?php
require_once '../config/conexion.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's ID
$id_usuario = $_SESSION['user']['id'];

// First, we need to get the client ID associated with this user
$query_cliente = "SELECT c._id 
                 FROM CLIENTE c
                 JOIN USUARIO u ON c._id = u.id_persona
                 WHERE u._id = :id_usuario";

try {
    $stmt = $conn->prepare($query_cliente);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente_data) {
        die("<div class='alert alert-danger container mt-5'>No se encontró información de cliente para este usuario.</div>");
    }
    
    $id_cliente = $cliente_data['_id'];
    
    // Get client personal info
    $query_info = "SELECT p.nombre, p.apellido, p.documento_identidad, p.telefono, p.email
                  FROM PERSONA p
                  WHERE p._id = :id_cliente";
                  
    $stmt = $conn->prepare($query_info);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $info_cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query for credit direct sales with their scheduled payments
    $query = "SELECT 
                v._id as id_venta, 
                v.fecha_venta, 
                v.monto_total, 
                v.adelanto, 
                v.saldo_pendiente, 
                v.estado as estado_venta,
                pp.id_pago,
                pp.fecha_pago,
                pp.monto as monto_programado,
                pp.estado as estado_pago,
                pp.monto_mora,
                pp.monto_pagado,
                pp.fecha_pagado
              FROM VENTA v
              LEFT JOIN PAGOS_PROGRAMADOS pp ON v._id = pp.id_venta
              WHERE v.tipo_pago = 'Crédito Directo' 
              AND v.estado = 'Pendiente'
              AND v.id_cliente = :id_cliente
              ORDER BY v._id, pp.fecha_pago";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize data by sale
    $ventas_con_pagos = [];
    foreach ($resultados as $row) {
        $id_venta = $row['id_venta'];
        if (!isset($ventas_con_pagos[$id_venta])) {
            $ventas_con_pagos[$id_venta] = [
                'venta' => [
                    'id_venta' => $row['id_venta'],
                    'fecha_venta' => $row['fecha_venta'],
                    'monto_total' => $row['monto_total'],
                    'adelanto' => $row['adelanto'],
                    'saldo_pendiente' => $row['saldo_pendiente'],
                    'estado_venta' => $row['estado_venta']
                ],
                'pagos' => []
            ];
        }
        
        if ($row['id_pago']) {
            $ventas_con_pagos[$id_venta]['pagos'][] = [
                'id_pago' => $row['id_pago'],
                'fecha_pago' => $row['fecha_pago'],
                'monto_programado' => $row['monto_programado'],
                'estado_pago' => $row['estado_pago'],
                'monto_mora' => $row['monto_mora'],
                'monto_pagado' => $row['monto_pagado'],
                'fecha_pagado' => $row['fecha_pagado']
            ];
        }
    }
    
} catch (PDOException $e) {
    die("<div class='alert alert-danger container mt-5'>Error al consultar la base de datos: " . $e->getMessage() . "</div>");
}

function obtenerNombreMes($numero_mes) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $meses[$numero_mes];
}

function obtenerClaseEstado($estado) {
    switch ($estado) {
        case 'Completada':
            return 'bg-success';
        case 'Pendiente':
            return 'bg-warning';
        case 'Vencida':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function obtenerIconoEstado($estado) {
    switch ($estado) {
        case 'Completada':
            return 'bi-check-circle-fill';
        case 'Pendiente':
            return 'bi-clock-fill';
        case 'Vencida':
            return 'bi-exclamation-triangle-fill';
        default:
            return 'bi-question-circle-fill';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos Programados - JCAutomotors</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/pagosP.css">
</head>
<body>
    
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="logo-container">
                <img src="../public/logo.png" alt="JCAutomotors Logo" class="logo-img">
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                    <i class="bi bi-list text-light fs-2"></i>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="catalogo.php">
                            <i class="bi bi-bicycle me-1"></i>Catálogo
                        </a>
                    </li>
                    <a class="nav-link" href="javascript:history.back()" class="btn btn-volver">
                        <i class="bi bi-arrow-left text-white"></i> Volver
                    </a>
                </ul>
            </div>
        </div>
    </nav>
</header>

<section class="hero">
    <div class="container">
        <h1 class="hero-title">Mis Pagos Programados</h1>
        <p class="hero-text">
            Seguimiento detallado de tus pagos mensuales y estado de cada cuota programada.
        </p>
    </div>
</section>

<section class="container my-5">
    <?php if (!empty($ventas_con_pagos)): ?>
        <?php foreach ($ventas_con_pagos as $venta_data): 
            $venta = $venta_data['venta'];
            $pagos = $venta_data['pagos'];
            
            // Calculate statistics
            $total_pagos = count($pagos);
            $pagos_completados = array_filter($pagos, function($p) { return $p['estado_pago'] === 'Completada'; });
            $pagos_realizados = count($pagos_completados);
            $porcentaje_pagado = ($total_pagos > 0) ? ($pagos_realizados / $total_pagos) * 100 : 0;
            
            // Group payments by month/year
            $pagos_por_mes = [];
            $contador_mes = 1;
            foreach ($pagos as $pago) {
                $fecha = new DateTime($pago['fecha_pago']);
                $mes_año = $fecha->format('Y-m');
                
                if (!isset($pagos_por_mes[$mes_año])) {
                    $pagos_por_mes[$mes_año] = [
                        'numero_mes' => $contador_mes,
                        'nombre_mes' => obtenerNombreMes($fecha->format('n')),
                        'año' => $fecha->format('Y'),
                        'pagos' => []
                    ];
                    $contador_mes++;
                }
                $pagos_por_mes[$mes_año]['pagos'][] = $pago;
            }
        ?>
            <div class="venta-card">
                <div class="venta-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>
                                Venta #<?php echo htmlspecialchars($venta['id_venta'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-calendar3 me-1"></i>
                                Fecha de compra: <?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">
                                Crédito Directo
                            </span>
                        </div>
                    </div>
                    
                    <div class="cliente-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-1"><?php echo htmlspecialchars($info_cliente['nombre'] . ' ' . $info_cliente['apellido'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <small>CI: <?php echo htmlspecialchars($info_cliente['documento_identidad'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small>
                                    <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($info_cliente['telefono'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($info_cliente['email'], ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen de la venta -->
                <div class="resumen-venta">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="resumen-item">
                                <div class="resumen-valor">Bs. <?php echo number_format((float)$venta['monto_total'], 2); ?></div>
                                <div class="resumen-label">Monto Total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resumen-item">
                                <div class="resumen-valor">Bs. <?php echo number_format((float)$venta['adelanto'], 2); ?></div>
                                <div class="resumen-label">Adelanto</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resumen-item">
                                <div class="resumen-valor">Bs. <?php echo number_format((float)$venta['saldo_pendiente'], 2); ?></div>
                                <div class="resumen-label">Saldo Pendiente</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resumen-item">
                                <div class="resumen-valor"><?php echo $pagos_realizados; ?>/<?php echo $total_pagos; ?></div>
                                <div class="resumen-label">Pagos Realizados</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: <?php echo $porcentaje_pagado; ?>%"></div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted"><?php echo round($porcentaje_pagado); ?>% completado</small>
                    </div>
                </div>
                
                <!-- Timeline de pagos -->
                <div class="timeline-container">
                    <?php if (!empty($pagos_por_mes)): ?>
                        <div class="timeline-line"></div>
                        <?php foreach ($pagos_por_mes as $mes_data): ?>
                            <div class="mes-section">
                                <div class="mes-header">
                                    <div class="mes-number"><?php echo $mes_data['numero_mes']; ?></div>
                                    <div>
                                        <i class="bi bi-calendar-month me-2"></i>
                                        <?php echo $mes_data['nombre_mes'] . ' ' . $mes_data['año']; ?>
                                        <small class="ms-2 opacity-75">(<?php echo count($mes_data['pagos']); ?> pago<?php echo count($mes_data['pagos']) != 1 ? 's' : ''; ?>)</small>
                                    </div>
                                </div>
                                
                                <div class="pagos-mes">
                                    <?php foreach ($mes_data['pagos'] as $pago): ?>
                                        <div class="pago-item">
                                            <div class="pago-status status-<?php echo strtolower($pago['estado_pago']); ?>"></div>
                                            <div class="pago-content">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <div class="pago-fecha">
                                                            <i class="bi bi-calendar-check me-1"></i>
                                                            <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?>
                                                        </div>
                                                        <span class="badge badge-estado <?php echo obtenerClaseEstado($pago['estado_pago']); ?>">
                                                            <i class="<?php echo obtenerIconoEstado($pago['estado_pago']); ?>"></i>
                                                            <?php echo $pago['estado_pago']; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <!-- Monto programado (siempre visible) -->
                                                            <div class="col-sm-4">
                                                                <div class="pago-monto monto-programado">
                                                                    Bs. <?php echo number_format((float)$pago['monto_programado']); ?>
                                                                </div>
                                                                <small class="text-muted">Programado</small>
                                                            </div>
                                                            
                                                            <?php if ($pago['monto_mora'] > 0): ?>
                                                                <div class="col-sm-4">
                                                                    <div class="pago-monto monto-mora">
                                                                        Bs. <?php echo number_format((float)$pago['monto_mora']); ?>
                                                                    </div>
                                                                    <small class="text-muted">Mora</small>
                                                                </div>
                                                                
                                                            <?php endif; ?>
                                                            
                                                            
                                                            <!-- Monto pagado (solo visible si está completado) -->
                                                            <?php if ($pago['estado_pago'] === 'Completada'): ?>
                                                                <div class="col-sm-4">
                                                                    <div class="pago-monto monto-pagado">
                                                                        Bs. <?php echo number_format((float)$pago['monto_pagado']); ?>
                                                                    </div>
                                                                    <small class="text-muted">Pagado</small>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="col-sm-4">
                                                                    <div class="pago-monto monto-total" style="color:green">
                                                                        Bs. <?php echo number_format((float)($pago['monto_programado'] + $pago['monto_mora'])); ?>
                                                                    </div>
                                                                    <small class="text-muted">Total a pagar</small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-3 text-end">
                                                        <?php if ($pago['estado_pago'] === 'Completada' && $pago['fecha_pagado']): ?>
                                                            <small class="text-success">
                                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                                Pagado el <?php echo date('d/m/Y', strtotime($pago['fecha_pagado'])); ?>
                                                            </small>
                                                        <?php elseif ($pago['estado_pago'] === 'Pendiente'): ?>
                                                            <small class="text-warning">
                                                                <i class="bi bi-clock-fill me-1"></i>
                                                                Pendiente de pago
                                                            </small>
                                                        <?php elseif ($pago['estado_pago'] === 'Vencida'): ?>
                                                            <small class="text-danger">
                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                Pago vencido
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            No hay pagos programados para esta venta.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state text-center">
    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
    <h3 class="mt-3">No tienes pagos pendientes</h3>
    <p class="text-muted">Actualmente no tienes pagos pendientes con crédito directo.</p>
    <div class="mt-4">
        <a href="javascript:history.back()" class="btn btn-primary btn-lg">
    <i class="bi bi-arrow-left me-2 text-white" style="font-size: 1.5rem;"></i> Volver
</a>
    </div>
</div>
    <?php endif; ?>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// Add smooth animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar-custom');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
    
    // Add hover effects to payment items
    const pagoItems = document.querySelectorAll('.pago-item');
    pagoItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
        });
    });
});
</script>
</body>
</html>