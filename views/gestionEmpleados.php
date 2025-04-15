<?php
session_start();
require '../config/conexion.php';

$usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $usuario_logueado ? $usuario_logueado['id'] : null;

if (!$usuario_logueado) {
    header('Location: login.php');
    exit();
}

// Manejar solicitudes AJAX para obtener datos del empleado
if (isset($_GET['get_employee_data']) && isset($_GET['id'])) {
    $employeeId = $_GET['id'];
    $response = ['success' => false];
    
    try {
        $employeeQuery = "SELECT e.*, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, 
                          (SELECT usuario FROM USUARIO WHERE id_persona = e._id) as usuario,
                          COALESCE(e.foto, 'imagenes/empleados/default.png') as imagen
                          FROM EMPLEADO e
                          JOIN PERSONA p ON e._id = p._id
                          WHERE e._id = :id";
        $stmtEdit = $conn->prepare($employeeQuery);
        $stmtEdit->execute([':id' => $employeeId]);
        $employeeData = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        
        if ($employeeData) {
            $response = [
                'success' => true,
                'employee' => $employeeData
            ];
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Procesar formulario para agregar nuevo empleado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_employee') {
        try {
            // Procesar y guardar la foto
            $fotoUrl = 'imagenes/empleados/default.png';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $uploadDir = '../imagenes/empleados/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Validar tipo de archivo
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Solo se permiten imágenes JPG, JPEG, PNG o GIF");
                }
                
                // Validar tamaño (máximo 2MB)
                $maxFileSize = 2 * 1024 * 1024;
                if ($_FILES['foto']['size'] > $maxFileSize) {
                    throw new Exception("El archivo es demasiado grande. Tamaño máximo: 2MB");
                }
                
                // Generar nombre único
                $fileName = 'empleado_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadFile)) {
                    $fotoUrl = 'imagenes/empleados/' . $fileName;
                }
            }

            // Insertar en PERSONA
            $insertPersonaQuery = "INSERT INTO PERSONA (nombre, apellido, telefono, email, documento_identidad) 
                                  VALUES (:nombre, :apellido, :telefono, :email, :documento)";
            $stmtPersona = $conn->prepare($insertPersonaQuery);
            $stmtPersona->execute([
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':telefono' => $_POST['telefono'] ?? null,
                ':email' => $_POST['email'] ?? null,
                ':documento' => $_POST['documento_identidad']
            ]);
            
            // Obtener el ID recién insertado
            $nuevoId = $conn->lastInsertId();
            
            // Insertar en EMPLEADO
            $insertEmpleadoQuery = "INSERT INTO EMPLEADO (_id, cargo, salario, fecha_contratacion, id_rol, estado, foto) 
                                   VALUES (:id, :cargo, :salario, :fecha, :id_rol, 'Activo', :foto)";
            $stmtEmpleado = $conn->prepare($insertEmpleadoQuery);
            $stmtEmpleado->execute([
                ':id' => $nuevoId,
                ':cargo' => $_POST['cargo'],
                ':salario' => $_POST['salario'],
                ':fecha' => $_POST['fecha_contratacion'] ?? date('Y-m-d'),
                ':id_rol' => $_POST['id_rol'] ?? 2,
                ':foto' => $fotoUrl
            ]);
            
            // Insertar en USUARIO si se proporcionó
            if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
                $hashedPassword = hash('sha256', $_POST['password']);
                $insertUsuarioQuery = "INSERT INTO USUARIO (id_persona, usuario, password, id_rol) 
                                     VALUES (:id_persona, :usuario, :password, :id_rol)";
                $stmtUsuario = $conn->prepare($insertUsuarioQuery);
                $stmtUsuario->execute([
                    ':id_persona' => $nuevoId,
                    ':usuario' => $_POST['usuario'],
                    ':password' => $hashedPassword,
                    ':id_rol' => $_POST['id_rol'] ?? 2
                ]);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error al agregar empleado: " . $e->getMessage();
            error_log($error_message);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } elseif ($_POST['action'] == 'edit_employee' && isset($_POST['employee_id'])) {
        try {
            $employeeId = $_POST['employee_id'];
            
            // Procesar y actualizar la foto si existe
            $fotoUrl = $_POST['foto_actual'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $uploadDir = '../imagenes/empleados/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Validar tipo de archivo
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Solo se permiten imágenes JPG, JPEG, PNG o GIF");
                }
                
                // Validar tamaño (máximo 2MB)
                $maxFileSize = 2 * 1024 * 1024;
                if ($_FILES['foto']['size'] > $maxFileSize) {
                    throw new Exception("El archivo es demasiado grande. Tamaño máximo: 2MB");
                }
                
                // Eliminar la foto anterior si no es la default
                if ($fotoUrl != 'imagenes/empleados/default.png' && file_exists('../' . $fotoUrl)) {
                    unlink('../' . $fotoUrl);
                }
                
                // Generar nombre único
                $fileName = 'empleado_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadFile)) {
                    $fotoUrl = 'imagenes/empleados/' . $fileName;
                }
            }
            
            // Actualizar PERSONA
            $updatePersonaQuery = "UPDATE PERSONA SET 
                nombre = :nombre, 
                apellido = :apellido, 
                telefono = :telefono, 
                email = :email, 
                documento_identidad = :documento 
                WHERE _id = :id";
            $stmtPersona = $conn->prepare($updatePersonaQuery);
            $stmtPersona->execute([
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':telefono' => $_POST['telefono'] ?? null,
                ':email' => $_POST['email'] ?? null,
                ':documento' => $_POST['documento_identidad'],
                ':id' => $employeeId
            ]);
            
            // Actualizar EMPLEADO
            $updateEmpleadoQuery = "UPDATE EMPLEADO SET 
                cargo = :cargo, 
                salario = :salario, 
                id_rol = :id_rol, 
                foto = :foto 
                WHERE _id = :id";
            $stmtEmpleado = $conn->prepare($updateEmpleadoQuery);
            $stmtEmpleado->execute([
                ':cargo' => $_POST['cargo'],
                ':salario' => $_POST['salario'],
                ':id_rol' => $_POST['id_rol'] ?? 2,
                ':foto' => $fotoUrl,
                ':id' => $employeeId
            ]);
            
            // Actualizar contraseña si se proporcionó una nueva
            if (!empty($_POST['password'])) {
                $hashedPassword = hash('sha256', $_POST['password']);
                $checkUserQuery = "SELECT COUNT(*) FROM USUARIO WHERE id_persona = :id_persona";
                $stmtCheckUser = $conn->prepare($checkUserQuery);
                $stmtCheckUser->execute(['id_persona' => $employeeId]);
                $userExists = $stmtCheckUser->fetchColumn() > 0;
                
                if ($userExists) {
                    $updatePasswordQuery = "UPDATE USUARIO SET password = :password WHERE id_persona = :id_persona";
                    $stmtPassword = $conn->prepare($updatePasswordQuery);
                    $stmtPassword->execute([
                        ':password' => $hashedPassword,
                        ':id_persona' => $employeeId
                    ]);
                } else if (!empty($_POST['usuario'])) {
                    $insertUsuarioQuery = "INSERT INTO USUARIO (id_persona, usuario, password, id_rol) 
                                         VALUES (:id_persona, :usuario, :password, :id_rol)";
                    $stmtUsuario = $conn->prepare($insertUsuarioQuery);
                    $stmtUsuario->execute([
                        ':id_persona' => $employeeId,
                        ':usuario' => $_POST['usuario'],
                        ':password' => $hashedPassword,
                        ':id_rol' => $_POST['id_rol'] ?? 2
                    ]);
                }
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error al actualizar empleado: " . $e->getMessage();
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } elseif ($_POST['action'] == 'fire' || $_POST['action'] == 'rehire') {
        $employeeId = $_POST['employee_id'];
        $newStatus = ($_POST['action'] == 'fire') ? 'Despedido' : 'Activo';
        
        try {
            $updateQuery = "UPDATE EMPLEADO SET estado = :estado WHERE _id = :id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute(['estado' => $newStatus, 'id' => $employeeId]);
        
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $error_message = "Error al actualizar estado: " . $e->getMessage();
        }
    }
}

