<?php
ob_start(); // Inicia el almacenamiento en búfer de salida
require_once '../config/conexion.php';
require_once '../controllers/VentasAdminController.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if (!isset($_SESSION['user'])) {
            throw new Exception('Sesión expirada');
        }

        $controller = new VentasAdminController($conn);
        
        switch ($_POST['action']) {
            case 'completar_venta':
                $controller->completarVenta($_POST['id_venta']);
                echo json_encode(['success' => true, 'message' => 'Venta completada']);
                break;
                
            // Puedes añadir más acciones AJAX aquí
                
            default:
                throw new Exception('Acción no válida');
        }
        
        ob_end_clean(); // Limpiar buffer antes de enviar JSON
        exit;
        
    } catch (Exception $e) {
        ob_end_clean(); // Limpiar buffer antes de enviar JSON
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$controller = new VentasAdminController($conn);

// Obtener datos del usuario logueado
$user_id = $_SESSION['user']['id'] ?? null;
$userData = $controller->obtenerDatosUsuario($user_id) ?? [
    'nombre' => 'Usuario',
    'apellido' => '',
    'foto' => 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png',
    'cargo' => 'Administrador'
];

// Obtener filtros de la solicitud
$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'estado' => $_GET['estado'] ?? null,
    'tipo_pago' => $_GET['tipo_pago'] ?? null,
    'empleado' => $_GET['empleado'] ?? null
];

