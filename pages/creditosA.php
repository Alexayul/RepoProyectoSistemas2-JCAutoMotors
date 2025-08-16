<?php
include '../config/conexion.php';
include '../controllers/CreditoController.php';

session_start();
define('DEFAULT_AVATAR', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJzNC40NzcgMTAgMTAgMTAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnptMCAyYzQuNDE4IDAgOCAzLjU4MiA4IDhzLTMuNTgyIDgtOCA4LTgtMy41ODItOC04IDMuNTgyLTggOC04eiIvPjxwYXRoIGQ9Ik0xMiAzYy0yLjIxIDAtNCAxLjc5LTQgNHMxLjc5IDQgNCA0IDQtMS43OSA0LTRzLTEuNzktNC00LTR6bTAgN2MtMy4zMTMgMC02IDIuNjg3LTYgNnYxaDEydi0xYzAtMy4zMTMtMi42ODctNi02LTZ6Ii8+PC9zdmc+');

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];
$user_id = $_SESSION['user']['id'] ?? null;
$controller = new CreditoController($conn);
$userData = $controller->obtenerDatosUsuario($user_id) ?? [
    'nombre' => 'Usuario',
    'apellido' => '',
    'foto' => 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png',
    'cargo' => 'Administrador'
];
$current_page = basename($_SERVER['PHP_SELF']);
$usuario_logueado = $_SESSION['user'];
$creditoController = new CreditoController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creditoController->handleRequest();
    header("Location: creditosA.php");
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
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/creditosA.css">
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
                    <a class="nav-link <?php echo ($current_page == 'catalogoA.php') ? 'active' : ''; ?>" href="catalogoA.php">
                      <i class="bi bi-bicycle"></i>
                        <span>Inventario de motos</span>
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
                    <a class="nav-link <?php echo ($current_page == 'creditosA.php') ? 'active' : ''; ?>" href="creditosA.php">
                        <i class="bi bi-cash-stack"></i>
                        <span>Créditos Directos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'mantenimientosA.php') ? 'active' : ''; ?>" href="mantenimientosA.php">
                        <i class="bi bi-wrench"></i>
                        <span>Mantenimientos</span>
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
              <i class="bi bi-cash-stack"></i></i> Créditos Directos
            </h1>
                <div class="breadcrumbs">
                    <i class="bi bi-house-door me-1"></i> Inicio / Créditos Directos
                </div>
            </div>
            <div class="action-buttons">
                <a href="#" id="exportarCreditos" target="_blank" class="btn btn-dark">
                    <i class="bi bi-upload me-1"></i>Exportar
                </a>
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

       <!-- Stats Cards para Créditos -->
<div class="stats-row">
    <!-- Créditos Totales -->
    <div class="stat-card">
        <div class="stat-icon">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="stat-info">
            <h3><?= count($creditos) ?></h3>
            <p>Créditos Totales</p>
        </div>
    </div>
    
    <!-- Créditos Completados -->
    <div class="stat-card" style="--stat-color: var(--success); --stat-bg: var(--success-light);">
        <div class="stat-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?= count(array_filter($creditos, fn($c) => $c['estado'] === 'Completada')) ?></h3>
            <p>Completados</p>
        </div>
    </div>
    
    <!-- Créditos Pendientes -->
    <div class="stat-card" style="--stat-color: var(--warning); --stat-bg: var(--warning-light);">
        <div class="stat-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-info">
            <h3><?= count(array_filter($creditos, fn($c) => $c['estado'] === 'Pendiente')) ?></h3>
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
                $<?= number_format(array_sum(array_column($creditos, 'monto_total')), 2) ?><br>
                <small class="text-muted">Bs. <?= number_format(array_sum(array_column($creditos, 'monto_total')) * 7, 2) ?></small>
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
                $<?= number_format(array_sum(array_column($creditos, 'saldo_pendiente')), 2) ?><br>
                <small class="text-muted">Bs. <?= number_format(array_sum(array_column($creditos, 'saldo_pendiente')) * 7, 2) ?></small>
            </h3>
            <p>Saldo Pendiente</p>
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
            <a href="creditosA.php" class="btn btn-outline-secondary">
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
                                            <?php
