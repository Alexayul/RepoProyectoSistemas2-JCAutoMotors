<?php
// Incluir la conexión a la base de datos
include '../config/conexion.php';

// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    // Si no está logueado, redirigir a la página de login
    header('Location: login.php');
    exit;
}

$usuario_logueado = $_SESSION['user'];

try {
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }

    // Filtros para motos
    $brandFilter = isset($_POST['brand']) ? $_POST['brand'] : '';
    $modelFilter = isset($_POST['model']) ? $_POST['model'] : '';
    $ccFilter = isset($_POST['cc']) ? $_POST['cc'] : '';

    // Consulta para motos con filtros
    $queryMotos = "
    SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
           M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
    FROM MOTOCICLETA M
    INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
    WHERE 1=1
    ";

    if ($brandFilter) {
        $queryMotos .= " AND MM.marca = :marca";
    }
    if ($modelFilter) {
        $queryMotos .= " AND MM.modelo = :modelo";
    }
    if ($ccFilter) {
        $queryMotos .= " AND MM.cilindrada = :cilindrada";
    }

    $stmtMotos = $conn->prepare($queryMotos);
    if ($brandFilter) {
        $stmtMotos->bindParam(':marca', $brandFilter, PDO::PARAM_STR);
    }
    if ($modelFilter) {
        $stmtMotos->bindParam(':modelo', $modelFilter, PDO::PARAM_STR);
    }
    if ($ccFilter) {
        $stmtMotos->bindParam(':cilindrada', $ccFilter, PDO::PARAM_INT);
    }

    $stmtMotos->execute();
    $motocicletas = $stmtMotos->fetchAll(PDO::FETCH_ASSOC);

    // Filtros para accesorios
    $nombreFilter = isset($_POST['nombre_accesorio']) ? $_POST['nombre_accesorio'] : '';
    $categoriaFilter = isset($_POST['categoria']) ? $_POST['categoria'] : '';

    // Consulta para accesorios con filtros
    $queryAccesorios = "SELECT * FROM ACCESORIO WHERE 1=1";
    
    if ($nombreFilter) {
        $queryAccesorios .= " AND nombre LIKE :nombre";
    }

    $stmtAccesorios = $conn->prepare($queryAccesorios);
    
    if ($nombreFilter) {
        $nombreParam = "%$nombreFilter%";
        $stmtAccesorios->bindParam(':nombre', $nombreParam, PDO::PARAM_STR);
    }
    $stmtAccesorios->execute();
    $accesorios = $stmtAccesorios->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al cargar los datos: " . $e->getMessage());
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
    <link rel="stylesheet" href="../public/CatalogoMotosE.css">

</head>
<body>
    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="d-flex flex-column h-100">
                <div class="text-center mb-4">
                    <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 150px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active">
                            <i class="bi bi-bicycle me-2"></i>Catálogo de motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="accesorios.php">
                            <i class="bi bi-tools me-2"></i>Catálogo de accesorios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="ventas.php">
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
            </div>

            <!-- Filtros -->
            <div class="filter-section mb-4">
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
            <div class="row">
                <?php if ($motocicletas): ?>
                    <?php foreach ($motocicletas as $moto): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card dashboard-card h-100">
                                <div class="position-relative">
                                    <img src="../<?php echo htmlspecialchars($moto['imagen']); ?>" 
                                         class="card-img-top product-img" 
                                         alt="<?php echo htmlspecialchars($moto['marca']); ?> <?php echo htmlspecialchars($moto['modelo']); ?>">
                                    <span class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-<?php echo ($moto['cantidad'] > 0) ? 'success' : 'danger'; ?> inventory-badge">
                                            <?php echo ($moto['cantidad'] > 0) ? "Stock: ".$moto['cantidad'] : "Agotado"; ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($moto['marca']); ?> <?php echo htmlspecialchars($moto['modelo']); ?>
                                    </h5>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-speedometer2 text-primary me-2"></i>
                                        <small class="text-muted"><?php echo htmlspecialchars($moto['cilindrada']); ?> cc</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-palette text-primary me-2"></i>
                                        <small class="text-muted"><?php echo htmlspecialchars($moto['color']); ?></small>
                                    </div>
                                    <h4 class="text-primary mb-0">Bs. <?php echo number_format($moto['precio'], 2); ?></h4>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalles<?php echo $moto['moto_id']; ?>">
                                            <i class="bi bi-eye me-1"></i> Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Detalles Moto -->
                        <div class="modal fade" id="modalDetalles<?php echo $moto['moto_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header text-white" style="background-color:#a51314">
                                        <h5 class="modal-title">
                                            <i class="bi bi-bicycle me-2"></i>
                                            <?php echo htmlspecialchars($moto['marca']); ?> <?php echo htmlspecialchars($moto['modelo']); ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-4 mb-md-0">
                                                <div class="bg-light p-3 rounded text-center">
                                                    <img src="../<?php echo htmlspecialchars($moto['imagen']); ?>" 
                                                         class="img-fluid rounded" 
                                                         alt="<?php echo htmlspecialchars($moto['marca']); ?> <?php echo htmlspecialchars($moto['modelo']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h3 class="text-primary mb-0">Bs. <?php echo number_format($moto['precio'], 2); ?></h3>
                                                    <span class="badge bg-<?php echo ($moto['estado'] == 'Nuevo') ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($moto['estado']); ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="list-group list-group-flush mb-4">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="bi bi-speedometer2 text-primary me-2"></i> Cilindrada</span>
                                                        <strong><?php echo htmlspecialchars($moto['cilindrada']); ?> cc</strong>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="bi bi-palette text-primary me-2"></i> Color</span>
                                                        <strong><?php echo htmlspecialchars($moto['color']); ?></strong>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="bi bi-box-seam text-primary me-2"></i> Stock</span>
                                                        <strong><?php echo htmlspecialchars($moto['cantidad']); ?> unidades</strong>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="bi bi-calendar-check text-primary me-2"></i> Fecha ingreso</span>
                                                        <strong><?php echo htmlspecialchars($moto['fecha_ingreso']); ?></strong>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-<?php echo ($moto['cantidad'] > 0) ? 'success' : 'danger'; ?>">
                                                    <i class="bi bi-<?php echo ($moto['cantidad'] > 0) ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                                    <?php echo ($moto['cantidad'] > 0) ? 
                                                        "Disponible para venta inmediata" : 
                                                        "Producto agotado, consultar por próximos ingresos"; ?>
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
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i> No se encontraron motocicletas con los filtros seleccionados.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
        });
    </script>
</body>
</html>