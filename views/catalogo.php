<?php
include '../config/conexion.php';

try {
    // Verificar si la conexión está establecida
    if (!isset($conn)) {
        throw new Exception("Error en la conexión con la base de datos.");
    }
    $brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';

    // Consulta para obtener las motocicletas con su respectivo modelo y marca
    $query = "
    SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, 
           M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
    FROM MOTOCICLETA M
    INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
";
    
    if ($brandFilter) {
        $query .= " WHERE MM.marca = :marca";
    }

    $stmt = $conn->prepare($query);

    // Si hay filtro de marca, bindear el valor de la marca
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
    <link rel="stylesheet" href="../public/catalogo.css">
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

    <main class="custom-container mt-5 pt-5">
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


    <main class="custom-container mt-5 pt-5">    
        <?php if ($motocicletas): ?>
            <div class="row motorcycle-grid">
                <?php foreach ($motocicletas as $moto): ?>
                    <div class="col-md-6 mb-4">
                        <div class="motorcycle-card">
                            <img src="../public/imagenes/<?php echo htmlspecialchars($moto['moto_id']); ?>.jpg" 
                                 alt="<?php echo htmlspecialchars($moto['modelo']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x250?text=Moto+<?php echo urlencode($moto['modelo']); ?>'">
                            <div class="motorcycle-details">
                                <h2><?php echo htmlspecialchars($moto['modelo']); ?></h2>
                                <h6><?php echo htmlspecialchars($moto['marca']); ?></h6>
                                <div class="price">Bs. <?php echo number_format($moto['precio']); ?></div>
                                <button class="btn btn-details" data-bs-toggle="modal" data-bs-target="#motorcycleModal"
                                        data-marca="<?php echo htmlspecialchars($moto['marca']); ?>"
                                        data-modelo="<?php echo htmlspecialchars($moto['modelo']); ?>"
                                        data-cilindrada="<?php echo htmlspecialchars($moto['cilindrada']); ?>"
                                        data-color="<?php echo htmlspecialchars($moto['color']); ?>"
                                        data-precio="<?php echo number_format($moto['precio']); ?>"
                                        data-estado="<?php echo htmlspecialchars($moto['estado']); ?>"
                                        data-fecha="<?php echo htmlspecialchars($moto['fecha_ingreso']); ?>"
                                        data-cantidad="<?php echo htmlspecialchars($moto['cantidad']); ?>">
                                    Ver Detalles
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No hay motocicletas disponibles en este momento.
            </div>
        <?php endif; ?>
    </main>

    <!-- Motorcycle Details Modal -->
    <div class="modal fade" id="motorcycleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalImage" src="" alt="Motorcycle Image" class="img-fluid rounded">
                        </div>
                        <div class="col-md-6">
                            <div class="motorcycle-specs">
                                <div class="spec-item">
                                    <span class="spec-label">Marca:</span>
                                    <span class="spec-value" id="modalBrand"></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Modelo:</span>
                                    <span class="spec-value" id="modalModel"></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Cilindrada:</span>
                                    <span class="spec-value" id="modalCilindrada"></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Color:</span>
                                    <span class="spec-value" id="modalColor"></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Precio:</span>
                                    <span class="spec-value" id="modalPrice"></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Estado:</span>
                                    <span class="spec-value" id="modalState"></span>
                                </div>
                            </div>
                        </div>
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
                    <h5>Contacto</h5>
                    <ul>
                        <li><i class="bi bi-geo-alt me-2"></i> Av. Tejada Sorzano, La Paz, Bolivia</li>
                        <li><i class="bi bi-telephone me-2"></i> (591) 77530498</li>
                        <li><i class="bi bi-envelope me-2"></i> jcautomotors2@gmail.com</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 JCAutomotors. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../public/catalogo.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('motorcycleModal');
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const modalImage = modal.querySelector('#modalImage');
                const modalBrand = modal.querySelector('#modalBrand');
                const modalModel = modal.querySelector('#modalModel');
                const modalCilindrada = modal.querySelector('#modalCilindrada');
                const modalColor = modal.querySelector('#modalColor');
                const modalPrice = modal.querySelector('#modalPrice');
                const modalState = modal.querySelector('#modalState');

                modalImage.src = `../public/imagenes/${button.dataset.marca}-${button.dataset.modelo}.jpg`;
                modalBrand.textContent = button.dataset.marca;
                modalModel.textContent = button.dataset.modelo;
                modalCilindrada.textContent = button.dataset.cilindrada;
                modalColor.textContent = button.dataset.color;
                modalPrice.textContent = `$${button.dataset.precio}`;
                modalState.textContent = button.dataset.estado;
            });
        });
    </script>
</body>
</html>
