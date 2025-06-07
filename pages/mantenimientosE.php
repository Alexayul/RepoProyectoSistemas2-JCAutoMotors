<?php
require_once '../config/conexion.php';
require_once '../controllers/MantenimientoController.php';
session_start();

// Verificar inicio de sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Crear instancia del controlador
$mantenimientoController = new MantenimientoController($conn);

// Obtener datos del usuario logueado
$usuario_logueado = $_SESSION['user'];
$id_usuario = $_SESSION['user']['id'];

try {
    $id_empleado = $mantenimientoController->getIdEmpleado($id_usuario);
} catch (Exception $e) {
    die($e->getMessage());
}

// Manejar registro de nuevo mantenimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_mantenimiento'])) {
    $resultado = $mantenimientoController->crearMantenimiento($_POST, $id_empleado);
    
    if ($resultado['success']) {
        $_SESSION['mensaje'] = $resultado['message'];
        header("Location: mantenimientosE.php");
        exit;
    } else {
        $error = $resultado['message'];
    }
}

// Obtener datos necesarios
$clientes = $mantenimientoController->getClientes();
$motocicletas = $mantenimientoController->getMotocicletas();
$mantenimientos = $mantenimientoController->getMantenimientosEmpleado($id_empleado);

// Aplicar filtros si existen
$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'tipo' => $_GET['tipo'] ?? null,
    'cliente' => $_GET['cliente'] ?? null
];

// Eliminar filtros nulos o vacíos
$filtros = array_filter($filtros, function($value) {
    return $value !== null && $value !== '';
});

// Variable para mensaje cuando no hay resultados
$mensaje_filtro = 'No se encontraron mantenimientos con los filtros seleccionados.';

