<?php
include '../config/conexion.php';
include '../controllers/CatalogoController.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];

// Crear instancia del CatalogoController
$catalogoController = new CatalogoController($conn);

// Manejar solicitudes POST (agregar/editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $catalogoController->handleRequest();
    exit;
}

// Filtros para motocicletas
$brandFilter = isset($_POST['brand']) ? trim($_POST['brand']) : '';
$modelFilter = isset($_POST['model']) ? trim($_POST['model']) : '';
$ccFilter = isset($_POST['cc']) ? (int)$_POST['cc'] : '';

// Obtener motocicletas con filtros
$motocicletas = $catalogoController->obtenerMotocicletas($brandFilter, $modelFilter, $ccFilter);

// Calcular estadísticas
$totalMotos = array_sum(array_column($motocicletas, 'cantidad'));
$modelosUnicos = count(array_unique(array_column($motocicletas, 'modelo')));
$marcasUnicas = count(array_unique(array_column($motocicletas, 'marca')));

// Función para obtener códigos de colores
function getColorCode($colorName) {
    $colorMap = [
        'Rojo' => '#dc3545',
        'Azul' => '#0d6efd',
        'Negro' => '#000000',
        'Blanco' => '#ffffff',
        'Verde' => '#28a745',
        'Amarillo' => '#ffc107',
        'Gris' => '#6c757d',
        'Naranja' => '#fd7e14',
        'Morado' => '#6f42c1',
        'Rosado' => '#e83e8c',
        'Negro Mate' => '#0a0a0a',
        'Turquesa' => '#40e0d0', 
        'Blanco combinado' => '#f8f9fa',
    ];
    
    return $colorMap[$colorName] ?? '#6c757d'; 
}

