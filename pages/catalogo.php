<?php
include '../config/conexion.php';

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
    
    return $colorMap[$colorName] ?? '#6c757d'; 
}

try {
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }
    $brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';

    $query = "
    SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
           M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
    FROM MOTOCICLETA M
    INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
";
    
    if ($brandFilter) {
        $query .= " WHERE MM.marca = :marca";
    }

    $stmt = $conn->prepare($query);

    if ($brandFilter) {
        $stmt->bindParam(':marca', $brandFilter, PDO::PARAM_STR);
    }

    $stmt->execute();
    $motocicletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCAutomotors - Catálogo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Racing+Sans+One&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../public/css/catalogos.css">
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
                    <?php
                    session_start();
                    $usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                    ?>

                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-speedometer2 me-1"></i>Administración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-people me-1"></i>Empleado
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="catalogo.php">
                            <i class="bi bi-bicycle me-1"></i>Catálogo
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="direccion.php">
                            <i class="bi bi-geo-alt me-1"></i>Ubicación
                        </a>
                    </li> -->
                    <?php if ($usuario_logueado): ?>
                <li class="nav-item me-3">
                    <span class="navbar-text text-light">
                        <i class="bi bi-person-circle me-1"></i>
                        Bienvenido, <?php echo htmlspecialchars($usuario_logueado['usuario']); ?>
                    </span>
                </li>
            <?php endif; ?>
            <?php if ($usuario_logueado): ?>
                                    <li class="nav-item">
                                        <a class="nav-link btn" href="../public/logout.php">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Explora Nuestro Catálogo de Motos</h1>
            <p class="hero-text">
    Encuentra la moto perfecta para tus aventuras. Con calidad, potencia y estilo, cada modelo está diseñado para ofrecerte una experiencia única, ya sea en la carretera, senderos o la ciudad.
</p>
        </div>
    </section>
    <br>
    <section class="features-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="included-features">
                    <h3 class="text-center mb-4">Todas nuestras motocicletas incluyen</h3>
                    <div class="row g-4">
                        <div class="col-md-3 col-6">
                            <div class="feature-item">
                                <i class="bi bi-file-earmark-check feature-icon"></i>
                                <h5>RUAT</h5>
                                <p>Documentos oficiales al día</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="feature-item">
                                <i class="bi bi-shield-check feature-icon"></i>
                                <h5>1 año</h5>
                                <p>De garatía del motorizado</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="feature-item">
                                <i class="bi bi-box-seam feature-icon"></i>
                                <h5>Importación</h5>
                                <p>Pólizas de importación completa</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="feature-item">
                                <i class="bi bi-123 feature-icon"></i>
                                <h5>Placa</h5>
                                <p>Placa oficial incluida</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item special-feature">
                                <i class="bi bi-gift feature-icon"></i>
                                <h5>Casco original certificado</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item special-feature">
                                <i class="bi bi-gift feature-icon"></i>
                                <h5>Primer mantenimiento gratis</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    <main class="custom-container mt-3 pt-3">
    <form method="GET" class="mb-4">
        <div class="d-flex justify-content-start align-items-center flex-wrap">
            <label for="brandFilter" class="me-2" style="color:white">Filtrar por marca:</label>
            <div class="brand-buttons">
                <button type="submit" name="brand" value="" class="btn btn-secondary ms-2">Todas</button>
                <?php
                    $brandStmt = $conn->prepare("SELECT DISTINCT marca FROM MODELO_MOTO");
                    $brandStmt->execute();
                    $brands = $brandStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($brands as $brand) {
                        echo "<button type='submit' name='brand' value='" . htmlspecialchars($brand['marca']) . "' class='btn btn-primary ms-2'>" . htmlspecialchars($brand['marca']) . "</button>";
                    }
                ?>
            </div>
        </div>
    </form>
