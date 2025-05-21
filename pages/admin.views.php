<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Concesionario de Motos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../public/css/administrador.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
        </a>
    </div>
    
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="user-avatar">
            <img src="<?php echo htmlspecialchars($userData['foto'] ?? 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png'); ?>" alt="User">
        </div>
        <div class="user-info">
            <h5 class="user-name"><?php echo htmlspecialchars($userData['nombre'] . ' ' . $userData['apellido']); ?></h5>
            <p class="user-role">Administrador</p>
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
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'gestionEmpleados.php') ? 'active' : ''; ?>"  href="gestionEmpleados.php">
                    <i class="bi bi-people"></i>
                    <span>Empleados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'catalogo.php') ? 'active' : ''; ?>" href="catalogo.php">
                    <i class="bi bi-bicycle"></i>
                    <span>Catálogo</span>
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
            <h1 class="h3 mb-0">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard de Administración
            </h1>
            <div class="text-muted"><?php echo date('l, j F Y'); ?></div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 animate-fade">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center w-100 gap-3">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Modelos de Motocicletas</h6>
                            <h2 class="mb-0"><?php echo $stats['motocicletas']; ?></h2>
                            <small class="text-white-50">Disponibles</small>
                        </div>
                        <i class="bi bi-bicycle card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Ventas de Motocicletas</h6>
                            <h2 class="mb-0"><?php echo $stats['ventas_count']; ?></h2>
                            <small class="text-white-50">Este mes</small>
                        </div>
                        <i class="bi bi-cash-coin card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-dark-50 mb-1">Accesorios de motocicletas</h6>
                            <h2 class="mb-0"><?php echo $stats['accesorios']; ?></h2>
                            <small class="text-dark-50">En inventario</small>
                        </div>
                        <i class="bi bi-box-seam card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card bg-purple h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Mantenimientos de Motocicletas</h6>
                            <h2 class="mb-0"><?php echo $stats['mantenimientos_count']; ?></h2>
                            <small class="text-white-50">Este mes</small>
                        </div>
                        <i class="bi bi-tools card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="row mb-4 animate-fade">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ventas Mensuales</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Mantenimientos Mensuales</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="mantenimientosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas -->
    <div class="row animate-fade">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Últimas Ventas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_ventas as $venta): ?>
                                <tr>
                                    <td>#<?php echo $venta['_id']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?></td>
                                    <td><?php echo htmlspecialchars($venta['cliente']); ?></td>
                                    <td class="text-end">$<?php echo number_format($venta['monto_total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Gráfica de Ventas Mensuales
const ventasCtx = document.getElementById('ventasChart').getContext('2d');
const ventasChart = new Chart(ventasCtx, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [{
            label: 'Ventas $',
            data: [
                <?php 
                // Rellenar datos para los 12 meses
                $ventasData = array_fill(0, 12, 0);
                foreach ($ventas_mensuales as $venta) {
                    $ventasData[$venta['mes']-1] = $venta['total'];
                }
                echo implode(', ', $ventasData);
                ?>
            ],
            backgroundColor: 'rgba(230, 57, 70, 0.7)',
            borderColor: 'rgba(230, 57, 70, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Ventas: $' + context.raw.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfica de Mantenimientos Mensuales
const mantenimientosCtx = document.getElementById('mantenimientosChart').getContext('2d');
const mantenimientosChart = new Chart(mantenimientosCtx, {
    type: 'line',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [{
            label: 'Mantenimientos $',
            data: [
                <?php 
                // Rellenar datos para los 12 meses
                $mantData = array_fill(0, 12, 0);
                foreach ($mantenimientos_mensuales as $mant) {
                    $mantData[$mant['mes']-1] = $mant['total'];
                }
                echo implode(', ', $mantData);
                ?>
            ],
            backgroundColor: 'rgba(157, 78, 221, 0.2)',
            borderColor: 'rgba(157, 78, 221, 1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Mantenimientos: $' + context.raw.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
