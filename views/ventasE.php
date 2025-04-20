<?php
include '../config/conexion.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$usuario_logueado = $_SESSION['user'];
$id_empleado = $_SESSION['user']['id']; 

// Handle POST requests for completing a sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_venta'])) {
    $id_venta = intval($_POST['id_venta']);
    
    try {        
        // Update status using stored procedure
        $stmtUpdate = $conn->prepare("CALL sp_completar_venta(:id_venta)");
        $stmtUpdate->execute(['id_venta' => $id_venta]);
        $stmtUpdate->closeCursor();
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta completada exitosamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle GET requests for sale details
if (isset($_GET['id_venta']) && isset($_GET['action']) && $_GET['action'] == 'get_details') {
    $id_venta = intval($_GET['id_venta']);
    
    try {
        // Get sale information using stored procedure
        $stmtVenta = $conn->prepare("CALL sp_obtener_info_venta(:id_venta)");
        $stmtVenta->execute(['id_venta' => $id_venta]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
        $stmtVenta->closeCursor();
        
        // Get product details using stored procedure
        $stmtProductos = $conn->prepare("CALL sp_obtener_detalles_venta(:id_venta)");
        $stmtProductos->execute(['id_venta' => $id_venta]);
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        $stmtProductos->closeCursor();
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'venta' => $venta,
            'productos' => $productos
        ]);
        exit;
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Get clients for select
$stmtClientes = $conn->query("CALL sp_obtener_clientes()");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
$stmtClientes->closeCursor();

// Get employee's sales
$stmtVentas = $conn->prepare("CALL sp_obtener_ventas_empleado(:id_empleado)");
$stmtVentas->execute(['id_empleado' => $id_empleado]);
$ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);
$stmtVentas->closeCursor();

// Get available products (motorcycles and accessories)
$stmtProductos = $conn->query("CALL sp_obtener_productos_disponibles()");
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
$stmtProductos->closeCursor();

// Aplicar filtros si existen
if (isset($_GET['action']) && $_GET['action'] === 'filter') {
    $filtros = [
        'fecha_desde' => $_GET['fecha_desde'] ?? null,
        'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
        'estado' => $_GET['estado'] ?? null,
        'tipo_pago' => $_GET['tipo_pago'] ?? null
    ];

    $ventas = array_filter($ventas, function($v) use ($filtros) {
        // Filtro por fecha
        if ($filtros['fecha_desde'] && strtotime($v['fecha_venta']) < strtotime($filtros['fecha_desde'])) {
            return false;
        }
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


// Process new sale form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_venta'])) {
    try {
        $conn->beginTransaction();
        
        // Sale data
        $id_cliente = $_POST['cliente'];
        $tipo_pago = $_POST['tipo_pago'];
        $adelanto = floatval($_POST['adelanto']);
        
        // Calculate total amount
        $monto_total = 0;
        $productos_venta = [];
        
        foreach ($_POST['productos'] as $producto_id) {
            $tipo = $_POST['tipo_producto'][$producto_id];
            $cantidad = intval($_POST['cantidad'][$producto_id]);
            $precio = floatval($_POST['precio'][$producto_id]);
            
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
        $estado = ($adelanto >= $monto_total) ? 'COMPLETADA' : 'PENDIENTE';
        $saldo_pendiente = $monto_total - $adelanto;
        
        $stmtVenta = $conn->prepare("CALL sp_insertar_venta_completa(
            :cliente, :empleado, :tipo_pago, :monto, :adelanto, :saldo, :estado, @id_venta)");
        $stmtVenta->execute([
            'cliente' => $id_cliente,
            'empleado' => $id_empleado,
            'tipo_pago' => $tipo_pago,
            'monto' => $monto_total,
            'adelanto' => $adelanto,
            'saldo' => $saldo_pendiente,
            'estado' => $estado
        ]);
        $stmtVenta->closeCursor();
        
        // Get the generated sale ID
        $stmt = $conn->query("SELECT @id_venta as id_venta");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $id_venta = $result['id_venta'];
        
        // Insert sale details and update inventory
        foreach ($productos_venta as $producto) {
            // Insert detail using stored procedure
            $stmtDetalle = $conn->prepare("CALL sp_insertar_detalle_venta(
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
            
            // Update inventory using stored procedure
            if ($producto['tipo'] === 'motocicleta') {
                $stmtUpdate = $conn->prepare("CALL sp_actualizar_inventario_moto(:id, :cantidad, @resultado)");
            } else {
                $stmtUpdate = $conn->prepare("CALL sp_actualizar_inventario_accesorio(:id, :cantidad, @resultado)");
            }
            
            $stmtUpdate->execute([
                'id' => $producto['id'],
                'cantidad' => $producto['cantidad']
            ]);
            $stmtUpdate->closeCursor();
            
            // Check result
            $stmtResult = $conn->query("SELECT @resultado as resultado");
            $result = $stmtResult->fetch(PDO::FETCH_ASSOC);
            $stmtResult->closeCursor();
            
            if ($result['resultado'] === 0) {
                throw new Exception("No hay suficiente stock para el producto ID: {$producto['id']}");
            }
        }
        
        $conn->commit();
        $_SESSION['mensaje'] = "Venta registrada exitosamente!";
        header("Location: ventasE.php");
        exit;
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al registrar la venta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Automotors - Panel de Empleado</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/ventasE.css">
   
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="d-flex flex-column h-100">
                <div class="text-center mb-4">
                    <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="empleado.php">
                            <i class="bi bi-bicycle me-2"></i>Catálogo de motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="accesorios.php">
                            <i class="bi bi-tools me-2"></i>Catálogo de accesorios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#ventas" data-bs-toggle="tab">
                            <i class="bi bi-credit-card me-2"></i>Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="clientes.php">
                            <i class="bi bi-people-fill me-2"></i>Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="reportes.php">
                            <i class="bi bi-graph-up-arrow me-2"></i>Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="horarios.php">
                            <i class="bi bi-calendar-week me-2"></i>Horarios
                        </a>
                    </li>
                </ul>

                <div class="mt-auto p-3 user-profile">
                    <div class="d-flex align-items-center text-white mb-3">
                        <div class="me-3">
                            <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($usuario_logueado['usuario']); ?></div>
                            <small>Empleado</small>
                        </div>
                    </div>
                    <a href="../public/logout.php" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                    </a>
                </div>
            </div>
        </nav>
        
        <main>
            <div class="tab-content">
                <!-- SECCIÓN DE VENTAS -->
                <div class="tab-pane fade show active" id="ventas">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="bi bi-credit-card text-primary me-2"></i> Historial de ventas
                        </h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="abrirModal()">
                                <i class="bi bi-plus-circle me-1"></i> Nueva Venta
                            </button>
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer me-1"></i> Imprimir
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-upload me-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <div class="row mb-4 g-3"> 
                <div class="col">
                    <div class="card stats-card h-100"> 
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-cash-coin me-1"></i> Ventas Totales
                                    </h6>
                                    <h3 class="mb-0 text-primary">
                                        <?= count($ventas) ?>
                                    </h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-graph-up fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Completadas -->
                <div class="col">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-check-circle me-1"></i> Completadas
                                    </h6>
                                    <h3 class="mb-0 text-success">
                                        <?= count(array_filter($ventas, fn($v) => $v['estado'] === 'COMPLETADA')) ?>
                                    </h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-check2-circle fs-3 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pendientes -->
                <div class="col">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-clock-history me-1"></i> Pendientes
                                    </h6>
                                    <h3 class="mb-0 text-warning">
                                        <?= count(array_filter($ventas, fn($v) => $v['estado'] === 'PENDIENTE')) ?>
                                    </h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo Total -->
                <div class="col">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-cash-stack me-1"></i> Saldo Total
                                    </h6>
                                    <h3 class="mb-0 text-info">
                                        Bs. <?= number_format(array_sum(array_column($ventas, 'monto_total'))) ?>
                                    </h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-currency-dollar fs-3 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo Pendiente -->
                <div class="col">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Saldo Pendiente
                                    </h6>
                                    <h3 class="mb-0 text-danger">
                                        Bs. <?= number_format(array_sum(array_column($ventas, 'saldo_pendiente'))) ?>
                                    </h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-clock-history fs-3 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avanzados de Ventas -->
            <div class="filter-section mb-4">
                <h5 class="mb-4">
                    <i class="bi bi-funnel-fill text-primary me-2"></i>Filtrar Ventas
                </h5>
                <form method="GET" class="row g-3">
                    <input type="hidden" name="action" value="filter">
                    
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label">
                            <i class="bi bi-calendar-minus text-primary me-1"></i>Desde
                        </label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                            value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label">
                            <i class="bi bi-calendar-plus text-primary me-1"></i>Hasta
                        </label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                            value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="estado" class="form-label">
                            <i class="bi bi-ui-radios text-primary me-1"></i>Estado
                        </label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="COMPLETADA" <?= ($_GET['estado'] ?? '') === 'COMPLETADA' ? 'selected' : '' ?>>Completadas</option>
                            <option value="PENDIENTE" <?= ($_GET['estado'] ?? '') === 'PENDIENTE' ? 'selected' : '' ?>>Pendientes</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="tipo_pago" class="form-label">
                            <i class="bi bi-credit-card text-primary me-1"></i>Tipo Pago
                        </label>
                        <select class="form-select" id="tipo_pago" name="tipo_pago">
                            <option value="">Todos</option>
                            <option value="Al contado" <?= ($_GET['tipo_pago'] ?? '') === 'Al contado' ? 'selected' : '' ?>>Al contado</option>
                            <option value="Financiamiento bancario" <?= ($_GET['tipo_pago'] ?? '') === 'Financiamiento bancario' ? 'selected' : '' ?>>Financiamiento</option>
                            <option value="Crédito Directo" <?= ($_GET['tipo_pago'] ?? '') === 'Crédito Directo' ? 'selected' : '' ?>>Crédito Directo</option>
                        </select>
                    </div>
                    
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                        </button>
                        <a href="ventasE.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

                    <!-- Tabla de ventas -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Tipo Pago</th>
                                            <th>Monto Total</th>
                                            <th>Adelanto</th>
                                            <th>Saldo</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaVentas">
                                        <?php if (count($ventas) > 0): ?>
                                            <?php foreach($ventas as $v): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($v['fecha_venta'])) ?></td>
                                                    <td><?= htmlspecialchars($v['nombre_cliente'] ?? 'Cliente no registrado') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $v['tipo_pago'] == 'Al contado' ? 'success' : 
                                                            ($v['tipo_pago'] == 'Financiamiento bancario' ? 'info' : 'primary') 
                                                        ?>">
                                                            <?= htmlspecialchars($v['tipo_pago']) ?>
                                                        </span>
                                                    </td>
                                                    <td>Bs. <?= number_format($v['monto_total'], 2) ?></td>
                                                    <td>Bs. <?= number_format($v['adelanto'], 2) ?></td>
                                                    <td>Bs. <?= number_format($v['saldo_pendiente'], 2) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $v['estado'] == 'COMPLETADA' ? 'success' : 
                                                            ($v['estado'] == 'PENDIENTE' ? 'warning' : 'danger') 
                                                        ?>">
                                                            <?= htmlspecialchars($v['estado']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?= $v['_id'] ?>)">
                                                            <i class="bi bi-eye"></i> Detalle
                                                        </button>
                                                        <?php if ($v['estado'] == 'PENDIENTE'): ?>
                                                            <button class="btn btn-sm btn-outline-success" onclick="completarVenta(<?= $v['_id'] ?>)">
                                                                <i class="bi bi-check-circle"></i> Completar
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="bi bi-exclamation-circle fs-4 text-muted"></i>
                                                    <p class="mt-2">No has realizado ninguna venta aún</p>
                                                    <button class="btn btn-primary mt-2" onclick="abrirModal()">
                                                        <i class="bi bi-plus-circle me-1"></i> Crear primera venta
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nueva Venta -->
    <div class="modal fade" id="modalVenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i> Nueva Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="formVenta">
                    <div class="modal-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select class="form-select" name="cliente" required>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach($clientes as $c): ?>
                                    <option value="<?= $c['_id'] ?>"><?= htmlspecialchars($c['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Pago *</label>
                                <select class="form-select" name="tipo_pago" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Al contado">Al contado</option>
                                    <option value="Financiamiento bancario">Financiamiento bancario</option>
                                    <option value="Crédito Directo">Crédito Directo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Adelanto (Bs.) *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="adelanto" required>
                            </div>
                        </div>
                        
                        <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i> Productos Disponibles</h5>
                        <div id="listaProductos" class="row g-3">
                            <?php foreach($productos as $p): ?>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input producto-check" type="checkbox" 
                                                       name="productos[]" 
                                                       value="<?= $p['_id'] ?>"
                                                       id="producto_<?= $p['_id'] ?>"
                                                       data-precio="<?= $p['precio'] ?>" 
                                                       data-nombre="<?= htmlspecialchars($p['nombre']) ?>" 
                                                       data-cantidad="<?= $p['cantidad'] ?>"
                                                       data-tipo="<?= $p['tipo'] ?>">
                                                <label class="form-check-label" for="producto_<?= $p['_id'] ?>">
                                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                                    <?= $p['color'] ? ' - ' . htmlspecialchars($p['color']) : '' ?>
                                                    <span class="badge bg-<?= $p['tipo'] === 'motocicleta' ? 'primary' : 'info' ?> ms-2">
                                                        <?= ucfirst($p['tipo']) ?>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Disponibles: <?= $p['cantidad'] ?></small>
                                                <span class="fw-bold">Bs. <?= number_format($p['precio'], 2) ?></span>
                                            </div>
                                            <div class="mt-2">
                                                <label class="form-label">Cantidad:</label>
                                                <input type="number" min="1" max="<?= $p['cantidad'] ?>" 
                                                       class="form-control cant-input" 
                                                       name="cantidad[<?= $p['_id'] ?>]"
                                                       data-producto="<?= $p['_id'] ?>"
                                                       placeholder="Cantidad" 
                                                       value="1"
                                                       disabled>
                                                <input type="hidden" name="precio[<?= $p['_id'] ?>]" value="<?= $p['precio'] ?>">
                                                <input type="hidden" name="tipo_producto[<?= $p['_id'] ?>]" value="<?= $p['tipo'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <h5>Resumen de Venta</h5>
                            <div id="resumenVenta">
                                <p class="text-muted">Seleccione productos para ver el resumen</p>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <h5>Total:</h5>
                                <h4 id="totalVenta">Bs. 0.00</h4>
                                <input type="hidden" name="guardar_venta" value="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../public/ventasE.js"></script>
</body>
</html>