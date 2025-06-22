<?php
include '../config/conexion.php';
include '../controllers/CreditoController.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];
$creditoController = new CreditoController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creditoController->handleRequest();
    header("Location: creditosE.php");
    exit;
}

// Obtener filtros desde GET
$filtros = [
    'cliente'      => $_GET['cliente']      ?? '',
    'fecha_venta'  => $_GET['fecha_venta']  ?? '',
    'fecha_desde'  => $_GET['fecha_desde']  ?? '',
    'fecha_hasta'  => $_GET['fecha_hasta']  ?? '',
    'saldo'        => $_GET['saldo']        ?? '',
    'atraso'       => $_GET['atraso']       ?? '',
];

// Usar los filtros si hay algún filtro activo
if (
    !empty($filtros['cliente']) ||
    !empty($filtros['fecha_venta']) ||
    !empty($filtros['fecha_desde']) ||
    !empty($filtros['fecha_hasta']) ||
    !empty($filtros['saldo']) ||
    !empty($filtros['atraso'])
) {
    $creditos = $creditoController->obtenerCreditosFiltrados($filtros);
} else {
    $creditos = $creditoController->obtenerCreditosDirectos();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos Directos - JC Automotors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/creditosE.css">
    
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
                            <i class="bi bi-bicycle me-2"></i>Inventario de motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="accesorios.php">
                            <i class="bi bi-tools me-2"></i>Inventario de accesorios
                        </a>
                    </li>
                    <li class="nav-item">
                       <a class="nav-link text-white" href="ventasE.php">
                            <i class="bi bi-credit-card me-2"></i>Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="clientesE.php">
                            <i class="bi bi-people-fill me-2"></i>Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#credito" data-bs-toggle="tab">
                            <i class="bi bi-cash-stack me-2"></i>Crédito Directo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="mantenimientosE.php">
                            <i class="bi bi-tools me-2"></i>Mantenimientos
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
                <div class="tab-pane fade show active" id="creditos">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="bi bi-cash-stack text-primary me-2"></i> Créditos Directos
                        </h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <!-- <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer me-1"></i> Imprimir
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-upload me-1"></i> Exportar
                                </button> -->
                            </div>
                        </div>
                    </div>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
        <div class="row mb-4 g-3">
                <div class="col">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-cash-coin me-1"></i> Créditos Totales
                                    </h6>
                                    <h3 class="mb-0 text-primary">
                                        <?= count($creditos) ?>
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
                                        <?= count(array_filter($creditos, fn($c) => $c['estado'] === 'Completada')) ?>
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
                                        <?= count(array_filter($creditos, fn($c) => $c['estado'] === 'Pendiente')) ?>
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
                                        $ <?= number_format(array_sum(array_column($creditos, 'monto_total')), 2) ?>
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
                                        $ <?= number_format(array_sum(array_column($creditos, 'saldo_pendiente')), 2) ?>
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

   <div class="filter-section mb-4">
    <h5 class="mb-4">
        <i class="bi bi-funnel-fill text-primary me-2"></i>Filtrar Créditos
    </h5>
    <form method="GET" class="row g-3">
        <input type="hidden" name="action" value="filter">
        
        <!-- Filtro por cliente -->
        <div class="col-md-3">
            <label for="cliente" class="form-label">
                <i class="bi bi-person text-primary me-1"></i>Buscar cliente
            </label>
            <input type="text" class="form-control" id="cliente" name="cliente"
                placeholder="Nombre, cédula o teléfono"
                value="<?= htmlspecialchars($_GET['cliente'] ?? '') ?>">
        </div>
        
        <!-- Filtro por fecha de venta -->
        <div class="col-md-3">
            <label for="fecha_venta" class="form-label">
                <i class="bi bi-calendar-event text-primary me-1"></i>Fecha de venta
            </label>
            <select class="form-select" id="fecha_venta" name="fecha_venta">
                <option value="">Todas las fechas</option>
                <option value="hoy" <?= ($_GET['fecha_venta'] ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= ($_GET['fecha_venta'] ?? '') === 'semana' ? 'selected' : '' ?>>Esta semana</option>
                <option value="mes" <?= ($_GET['fecha_venta'] ?? '') === 'mes' ? 'selected' : '' ?>>Este mes</option>
                <option value="rango" <?= ($_GET['fecha_venta'] ?? '') === 'rango' ? 'selected' : '' ?>>Rango personalizado</option>
            </select>
        </div>
        
        <!-- Filtro por rango personalizado (se muestra solo si se selecciona "rango") -->
        <div class="col-md-4" id="rango-fechas-container" style="display: none;">
            <label class="form-label">Rango de fechas</label>
            <div class="input-group">
                <input type="date" class="form-control" name="fecha_desde" 
                    value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
                <span class="input-group-text">a</span>
                <input type="date" class="form-control" name="fecha_hasta" 
                    value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
            </div>
        </div>
        
        <!-- Filtro por saldo pendiente -->
        <div class="col-md-3">
            <label for="saldo" class="form-label">
                <i class="bi bi-cash-stack text-primary me-1"></i>Saldo pendiente
            </label>
            <select class="form-select" id="saldo" name="saldo">
                <option value="">Todos</option>
                <option value="0-500" <?= ($_GET['saldo'] ?? '') === '0-500' ? 'selected' : '' ?>>$0 - $500</option>
                <option value="500-1000" <?= ($_GET['saldo'] ?? '') === '500-1000' ? 'selected' : '' ?>>$500 - $1,000</option>
                <option value="1000+" <?= ($_GET['saldo'] ?? '') === '1000+' ? 'selected' : '' ?>>Más de $1,000</option>
            </select>
        </div>
        
        <!-- Filtro por días de atraso -->
        <div class="col-md-3">
            <label for="atraso" class="form-label">
                <i class="bi bi-clock-history text-primary me-1"></i>Días de atraso
            </label>
            <select class="form-select" id="atraso" name="atraso">
                <option value="">Todos</option>
                <option value="al-dia" <?= ($_GET['atraso'] ?? '') === 'al-dia' ? 'selected' : '' ?>>Al día</option>
                <option value="1-7" <?= ($_GET['atraso'] ?? '') === '1-7' ? 'selected' : '' ?>>1-7 días</option>
                <option value="7+" <?= ($_GET['atraso'] ?? '') === '7+' ? 'selected' : '' ?>>Más de 7 días</option>
            </select>
        </div>
        
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary me-2">
                <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
            </button>
            <a href="creditosE.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <?php if (empty($creditos)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> No hay clientes con crédito directo actualmente.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($creditos as $credito): 
                            $progreso = $credito['total_pagos'] > 0 ? ($credito['pagos_realizados'] / $credito['total_pagos']) * 100 : 0;
                        ?>
                            <div class="col-lg-6 col-xl-3 mb-3">
                                <div class="card card-credito h-100">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between align-items-start">
                                            <span><?= htmlspecialchars($credito['nombre']) ?></span>
                                            <span class="badge bg-<?= $credito['estado'] == 'Pendiente' ? 'warning' : 'success' ?> status-badge">
                                                <?= ucfirst($credito['estado']) ?>
                                            </span>
                                        </h5>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Progreso de pagos</small>
                                            <div class="progress progress-bar-custom mb-2">
                                                <div class="progress-bar bg-<?= $progreso == 100 ? 'success' : 'success' ?>" 
                                                     style="width: <?= $progreso ?>%"></div>
                                            </div>
                                            <small class="text-muted">
                                                <?= $credito['pagos_realizados'] ?> de <?= $credito['total_pagos'] ?> pagos realizados
                                            </small>
                                        </div>

                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Total</small>
                                                <strong>$<?= number_format($credito['monto_total'], 2) ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Adelanto</small>
                                                <strong class="text-success">$<?= number_format($credito['adelanto'], 2) ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Pendiente</small>
                                                <strong class="text-danger">$<?= number_format($credito['saldo_pendiente'], 2) ?></strong>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalPagos<?= $credito['id_venta'] ?>">
                                                <i class="bi bi-calendar-check me-1"></i> Gestionar Pagos
                                            </button>
                                            
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small><i class="bi bi-calendar me-1"></i> Venta: <?= date('d/m/Y', strtotime($credito['fecha_venta'])) ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Pagos -->
                            <div class="modal fade" id="modalPagos<?= $credito['id_venta'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xxl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-calendar-check me-2"></i>
                                                Plan de Pagos - <?= htmlspecialchars($credito['nombre']) ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php 
                                            $pagos = $creditoController->obtenerPagosProgramados($credito['id_venta']);
                                            $totales = $creditoController->obtenerTotalesPagos($credito['id_venta']);
                                            if (empty($pagos)): ?>
                                                <div class="alert alert-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i> 
                                                    Este crédito no tiene pagos programados.
                                                </div>
                                                <!-- Formulario para programar pagos personalizados -->
                                                <form method="POST" id="formProgramarPagos<?= $credito['id_venta'] ?>">
                                                    <input type="hidden" name="action" value="programar_pagos">
                                                    <input type="hidden" name="id_venta" value="<?= $credito['id_venta'] ?>">
                                                    <input type="hidden" name="saldo_pendiente" value="<?= $credito['saldo_pendiente'] ?>">
                                                    
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Programar Pagos Personalizados</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Saldo a distribuir:</label>
                                                                    <input type="text" class="form-control" 
                                                                           value="$<?= number_format($credito['saldo_pendiente'], 2) ?>" 
                                                                           readonly>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Número de pagos:</label>
                                                                    <select class="form-select" id="numPagos<?= $credito['id_venta'] ?>" 
                                                                            onchange="generarCamposPagos(<?= $credito['id_venta'] ?>, <?= $credito['saldo_pendiente'] ?>)">
                                                                        <option value="">Seleccione...</option>
                                                                        <?php for($i = 1; $i <= 24; $i++): ?>
                                                                            <option value="<?= $i ?>"><?= $i ?> pago<?= $i > 1 ? 's' : '' ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div id="camposPagos<?= $credito['id_venta'] ?>"></div>
                                                            
                                                            <div class="mt-3">
                                                                <button type="button" class="btn btn-info me-2"
                                                                        onclick="autocompletarFechas(<?= $credito['id_venta'] ?>)">
                                                                    <i class="bi bi-calendar-range me-1"></i> Autocompletar Fechas
                                                                </button>
                                                                <button type="submit" class="btn btn-primary" id="btnGuardarPagos<?= $credito['id_venta'] ?>" style="display: none;">
                                                                    <i class="bi bi-save me-1"></i> Guardar Plan de Pagos
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                <form method="POST">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Fecha Pago</th>
                                                            <th>Monto $</th>
                                                            <th>Monto Bs.</th>
                                                            <th>Mora $</th>
                                                            <th>Mora Bs.</th>
                                                            <th>Monto Total</th>
                                                            <th>Estado</th>
                                                            <th>Fecha Regristro</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pagos as $pago): 
                                                            $hoy = new DateTime();
                                                            $fechaPago = new DateTime($pago['fecha_pago']);
                                                            $estaAtrasado = $hoy > $fechaPago && ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado');
                                                        ?>
                                                            <tr class="<?= $pago['estado'] == 'Completada' ? 'table-success' : ($estaAtrasado ? 'table-danger' : '') ?>">
                                                                <td>
                                                                    <i class="bi bi-calendar me-1"></i>
                                                                    <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                                                </td>
                                                                <td>
                                                                    <strong>$<?= number_format($pago['monto'], 2) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <strong>$<?= number_format($pago['monto']*7, 2) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // Calcular mora en tiempo real para cuotas atrasadas o pendientes vencidas
                                                                    $mora = 0;
                                                                    $hoy = new DateTime();
                                                                    $fechaPago = new DateTime($pago['fecha_pago']);
                                                                    $estaAtrasado = $hoy > $fechaPago && ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado');
                                                                    if ($estaAtrasado) {
                                                                        $diasAtraso = $fechaPago->diff($hoy)->days;
                                                                        $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
                                                                    }
                                                                    ?>
                                                                    <?php if ($mora > 0): ?>
                                                                        <span class="badge bg-warning">$<?= number_format($mora, 2) ?></span>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">$0.00</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // Calcular mora en tiempo real para cuotas atrasadas o pendientes vencidas
                                                                    $mora = 0;
                                                                    $hoy = new DateTime();
                                                                    $fechaPago = new DateTime($pago['fecha_pago']);
                                                                    $estaAtrasado = $hoy > $fechaPago && ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado');
                                                                    if ($estaAtrasado) {
                                                                        $diasAtraso = $fechaPago->diff($hoy)->days;
                                                                        $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
                                                                    }
                                                                    ?>
                                                                    <?php if ($mora > 0): ?>
                                                                        <span class="badge bg-warning">$<?= number_format($mora*7, 2) ?></span>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">$0.00</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <strong>$<?= number_format($pago['monto_pagado'], 2) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-<?= 
                                                                        $pago['estado'] == 'Completada' ? 'success' : 
                                                                        ($estaAtrasado ? 'danger' : 'warning') ?>">
                                                                        <?= ucfirst($pago['estado']) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if ($pago['fecha_pagado']): ?>
                                                                        <?= date('d/m/Y', strtotime($pago['fecha_pagado'])) ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">-</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado'): ?>
                                                                        <form method="POST" class="d-inline">
                                                                            <input type="hidden" name="action" value="registrar_pago">
                                                                            <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                                            <input type="hidden" name="monto_pagado" value="<?= $pago['monto'] + ($pago['monto_mora'] ?? 0) ?>">
                                                                            <input type="hidden" name="id_venta" value="<?= $credito['id_venta'] ?>">
                                                                            <button type="submit" class="btn btn-success btn-sm" title="Registrar Pago">
                                                                                <i class="bi bi-cash-coin"></i>
                                                                            </button>
                                                                        </form>
                                                                        <form method="POST" class="d-inline">
                                                                            <input type="hidden" name="action" value="marcar_no_pagado">
                                                                            <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">
                                                                            <input type="hidden" name="id_venta" value="<?= $credito['id_venta'] ?>">
                                                                            <button type="submit" class="btn btn-danger btn-sm" title="No pagado">
                                                                                <i class="bi bi-x-circle"></i>
                                                                            </button>
                                                                        </form>
                                                                        
                                                                    <?php else: ?>
                                                                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>

                                                        <?php
                                                        // Calcular totales con mora en tiempo real para mostrar en el resumen
                                                        $total = 0;
                                                        $totalConMora = 0;
                                                        $hoy = new DateTime();
                                                        foreach ($pagos as $pago) {
                                                            $total += floatval($pago['monto']);
                                                            $fechaPago = new DateTime($pago['fecha_pago']);
                                                            $mora = 0;
                                                            $estaAtrasado = $hoy > $fechaPago && ($pago['estado'] == 'Pendiente' || $pago['estado'] == 'Atrasado');
                                                            if ($estaAtrasado) {
                                                                $diasAtraso = $fechaPago->diff($hoy)->days;
                                                                $mora = round($pago['monto'] * 0.02 * $diasAtraso, 2);
                                                            }
                                                            $totalConMora += floatval($pago['monto']) + $mora;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                </form>
                                            </div>
                                            <div class="mt-4">
                                                <div style="background: linear-gradient(135deg, #e3f6ff 0%, #d1f0ff 100%);" class="text-dark p-4 rounded-lg shadow-sm">
                                                    <h5 class="font-weight-bold mb-4 text-center" style="color: #0066cc; text-transform: uppercase; letter-spacing: 1px; font-size: 1.1rem;">Resumen de Pago</h5>
                                                    
                                                    <div class="d-flex justify-content-between border-bottom pb-3 mb-3" style="border-color: rgba(0, 153, 255, 0.2) !important;">
                                                        <span class="font-weight-semibold" style="color: #555;">Monto total:</span>
                                                        <span class="font-weight-medium" style="color: #222;">
                                                            $<?= number_format($total, 2) ?>
                                                            <small class="text-muted ml-1">| <?= number_format($total * 7, 2) ?> Bs.</small>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between border-bottom pb-3 mb-3" style="border-color: rgba(0, 153, 255, 0.2) !important;">
                                                        <span class="font-weight-bold" style="color: #555;">Monto con mora:</span>
                                                        <span class="font-weight-bold" style="color: #ff6b00;">
                                                            $<?= number_format($totalConMora, 2) ?>
                                                            <small class="text-muted ml-1">| <?= number_format($totalConMora * 7, 2) ?> Bs.</small>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between pt-3">
                                                        <span class="font-weight-bold" style="color: #555;">Saldo pendiente:</span>
                                                        <span class="font-weight-bold <?= $credito['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success' ?>" style="font-size: 1.05rem;">
                                                            $<?= number_format($credito['saldo_pendiente'], 2) ?>
                                                            <small class="<?= $credito['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success' ?> ml-1">| <?= number_format($credito['saldo_pendiente'] * 7, 2) ?> Bs.</small>
                                                        </span>
                                                    </div>

                                                    <!-- Efecto decorativo opcional -->
                                                    <div class="mt-3 pt-2" style="border-top: 1px dashed rgba(0, 153, 255, 0.3);">
                                                        <small class="text-muted d-block text-center" style="font-size: 0.75rem;">Transacción <?= date('d/m/Y') ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/creditosE.js"></script>
</body>
</html>