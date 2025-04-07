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

    $brandFilter = isset($_POST['brand']) ? $_POST['brand'] : '';
    $modelFilter = isset($_POST['model']) ? $_POST['model'] : '';
    $ccFilter = isset($_POST['cc']) ? $_POST['cc'] : '';

    $query = "
    SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
           M.color, M.precio, M.estado, M.fecha_ingreso, M.cantidad 
    FROM MOTOCICLETA M
    INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
    WHERE 1=1
    ";

    if ($brandFilter) {
        $query .= " AND MM.marca = :marca";
    }
    if ($modelFilter) {
        $query .= " AND MM.modelo = :modelo";
    }
    if ($ccFilter) {
        $query .= " AND MM.cilindrada = :cilindrada";
    }

    $stmt = $conn->prepare($query);
    if ($brandFilter) {
        $stmt->bindParam(':marca', $brandFilter, PDO::PARAM_STR);
    }
    if ($modelFilter) {
        $stmt->bindParam(':modelo', $modelFilter, PDO::PARAM_STR);
    }
    if ($ccFilter) {
        $stmt->bindParam(':cilindrada', $ccFilter, PDO::PARAM_INT);
    }

    $stmt->execute();
    $motocicletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtAccesorios = $conn->prepare("SELECT * FROM ACCESORIO");
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
    <title>Panel de Empleados</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/empleado.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Racing+Sans+One&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../public/empleado.css">
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Concesionaria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-speedometer2 me-1"></i>Ver Horario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-people me-1"></i>Solicitar Permiso
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="catalogo.php">
                            <i class="bi bi-bicycle me-1"></i>Catálogo
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
                            <a class="nav-link btn" href="../public/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5">
        <h3>Lista de Motos Disponibles</h3>
        
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="brand" class="form-label">Marca</label>
                    <input type="text" id="brand" name="brand" class="form-control" value="<?php echo htmlspecialchars($brandFilter); ?>">
                </div>
                <div class="col-md-4">
                    <label for="model" class="form-label">Modelo</label>
                    <input type="text" id="model" name="model" class="form-control" value="<?php echo htmlspecialchars($modelFilter); ?>">
                </div>
                <div class="col-md-4">
                    <label for="cc" class="form-label">Cilindrada</label>
                    <input type="number" id="cc" name="cc" class="form-control" min="50" max="5000" oninput="filtrarMotos()">
                    <small id="ccError" class="text-danger d-none">Ingrese un valor entre 50 y 5000</small>
                </div>

            </div>
            <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
        </form>

        <div class="row">
            <?php if ($motocicletas): ?>
                <?php foreach ($motocicletas as $moto): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="../<?php echo htmlspecialchars($moto['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($moto['modelo']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($moto['modelo']); ?></h5>
                                <p class="card-text"><strong>Marca:</strong> <?php echo htmlspecialchars($moto['marca']); ?></p>
                                <p class="card-text"><strong>Precio:</strong> Bs. <?php echo number_format($moto['precio']); ?></p>
                                <p class="card-text"><strong>Stock:</strong> <?php echo htmlspecialchars($moto['cantidad']); ?> unidades</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDetalles<?php echo $moto['moto_id']; ?>">Ver Detalles</button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAccesorios">Ver Accesorios</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="modalDetalles<?php echo $moto['moto_id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $moto['moto_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel<?php echo $moto['moto_id']; ?>">Detalles de <?php echo htmlspecialchars($moto['modelo']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Marca:</strong> <?php echo htmlspecialchars($moto['marca']); ?></p>
                                    <p><strong>Cilindrada:</strong> <?php echo htmlspecialchars($moto['cilindrada']); ?> cc</p>
                                    <p><strong>Color:</strong> <?php echo htmlspecialchars($moto['color']); ?></p>
                                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($moto['estado']); ?></p>
                                    <p><strong>Fecha de Ingreso:</strong> <?php echo htmlspecialchars($moto['fecha_ingreso']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No hay motocicletas disponibles.</div>
            <?php endif; ?>
        </div>

        <div class="modal fade" id="modalAccesorios" tabindex="-1" aria-labelledby="modalAccesoriosLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lista de Accesorios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Lista de Accesorios:</h6>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th> <!-- Columna de cantidad agregada -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accesorios as $accesorio): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($accesorio['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($accesorio['descripcion']); ?></td>
                                        <td>Bs. <?php echo number_format($accesorio['precio']); ?></td>
                                        <td><?php echo htmlspecialchars($accesorio['cantidad']); ?></td> <!-- Mostrar la cantidad -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
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

</body>
</html>
