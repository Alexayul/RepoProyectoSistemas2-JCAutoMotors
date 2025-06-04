<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/AdminController.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario logueado
$usuario_logueado = $_SESSION['user'];
$id_usuario = $_SESSION['user']['id'];

// Obtener datos extendidos del usuario para el sidebar
function obtenerDatosUsuario($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                            FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                            WHERE p._id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($userData['foto'])) {
        $userData['foto'] = 'data:image/jpeg;base64,' . base64_encode($userData['foto']);
    } else {
        $userData['foto'] = 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png';
    }
    return $userData;
}
$userData = obtenerDatosUsuario($conn, $id_usuario);

// Obtener página actual para el sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Instanciar el controlador de admin y obtener los datos necesarios
$adminController = new AdminController($conn);

// Estadísticas principales
$stats = $adminController->getDashboardStats();

// Ventas mensuales
$ventas_mensuales = $adminController->getVentasMensuales();

// Mantenimientos mensuales
$mantenimientos_mensuales = $adminController->getMantenimientosMensuales();

// Modelos más vendidos
$top_modelos = $adminController->getTopModelos();
?>
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gradient-colors"></script>
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/administrador.css">
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
                    <a class="nav-link" href="../public/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
        

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Panel de Administración
                    </h1>
                    <p class="text-muted mb-0">Bienvenido al panel de administración del concesionario de motos.</p>
                </div>
            </div>
        </div>

        <div class="row mb-3 animate-fade" data-aos="fade-up">
            <!-- Tarjeta 1: Motocicletas disponibles -->
            <div class="col-xl col-lg-2 col-md-4 col-6 mb-4">
                <div class="card stat-card bg-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center w-100 gap-3">
                            <div>
                                <h6 class="text-uppercase text-white-50 mb-1">Motocicletas</h6>
                                <h2 class="mb-0 fs-1"><?php echo $stats['motocicletas']; ?></h2>
                                <small class="text-white-50">Disponibles</small>
                            </div>
                            <i class="bi bi-bicycle card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta 2: Ventas del mes -->
            <div class="col-xl col-lg-2 col-md-4 col-6 mb-4">
                <div class="card stat-card bg-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-white-50 mb-1">Ventas</h6>
                                <h2 class="mb-0"><?php echo $stats['ventas_count']; ?></h2>
                                <small class="text-white-50">Este mes</small>
                            </div>
                            <i class="bi bi-cash-coin card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta 3: Accesorios en inventario -->
            <div class="col-xl col-lg-2 col-md-4 col-6 mb-4">
                <div class="card stat-card bg-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-dark-50 mb-1">Accesorios</h6>
                                <h2 class="mb-0"><?php echo $stats['accesorios']; ?></h2>
                                <small class="text-dark-50">En inventario</small>
                            </div>
                            <i class="bi bi-box-seam card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta 4: Mantenimientos del mes -->
            <div class="col-xl col-lg-2 col-md-4 col-6 mb-4">
                <div class="card stat-card bg-purple h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-white-50 mb-1">Mantenimientos</h6>
                                <h2 class="mb-0"><?php echo $stats['mantenimientos_count']; ?></h2>
                                <small class="text-white-50">Este mes</small>
                            </div>
                            <i class="bi bi-tools card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta 5: Ingresos del mes en Bs. -->
            <div class="col-xl col-lg-2 col-md-4 col-6 mb-4">
                <div class="card stat-card bg-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-white-50 mb-1">Ingresos</h6>
                                <h2 class="mb-0">Bs. <?php echo number_format($stats['ventas_total'] * 7); ?></h2>
                                <small class="text-white-50">Este mes</small>
                            </div>
                            <i class="bi bi-graph-up card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas principales -->
        <div class="row mb-4" data-aos="fade-up">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rendimiento Mensual</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active">2025</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Distribución de Ventas</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 350px;">
                            <canvas id="salesDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila de gráficas -->
        <div class="row mb-4" data-aos="fade-up">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ventas Mensuales</h5>
                        <select class="form-select form-select-sm w-auto">
                            <option>2025</option>
                        </select>
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
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Mantenimientos Mensuales</h5>
                        <select class="form-select form-select-sm w-auto">
                            <option>2025</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="mantenimientosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tercera fila - Modelos más vendidos y progreso de metas -->
        <div class="row mb-4" data-aos="fade-up">
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Modelos Más Vendidos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($top_modelos)): ?>
                            <?php foreach ($top_modelos as $modelo): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?php echo htmlspecialchars($modelo['marca'] . ' ' . $modelo['modelo']); ?></span>
                                        <span class="text-dark fw-bold"><?php echo $modelo['cantidad']; ?> ventas</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" 
                                                role="progressbar" 
                                                style="width: <?php echo ($modelo['cantidad'] / max(array_column($top_modelos, 'cantidad'))) * 100; ?>%" 
                                                aria-valuenow="<?php echo $modelo['cantidad']; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="<?php echo max(array_column($top_modelos, 'cantidad')); ?>">
                                            </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bar-chart-line fs-1"></i>
                                <p class="mt-2">No hay datos de ventas disponibles</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Progreso de Metas Mensuales</h5>
                        <div>
                            <span class="badge bg-primary me-2">Ventas: 75%</span>
                            <span class="badge bg-success">Servicios: 60%</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="goalsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// Inicializar animaciones
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});