// Obtener marcas únicas para el select de marcas
$marcas = [];
foreach ($motocicletas as $moto) {
    if (!in_array($moto['marca'], $marcas)) {
        $marcas[] = $moto['marca'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Motos - JC Automotors</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/CatalogoMotosE.css">       
</head>
<body>
    <div class="container-fluid p-0">
        <nav id="sidebar" class="sidebar">
            <div class="d-flex flex-column h-100">
                <div class="text-center mb-4">
                    <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active">
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
                        <a class="nav-link text-white" href="creditosE.php">
                            <i class="bi bi-cash-stack me-2"></i>Crédito Directo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-bicycle text-primary me-2"></i> Inventario de Motos
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMoto">
                            <i class="bi bi-plus-circle me-1"></i> Agregar Moto
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-printer me-1"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-upload me-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
            <!-- Mostrar mensajes de éxito o error -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-speedometer2 me-1"></i> Motos en Stock
                                    </h6>
                                    <h3 class="mb-0 text-primary"><?php echo $totalMotos; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-bicycle fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-collection me-1"></i> Modelos Disponibles
                                    </h6>
                                    <h3 class="mb-0 text-primary"><?php echo $modelosUnicos; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-tags fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-building me-1"></i> Marcas Disponibles
                                    </h6>
                                    <h3 class="mb-0 text-primary"><?php echo $marcasUnicas; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-building fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-section mb-4 p-4 bg-white rounded shadow-sm">
                <h5 class="mb-4">
                    <i class="bi bi-funnel-fill text-primary me-2"></i>Filtrar Motocicletas
                </h5>
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="brand" class="form-label">
                            <i class="bi bi-tag-fill text-primary me-1"></i>Marca
                        </label>
                        <input type="text" class="form-control" id="brand" name="brand" 
                               value="<?php echo htmlspecialchars($brandFilter); ?>" placeholder="Ej. Honda">
                    </div>
                    <div class="col-md-4">
                        <label for="model" class="form-label">
                            <i class="bi bi-gear-fill text-primary me-1"></i>Modelo
                        </label>
                        <input type="text" class="form-control" id="model" name="model" 
                               value="<?php echo htmlspecialchars($modelFilter); ?>" placeholder="Ej. Tornado">
                    </div>
                    <div class="col-md-4">
                        <label for="cc" class="form-label">
                            <i class="bi bi-speedometer text-primary me-1"></i>Cilindrada (cc)
                        </label>
                        <input type="number" class="form-control" id="cc" name="cc" 
                               min="50" max="5000" value="<?php echo htmlspecialchars($ccFilter); ?>" placeholder="50-5000">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                        </button>
                        <a href="empleado.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Catálogo de Motos -->
            <div class="row g-4 mb-4">
                <?php if (!empty($motocicletas)): ?>
                    <?php foreach ($motocicletas as $moto): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm employee-card">
                                <div class="position-relative">
                                    <?php
                                    $defaultImage = 'https://via.placeholder.com/500x300?text='.urlencode($moto['marca'].'+'.$moto['modelo']);
                                    $imagenSrc = !empty($moto['imagen']) ? 
                                        'data:image/jpeg;base64,'.base64_encode($moto['imagen']) : 
                                        $defaultImage;
                                    ?>
                                    <div class="ratio ratio-4x3"> 
                                        <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                            class="card-img-top object-fit-cover"
                                            alt="<?= htmlspecialchars($moto['marca'].' '.$moto['modelo']) ?>"
                                            loading="lazy"
                                            onerror="this.src='<?= htmlspecialchars($defaultImage) ?>'">
                                    </div>
                                </div>
                                
                                <!-- Cuerpo de la tarjeta -->
                                <div class="card-body pt-0">
                                    <h5 class="card-title fw-bold mb-1">
                                        <?= htmlspecialchars($moto['marca']) ?> <?= htmlspecialchars($moto['modelo']) ?>
                                    </h5>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($moto['cilindrada']) ?> cc</p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-<?= ($moto['cantidad'] > 0) ? 'info' : 'danger' ?>">
                                            <?= ($moto['cantidad'] > 0) ? "Disponible: ".$moto['cantidad'] : "Agotado" ?>
                                        </span>
                                        <small class="text-muted">Ingreso: <?= date('d/m/Y', strtotime($moto['fecha_ingreso'])) ?></small>
                                    </div>
                                    
                                    <div class="p-2 bg-light rounded mt-2">
                                        <div class="row text-center">
                                            <div class="col-6 border-end">
                                                <small class="text-muted d-block">Precio USD</small>
                                                <strong class="text-primary">$<?= number_format($moto['precio'], 2) ?></strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Precio Bs</small>
                                                <strong class="text-primary">Bs. <?= number_format($moto['precio'] * 7, 2) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Acciones para empleados -->
                                <div class="card-footer bg-white border-0 pt-0">
                                    <div class="card-footer bg-white border-0 pt-0">
                                        <div class="d-flex gap-2"> <!-- Flexbox con espacio entre botones -->
                                            <!-- Botón Ver Detalles -->
                                            <button class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 d-flex align-items-center justify-content-center flex-grow-1 transition-all"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalles<?= $moto['moto_id'] ?>">
                                                <i class="bi bi-eye-fill me-2 fs-5"></i>
                                                <span class="fw-medium">Ver detalles</span>
                                            </button>
                                            
                                            <!-- Botón Editar -->
                                            <button class="btn btn-outline-warning btn-sm rounded-pill px-3 py-2 d-flex align-items-center justify-content-center flex-grow-1 transition-all"
                                                    onclick="editarMoto('<?= $moto['moto_id'] ?>')">
                                                <i class="bi bi-pencil-square me-2 fs-5"></i>
                                                <span class="fw-medium">Editar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de detalles para cada moto -->
                        <div class="modal fade" id="modalDetalles<?= $moto['moto_id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">
                                            <i class="bi bi-bicycle me-2"></i>
                                            Detalles: <?= htmlspecialchars($moto['marca']) ?> <?= htmlspecialchars($moto['modelo']) ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="sticky-top pt-3">
                                                    <div class="bg-light p-3 rounded text-center mb-3">
                                                        <img src="<?= !empty($moto['imagen']) ? 'data:image/jpeg;base64,'.base64_encode($moto['imagen']) : $defaultImage ?>" 
                                                            class="img-fluid rounded" 
                                                            alt="<?= htmlspecialchars($moto['marca'].' '.$moto['modelo']) ?>">
                                                    </div>
                                                    <div class="alert <?= ($moto['cantidad'] > 0) ? 'alert-success' : 'alert-danger' ?>">
                                                        <i class="bi bi-<?= ($moto['cantidad'] > 0) ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                                                        <strong><?= ($moto['cantidad'] > 0) ? 'DISPONIBLE EN INVENTARIO' : 'SIN STOCK' ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <h4 class="mb-3">Información de la Moto</h4>
                                                
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th><i class="bi bi-speedometer2 me-2"></i> Cilindrada:</th>
                                                        <td><?= htmlspecialchars($moto['cilindrada']) ?> cc</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="bi bi-palette me-2"></i> Color:</th>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="color-swatch me-2" 
                                                                    style="background-color: <?= htmlspecialchars(getColorCode($moto['color'])) ?>;
                                                                            width: 20px; 
                                                                            height: 20px;
                                                                            border-radius: 50%;
                                                                            border: 1px solid #dee2e6;
                                                                            display: inline-block;"
                                                                    title="<?= htmlspecialchars($moto['color']) ?>">
                                                                </div>
                                                                <span><?= htmlspecialchars($moto['color']) ?></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="bi bi-box-seam me-2"></i> Stock:</th>
                                                        <td><?= htmlspecialchars($moto['cantidad']) ?> unidades</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="bi bi-calendar-check me-2"></i> Fecha ingreso:</th>
                                                        <td><?= htmlspecialchars($moto['fecha_ingreso']) ?></td>
                                                    </tr>
                                                    
                                                    <tr class="table-primary">
                                                        <th><i class="bi bi-currency-dollar me-2"></i> Precio USD:</th>
                                                        <td class="fw-bold">$<?= number_format($moto['precio'], 2) ?></td>
                                                    </tr>
                                                    <tr class="table-primary">
                                                        <th><i class="bi bi-cash-coin me-2"></i> Precio Bs:</th>
                                                        <td class="fw-bold">Bs. <?= number_format($moto['precio'] * 7, 2) ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle me-1"></i> Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center py-4">
                            <i class="bi bi-exclamation-triangle fs-4 me-2"></i>
                            <h4 class="d-inline-block">No se encontraron motocicletas</h4>
                            <p class="mt-2 mb-0">No hay motocicletas que coincidan con los criterios actuales.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
<!-- Modal para Agregar Moto -->
<div class="modal fade" id="modalAgregarMoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient bg-danger text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2"></i>Agregar Nueva Motocicleta</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="agregar">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select shadow-sm" id="agregarMarca" name="marca" required>
                                    <option value="" selected disabled>Seleccione una marca</option>
                                    <?php foreach($marcas as $marca): ?>
                                        <option value="<?= htmlspecialchars($marca) ?>"><?= htmlspecialchars($marca) ?></option>
                                    <?php endforeach; ?>
                                    <option value="otro">Otra marca (especificar)</option>
                                </select>
                                <label for="agregarMarca" class="text-muted">Marca</label>
                            </div>
                            <div id="otraMarcaContainer" class="mt-2 d-none">
                                <div class="form-floating">
                                    <input type="text" class="form-control shadow-sm" id="otraMarcaInput" name="otra_marca" placeholder=" ">
                                    <label for="otraMarcaInput" class="text-muted">Especifique la nueva marca</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control shadow-sm" id="agregarModelo" name="modelo" placeholder=" " required>
                                <label for="agregarModelo" class="text-muted">Modelo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control shadow-sm" id="agregarCilindrada" name="cilindrada" min="50" max="5000" placeholder=" " required>
                                <label for="agregarCilindrada" class="text-muted">Cilindrada (cc)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select shadow-sm" id="agregarColor" name="color" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Rojo">Rojo</option>
                                    <option value="Azul">Azul</option>
                                    <option value="Negro">Negro</option>
                                    <option value="Blanco">Blanco</option>
                                    <option value="Verde">Verde</option>
                                    <option value="Amarillo">Amarillo</option>
                                    <option value="Gris">Gris</option>
                                    <option value="Naranja">Naranja</option>
                                    <option value="Morado">Morado</option>
                                    <option value="Rosado">Rosado</option>
                                    <option value="Negro Mate">Negro Mate</option>
                                    <option value="Turquesa">Turquesa</option>
                                    <option value="Blanco combinado">Blanco combinado</option>
                                </select>
                                <label for="agregarColor" class="text-muted">Color</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" step="0.01" class="form-control shadow-sm" id="agregarPrecio" name="precio" placeholder=" " required>
                                <label for="agregarPrecio" class="text-muted">Precio (USD)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control shadow-sm" id="agregarCantidad" name="cantidad" min="1" placeholder=" " required>
                                <label for="agregarCantidad" class="text-muted">Cantidad en Stock</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control shadow-sm" id="agregarFechaIngreso" name="fecha_ingreso" placeholder=" " required>
                                <label for="agregarFechaIngreso" class="text-muted">Fecha de Ingreso</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="agregarImagen" class="form-label fw-semibold">Imagen de la Moto</label>
                                <input type="file" class="form-control shadow-sm" id="agregarImagen" name="imagen" accept="image/*">
                                <div class="form-text">Formatos aceptados: JPG, PNG, WEBP (Max. 2MB)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill text-white">Guardar Moto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Moto -->
<div class="modal fade" id="modalEditarMoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient bg-danger text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Motocicleta</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" id="edit_moto_id" name="moto_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select shadow-sm" id="edit_marca" name="marca" required>
                                    <option value="" selected disabled>Seleccione una marca</option>
                                    <?php foreach($marcas as $marca): ?>
                                        <option value="<?= htmlspecialchars($marca) ?>"><?= htmlspecialchars($marca) ?></option>
                                    <?php endforeach; ?>
                                    <option value="otro">Otra marca (especificar)</option>
                                </select>
                                <label for="edit_marca" class="text-muted">Marca</label>
                            </div>
                            <div id="editOtraMarcaContainer" class="mt-2 d-none">
                                <div class="form-floating">
                                    <input type="text" class="form-control shadow-sm" id="editOtraMarcaInput" name="otra_marca" placeholder=" ">
                                    <label for="editOtraMarcaInput" class="text-muted">Especifique la nueva marca</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control shadow-sm" id="edit_modelo" name="modelo" placeholder=" " required>
                                <label for="edit_modelo" class="text-muted">Modelo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control shadow-sm" id="edit_cilindrada" name="cilindrada" min="50" max="5000" placeholder=" " required>
                                <label for="edit_cilindrada" class="text-muted">Cilindrada (cc)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select shadow-sm" id="edit_color" name="color" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Rojo">Rojo</option>
                                    <option value="Azul">Azul</option>
                                    <option value="Negro">Negro</option>
                                    <option value="Blanco">Blanco</option>
                                    <option value="Verde">Verde</option>
                                    <option value="Amarillo">Amarillo</option>
                                    <option value="Gris">Gris</option>
                                    <option value="Naranja">Naranja</option>
                                    <option value="Morado">Morado</option>
                                    <option value="Rosado">Rosado</option>
                                    <option value="Negro Mate">Negro Mate</option>
                                    <option value="Turquesa">Turquesa</option>
                                    <option value="Blanco combinado">Blanco combinado</option>
                                </select>
                                <label for="edit_color" class="text-muted">Color</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" step="0.01" class="form-control shadow-sm" id="edit_precio" name="precio" placeholder=" " required>
                                <label for="edit_precio" class="text-muted">Precio (USD)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control shadow-sm" id="edit_cantidad" name="cantidad" min="1" placeholder=" " required>
                                <label for="edit_cantidad" class="text-muted">Cantidad en Stock</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control shadow-sm" id="edit_fecha_ingreso" name="fecha_ingreso" placeholder=" " required>
                                <label for="edit_fecha_ingreso" class="text-muted">Fecha de Ingreso</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="edit_imagen" class="form-label fw-semibold">Imagen de la Moto</label>
                                <input type="file" class="form-control shadow-sm" id="edit_imagen" name="imagen" accept="image/*">
                                <div class="form-text">Dejar vacío para mantener la imagen actual</div>
                                <div id="edit_imagen_preview" class="mt-3 text-center">
                                    <img src="" class="img-thumbnail d-none" id="previewImage" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill text-white">Actualizar Moto</button>
                </div>
            </form>
        </div>
    </div>
</div>
        </main>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
$(document).ready(function() {
        // Validación de cilindrada
        $('#cc').on('change', function() {
            const value = parseInt($(this).val());
            if (value < 50 || value > 5000) {
                alert('La cilindrada debe estar entre 50 y 5000 cc');
                $(this).val('');
            }
        });
        
        // Manejo de imágenes con error
        $('.product-img').on('error', function() {
            $(this).attr('src', 'https://via.placeholder.com/500x300?text=Imagen+no+disponible');
        });
    });
    // Para el modal de agregar
    const marcaSelect = document.getElementById('agregarMarca');
    const otraMarcaContainer = document.getElementById('otraMarcaContainer');
    const otraMarcaInput = document.getElementById('otraMarcaInput');
    
    marcaSelect.addEventListener('change', function() {
        if(this.value === 'otro') {
            otraMarcaContainer.classList.remove('d-none');
            otraMarcaInput.disabled = false;
            otraMarcaInput.required = true;
        } else {
            otraMarcaContainer.classList.add('d-none');
            otraMarcaInput.disabled = true;
            otraMarcaInput.required = false;
        }
    });
    
    // Para el modal de editar
    const editMarcaSelect = document.getElementById('edit_marca');
    const editOtraMarcaContainer = document.getElementById('editOtraMarcaContainer');
    const editOtraMarcaInput = document.getElementById('editOtraMarcaInput');

    if (editMarcaSelect) {
        editMarcaSelect.addEventListener('change', function() {
            if (this.value === 'otro') {
                editOtraMarcaContainer.classList.remove('d-none');
                editOtraMarcaInput.disabled = false;
                editOtraMarcaInput.required = true;
            } else {
                editOtraMarcaContainer.classList.add('d-none');
                editOtraMarcaInput.disabled = true;
                editOtraMarcaInput.required = false;
            }
        });
    }

    // Cuando se abre el modal de editar, selecciona la marca y muestra el campo si es necesario
    function editarMoto(motoId) {
        $.ajax({
            url: window.location.pathname,
            type: 'POST',
            data: { action: 'obtener', moto_id: motoId },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    const m = response.data;
                    $('#edit_moto_id').val(m.moto_id);
                    $('#edit_modelo').val(m.modelo);
                    $('#edit_cilindrada').val(m.cilindrada);
                    $('#edit_color').val(m.color);
                    $('#edit_precio').val(m.precio);
                    $('#edit_cantidad').val(m.cantidad);
                    $('#edit_fecha_ingreso').val(m.fecha_ingreso);

                    // Si la marca no está en el select, selecciona "otro", muestra el input y coloca el valor
                    if ($('#edit_marca option[value="' + m.marca + '"]').length === 0) {
                        $('#edit_marca').val('otro').trigger('change');
                        $('#editOtraMarcaInput').val(m.marca);
                    } else {
                        $('#edit_marca').val(m.marca).trigger('change');
                        $('#editOtraMarcaInput').val('');
                    }

                    // Imagen preview
                    if (m.imagen) {
                        $('#edit_imagen_preview').html('<img src="data:image/jpeg;base64,' + m.imagen + '" class="img-fluid rounded" style="max-height:120px;">');
                    } else {
                        $('#edit_imagen_preview').html('');
                    }

                    var modal = new bootstrap.Modal(document.getElementById('modalEditarMoto'));
                    modal.show();
                } else {
                    alert(response && response.message ? response.message : 'No se pudo cargar la información de la moto.');
                }
            },
            error: function(xhr) {
                let msg = 'Error al obtener los datos de la moto.';
                if (xhr.responseText && xhr.responseText.startsWith('<!DOCTYPE')) {
                    msg += ' El servidor devolvió HTML en vez de JSON. Verifica que no haya redirecciones o errores PHP.';
                }
                alert(msg);
            }
        });
    }

    // Al enviar el formulario, si la marca seleccionada es "otro", usa el valor del input como marca real
    $(document).on('submit', 'form', function(e) {
        // Solo aplica para los formularios de agregar/editar moto
        const $form = $(this);
        const marcaSelect = $form.find('select[name="marca"]');
        const otraMarcaInput = $form.find('input[name="otra_marca"]');
        if (marcaSelect.length && otraMarcaInput.length && marcaSelect.val() === 'otro') {
            // Sobrescribe el valor del select con el valor del input antes de enviar
            marcaSelect.append($('<option>', {
                value: otraMarcaInput.val(),
                text: otraMarcaInput.val(),
                selected: true
            }));
        }
    });
    </script>
</body>
</html>