// Obtener datos para editar empleado (para cuando se carga la página con ?edit=id)
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $employeeId = $_GET['edit'];
    try {
        $employeeQuery = "SELECT e.*, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, 
                          (SELECT usuario FROM USUARIO WHERE id_persona = e._id) as usuario,
                          COALESCE(e.foto, 'imagenes/empleados/default.png') as imagen
                          FROM EMPLEADO e
                          JOIN PERSONA p ON e._id = p._id
                          WHERE e._id = :id";
        $stmtEdit = $conn->prepare($employeeQuery);
        $stmtEdit->execute([':id' => $employeeId]);
        $employeeData = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error al obtener datos del empleado: " . $e->getMessage();
    }
}

// Obtener conteo de empleados activos e inactivos
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

// Query para obtener todos los empleados
$query = "SELECT e._id as id, p.nombre, p.apellido, e.cargo, e.salario, 
         e.fecha_contratacion, p.telefono, p.email, e.estado, p.documento_identidad,
         COALESCE(e.foto, 'imagenes/empleados/default.png') as imagen
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

// Obtener datos del perfil del usuario
$userQuery = "SELECT p.nombre, p.apellido, e.foto, e.cargo
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
        'foto' => 'imagenes/empleados/default.png',
        'cargo' => 'No especificado'
    ];
}

