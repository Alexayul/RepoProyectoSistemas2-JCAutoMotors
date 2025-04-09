<?php
require '../config/conexion.php';
session_start();
require '../config/conexion.php';

$usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $usuario_logueado ? $usuario_logueado['id'] : null;

// Check if user is logged in
if (!$usuario_logueado) {
    // Redirect to login page if not logged in
    header('Location: ../index.php');
    exit();
}

// Get the count of active and inactive employees
$countQuery = "SELECT 
                SUM(CASE WHEN estado = 'Activo' OR estado IS NULL THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN estado = 'Despedido' THEN 1 ELSE 0 END) as inactive_count
               FROM EMPLEADO";
               
try {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC);
    $activeCount = $counts['active_count'] ?? 0;
    $inactiveCount = $counts['inactive_count'] ?? 0;
} catch (PDOException $e) {
    $activeCount = 0;
    $inactiveCount = 0;
}

// Query to get employee data with JOIN to get all needed information
$query = "SELECT e._id as id, p.nombre, p.apellido, e.cargo, e.salario, 
         e.fecha_contratacion, p.telefono, p.email, e.estado, 
         COALESCE(p.foto_url, 'https://cdn-icons-png.flaticon.com/512/17320/17320345.png') as imagen
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

// Get user profile data
$userQuery = "SELECT p.nombre, p.apellido, p.foto_url, e.cargo
              FROM PERSONA p
              LEFT JOIN EMPLEADO e ON p._id = e._id
              WHERE p._id = :user_id";
              
try {
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute(['user_id' => $user_id]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $userData = [
        'nombre' => 'Usuario',
        'apellido' => '',
        'foto_url' => 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png',
        'cargo' => 'No especificado'
    ];
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

// Get the current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/gestionE.css">
</head>
<body>
    <!-- Sidebar Vertical -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <img src="../public/logo.png" alt="JC Automotors" class="img-fluid" style="max-height: 180px;">
            </a>
        </div>
        
        <!-- User Profile Section -->
        <div class="user-profile">
            <div class="user-avatar">
                <img src="<?php echo htmlspecialchars($userData['foto_url'] ?? 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png'); ?>" alt="User">
            </div>
            <div class="user-info">
                <h5 class="user-name"><?php echo htmlspecialchars($userData['nombre'] . ' ' . $userData['apellido']); ?></h5>
                <p class="user-role"><?php echo htmlspecialchars($userData['cargo'] ?? 'Usuario'); ?></p>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="../index.php">
                        <i class="bi bi-house-door"></i>
                        <span>Inicio</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'gestionEmpleados.php') ? 'active' : ''; ?>"  href="gestionEmpleados.php">
                        <i class="bi bi-people"></i>
                        <span>Empleados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'catalogo.php') ? 'active' : ''; ?>" href="catalogo.php">
                        <i class="bi bi-bicycle"></i>
                        <span>Catálogo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ventas.php') ? 'active' : ''; ?>" href="ventas.php">
                        <i class="bi bi-cash-coin"></i>
                        <span>Ventas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1><i class="bi bi-people me-2"></i>Gestión de Empleados</h1>
                <div class="breadcrumbs">
                    <i class="bi bi-house-door me-1"></i> Inicio / Empleados
                </div>
            </div>
            <div class="action-buttons">
                <a href="#" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Empleado
                </a>
                <a href="#" class="btn btn-dark">
                    <i class="bi bi-upload me-1"></i>Exportar
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($employees); ?></h3>
                    <p>Total Empleados</p>
                </div>
            </div>
            
            <div class="stat-card active-employees">
                <div class="stat-icon">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $activeCount; ?></h3>
                    <p>Empleados Activos</p>
                </div>
            </div>
            
            <div class="stat-card inactive-employees">
                <div class="stat-icon">
                    <i class="bi bi-person-dash-fill"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $inactiveCount; ?></h3>
                    <p>Empleados Inactivos</p>
                </div>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="search-container">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="employeeSearch" class="search-input" placeholder="Buscar empleado por nombre, cargo...">
        </div>

        <div class="row" id="employeeContainer">
            <?php foreach ($employees as $employee): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="employee-card">
                        <div class="employee-img" style="background-image: url('<?php echo htmlspecialchars($employee['imagen']); ?>');">
                            <span class="employee-status <?php echo ($employee['estado'] == 'Activo' || !isset($employee['estado'])) ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo isset($employee['estado']) ? htmlspecialchars($employee['estado']) : 'Activo'; ?>
                            </span>
                        </div>
                        <div class="employee-info">
                            <h3 class="employee-name"><?php echo htmlspecialchars($employee['nombre'] . ' ' . $employee['apellido']); ?></h3>
                            
                            <div class="employee-detail">
                                <strong>Cargo:</strong> <span><?php echo htmlspecialchars($employee['cargo']); ?></span>
                            </div>
                            
                            <div class="employee-detail">
                                <strong>Salario:</strong> <span>$<?php echo number_format($employee['salario'], 2); ?></span>
                            </div>
                            
                            <div class="employee-detail">
                                <strong>Contratación:</strong> <span><?php echo date('d/m/Y', strtotime($employee['fecha_contratacion'])); ?></span>
                            </div>
                            
                            <?php if(!empty($employee['telefono'])): ?>
                            <div class="employee-detail">
                                <strong>Teléfono:</strong> <span><?php echo htmlspecialchars($employee['telefono']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($employee['email'])): ?>
                            <div class="employee-detail">
                                <strong>Email:</strong> <span><?php echo htmlspecialchars($employee['email']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="employee-actions">
                                <a href="#" class="btn btn-edit">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </a>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                    
                                    <?php if (!isset($employee['estado']) || $employee['estado'] == 'Activo'): ?>
                                        <input type="hidden" name="action" value="fire">
                                        <button type="submit" class="btn btn-fire">
                                            <i class="bi bi-person-dash me-1"></i> Despedir
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="rehire">
                                        <button type="submit" class="btn btn-rehire">
                                            <i class="bi bi-person-plus me-1"></i> Recontratar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Employee search functionality
        const searchInput = document.getElementById('employeeSearch');
        const employeeCards = document.querySelectorAll('.employee-card');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            employeeCards.forEach(card => {
                const container = card.closest('.col-md-6');
                const employeeName = card.querySelector('.employee-name').textContent.toLowerCase();
                const employeePosition = card.querySelector('.employee-detail span').textContent.toLowerCase();
                
                if (employeeName.includes(searchTerm) || employeePosition.includes(searchTerm)) {
                    container.style.display = '';
                } else {
                    container.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>