// Aplicar filtros solo si hay filtros válidos
if (!empty($filtros) && isset($_GET['action']) && $_GET['action'] === 'filter') {
    $mantenimientos_filtrados = $mantenimientoController->filtrarMantenimientos($mantenimientos, $filtros);
    
    // Si no hay resultados, mantener un mensaje informativo
    if (empty($mantenimientos_filtrados)) {
        $mantenimientos = [];
    } else {
        $mantenimientos = $mantenimientos_filtrados;
    }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Automotors - Mantenimientos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/MantemientoE.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <a class="nav-link text-white" href="#">
                            <i class="bi bi-cash-stack me-2"></i>Crédito Directo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#mantenimientos" data-bs-toggle="tab">
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
                <div class="tab-pane fade show active" id="mantenimientos">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="bi bi-tools text-primary me-2"></i> Historial de Mantenimientos
                        </h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="abrirModal()">
                                <i class="bi bi-plus-circle me-1"></i> Nuevo Mantenimiento
                            </button>
                        </div>
                    </div>

                    <!-- Estadísticas de Mantenimientos -->
                    <div class="row mb-4 g-3">
                        <div class="col">
                            <div class="card stats-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">
                                                <i class="bi bi-tools me-1"></i> Total Mantenimientos
                                            </h6>
                                            <h3 class="mb-0 text-primary">
                                                <?= count($mantenimientos ?? []) ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mantenimientos gratuitos por venta -->
                        <div class="col">
                            <div class="card stats-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">
                                                <i class="bi bi-gift me-1"></i> Mantenimientos Gratuitos
                                            </h6>
                                            <h3 class="mb-0 text-success">
                                                <?= count(array_filter($mantenimientos ?? [], fn($m) => $m['es_gratuito'] == 1)) ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ingresos por Mantenimientos -->
                        <div class="col">
                            <div class="card stats-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">
                                                <i class="bi bi-cash-coin me-1"></i> Ingresos por Mantenimientos
                                            </h6>
                                            <h3 class="mb-0 text-info">
                                                $ <?= number_format(array_sum(array_column($mantenimientos ?? [], 'costo')), 2) ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Mantenimientos -->
                    <div class="filter-section mb-4">
                        <h5 class="mb-4">
                            <i class="bi bi-funnel-fill text-primary me-2"></i>Filtrar Mantenimientos
                        </h5>
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="action" value="filter">
                            
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">
                                    <i class="bi bi-calendar-minus text-primary me-1"></i>Desde
                                </label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">
                                    <i class="bi bi-calendar-plus text-primary me-1"></i>Hasta
                                </label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="tipo" class="form-label">
                                    <i class="bi bi-wrench text-primary me-1"></i>Tipo de Mantenimiento
                                </label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="Cambio de aceite">Cambio de aceite</option>
                                    <option value="Revisión general">Revisión general</option>
                                    <option value="Reparación">Reparación</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="cliente" class="form-label">
                                    <i class="bi bi-person text-primary me-1"></i>Cliente
                                </label>
                                <select class="form-select" id="cliente" name="cliente">
                                    <option value="">Todos</option>
                                    <?php foreach(($clientes ?? []) as $cliente): ?>
                                        <option value="<?= $cliente['_id'] ?>">
                                            <?= htmlspecialchars($cliente['nombre_completo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                            
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                                </button>
                                <a href="mantenimientosE.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de Mantenimientos -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php if (!empty($mantenimientos)): ?>
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Motocicleta</th>
                                                <th>Tipo</th>
                                                <th>Costo</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($mantenimientos as $m): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                                                    <td><?= htmlspecialchars($m['nombre_cliente']) ?></td>
                                                    <td><?= htmlspecialchars($m['modelo_motocicleta']) ?></td>
                                                    <td><?= htmlspecialchars($m['tipo']) ?></td>
                                                    <td>Bs <?= number_format($m['costo'], 2) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $m['es_gratuito'] ? 'success' : 'primary'
                                                        ?>">
                                                            <?= $m['es_gratuito'] ? 'Gratuito' : 'Pagado' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalleMantenimiento(<?= $m['_id'] ?>)">
                                                            <i class="bi bi-eye"></i> Detalle
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="alert alert-info text-center" role="alert">
                                        <?= $mensaje_filtro ?? 'No hay mantenimientos disponibles.' ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nuevo Mantenimiento -->
    <div class="modal fade" id="modalMantenimiento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-tools me-2"></i> Nuevo Mantenimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="formMantenimiento">
                    <div class="modal-body">
                        <!-- Campos para registrar mantenimiento -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Cliente *</label>
                                <select class="form-select" name="cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach(($clientes ?? []) as $c): ?>
                                        <option value="<?= $c['_id'] ?>"><?= htmlspecialchars($c['nombre_completo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motocicleta *</label>
                                <select class="form-select" name="motocicleta" required>
                                    <option value="">Seleccione una motocicleta</option>
                                    <?php foreach(($motocicletas ?? []) as $m): ?>
                                        <option value="<?= $m['_id'] ?>">
                                            <?= htmlspecialchars($m['modelo'] . ' - ' . $m['color']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Mantenimiento *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="Cambio de aceite">Cambio de aceite</option>
                                    <option value="Revisión general">Revisión general</option>
                                    <option value="Reparación">Reparación</option>
                                </select>
                            </div>
                            <!-- ¿Es gratuito? -->
                            <div class="col-md-6">
                                <label for="es_gratuito" class="form-label">¿Es gratuito?</label>
                                <select class="form-select" id="es_gratuito" name="es_gratuito" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <input type="number" 
                                step="0.01" 
                                min="0" 
                                max="10000" 
                                class="form-control" 
                                name="costo_bs" 
                                id="costo_bs" 
                                required
                                placeholder="Ingrese el costo"
                                oninput="this.value = Math.abs(this.value)">
                            <div class="invalid-feedback">
                                Por favor, ingrese un costo válido (entre 0 y 10,000)
                            </div>


                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="guardar_mantenimiento" value="1" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Mantenimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../public/js/mantenimientosE.js"></script>
</body>
</html>