$badgeColor = $credito['estado'] == 'Pendiente' ? '#701106' : '#050506';
?>
<span class="badge status-badge" style="background:<?= $badgeColor ?>; color:#fff;">
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
                                                                    <?php if ($pago['estado'] == 'Completada') {
                                                                        $badgePagoColor = '#050506';
                                                                        $badgeTextColor = '#fff';
                                                                    } elseif ($estaAtrasado) {
                                                                        $badgePagoColor = '#fff';
                                                                        $badgeTextColor = '#701106';
                                                                    } else {
                                                                        $badgePagoColor = '#701106';
                                                                        $badgeTextColor = '#fff';
                                                                    }
                                                                    ?>
                                                                    <span class="badge" style="background:<?= $badgePagoColor ?>; color:<?= $badgeTextColor ?>; border:1px solid #701106;">
                                                                        <?= ucfirst($pago['estado'] == 'Atrasado' ? 'Atrasado' : $pago['estado']) ?>
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
    <!-- Estadísticas de Créditos -->
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Aquí puedes agregar scripts para la lógica de los formularios de pagos personalizados si lo necesitas -->
    <script>
    // Función para generar los campos de pagos personalizados
    function generarCamposPagos(idVenta, saldoPendiente) {
        var numPagos = document.getElementById('numPagos' + idVenta).value;
        var contenedor = document.getElementById('camposPagos' + idVenta);
        contenedor.innerHTML = '';
        if (!numPagos || numPagos < 1) {
            document.getElementById('btnGuardarPagos' + idVenta).style.display = 'none';
            return;
        }
        for (var i = 1; i <= numPagos; i++) {
            contenedor.innerHTML += `
                <div class="row mb-2 align-items-end">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label">Fecha del pago ${i}:</label>
                        <input type="date" name="fechas_pago[]" class="form-control fecha-input" required>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="form-label">Monto del pago ${i}:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="montos_pago[]" class="form-control monto-input" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-content-center align-items-center">
                        <span class="badge bg-secondary">${i}</span>
                    </div>
                </div>
            `;
        }
        document.getElementById('btnGuardarPagos' + idVenta).style.display = 'inline-block';
    }

    // Función para distribuir equitativamente el saldo pendiente entre los pagos
    function distribuirEquitativamente(idVenta, saldoPendiente) {
        var numPagos = document.getElementById('numPagos' + idVenta).value;
        if (!numPagos || numPagos < 1) return;

        // Asegurarse de que los campos existen antes de distribuir
        var inputs = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        if (inputs.length !== parseInt(numPagos)) {
            generarCamposPagos(idVenta, saldoPendiente);
            inputs = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        }

        var montoBase = Math.floor((saldoPendiente / numPagos) * 100) / 100;
        var montos = [];
        var total = 0;
        for (var i = 0; i < numPagos - 1; i++) {
            montos.push(montoBase);
            total += montoBase;
        }
        // El último pago ajusta para cubrir el total exacto
        montos.push(Math.round((saldoPendiente - total) * 100) / 100);

        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = montos[i];
        }
    }

    // Autocompletar fechas mensuales y dividir montos equitativamente
    function autocompletarFechas(idVenta) {
        var inputsFecha = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="fechas_pago[]"]');
        var inputsMonto = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        if (inputsFecha.length === 0) return;
        var primeraFecha = inputsFecha[0].value;
        if (!primeraFecha) return;
        var fecha = new Date(primeraFecha);
        for (var i = 0; i < inputsFecha.length; i++) {
            var nuevaFecha = new Date(fecha);
            nuevaFecha.setMonth(fecha.getMonth() + i);
            // Ajuste para meses con menos días (ej: 31 de febrero)
            var dia = fecha.getDate();
            nuevaFecha.setDate(Math.min(dia, daysInMonth(nuevaFecha.getFullYear(), nuevaFecha.getMonth() + 1)));
            inputsFecha[i].value = nuevaFecha.toISOString().slice(0, 10);
        }
        // También dividir montos automáticamente
        var saldoPendiente = 0;
        // Buscar el saldo pendiente desde el input oculto
        var saldoInput = document.querySelector('#formProgramarPagos' + idVenta + ' input[name="saldo_pendiente"]');
        if (saldoInput) saldoPendiente = parseFloat(saldoInput.value);
        var numPagos = inputsMonto.length;
        if (numPagos > 0 && saldoPendiente > 0) {
            var montoBase = Math.floor((saldoPendiente / numPagos) * 100) / 100;
            var montos = [];
            var total = 0;
            for (var i = 0; i < numPagos - 1; i++) {
                montos.push(montoBase);
                total += montoBase;
            }
            montos.push(Math.round((saldoPendiente - total) * 100) / 100);
            for (var i = 0; i < inputsMonto.length; i++) {
                inputsMonto[i].value = montos[i];
            }
        }
    }

    // Helper para obtener días en un mes
    function daysInMonth(year, month) {
        return new Date(year, month, 0).getDate();
    }

    document.getElementById('fecha_venta').addEventListener('change', function() {
        document.getElementById('rango-fechas-container').style.display = 
            this.value === 'rango' ? 'block' : 'none';
    });

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('fecha_venta').value === 'rango') {
            document.getElementById('rango-fechas-container').style.display = 'block';
        }

        // Limpiar filtros (solo para el botón de limpiar, no para los links de los modals)
        var limpiarBtn = document.querySelector('.filter-section a[href="creditosA.php"]');
        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'creditosA.php?action=filter&limpiar=1';
            });
        }

        // Limpiar campos generados dinámicamente al cerrar cualquier modal de pagos
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                // Busca todos los contenedores de campos de pagos dentro del modal y límpialos
                var camposPagos = modal.querySelectorAll('[id^="camposPagos"]');
                camposPagos.forEach(function(contenedor) {
                    contenedor.innerHTML = '';
                });
                // Opcional: también limpia el selector de número de pagos
                var numPagosSelect = modal.querySelector('[id^="numPagos"]');
                if (numPagosSelect) numPagosSelect.value = '';
                // Oculta el botón de guardar si existe
                var btnGuardar = modal.querySelector('[id^="btnGuardarPagos"]');
                if (btnGuardar) btnGuardar.style.display = 'none';
            });
        });
    });

    document.getElementById('exportarCreditos').addEventListener('click', function(e) {
        e.preventDefault();
        // Obtén los valores de los filtros
        const cliente = document.getElementById('cliente').value;
        const fecha_venta = document.getElementById('fecha_venta').value;
        const fecha_desde = document.querySelector('input[name="fecha_desde"]').value;
        const fecha_hasta = document.querySelector('input[name="fecha_hasta"]').value;
        const saldo = document.getElementById('saldo').value;
        const atraso = document.getElementById('atraso').value;
        // Construye la URL con los filtros
        let url = '../helpers/ReporteCreditosA.php?';
        if (cliente) url += 'cliente=' + encodeURIComponent(cliente) + '&';
        if (fecha_venta) url += 'fecha_venta=' + encodeURIComponent(fecha_venta) + '&';
        if (fecha_desde) url += 'fecha_desde=' + encodeURIComponent(fecha_desde) + '&';
        if (fecha_hasta) url += 'fecha_hasta=' + encodeURIComponent(fecha_hasta) + '&';
        if (saldo) url += 'saldo=' + encodeURIComponent(saldo) + '&';
        if (atraso) url += 'atraso=' + encodeURIComponent(atraso) + '&';
        window.open(url, '_blank');
    });
    </script>
</body>
</html>