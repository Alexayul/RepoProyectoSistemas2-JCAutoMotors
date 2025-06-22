<?php
require_once '../config/conexion.php';
require_once '../controllers/MantenimientosAdminController.php';

session_start();

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
$current_page = basename($_SERVER['PHP_SELF']);

$controller = new MantenimientosAdminController($conn);

$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'tipo' => $_GET['tipo'] ?? null,
    'cliente' => $_GET['cliente'] ?? null,
    'empleado' => $_GET['empleado'] ?? null
];

$clientes = $controller->obtenerClientes();
$empleados = $controller->obtenerEmpleados();
$mantenimientos = $controller->obtenerMantenimientos($filtros);

// Estadísticas para la fila de stats
$total_mantenimientos = count($mantenimientos);
$total_gratuitos = count(array_filter($mantenimientos, fn($m) => $m['es_gratuito']));
$total_pagados = $total_mantenimientos - $total_gratuitos;
$total_bs = array_sum(array_column($mantenimientos, 'costo'));
$promedio_bs = $total_mantenimientos > 0 ? $total_bs / $total_mantenimientos : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantenimientos - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <p class="user-role"><?php echo htmlspecialchars($userData['cargo'] ?? 'Administrador'); ?></p>
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
<!-- Fin Sidebar -->

<div class="main-content">
    <!-- Encabezado y botones de acción para mantenimientos -->
    <div class="content-header">
        <div>
            <h1 class="h2">
                <i class="bi bi-tools text-dark me-2"></i> Historial de Mantenimientos
            </h1>
            <div class="breadcrumbs">
                <i class="bi bi-house-door me-1"></i> Inicio / Mantenimientos
            </div>
        </div>
        <div class="action-buttons">
            <a href="#" id="exportarMantenimientos" target="_blank" class="btn btn-dark">
                <i class="bi bi-upload me-1"></i>Exportar
            </a>
        </div>
    </div>

    <!-- Fila de estadísticas (ahora arriba de los filtros) -->
    <div class="stats-row mb-4 d-flex flex-wrap gap-3">
        <div class="stat-card flex-fill text-center">
            <div class="stat-icon mb-2">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_mantenimientos ?></h3>
                <p class="mb-0">Total Mantenimientos</p>
            </div>
        </div>
        <div class="stat-card flex-fill text-center">
            <div class="stat-icon mb-2">
                <i class="bi bi-gift"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_gratuitos ?></h3>
                <p class="mb-0">Gratuitos</p>
            </div>
        </div>
        <div class="stat-card flex-fill text-center">
            <div class="stat-icon mb-2">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_pagados ?></h3>
                <p class="mb-0">Pagados</p>
            </div>
        </div>
        <div class="stat-card flex-fill text-center">
            <div class="stat-icon mb-2">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-info">
                <h3>Bs <?= number_format($total_bs, 2) ?></h3>
                <p class="mb-0">Total Facturado</p>
            </div>
        </div>
        <div class="stat-card flex-fill text-center">
            <div class="stat-icon mb-2">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-info">
                <h3>Bs <?= number_format($promedio_bs, 2) ?></h3>
                <p class="mb-0">Promedio x Mantenimiento</p>
            </div>
        </div>
    </div>

    <!-- Formulario de filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo">
                <option value="">Todos</option>
                <option value="Cambio de aceite">Cambio de aceite</option>
                <option value="Revisión general">Revisión general</option>
                <option value="Reparación">Reparación</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Cliente</label>
            <select class="form-select" name="cliente">
                <option value="">Todos</option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['_id'] ?>" <?= ($_GET['cliente'] ?? '') == $c['_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Empleado</label>
            <select class="form-select" name="empleado">
                <option value="">Todos</option>
                <?php foreach($empleados as $e): ?>
                    <option value="<?= $e['_id'] ?>" <?= ($_GET['empleado'] ?? '') == $e['_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="mantenimientosA.php" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th>Motocicleta</th>
                            <th>Tipo</th>
                            <th>Costo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mantenimientos as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                            <td><?= htmlspecialchars($m['nombre_cliente']) ?></td>
                            <td><?= htmlspecialchars($m['nombre_empleado']) ?></td>
                            <td><?= htmlspecialchars($m['modelo_motocicleta']) ?></td>
                            <td><?= htmlspecialchars($m['tipo']) ?></td>
                            <td>Bs <?= number_format($m['costo'], 2) ?></td>
                            <td>
                                <?php
                                    $badgeColor = $m['es_gratuito'] ? '#701106' : '#050506';
                                    $badgeText = $m['es_gratuito'] ? 'Gratuito' : 'Pagado';
                                ?>
                                <span class="badge" style="background:<?= $badgeColor ?>; color:#fff;">
                                    <?= $badgeText ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($mantenimientos)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay mantenimientos registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Mantenimiento -->
<div class="modal fade" id="modalDetalleMantenimiento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- El contenido se reemplaza dinámicamente -->
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/mantenimientosA.js"></script>
<script>
document.getElementById('exportarMantenimientos').addEventListener('click', function(e) {
    e.preventDefault();
    // Obtén los valores de los filtros
    const fecha_desde = document.querySelector('input[name="fecha_desde"]').value;
    const fecha_hasta = document.querySelector('input[name="fecha_hasta"]').value;
    const tipo = document.querySelector('select[name="tipo"]').value;
    const cliente = document.querySelector('select[name="cliente"]').value;
    const empleado = document.querySelector('select[name="empleado"]').value;
    // Construye la URL con los filtros
    let url = '../helpers/ReporteMantenimientosA.php?';
    if (fecha_desde) url += 'fecha_desde=' + encodeURIComponent(fecha_desde) + '&';
    if (fecha_hasta) url += 'fecha_hasta=' + encodeURIComponent(fecha_hasta) + '&';
    if (tipo) url += 'tipo=' + encodeURIComponent(tipo) + '&';
    if (cliente) url += 'cliente=' + encodeURIComponent(cliente) + '&';
    if (empleado) url += 'empleado=' + encodeURIComponent(empleado) + '&';
    window.open(url, '_blank');
});
</script>
</body>
</html>