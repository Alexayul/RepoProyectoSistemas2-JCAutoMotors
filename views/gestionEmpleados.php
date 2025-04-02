<?php
require '../config/conexion.php';
session_start();
require '../config/conexion.php';

$usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $usuario_logueado ? $usuario_logueado['id'] : null;

// You can run this SQL: ALTER TABLE EMPLEADO ADD COLUMN estado VARCHAR(20) DEFAULT 'Activo';

// Query to get employee data with JOIN to get all needed information
$query = "SELECT e._id as id, p.nombre, p.apellido, e.cargo, e.salario, 
         e.fecha_contratacion, p.telefono, p.email, e.estado, 
         COALESCE(p.foto_url, 'https://i.pinimg.com/736x/e9/5a/aa/e95aaa1289fb8108ec2d974c9e12e183.jpg') as imagen
         FROM EMPLEADO e
         JOIN PERSONA p ON e._id = p._id
         ORDER BY p.nombre ASC";


try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}


// Process form submissions for firing or rehiring
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['employee_id'])) {
        $employeeId = $_POST['employee_id'];
        $newStatus = ($_POST['action'] == 'fire') ? 'Despedido' : 'Activo';
        
        try {
            $updateQuery = "UPDATE EMPLEADO SET estado = :estado WHERE _id = :id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute(['estado' => $newStatus, 'id' => $employeeId]);
        
            // Redirigir para evitar reenvío del formulario
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "Error al actualizar: " . $e->getMessage();
        }
        
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - JC Automotors</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/gestionEmpleados.css">
</head>

<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="bi bi-list text-light fs-2"></i>
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($usuario_logueado): ?>
                            <li class="nav-item me-3">
                                <span class="navbar-text text-light">
                                    <i class="bi bi-person-circle me-1"></i>
                                    Bienvenido, <?php echo htmlspecialchars($usuario_logueado['usuario']); ?>
                                </span>
                            </li>
                            
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house-door me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="bi bi-speedometer2 me-1"></i>Administración
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="bi bi-people me-1"></i>Gestionar Empleados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="catalogo.php">
                                <i class="bi bi-bicycle me-1"></i>Catálogo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#ubicacion">
                                <i class="bi bi-geo-alt me-1"></i>Ubicación
                            </a>
                        </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../views/login.php">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <?php $employees = array_filter($employees, function($employee) use ($user_id) {
        return $employee['id'] != $user_id; // Filtra la lista y elimina el usuario autenticado
    }); ?>

    <div class="container-fluid">
        <div class="employee-grid">
            <?php foreach ($employees as $employee): ?>
                <div class="employee-card animate__animated animate__fadeIn">
                    <div class="employee-card-header">
                        <span class="badge-status <?php echo ($employee['estado'] == 'Activo' || !isset($employee['estado'])) ? 'status-active' : 'status-fired'; ?>">
                            <?php echo isset($employee['estado']) ? htmlspecialchars($employee['estado']) : 'Activo'; ?>
                        </span>
                        <img src="<?php echo htmlspecialchars($employee['imagen']); ?>" alt="Foto de <?php echo htmlspecialchars($employee['nombre']); ?>">
                    </div>
                    <div class="employee-card-body">
                        <h3><?php echo htmlspecialchars($employee['nombre'] . ' ' . $employee['apellido']); ?></h3>
                        <p><strong>Cargo:</strong> <?php echo htmlspecialchars($employee['cargo']); ?></p>
                        <p><strong>Salario:</strong> $<?php echo number_format($employee['salario'], 2); ?></p>
                        <p><strong>Fecha de Contratación:</strong> <?php echo date('d/m/Y', strtotime($employee['fecha_contratacion'])); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($employee['telefono']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                        
                        <div class="employee-actions">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                
                                <?php if (!isset($employee['estado']) || $employee['estado'] == 'Activo'): ?>
                                    <input type="hidden" name="action" value="fire">
                                    <button type="submit" class="btn btn-fire">
                                        <i class="bi bi-person-dash"></i> Despedir
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="rehire">
                                    <button type="submit" class="btn btn-rehire">
                                        <i class="bi bi-person-plus"></i> Recontratar
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>



    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>