</main>
<main class="custom-container mt-4 pt-4">
    <?php if ($motocicletas): ?>
        <div class="row motorcycle-grid g-5">
            <?php foreach ($motocicletas as $moto): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card motorcycle-card border-0 shadow-sm">
                    <?php 
                        $defaultImage = 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80&text=' . urlencode($moto['modelo']);
                        ?>
                        
                        <div class="motorcycle-image-container">
                            <?php if (!empty($moto['imagen'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($moto['imagen']); ?>" 
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($moto['modelo']); ?>"
                                     onerror="this.src='<?php echo $defaultImage; ?>'">
                            <?php else: ?>
                                <img src="<?php echo $defaultImage; ?>" 
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($moto['modelo']); ?>">
                            <?php endif; ?>
                            <div class="image-overlay">
                                <button class="btn btn-outline-light btn-sm rounded-pill px-3" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#motorcycleModal"
                                        data-marca="<?php echo htmlspecialchars($moto['marca']); ?>"
                                        data-modelo="<?php echo htmlspecialchars($moto['modelo']); ?>"
                                        data-cilindrada="<?php echo htmlspecialchars($moto['cilindrada']); ?>"
                                        data-color="<?php echo htmlspecialchars($moto['color']); ?>"
                                        data-precio="<?php echo number_format($moto['precio']); ?>"
                                        data-estado="<?php echo htmlspecialchars($moto['estado']); ?>"
                                        <?php if (!empty($moto['imagen'])): ?>
                                            data-imagen="<?php echo base64_encode($moto['imagen']); ?>"
                                        <?php endif; ?>>
                                    <i class="bi bi-zoom-in me-1"></i> Vista rápida
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success bg-opacity-10 text-success">Nuevo</span>
                                <span class="text-muted small"><i class="bi bi-speedometer2 me-1"></i> <?php echo htmlspecialchars($moto['cilindrada']); ?> cc</span>
                            </div>
                            
                            <!-- Modelo -->
                            <h3 class="h5 card-title mb-2" style="min-height: 48px; font-weight: bold;">
                                <?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?>
                            </h3>
                            
                            <!-- Color -->
                            <div class="d-flex align-items-center mb-3">
                            <div class="color-swatch me-2" 
                                style="background-color: <?php echo htmlspecialchars(getColorCode($moto['color'])); ?>"
                                title="<?php echo htmlspecialchars($moto['color']); ?>">
                            </div>
                                <small class="text-muted">Color: <?php echo htmlspecialchars($moto['color']); ?></small>
                            </div>
                            
                            <!-- Precios -->
                            <div class="price-container bg-light p-3 rounded mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block">Precio USD</small>
                                        <span class="h5 text-danger fw-bold">$<?php echo number_format($moto['precio']); ?></span>
                                    </div>
                                    <div class="vr mx-2"></div>
                                    <div>
                                        <small class="text-muted d-block">Precio Bs</small>
                                        <span class="h5 text-danger fw-bold">Bs. <?php echo number_format($moto['precio'] * 7); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger rounded-pill" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#motorcycleModal"
                                        data-imagen="<?php echo base64_encode($moto['imagen']); ?>"
                                        data-marca="<?php echo htmlspecialchars($moto['marca']); ?>"
                                        data-modelo="<?php echo htmlspecialchars($moto['modelo']); ?>"
                                        data-cilindrada="<?php echo htmlspecialchars($moto['cilindrada']); ?>"
                                        data-color="<?php echo htmlspecialchars($moto['color']); ?>"
                                        data-precio="<?php echo number_format($moto['precio']); ?>"
                                        data-estado="<?php echo htmlspecialchars($moto['estado']); ?>">
                                    <i class="bi bi-eye-fill me-1"></i> Ver detalles
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-4">
            <div class="py-3">
                <i class="bi bi-exclamation-circle display-4 text-info mb-3"></i>
                <h3 class="h4">No hay motocicletas disponibles</h3>
                <p class="mb-0">Actualmente no tenemos motocicletas en nuestro catálogo. Por favor, vuelve a consultar más tarde.</p>
            </div>
        </div>
    <?php endif; ?>
</main>

<div class="modal fade" id="motorcycleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title fw-bold" id="modalTitle"></h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="position-relative">
                        <img id="modalImage" src="" alt="Imagen de la motocicleta" 
                            class="img-fluid rounded-3 shadow-sm w-100" 
                            style="object-fit: contain; background-color: #f8f9fa;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-warning text-dark ">
                                    <i class="bi bi-star-fill me-1"></i> Destacado
                                </span>
                            </div>
                        </div>
                        <div class="alert alert-info mt-4 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <small>Precios sujetos a cambio sin previo aviso. Incluye impuestos y gastos de matriculación.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3 gap-2 align-items-center">
              
                    </div>
                    </div>
                    <div class="col-md-6">
                        <div class="motorcycle-specs">
                            <div class="spec-item d-flex align-items-center py-2 border-bottom">
                                <i class="bi bi-building fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Marca:</span>
                                    <span class="spec-value text-muted" id="modalBrand"></span>
                                </div>
                            </div>
                            <div class="spec-item d-flex align-items-center py-2 border-bottom">
                                <i class="bi bi-tag fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Modelo:</span>
                                    <span class="spec-value text-muted" id="modalModel"></span>
                                </div>
                            </div>
                            <div class="spec-item d-flex align-items-center py-2 border-bottom">
                                <i class="bi bi-speedometer2 fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Cilindrada:</span>
                                    <span class="spec-value text-muted" id="modalCilindrada"></span>
                                </div>
                            </div>
                            <div class="spec-item d-flex align-items-center py-2 border-bottom">
                                <i class="bi bi-palette fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Color:</span>
                                    <span class="spec-value text-muted" id="modalColor"></span>
                                </div>
                            </div>
                            <div class="spec-item d-flex align-items-center py-2 border-bottom">
                                <i class="bi bi-currency-dollar fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Precio (USD):</span>
                                    <span class="spec-value text-muted fw-bold" id="modalPrice" style="color: #a51314;"></span>
                                </div>
                            </div>
                            <div class="spec-item d-flex align-items-center py-2">
                                <i class="bi bi-cash-coin fs-5 text-danger me-3"></i>
                                <div>
                                    <span class="spec-label fw-bold d-block">Precio (Bs.):</span>
                                    <span class="spec-value text-muted fw-bold" id="modalPrice2" style="color: #a51314;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4 justify-content-center">
                    <div class="col-md-6 mb-2">
                        <a id="whatsappButton" class="btn btn-success w-100 py-3 rounded-pill fw-bold text-center" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i> Consultar por WhatsApp
                        </a>
                    </div>
                    </div>

            </div>
        </div>
    </div>
</div>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="copyToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Enlace copiado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    El enlace ha sido copiado al portapapeles.
                </div>
            </div>
        </div>
<footer>
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="footer-brand">JCAutomotors</div>
                        <p>Tu concesionario de confianza con más de 15 años de experiencia en el mundo de las motocicletas.</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/JcAutomotorsLaPazBo" target="_blank"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.tiktok.com/@jc_automotors" target="_blank" ><i class="bi bi-tiktok"></i></a>
                            <a href="https://www.instagram.com/jc_automotors.lp/" target="_blank"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4 footer-links">
                        <h5>Enlaces rápidos</h5>
                        <ul>
                            <li><a href="/">Inicio</a></li>
                            <li><a href="/catalogo">Catálogo</a></li>
                            <li><a href="/nosotros">Sobre nosotros</a></li>
                            <li><a href="/contacto">Contacto</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4 footer-links">
                        <h5>Servicios</h5>
                        <ul>
                            <li><a href="/servicios/financiamiento">Financiamiento</a></li>
                            <li><a href="/servicios/mantenimiento">Mantenimiento</a></li>
                            <li><a href="/servicios/seguro">Seguros</a></li>
                            <li><a href="/servicios/accesorios">Accesorios</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4 footer-links">
                        <h5>Contacto</h5>
                        <ul>
                            <li><i class="bi bi-geo-alt me-2"></i> Av. Tejada Sorzano entre Calles Puerto Rico y Costa Rica #855. Edif. Dica, La Paz, Bolivia</li>
                            <li><i class="bi bi-telephone me-2"></i> (591) 62466711</li>
                            <li><i class="bi bi-envelope me-2"></i> jcautomotors2@gmail.com</li>
                            <li><i class="bi bi-clock me-2"></i> Lun-Vie: 9:30 - 19:00</li>
                            <li><i class="bi bi-clock me-2"></i> Sábado: 10:00 - 14:00</li>
                        </ul>
                    </div>
                </div>
                <div class="copyright">
                    <p>&copy; 2025 JCAutomotors. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>

    <script src="../public/js/catalogo.js"></script>
</body>
</html>
