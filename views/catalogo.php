<php?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCAutomotors - Catálogo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/RepoProyectoSistemas2-JCAutoMotors/public/catalogo.css">
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <!-- Logo -->
                <div class="logo-container">
                    <img src="/RepoProyectoSistemas2-JCAutoMotors/public/logo.png" alt="JCAutomotors Logo" class="logo-img">
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="bi bi-list text-light fs-2"></i>
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/JCAutomotors/index.php">
                                <i class="bi bi-house-door me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./login.php">
                                <i class="bi bi-speedometer2 me-1"></i>Administración
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./login.php">
                                <i class="bi bi-people me-1"></i>Empleado
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="catalogo.php">
                                <i class="bi bi-bicycle me-1"></i>Catálogo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/direccion">
                                <i class="bi bi-geo-alt me-1"></i>Ubicación
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

   
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Explora Nuestro Catálogo de Motos</h1>
            <p class="hero-text">Encuentra la moto perfecta para tus aventuras. Calidad, potencia y estilo al mejor precio.</p>
        </div>
    </section>

    <!-- Filter Bar -->
    <div class="container py-5">
        <div class="filter-bar">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h4 class="mb-3 mb-md-0">Filtrar por marca:</h4>
                </div>
                <div class="col-md-9">
                    <button class="btn btn-dark filter-btn active" data-filter="all">Todas</button>
                    <button class="btn btn-dark filter-btn" data-filter="yamaha">Yamaha</button>
                    <button class="btn btn-dark filter-btn" data-filter="honda">Honda</button>
                    <button class="btn btn-dark filter-btn" data-filter="kawasaki">Kawasaki</button>
                    <button class="btn btn-dark filter-btn" data-filter="suzuki">Suzuki</button>
                    <button class="btn btn-dark filter-btn" data-filter="ducati">Ducati</button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Yamaha MT-07 -->
            <div class="col-md-4 moto-card" data-brand="yamaha">
                <div class="card">
                    <img src="https://global-fs.webike-cdn.net/@japan/magazine/wp-content/uploads/2023/08/YAMAHA_MT-07_01_M.jpg" alt="Yamaha MT-07">
                    <div class="card-body text-center">
                        <h5 class="card-title">Yamaha MT-07</h5>
                        <div class="mb-3">
                            <span class="badge bg-secondary">Naked</span>
                            <span class="badge bg-secondary">689cc</span>
                        </div>
                        <p class="card-text">Motor de 689cc, ideal para ciudad y carretera.</p>
                        <div class="price-tag">Bs. 15.000</div>
                        <button class="btn btn-primary ver-detalles" data-bs-toggle="modal" data-bs-target="#modalMT07">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalMT07" tabindex="-1" aria-labelledby="modalLabelCB500X" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelCB500X">Honda CB500X</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="https://global-fs.webike-cdn.net/@japan/magazine/wp-content/uploads/2023/08/YAMAHA_MT-07_01_M.jpg" alt="Honda CB500X" class="img-fluid">
                                </div>
                                <div class="col-md-6">
                                    <h6>Especificaciones:</h6>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Motor</th>
                                                <td>471cc, 2 cilindros en paralelo</td>
                                            </tr>
                                            <tr>
                                                <th>Potencia</th>
                                                <td>47 CV a 8,500 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Par motor</th>
                                                <td>43 Nm a 7,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión delantera</th>
                                                <td>Horquilla telescópica</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión trasera</th>
                                                <td>Monoshock</td>
                                            </tr>
                                            <tr>
                                                <th>Precio</th>
                                                <td>Bs. 15.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary">Comprar ahora</button>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Ducati Panigale V2 -->
            <div class="col-md-4 moto-card" data-brand="ducati">
                <div class="card">
                    <img src="https://www.excelenciasdelmotor.com/sites/default/files/2022-07/portadmoto.jpg" alt="Ducati Panigale V2">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ducati Panigale V2</h5>
                        <div class="mb-3">
                            <span class="badge bg-secondary">Sport</span>
                            <span class="badge bg-secondary">955cc</span>
                        </div>
                        <p class="card-text">Una moto de alto rendimiento, con un motor de 955cc.</p>
                        <div class="price-tag">Bs. 40.000</div>
                        <button class="btn btn-primary ver-detalles" data-bs-toggle="modal" data-bs-target="#modalPanigaleV2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalPanigaleV2" tabindex="-1" aria-labelledby="modalLabelNinja400" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelNinja400">Kawasaki Ninja 400</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="https://www.excelenciasdelmotor.com/sites/default/files/2022-07/portadmoto.jpg" alt="Kawasaki Ninja 400" class="img-fluid">
                                </div>
                                <div class="col-md-6">
                                    <h6>Especificaciones:</h6>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Motor</th>
                                                <td>399cc, 2 cilindros en paralelo</td>
                                            </tr>
                                            <tr>
                                                <th>Potencia</th>
                                                <td>45 CV a 10,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Par motor</th>
                                                <td>38 Nm a 8,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión delantera</th>
                                                <td>Horquilla telescópica</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión trasera</th>
                                                <td>Monoshock</td>
                                            </tr>
                                            <tr>
                                                <th>Precio</th>
                                                <td>Bs. 40.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary">Comprar ahora</button>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Kawasaki Ninja 400 -->
            <div class="col-md-4 moto-card" data-brand="kawasaki">
                <div class="card">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Kawasaki_Ninja_400_KRT_SlantView_resized.jpg" alt="Kawasaki Ninja 400">
                    <div class="card-body text-center">
                        <h5 class="card-title">Kawasaki Ninja 400</h5>
                        <div class="mb-3">
                            <span class="badge bg-secondary">Sport</span>
                            <span class="badge bg-secondary">399cc</span>
                        </div>
                        <p class="card-text">Una moto deportiva ligera y ágil, perfecta para principiantes y expertos.</p>
                        <div class="price-tag">Bs. 12.000</div>
                        <button class="btn btn-primary ver-detalles" data-bs-toggle="modal" data-bs-target="#modalNinja400">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        
            <!-- Modal for Kawasaki Ninja 400 -->
            <div class="modal fade" id="modalNinja400" tabindex="-1" aria-labelledby="modalLabelNinja400" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelNinja400">Kawasaki Ninja 400</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Kawasaki_Ninja_400_KRT_SlantView_resized.jpg" alt="Kawasaki Ninja 400" class="img-fluid">
                                </div>
                                <div class="col-md-6">
                                    <h6>Especificaciones:</h6>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Motor</th>
                                                <td>399cc, 2 cilindros en paralelo</td>
                                            </tr>
                                            <tr>
                                                <th>Potencia</th>
                                                <td>45 CV a 10,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Par motor</th>
                                                <td>38 Nm a 8,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión delantera</th>
                                                <td>Horquilla telescópica</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión trasera</th>
                                                <td>Monoshock</td>
                                            </tr>
                                            <tr>
                                                <th>Precio</th>
                                                <td>Bs. 12.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary">Comprar ahora</button>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Honda CB500X -->
            <div class="col-md-4 moto-card" data-brand="honda">
                <div class="card">
                    <img src="https://www.mundomotero.com/wp-content/uploads/2020/09/Honda_CB500X_2022_08.jpg" alt="Honda CB500X">
                    <div class="card-body text-center">
                        <h5 class="card-title">Honda CB500X</h5>
                        <div class="mb-3">
                            <span class="badge bg-secondary">Adventure</span>
                            <span class="badge bg-secondary">471cc</span>
                        </div>
                        <p class="card-text">Una moto versátil para aventuras en carretera y fuera de ella.</p>
                        <div class="price-tag">Bs. 18.000</div>
                        <button class="btn btn-primary ver-detalles" data-bs-toggle="modal" data-bs-target="#modalCB500X">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        
            <!-- Modal for Honda CB500X -->
            <div class="modal fade" id="modalCB500X" tabindex="-1" aria-labelledby="modalLabelCB500X" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelCB500X">Honda CB500X</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="https://www.mundomotero.com/wp-content/uploads/2020/09/Honda_CB500X_2022_08.jpg" alt="Honda CB500X" class="img-fluid">
                                </div>
                                <div class="col-md-6">
                                    <h6>Especificaciones:</h6>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Motor</th>
                                                <td>471cc, 2 cilindros en paralelo</td>
                                            </tr>
                                            <tr>
                                                <th>Potencia</th>
                                                <td>47 CV a 8,500 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Par motor</th>
                                                <td>43 Nm a 7,000 rpm</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión delantera</th>
                                                <td>Horquilla telescópica</td>
                                            </tr>
                                            <tr>
                                                <th>Suspensión trasera</th>
                                                <td>Monoshock</td>
                                            </tr>
                                            <tr>
                                                <th>Precio</th>
                                                <td>Bs. 18.000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary">Comprar ahora</button>
                        </div>
                    </div>
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
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 footer-links">
                    <h5>Enlaces rápidos</h5>
                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li><a href="views/catalogo.php">Catálogo</a></li>
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
                        <li><i class="bi bi-telephone me-2"></i> (591) 77530498</li>
                        <li><i class="bi bi-envelope me-2"></i> jcautomotors2@gmail.com</li>
                        <li><i class="bi bi-clock me-2"></i> Lun-Sáb: 8:00 - 18:00</li>
                        <li><i class="bi bi-clock me-2"></i> Sáb: 8:00 - 12:00</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 JCAutomotors. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <script src="public/catalogo.js"></script>
</body>
</html>

    