// Obtener datos para la vista
$clientes = $controller->obtenerClientes();
$ventas = $controller->obtenerVentas($filtros);
$productos = $controller->obtenerProductosDisponibles();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - JC Automotors</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/ventasA.css">
</head>
<body>
    <!-- Sidebar Vertical -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
            </a>
        </div>
        
        <!-- User Profile Section -->
        <div class="user-profile">
            <div class="user-avatar">
                <img src="<?php echo htmlspecialchars($userData['foto']); ?>" alt="User">
            </div>
            <div class="user-info">
                <h5 class="user-name"><?php echo htmlspecialchars($userData['nombre'] . ' ' . $userData['apellido']); ?></h5>
                <p class="user-role"><?php echo htmlspecialchars($userData['cargo']); ?></p>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="../index.php">
                        <i class="bi bi-house-door"></i>
                        <span>Inicio</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Panel administrativo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'gestionEmpleados.php') ? 'active' : ''; ?>"  href="gestionEmpleados.php">
                        <i class="bi bi-people"></i>
                        <span>Empleados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'clientesA.php') ? 'active' : ''; ?>" href="clientesA.php">
                       <i class="bi bi-person me-2"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ventasA.php') ? 'active' : ''; ?>" href="ventasA.php">
                        <i class="bi bi-cash-coin"></i>
                        <span>Ventas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="main-content">
        <div class="content-header">
            <div>
            <h1 class="h2">
             <i class="bi bi-credit-card text-dark me-2"></i> Historial de Ventas
            </h1>
                <div class="breadcrumbs">
                    <i class="bi bi-house-door me-1"></i> Inicio / Ventas
                </div>
            </div>
            <div class="action-buttons">
            <button type="button" class="btn btn-primary me-2" onclick="abrirModal()">
                                <i class="bi bi-plus-circle me-1"></i> Nueva Venta
                            </button>
                <a href="#" class="btn btn-dark">
                    <i class="bi bi-upload me-1"></i>Exportar
                </a>
            </div>
        </div>
                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['mensaje'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['mensaje']); ?>
                    <?php endif; ?>

            <!-- Stats Cards para Ventas -->
            <div class="stats-row">
                <!-- Ventas Totales -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count($ventas) ?></h3>
                        <p>Ventas Totales</p>
                    </div>
                </div>
                
                <!-- Ventas Completadas -->
                <div class="stat-card" style="--stat-color: var(--success); --stat-bg: var(--success-light);">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($ventas, fn($v) => $v['estado'] === 'Completada')) ?></h3>
                        <p>Completadas</p>
                    </div>
                </div>
                
                <!-- Ventas Pendientes -->
                <div class="stat-card" style="--stat-color: var(--warning); --stat-bg: var(--warning-light);">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($ventas, fn($v) => $v['estado'] === 'Pendiente')) ?></h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <!-- Saldo Total -->
                <div class="stat-card" style="--stat-color: var(--info); --stat-bg: var(--info-light);">
                    <div class="stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            $ <?= number_format(array_sum(array_column($ventas, 'monto_total'))) ?><br>
                            <small class="text-muted">Bs. <?= number_format(array_sum(array_column($ventas, 'monto_total')) * 7) ?></small>
                        </h3>
                        <p>Saldo Total</p>
                    </div>
                </div>

                <!-- Saldo Pendiente -->
                <div class="stat-card" style="--stat-color: var(--danger); --stat-bg: var(--danger-light);">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            $ <?= number_format(array_sum(array_column($ventas, 'saldo_pendiente'))) ?><br>
                            <small class="text-muted">Bs. <?= number_format(array_sum(array_column($ventas, 'saldo_pendiente')) * 7) ?></small>
                        </h3>
                        <p>Saldo Pendiente</p>
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
                        
                        <!-- Filtro por rango de fechas -->
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
                                <!-- Nuevo filtro por empleado -->
                                <div class="col-md-2">
                            <label for="empleado" class="form-label">
                                <i class="bi bi-person text-primary me-1"></i>Empleado
                            </label>
                            <select class="form-select" id="empleado" name="empleado">
                                <option value="">Todos</option>
                                <?php
                                // Obtener lista de empleados para el select
                                $stmtEmpleados = $conn->query("SELECT E._id, P.nombre, P.apellido FROM EMPLEADO E JOIN PERSONA P ON E._id = P._id ORDER BY P.nombre");
                                $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($empleados as $emp) {
                                    $selected = ($_GET['empleado'] ?? '') == $emp['_id'] ? 'selected' : '';
                                    echo "<option value='{$emp['_id']}' $selected>{$emp['nombre']} {$emp['apellido']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Filtro por estado -->
                        <div class="col-md-2">
                            <label for="estado" class="form-label">
                                <i class="bi bi-ui-radios text-primary me-1"></i>Estado
                            </label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Completada" <?= ($_GET['estado'] ?? '') === 'Completada' ? 'selected' : '' ?>>Completadas</option>
                                <option value="Pendiente" <?= ($_GET['estado'] ?? '') === 'Pendiente' ? 'selected' : '' ?>>Pendientes</option>
                            </select>
                        </div>
                        
                        <!-- Filtro por tipo de pago -->
                        <div class="col-md-2">
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
                        <!-- Botones de acción -->
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                            </button>
                            <a href="ventasA.php" class="btn btn-outline-secondary">
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
                                            <th>Empleado</th>
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
                                                    <td><?= htmlspecialchars($v['nombre_empleado'] ?? 'Empleado no registrado') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $v['tipo_pago'] == 'Al contado' ? 'success' : 
                                                            ($v['tipo_pago'] == 'Financiamiento bancario' ? 'info' : 'primary') 
                                                        ?>">
                                                            <?= htmlspecialchars($v['tipo_pago']) ?>
                                                        </span>
                                                    </td>
                                                    <td>$ <?= number_format($v['monto_total']) ?></td>
                                                    <td>$ <?= number_format($v['adelanto']) ?></td>
                                                    <td>$ <?= number_format($v['saldo_pendiente']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $v['estado'] == 'Completada' ? 'success' : 
                                                            ($v['estado'] == 'Pendiente' ? 'warning' : 'danger') 
                                                        ?>">
                                                            <?= htmlspecialchars($v['estado']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?= $v['_id'] ?>)">
                                                            <i class="bi bi-eye"></i> Detalle
                                                        </button>
                                                        <?php if ($v['estado'] == 'Pendiente'): ?>
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
                                                    <p class="mt-2">No se encontraron ventas</p>
                                                    <button class="btn btn-primary mt-2" onclick="abrirModal()">
                                                        <i class="bi bi-plus-circle me-1"></i> Crear nueva venta
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
    </div>

<!-- Modal Nueva Venta - Versión con Doble Moneda -->
<div class="modal fade" id="modalVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xxl">
        <div class="modal-content" style="min-height: 80vh;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i> Nueva Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="formVenta">
                <div class="modal-body d-flex flex-column" style="min-height: calc(80vh - 120px);">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-currency-exchange"></i> Tipo de cambio: 1 USD = 7 Bs.
                    </div>
                    
                    <div class="row flex-grow-1">
                        <!-- Columna Izquierda - Formulario -->
                        <div class="col-lg-5 d-flex flex-column">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-3">
                                        <label class="form-label">Cliente *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <select class="form-select" name="cliente" id="selectCliente" required>
                                                <option value="">Seleccione un cliente</option>
                                                <?php foreach($clientes as $c): ?>
                                                    <option value="<?= $c['_id'] ?>"><?= htmlspecialchars($c['nombre_completo']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                   
                                    <div class="row mb-3 g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de Pago *</label>
                                            <select class="form-select" name="tipo_pago" id="tipoPago" required>
                                                <option value="">Seleccione...</option>
                                                <option value="Al contado">Al contado</option>
                                                <option value="Financiamiento bancario">Financiamiento bancario</option>
                                                <option value="Crédito Directo">Crédito Directo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Adelanto (Bs.) *</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="adelanto" id="adelanto" required>
                                            <small class="text-muted" id="adelantoUSD">0.00 $</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto" style="margin-top: 50px;">
                                        <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i> Productos Disponibles</h5>
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="text" class="form-control" id="buscadorProductos" placeholder="Buscar productos...">
                                                <button class="btn btn-outline-secondary" type="button" id="btnFiltrarMotocicletas">
                                                    <i class="bi bi-bicycle"></i> Motos
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" id="btnFiltrarAccesorios">
                                                    <i class="bi bi-tools"></i> Accesorios
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div id="listaProductos" class="row g-2 flex-grow-1" style="max-height: 300px; overflow-y: auto;">
                                            <?php foreach($productos as $p): 
                                                $precio_bs = $p['precio'] * 7; // Convertir $ a Bs.
                                            ?>
                                                <div class="col-6 producto-item" data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>" data-tipo="<?= strtolower($p['tipo']) ?>">
                                                    <div class="card h-100">
                                                        <div class="card-body p-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input producto-check" type="checkbox" 
                                                                       name="productos[]" 
                                                                       value="<?= $p['_id'] ?>"
                                                                       id="producto_<?= $p['_id'] ?>"
                                                                       data-precio="<?= $precio_bs ?>" 
                                                                       data-precio-usd="<?= $p['precio'] ?>" 
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
                                                                <div class="text-end">
                                                                    <div class="fw-bold">$ <?= number_format($p['precio']) ?></div>
                                                                    <small class="text-muted">Bs. <?= number_format($precio_bs) ?></small>
                                                                </div>
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
                                                                <input type="hidden" name="precio[<?= $p['_id'] ?>]" value="<?= $precio_bs ?>">
                                                                <input type="hidden" name="tipo_producto[<?= $p['_id'] ?>]" value="<?= $p['tipo'] ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-lg-7 d-flex flex-column">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title border-bottom pb-2">Resumen de Venta</h5>
                                    <div id="resumenVenta" class="flex-grow-1" style="overflow-y: auto; height: 800px;">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle"></i> Seleccione productos para ver el resumen
                                        </div>
                                    </div>

                                    <div class="mt-auto border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5>Subtotal:</h5>
                                            <div class="text-end">
                                                <h5 id="subtotalVenta">$ 0.00</h5>
                                                <small class="text-muted" id="subtotalVentaBs">Bs. 0.00</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5>Adelanto:</h5>
                                            <div class="text-end">
                                                <h5 id="adelantoResumen">Bs. 0.00</h5>
                                                <small class="text-muted" id="adelantoResumenUSD">$ 0.00</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="descuento" id="inputDescuento" value="0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5>Saldo Pendiente:</h5>
                                            <div class="text-end">
                                                <h5 id="saldoResumen">Bs. 0.00</h5>
                                                <small class="text-muted" id="saldoResumenUSD">$ 0.00</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                            <h4>Total:</h4>
                                            <div class="text-end">
                                                <h3 id="totalVenta" class="text-primary">$ 0.00</h3>
                                                <small class="text-muted" id="totalVentaBs">Bs. 0.00</small>
                                            </div>
                                            <input type="hidden" name="guardar_venta" value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script src="../public/js/ventasA.js"></script>
</body>
</html>

<?php
// Manejo del formulario de nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_venta'])) {
    try {
        $data = [
            'cliente' => $_POST['cliente'],
            'tipo_pago' => $_POST['tipo_pago'],
            'adelanto' => floatval($_POST['adelanto']),
            'monto_total' => array_sum(array_map(fn($id) => $_POST['precio'][$id] * $_POST['cantidad'][$id], $_POST['productos'])),
            'saldo_pendiente' => array_sum(array_map(fn($id) => $_POST['precio'][$id] * $_POST['cantidad'][$id], $_POST['productos'])) - floatval($_POST['adelanto']),
            'estado' => (floatval($_POST['adelanto']) >= array_sum(array_map(fn($id) => $_POST['precio'][$id] * $_POST['cantidad'][$id], $_POST['productos']))) ? 'Completada' : 'Pendiente',
            'productos' => array_map(fn($id) => [
                'id' => $id,
                'tipo' => $_POST['tipo_producto'][$id],
                'cantidad' => intval($_POST['cantidad'][$id]),
                'precio' => floatval($_POST['precio'][$id]),
                'subtotal' => floatval($_POST['precio'][$id]) * intval($_POST['cantidad'][$id])
            ], $_POST['productos'])
        ];

        $controller->registrarVenta($data, $_SESSION['user']['id']);
        $_SESSION['mensaje'] = "Venta registrada exitosamente!";
        header("Location: ventasA.php");
        exit;
    } catch (Exception $e) {
        // Capturar errores y mostrarlos para depuración
        $error = "Error al registrar la venta: " . $e->getMessage();
        error_log($error); // Registrar el error en el log del servidor
    }
}

ob_end_flush(); // Envía el contenido del búfer de salida
?>