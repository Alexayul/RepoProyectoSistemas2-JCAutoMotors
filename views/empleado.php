<?php
include '../config/conexion.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];

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
      // Añade más colores según necesites
    ];
    
    return $colorMap[$colorName] ?? '#6c757d'; // Color por defecto (gris)
}
try {
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }

    // Filtros para motocicletas
    $brandFilter = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $modelFilter = isset($_POST['model']) ? trim($_POST['model']) : '';
    $ccFilter = isset($_POST['cc']) ? (int)$_POST['cc'] : '';

    $queryMotos = "
    SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
           M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
    FROM MOTOCICLETA M
    INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
    WHERE 1=1
    ";

    if (!empty($brandFilter)) {
        $queryMotos .= " AND MM.marca LIKE :marca";
    }
    if (!empty($modelFilter)) {
        $queryMotos .= " AND MM.modelo LIKE :modelo";
    }
    if (!empty($ccFilter) && $ccFilter > 0) {
        $queryMotos .= " AND MM.cilindrada = :cilindrada";
    }

    $stmtMotos = $conn->prepare($queryMotos);
    
    if (!empty($brandFilter)) {
        $brandParam = "%$brandFilter%";
        $stmtMotos->bindParam(':marca', $brandParam, PDO::PARAM_STR);
    }
    if (!empty($modelFilter)) {
        $modelParam = "%$modelFilter%";
        $stmtMotos->bindParam(':modelo', $modelParam, PDO::PARAM_STR);
    }
    if (!empty($ccFilter) && $ccFilter > 0) {
        $stmtMotos->bindParam(':cilindrada', $ccFilter, PDO::PARAM_INT);
    }

    $stmtMotos->execute();
    $motocicletas = $stmtMotos->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error al cargar los datos: " . $e->getMessage());
    die("<div class='alert alert-danger'>Error al cargar los datos. Por favor, intente nuevamente.</div>");
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
    <div class="container-fluid p-0">
         <nav id="sidebar" class="sidebar">
            <div class="d-flex flex-column h-100">
                <div class="text-center mb-4">
                    <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active">
                            <i class="bi bi-bicycle me-2"></i>Catálogo de motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white"href="accesorios.php">
                            <i class="bi bi-tools me-2"></i>Catálogo de accesorios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="ventasE.php">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-bicycle text-primary me-2"></i> Catálogo de Motos
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
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
                                    <h3 class="mb-0 text-primary">
                                        <?php 
                                            $totalMotos = array_sum(array_column($motocicletas, 'cantidad'));
                                            echo $totalMotos;
                                        ?>
                                    </h3>
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
                                    <h3 class="mb-0 text-primary">
                                        <?php 
                                            $modelosUnicos = array_unique(array_column($motocicletas, 'modelo'));
                                            echo count($modelosUnicos);
                                        ?>
                                    </h3>
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
                                    <h3 class="mb-0 text-primary">
                                        <?php 
                                            $marcasUnicas = array_unique(array_column($motocicletas, 'marca'));
                                            echo count($marcasUnicas);
                                        ?>
                                    </h3>
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
                                            <?= ($moto['cantidad'] > 0) ? "Disponible: ".$moto['cantidad'] : "AGOTADO" ?>
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
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-sm btn-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalles<?= $moto['moto_id'] ?>">
                                            <i class="bi bi-eye me-1"></i> Ver detalles completos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle me-1"></i> Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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
    <!-- Bootstrap Bundle with Popper -->
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
    </script>
</body>
</html>