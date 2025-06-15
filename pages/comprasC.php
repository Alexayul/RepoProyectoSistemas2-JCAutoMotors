<?php
require_once '../config/conexion.php'; // Asegúrate que la ruta es correcta
require_once '../controllers/ComprasController.php';

// La conexión ya está creada en conexion.php ($conn)
$controller = new ComprasController($conn); // Pasamos la conexión existente
$data = $controller->mostrarCompras();
$compras = $data['compras'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras - JCAutomotors</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/compras.css">
</head>
<body>
    
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Logo -->
            <div class="logo-container">
                <img src="../public/logo.png" alt="JCAutomotors Logo" class="logo-img">
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                    <i class="bi bi-list text-light fs-2"></i>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="catalogo.php">
                            <i class="bi bi-bicycle me-1"></i>Catálogo
                        </a>
                    </li>
                    <a class="nav-link" href="javascript:history.back()" class="btn btn-volver">
                    <i class="bi bi-arrow-left text-white"></i> Volver
                </a>
                </ul>
            </div>
        </div>
    </nav>
</header>

<section class="hero">
    <div class="container">
        <h1 class="hero-title">Mi Historial de Compras</h1>
        <p class="hero-text">
            Consulta todas tus compras realizadas, desde motocicletas hasta accesorios. Aquí encontrarás los detalles de cada adquisición que has hecho en nuestro concesionario.
        </p>
    </div>
</section>

<section class="features-section container">
    <?php if (count($compras) > 0): ?>
        <?php foreach ($compras as $compra): ?>
            <div class="compra-card">
                <div class="compra-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="compra-date">
                                <i class="bi bi-calendar-check"></i>
                                <?php echo date('d/m/Y', strtotime($compra['fecha_venta'])); ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge-total">
                                Bs. <?php echo number_format($compra['monto_total']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Motocicletas compradas -->
                    <?php if (count($compra['motos']) > 0): ?>
                        <div class="productos-section">
                            <h6 class="productos-title">
                                <i class="bi bi-bicycle"></i>
                                Motocicletas (<?php echo count($compra['motos']); ?>)
                            </h6>
                            <div class="row">
                                <?php foreach ($compra['motos'] as $moto): ?>
                                    <div class="col-lg-6 mb-3">
                                        <div class="producto-item">
                                            <div class="producto-nombre">
                                                <?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?>
                                            </div>
                                            <div class="producto-specs">
                                                <span class="spec-badge cilindrada">
                                                    <i class="bi bi-speedometer2"></i>
                                                    <?php echo htmlspecialchars($moto['cilindrada']); ?>cc
                                                </span>
                                                <span class="spec-badge color">
                                                    <i class="bi bi-palette"></i>
                                                    <?php echo htmlspecialchars($moto['color']); ?>
                                                </span>
                                            </div>
                                            <div class="producto-precio">
                                                <div class="precio-valor">
                                                    Bs. <?php echo number_format($moto['subtotal']); ?>
                                                </div>
                                                <div class="precio-cantidad">
                                                    <i class="bi bi-box"></i>
                                                    <?php echo htmlspecialchars($moto['cantidad']); ?> unidad(es)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Accesorios comprados -->
                    <?php if (count($compra['accesorios']) > 0): ?>
                        <div class="productos-section">
                            <h6 class="productos-title">
                                <i class="bi bi-tools"></i>
                                Accesorios (<?php echo count($compra['accesorios']); ?>)
                            </h6>
                            <div class="row">
                                <?php foreach ($compra['accesorios'] as $accesorio): ?>
                                    <div class="col-lg-6 mb-3">
                                        <div class="producto-item">
                                            <div class="producto-nombre">
                                                <?php echo htmlspecialchars($accesorio['nombre']); ?>
                                            </div>
                                            <div class="producto-specs">
                                                <small class="text-white">
                                                    <i class="bi bi-info-circle"></i>
                                                    <?php echo htmlspecialchars($accesorio['descripcion']); ?>
                                                </small>
                                            </div>
                                            <div class="producto-precio">
                                                <div class="precio-valor">
                                                    Bs. <?php echo number_format($accesorio['subtotal'], 2); ?>
                                                </div>
                                                <div class="precio-cantidad">
                                                    <i class="bi bi-box"></i>
                                                    <?php echo htmlspecialchars($accesorio['cantidad']); ?> unidad(es)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state text-center py-5" style="width: 600px; margin: 0 auto;">
    <i class="bi bi-cart-x" style="font-size: 4rem; color: var(--primary);"></i>
    <h3 class="mt-3 mb-4" style="color: var(--ligth); font-weight: 400;">¡Aún no has realizado ninguna compra!</h3>
    
    <div class="d-flex justify-content-center gap-3">
        <a href="catalogo.php" class="btn btn-lg px-4" 
           style="background-color: var(--primary); color: white; border-radius: 8px; transition: var(--standard-transition);">
            <i class="bi bi-bicycle me-2 text-white"></i> Ver catálogo
        </a>
        <a href="javascript:history.back()" class="btn btn-lg px-4" 
           style="background-color: white; color: var(--dark); border: 1px solid var(--medium-gray); border-radius: 8px; transition: var(--standard-transition);">
            <i class="bi bi-arrow-left me-2"></i> Volver
        </a>
    </div>
</div>
    <?php endif; ?>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>