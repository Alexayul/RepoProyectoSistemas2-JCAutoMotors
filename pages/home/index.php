<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCAutomotors - Inicio</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Racing+Sans+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/index.css">
    <link rel="stylesheet" href="public/css/transiciones.css">  
</head>
<body>
   
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="logo-container">
                <img src="public/logo.png" alt="JCAutomotors Logo" class="logo-img">
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                    <i class="bi bi-list text-light fs-2"></i>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php
                    $usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                    ?>

                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="bi bi-speedometer2 me-1"></i>Administración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="bi bi-people me-1"></i>Empleado
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/catalogo.php">
                            <i class="bi bi-bicycle me-1"></i>Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#ubicacion">
                            <i class="bi bi-geo-alt me-1"></i>Ubicación
                        </a>
                    </li>
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
                            <a class="nav-link btn" href="public/logout.php">
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
            <h1 class="hero-title">Pasión por las Dos Ruedas</h1>
            <p class="hero-text">Descubre nuestra amplia colección de motocicletas de alta calidad. Desde motos deportivas hasta cruisers clásicas, tenemos lo que buscas con las mejores condiciones del mercado.</p>
            <a href="pages/catalogo.php" class="btn btn-primary">Ver catálogo <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
    </section>

    <!-- Resto del código de secciones anteriores se mantiene igual -->

    <section class="best-sellers py-5">
        <div class="container">
            <h2 class="section-title mb-4">Motos más vendidas</h2>
            <div class="row g-4">
                <?php if (!empty($motosMasVendidas)): ?>
                    <?php foreach ($motosMasVendidas as $moto): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <?php
                                $defaultImage = 'https://via.placeholder.com/500x300?text='.urlencode($moto['marca'].'+'.$moto['modelo']);
                                $imagenSrc = !empty($moto['imagen']) ? 
                                    'data:image/jpeg;base64,'.base64_encode($moto['imagen']) : 
                                    $defaultImage;
                                ?>
                                
                                <div class="motorcycle-image-container">
                                    <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                        class="card-img-top img-fluid" 
                                        alt="<?= htmlspecialchars($moto['marca'].' '.$moto['modelo']) ?>"
                                        style="height: 220px; object-fit: cover;"
                                        onerror="this.src='<?= htmlspecialchars($defaultImage) ?>'">
                                </div>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0 fw-bold"><?= htmlspecialchars($moto['marca'].' '.$moto['modelo']) ?></h5>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="color-swatch me-2" 
                                            style="background-color: <?= htmlspecialchars(getColorCode($moto['color'])) ?>; 
                                                    border: 1px solid #ddd;"
                                            title="<?= htmlspecialchars($moto['color']) ?>">
                                        </div>
                                        <small class="text-muted">Color: <?= htmlspecialchars($moto['color']) ?></small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <small class="text-muted">Precio USD</small>
                                            <p class="mb-0 fw-bold" style="color: #a51314">$<?= number_format($moto['precio']) ?></p>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Precio Bs</small>
                                            <h5 class="mb-0 fw-bold" style="color: #a51314">Bs. <?= number_format($moto['precio'] * 7) ?></h5>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-white border-0 pt-0">
                                    <a href="pages/catalogo.php" class="btn w-100 rounded-pill" style="background-color: #a51314; color: white;">
                                        <i class="bi bi-eye-fill me-2"></i> Ver Catálogo Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            No se encontraron motos disponibles en este momento.
                        </div>
                        <a href="pages/catalogo.php" class="btn btn-primary mt-3">
                            Ver Catálogo Completo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

 <section class="map-section py-5" style="background-color: #f7f7f7;">
            <div class="container">
                <div class="text-center mb-5">
                    
                    <h2 class="section-title fw-bold" style="color: var(--primary); ">Encuéntranos Fácilmente</h2>
                    <div class="divider mx-auto my-3" style="width: 80px; height: 3px; background-color: var(--primary);"></div>
                    <p class="lead">Visítanos en nuestra concesionaria de motos y descubre los mejores modelos con atención personalizada.</p>
                </div>
                
                <div class="row align-items-center gy-4">
                    <div class="col-lg-4">
                        <div class="contact-info p-4 bg-white rounded shadow-sm">
                            <h4 class="fw-bold mb-4 border-bottom pb-2" style="color: var(--primary);">
                                <i class="bi bi-shop"></i> JC AUTOMOTORS - La Paz
                            </h4>
                            
                            <div class="contact-details">
                                <div class="d-flex mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-geo-alt-fill fs-4" style="color: var(--primary);"></i>
                                    </div>
                                    <div>
                                        <strong>Dirección:</strong><br>
                                        Av. Tejada Sorzano entre Calles Puerto Rico y Costa Rica #855. Edif. Dica, La Paz, Bolivia
                                    </div>
                                </div>
                                
                                <div class="d-flex mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-telephone-fill fs-4" style="color: var(--primary);"></i>
                                    </div>
                                    <div>
                                        <strong>Teléfono:</strong><br>
                                        <a href="tel:+591 77530498" class="text-decoration-none" style="color: var(--secondary);">(591) 62466711</a>
                                    </div>
                                </div>
                                
                                <div class="d-flex mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-envelope-fill fs-4" style="color: var(--primary);"></i>
                                    </div>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <a href="mailto:info@jcautomotors.com" class="text-decoration-none" style="color: var(--secondary);">info@jcautomotors.com</a>
                                    </div>
                                </div>
                                
                                <div class="d-flex mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-clock-fill fs-4" style="color: var(--primary);"></i>
                                    </div>
                                    <div>
                                        <strong>Horario:</strong><br>
                                        Lun-Vie: 9:30 - 19:00<br>
                                        Sábado: 10:00 - 14:00
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="https://www.google.com/maps/place/JC+AUTOMOTORS/@-16.4875264,-68.1249366,20z/data=!4m6!3m5!1s0x915f213723448c1d:0x2631228765055366!8m2!3d-16.4875389!4d-68.124481!16s%2Fg%2F11mx74vgrt?hl=en&entry=ttu&g_ep=EgoyMDI1MDMxMi4wIKXMDSoASAFQAw%3D%3D" 
                                target="_blank" 
                                class="btn w-100 d-flex align-items-center justify-content-center" 
                                style="background-color: var(--primary); color: var(--light);">
                                    <i class="bi bi-map me-2"></i> Cómo llegar
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mapa con marcador -->
                    <div class="col-lg-8">
                        <div class="map-container rounded shadow position-relative">
                            <div class="map-overlay position-absolute top-0 end-0 p-2 bg-white m-3 rounded shadow-sm d-none d-md-block">
                                <span class="badge rounded-pill" style="background-color: var(--primary);">JC AUTOMOTORS</span>
                            </div>
                            <iframe 
                                width="100%" 
                                height="450" 
                                style="border:0; border-radius: 10px;" 
                                loading="lazy" 
                                allowfullscreen 
                                referrerpolicy="no-referrer-when-downgrade" 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d238.80852813670455!2d-68.1249366!3d-16.4875264!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x915f213723448c1d%3A0x2631228765055366!2sJC%20AUTOMOTORS!5e0!3m2!1sen!2sus!4v1710810075891!5m2!1sen!2sus">
                            </iframe>
                        </div>
                        
                        <div class="row mt-3 g-2">
                            <div class="col-sm-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="bi bi-p-circle-fill fs-4" style="color: var(--primary);"></i>
                                    <p class="mb-0 mt-2"><small>Estacionamiento gratuito</small></p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="bi bi-cup-hot-fill fs-4" style="color: var(--primary);"></i>
                                    <p class="mb-0 mt-2"><small>Café de cortesía</small></p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="bi bi-wifi fs-4" style="color: var(--primary);"></i>
                                    <p class="mb-0 mt-2"><small>WiFi disponible</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2 class="cta-title">¿Listo para encontrar tu moto ideal?</h2>
                <p class="cta-text">Visita nuestro concesionario hoy mismo y déjanos ayudarte a encontrar la motocicleta perfecta para ti. Ofrecemos pruebas de manejo y asesoramiento personalizado.</p>
                <a href="/direccion" class="btn btn-light">Visítanos hoy <i class="bi bi-geo-alt ms-2"></i></a>
            </div>
        </section>
    
        <!-- Footer -->
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/index.js"></script>
</body>
</html>
