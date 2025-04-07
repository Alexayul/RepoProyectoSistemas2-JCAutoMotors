<?php
require '../config/conexion.php';
session_start();

$usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $usuario_logueado ? $usuario_logueado['id'] : null;

if (!$usuario_logueado) {
    header('Location: ../index.php');
    exit();
}

// Obtener estadísticas para el dashboard
$stats = [];
try {
    // Total motocicletas disponibles
    $stmt = $conn->query("SELECT SUM(cantidad) FROM MOTOCICLETA WHERE estado = 'Disponible'");
    $stats['motocicletas'] = $stmt->fetchColumn() ?? 0;

    // Ventas del mes con información de cliente
    $stmt = $conn->query("SELECT COUNT(*) as count, SUM(v.monto_total) as total 
                         FROM VENTA v
                         WHERE MONTH(v.fecha_venta) = MONTH(CURRENT_DATE())");
    $ventas = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['ventas_count'] = $ventas['count'];
    $stats['ventas_total'] = $ventas['total'] ?? 0;

    // Mantenimientos del mes con información de cliente
    $stmt = $conn->query("SELECT COUNT(*) as count, SUM(m.costo) as total 
                         FROM MANTENIMIENTO m
                         WHERE MONTH(m.fecha) = MONTH(CURRENT_DATE())");
    $mantenimientos = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['mantenimientos_count'] = $mantenimientos['count'];
    $stats['mantenimientos_total'] = $mantenimientos['total'] ?? 0;

    // Accesorios en stock
    $stmt = $conn->query("SELECT SUM(cantidad) FROM ACCESORIO WHERE estado = 'Disponible'");
    $stats['accesorios'] = $stmt->fetchColumn() ?? 0;

    // Últimas ventas con nombre de cliente
    $stmt = $conn->query("SELECT v._id, v.fecha_venta, v.monto_total, p.nombre as cliente
                         FROM VENTA v
                         JOIN CLIENTE c ON v.id_cliente = c._id
                         JOIN PERSONA p ON c._id = p._id
                         ORDER BY v.fecha_venta DESC LIMIT 5");
    $ultimas_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Próximos mantenimientos con detalles completos
    $stmt = $conn->query("SELECT m._id, m.fecha, m.tipo, 
                         mo.marca, mo.modelo, 
                         CONCAT(p.nombre, ' ', p.apellido) as cliente
                         FROM MANTENIMIENTO m
                         JOIN MOTOCICLETA mot ON m.id_motocicleta = mot._id
                         JOIN MODELO_MOTO mo ON mot.id_modelo = mo._id
                         JOIN CLIENTE c ON m.id_cliente = c._id
                         JOIN PERSONA p ON c._id = p._id
                         WHERE m.fecha >= CURDATE()
                         ORDER BY m.fecha ASC LIMIT 5");
    $proximos_mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Modelos más vendidos con detalles
    $stmt = $conn->query("SELECT mo.marca, mo.modelo, COUNT(*) as cantidad 
                         FROM VENTA v
                         JOIN DETALLE_VENTA dv ON v._id = dv.id_venta
                         JOIN MOTOCICLETA mot ON dv.id_producto = mot._id AND dv.tipo_producto = 'motocicleta'
                         JOIN MODELO_MOTO mo ON mot.id_modelo = mo._id
                         GROUP BY mo.marca, mo.modelo
                         ORDER BY cantidad DESC LIMIT 3");
    $top_modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en las consultas: " . $e->getMessage());
}

// Obtener datos para gráfico de ventas mensuales
$ventas_mensuales = [];
try {
    $stmt = $conn->query("SELECT MONTH(fecha_venta) as mes, SUM(monto_total) as total 
                         FROM VENTA 
                         WHERE YEAR(fecha_venta) = YEAR(CURRENT_DATE())
                         GROUP BY MONTH(fecha_venta)");
    $ventas_mensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventas_mensuales = [
        ['mes' => 1, 'total' => 120000],
        ['mes' => 2, 'total' => 150000],
        ['mes' => 3, 'total' => 180000],
        ['mes' => 4, 'total' => 160000],
        ['mes' => 5, 'total' => 200000],
        ['mes' => 6, 'total' => 220000]
    ];
}

// Obtener datos para gráfico de mantenimientos mensuales
$mantenimientos_mensuales = [];
try {
    $stmt = $conn->query("SELECT MONTH(fecha) as mes, SUM(costo) as total 
                         FROM MANTENIMIENTO 
                         WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
                         GROUP BY MONTH(fecha)");
    $mantenimientos_mensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Datos de ejemplo si hay error
    foreach ($ventas_mensuales as $venta) {
        $mantenimientos_mensuales[] = [
            'mes' => $venta['mes'],
            'total' => $venta['total'] * 0.3 // 30% de las ventas como mantenimientos
        ];
    }
}

// Datos del usuario
$userQuery = "SELECT p.nombre, p.apellido, p.foto_url, e.cargo
              FROM PERSONA p
              LEFT JOIN EMPLEADO e ON p._id = e._id
              WHERE p._id = :user_id";
              
try {
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute(['user_id' => $user_id]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $userData = [
        'nombre' => 'Usuario',
        'apellido' => '',
        'foto_url' => 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png',
        'cargo' => 'No especificado'
    ];
}

$current_page = basename($_SERVER['PHP_SELF']);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../public/administrador.css">
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
                <img src="<?php echo htmlspecialchars($userData['foto_url'] ?? 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png'); ?>" alt="User">
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
                    <a class="nav-link <?php echo ($current_page == 'ventas.php') ? 'active' : ''; ?>" href="ventas.php">
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

        <!--<div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Próximos Mantenimientos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Motocicleta</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximos_mantenimientos as $mant): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($mant['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($mant['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($mant['marca'] . ' ' . $mant['modelo']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $mant['tipo']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>-->
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