<?php
include '../config/conexion.php';

// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];

try {
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }
    
    // Inicializar filtros
    $nombreFilter = isset($_POST['nombre_accesorio']) ? $_POST['nombre_accesorio'] : '';
    $categoriaFilter = isset($_POST['categoria']) ? $_POST['categoria'] : '';

    // Consulta base para accesorios
    $queryAccesorios = "SELECT * FROM ACCESORIO WHERE 1=1";
    
    // Aplicar filtros
    if ($nombreFilter) {
        $queryAccesorios .= " AND nombre LIKE :nombre";
    }

    $stmtAccesorios = $conn->prepare($queryAccesorios);
    
    if ($nombreFilter) {
        $nombreParam = "%$nombreFilter%";
        $stmtAccesorios->bindParam(':nombre', $nombreParam, PDO::PARAM_STR);
    }
    if ($categoriaFilter) {
        $stmtAccesorios->bindParam(':categoria', $categoriaFilter, PDO::PARAM_STR);
    }
    
    $stmtAccesorios->execute();
    $accesorios = $stmtAccesorios->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}
?><?php

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Accesorios - JC Automotors</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="../public/css/CatalogoAccesoriosE.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
   
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
                        <a class="nav-link" href="empleado.php">
                            <i class="bi bi-bicycle me-2"></i>Catálogo de motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active">
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
                    <i class="bi bi-tools text-primary me-2"></i> Catálogo de Accesorios
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
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">
                                        <i class="bi bi-box-seam me-1"></i> Accesorios en Stock
                                    </h6>
                                    <h3 class="mb-0 text-primary">
                                        <?php 
                                            $totalAccesorios = array_sum(array_column($accesorios, 'cantidad'));
                                            echo $totalAccesorios;
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-tools fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-section mb-4">
                <h5 class="mb-3">
                    <i class="bi bi-funnel-fill text-primary me-2"></i>Filtrar Accesorios
                </h5>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre_accesorio" class="form-label">
                            <i class="bi bi-search text-primary me-1"></i>Nombre
                        </label>
                        <input type="text" class="form-control" id="nombre_accesorio" name="nombre_accesorio" 
                               value="<?php echo htmlspecialchars($nombreFilter); ?>" placeholder="Ej. Casco Integral">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                        </button>
                        <a href="accesorios.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de accesorios -->
            <div class="table-responsive">
                <table class="table table-hover" id="accesoriosTable">
                    <thead>
                        <tr>
                            <th><i class="bi bi-image me-1"></i> Imagen</th>
                            <th><i class="bi bi-tag me-1"></i> Nombre</th>
                            <th><i class="bi bi-card-text me-1"></i> Descripción</th>
                            <th><i class="bi bi-currency-dollar me-1"></i> Precio</th>
                            <th><i class="bi bi-box-seam me-1"></i> Stock</th>
                            <th><i class="bi bi-info-circle me-1"></i> Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($accesorios): ?>
                            <?php foreach ($accesorios as $accesorio): ?>
                                <tr data-bs-toggle="modal" data-bs-target="#modalAccesorio<?php echo $accesorio['_id']; ?>" style="cursor: pointer;">
                                    <td>
                                        <?php if (!empty($accesorio['imagen'])): ?>
                                            <img src="../<?php echo htmlspecialchars($accesorio['imagen']); ?>" 
                                                 class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="bi bi-image text-muted fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($accesorio['nombre']); ?></td>
                                    <td><?php echo strlen($accesorio['descripcion']) > 50 ? substr(htmlspecialchars($accesorio['descripcion']), 0, 50).'...' : htmlspecialchars($accesorio['descripcion']); ?></td>
                                    <td class="fw-bold text-primary">Bs. <?php echo number_format($accesorio['precio'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($accesorio['cantidad'] > 0) ? 'success' : 'danger'; ?>">
                                            <?php echo htmlspecialchars($accesorio['cantidad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($accesorio['estado'] == 'Disponible') ? 'success' : 'warning'; ?>">
                                            <?php echo htmlspecialchars($accesorio['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                                
                                <!-- Modal Detalles Accesorio -->
                                <div class="modal fade" id="modalAccesorio<?php echo $accesorio['_id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-tools me-2"></i>
                                                    <?php echo htmlspecialchars($accesorio['nombre']); ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-4 mb-4 mb-md-0">
                                                        <div class="bg-light p-3 rounded text-center">
                                                            <?php if (!empty($accesorio['imagen'])): ?>
                                                                <img src="../<?php echo htmlspecialchars($accesorio['imagen']); ?>" 
                                                                     class="img-fluid rounded">
                                                            <?php else: ?>
                                                                <div class="p-4">
                                                                    <i class="bi bi-image text-muted fs-1"></i>
                                                                    <p class="text-muted mt-2">Sin imagen disponible</p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h3 class="text-primary mb-0">Bs. <?php echo number_format($accesorio['precio'], 2); ?></h3>
                                                            <span class="badge bg-<?php echo ($accesorio['estado'] == 'Disponible') ? 'success' : 'warning'; ?>">
                                                                <?php echo htmlspecialchars($accesorio['estado']); ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <div class="list-group list-group-flush mb-4">
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span><i class="bi bi-box-seam text-primary me-2"></i> Stock</span>
                                                                <strong><?php echo htmlspecialchars($accesorio['cantidad']); ?> unidades</strong>
                                                            </div>
                                                            
                                                        </div>
                                                        
                                                        <div>
                                                            <h6 class="text-primary"><i class="bi bi-card-text me-2"></i>Descripción:</h6>
                                                            <p><?php echo htmlspecialchars($accesorio['descripcion']); ?></p>
                                                        </div>
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
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i> No se encontraron accesorios con los filtros seleccionados.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>