// Paleta de colores actualizada
const colors = {
    ventas: {
        primary: '#a51314',     // Rojo principal
        secondary: '#701106',   // Rojo oscuro
        light: '#f1d6d6'        // Rojo claro
    },
    mantenimientos: {
        primary: '#1e3a8a',     // Azul oscuro
        secondary: '#3b82f6',   // Azul principal
        light: '#dbeafe'        // Azul claro
    },
    otros: {
        success: '#198754',
        warning: '#ffc107',
        info: '#42c4de',
        purple: '#9d4edd'
    }
};

// Datos para los gráficos
const monthlyData = {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    ventas: [
        <?php 
        $ventasData = array_fill(0, 12, 0);
        foreach ($ventas_mensuales as $venta) {
            $ventasData[$venta['mes']-1] = $venta['total'] * 7;
        }
        echo implode(', ', $ventasData);
        ?>
    ],
    mantenimientos: [
        <?php 
        $mantData = array_fill(0, 12, 0);
        foreach ($mantenimientos_mensuales as $mant) {
            $mantData[$mant['mes']-1] = $mant['total'];
        }
        echo implode(', ', $mantData);
        ?>
    ]
};

// Gráfico de Rendimiento
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'line',
    data: {
        labels: monthlyData.labels,
        datasets: [
            {
                label: 'Ventas (Bs.)',
                data: monthlyData.ventas,
                borderColor: colors.ventas.primary,
                backgroundColor: hexToRgba(colors.ventas.primary, 0.1),
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: colors.ventas.primary,
                pointRadius: 4,
                pointHoverRadius: 6
            },
            {
                label: 'Mantenimientos (Bs.)',
                data: monthlyData.mantenimientos,
                borderColor: colors.mantenimientos.primary,
                backgroundColor: hexToRgba(colors.mantenimientos.primary, 0.1),
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: colors.mantenimientos.primary,
                pointRadius: 4,
                pointHoverRadius: 6
            }
        ]
    },
    options: getChartOptions('Comparativa Mensual (Bs.)')
});