// Obtener el nombre de la página actual para resaltar el menú activo
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
                <img src="<?php echo htmlspecialchars($userData['foto'] ?? 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png'); ?>" alt="User">
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
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Empleado
                </button>
                <a href="#" class="btn btn-dark">
                    <i class="bi bi-upload me-1"></i>Exportar
                </a>
            </div>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> El empleado ha sido agregado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> La información del empleado ha sido actualizada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
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
                    <div class="employee-img" style="background-image: url('../<?php echo htmlspecialchars($employee['imagen']); ?>');">
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
                                <strong>Fecha de Contratación:</strong> <span><?php echo date('d/m/Y', strtotime($employee['fecha_contratacion'])); ?></span>
                            </div>
                            
                            <?php if(!empty($employee['documento_identidad'])): ?>
                            <div class="employee-detail">
                                <strong>Carnet de Identidad:</strong> <span><?php echo htmlspecialchars($employee['documento_identidad']); ?></span>
                            </div>
                            <?php endif; ?>
                            
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
                                <a href="#" style="background-color:#d5a30e" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-employee-id="<?php echo $employee['id']; ?>">
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

    <!-- Modal para Agregar Empleado -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel"><i class="bi bi-person-plus me-2"></i>Agregar Nuevo Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_employee">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="documento_identidad" class="form-label">Carnet de Identidad</label>
                                <input type="text" class="form-control" id="documento_identidad" name="documento_identidad" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cargo" class="form-label">Cargo</label>
                                <select class="form-select" id="cargo" name="cargo" required>
                                    <option value="">Seleccionar cargo</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Mecánico">Mecánico</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="salario" class="form-label">Salario</label>
                                <input type="number" class="form-control" id="salario" name="salario" value="2500" min="0" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                                <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3" hidden>
                                <label for="id_rol" class="form-label">Rol</label>
                                <input type="number" class="form-control" id="id_rol" name="id_rol" value="2" readonly>
                                <small class="form-text text-muted">Rol asignado por defecto</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto del Empleado</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            <small class="form-text text-muted">Formatos soportados: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
                        </div>
                        
                        <div class="mb-3 text-center">
                            <img id="foto-preview" class="upload-preview" src="https://cdn-icons-png.flaticon.com/512/17320/17320345.png" alt="Vista previa">
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="form-text text-muted">Avise al empleado que debe cambiar su contraseña</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" >Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Empleado -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel"><i class="bi bi-pencil-square me-2"></i>Editar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" id="editEmployeeForm">
                    <input type="hidden" name="action" value="edit_employee">
                    <input type="hidden" name="employee_id" id="edit_employee_id">
                    <input type="hidden" name="foto_actual" id="foto_actual">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_documento_identidad" class="form-label">Carnet de Identidad</label>
                                <input type="text" class="form-control" id="edit_documento_identidad" name="documento_identidad" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="edit_telefono" name="telefono">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cargo" class="form-label">Cargo</label>
                                <select class="form-select" id="edit_cargo" name="cargo" required>
                                    <option value="">Seleccionar cargo</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Mecánico">Mecánico</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_salario" class="form-label">Salario</label>
                                <input type="number" class="form-control" id="edit_salario" name="salario" min="0" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="mb-3" hidden>
                            <label for="edit_id_rol" class="form-label">Rol</label>
                            <input type="number" class="form-control" id="edit_id_rol" name="id_rol" value="2" readonly>
                            <small class="form-text text-muted">Rol asignado por defecto</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_foto" class="form-label">Foto del Empleado</label>
                            <input type="file" class="form-control" id="edit_foto" name="foto" accept="image/*">
                            <small class="form-text text-muted">Deje vacío para mantener la foto actual.</small>
                        </div>
                        
                        <div class="mb-3 text-center">
                            <img id="edit-foto-preview" class="upload-preview" src="" alt="Vista previa">
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="edit_usuario" name="usuario" readonly>
                                <small class="form-text text-muted">El nombre de usuario no se puede modificar</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_password" class="form-label">Nueva Contraseña (opcional)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                                <small class="form-text text-muted">Dejar vacío para mantener la contraseña actual</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../public/gestionE.js"></script>
</body>
</html>