// Gráfico de Distribución de Ventas
const salesDistributionCtx = document.getElementById('salesDistributionChart').getContext('2d');
const salesDistributionChart = new Chart(salesDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Motocicletas', 'Accesorios', 'Servicios', 'Repuestos'],
        datasets: [{
            data: [65, 15, 12, 8],
            backgroundColor: [
                colors.ventas.primary,
                colors.otros.warning,
                colors.mantenimientos.primary,
                colors.otros.purple
            ],
            borderWidth: 0,
            cutout: '70%'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        family: 'Montserrat',
                        size: 12
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.label}: ${context.raw}% (Bs. ${Math.round(context.parsed * monthlyData.ventas.reduce((a,b) => a + b, 0) / 100).toLocaleString()})`;
                    }
                }
            }
        }
    }
});

// Gráfica de Ventas Mensuales
const ventasCtx = document.getElementById('ventasChart').getContext('2d');
const ventasChart = new Chart(ventasCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Ventas (Bs.)',
            data: monthlyData.ventas,
            backgroundColor: createGradient(ventasCtx, colors.ventas.primary, colors.ventas.secondary),
            borderColor: colors.ventas.primary,
            borderWidth: 0,
            borderRadius: 6,
            barPercentage: 0.7
        }]
    },
    options: getChartOptions('Ventas Mensuales (Bs.)', false)
});

// Gráfica de Mantenimientos Mensuales
const mantenimientosCtx = document.getElementById('mantenimientosChart').getContext('2d');
const mantenimientosChart = new Chart(mantenimientosCtx, {
    type: 'line',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Mantenimientos (Bs.)',
            data: monthlyData.mantenimientos,
            backgroundColor: hexToRgba(colors.mantenimientos.primary, 0.1),
            borderColor: colors.mantenimientos.primary,
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointBackgroundColor: colors.mantenimientos.primary,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: getChartOptions('Mantenimientos Mensuales (Bs.)', false)
});

// Gráfico de Progreso de Metas
const goalsCtx = document.getElementById('goalsChart').getContext('2d');
const goalsChart = new Chart(goalsCtx, {
    type: 'radar',
    data: {
        labels: ['Ventas', 'Servicios', 'Clientes nuevos', 'Satisfacción', 'Eficiencia'],
        datasets: [
            {
                label: 'Meta',
                data: [100, 100, 100, 100, 100],
                backgroundColor: hexToRgba(colors.ventas.primary, 0.1),
                borderColor: hexToRgba(colors.ventas.primary, 0.5),
                borderWidth: 1,
                pointBackgroundColor: hexToRgba(colors.ventas.primary, 0.5),
                pointRadius: 0
            },
            {
                label: 'Actual',
                data: [75, 60, 80, 90, 70],
                backgroundColor: hexToRgba(colors.ventas.primary, 0.3),
                borderColor: colors.ventas.primary,
                borderWidth: 2,
                pointBackgroundColor: colors.ventas.primary,
                pointRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        family: 'Montserrat',
                        size: 12
                    },
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.raw}%`;
                    }
                }
            }
        },
        scales: {
            r: {
                angleLines: {
                    color: 'rgba(0,0,0,0.1)'
                },
                suggestedMin: 0,
                suggestedMax: 100,
                ticks: {
                    backdropColor: 'transparent',
                    stepSize: 20
                }
            }
        }
    }
});

// Función para crear gradientes en los gráficos
function createGradient(ctx, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

// Función para convertir hex a rgba
function hexToRgba(hex, alpha = 1) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Función para opciones comunes de gráficos
function getChartOptions(title, showLegend = true) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: showLegend,
                position: 'top',
                labels: {
                    font: {
                        family: 'Montserrat',
                        size: 13
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            title: {
                display: !!title,
                text: title,
                font: {
                    family: 'Montserrat',
                    size: 16,
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 20
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.9)',
                titleFont: {
                    family: 'Montserrat',
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Montserrat',
                    size: 12
                },
                padding: 12,
                usePointStyle: true,
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: Bs. ${context.raw.toLocaleString()}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: 'Montserrat'
                    }
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        family: 'Montserrat'
                    },
                    callback: function(value) {
                        return `Bs. ${value.toLocaleString()}`;
                    }
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };
}
setInterval(() => {
    const randomUpdate = Math.floor(Math.random() * 70000) - 35000;
    const newVentasData = monthlyData.ventas.map(v => Math.max(0, v + randomUpdate));
    const newMantData = monthlyData.mantenimientos.map(m => Math.max(0, m + randomUpdate * 0.3));
    
    ventasChart.data.datasets[0].data = newVentasData;
    ventasChart.update();
    
    mantenimientosChart.data.datasets[0].data = newMantData;
    mantenimientosChart.update();
    
    performanceChart.data.datasets[0].data = newVentasData;
    performanceChart.data.datasets[1].data = newMantData;
    performanceChart.update();
}, 30000);
</script>
</body>
</html>