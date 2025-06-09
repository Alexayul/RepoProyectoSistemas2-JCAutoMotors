<?php
session_start();
require '../config/conexion.php';

// Definir avatar por defecto en base64 (SVG)
define('DEFAULT_AVATAR', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJzNC40NzcgMTAgMTAgMTAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnptMCAyYzQuNDE4IDAgOCAzLjU4MiA4IDhzLTMuNTgyIDgtOCA4LTgtMy41ODItOC04IDMuNTgyLTggOC04eiIvPjxwYXRoIGQ9Ik0xMiAzYy0yLjIxIDAtNCAxLjc5LTQgNHMxLjc5IDQgNCA0IDQtMS43OSA0LTRzLTEuNzktNC00LTR6bTAgN2MtMy4zMTMgMC02IDIuNjg3LTYgNnYxaDEydi0xYzAtMy4zMTMtMi42ODctNi02LTZ6Ii8+PC9zdmc+');

$usuario_logueado = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $usuario_logueado ? $usuario_logueado['id'] : null;

if (!$usuario_logueado) {
    header('Location: login.php');
    exit();
}

// 1. Obtener conteo de empleados activos/inactivos
try {
    $countStmt = $conn->prepare("SELECT 
        SUM(CASE WHEN estado = 'Activo' OR estado IS NULL THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN estado = 'Despedido' THEN 1 ELSE 0 END) as inactive_count
        FROM EMPLEADO");
    $countStmt->execute();
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $activeCount = $counts['active_count'] ?? 0;
    $inactiveCount = $counts['inactive_count'] ?? 0;
    
} catch (PDOException $e) {
    $activeCount = 0;
    $inactiveCount = 0;
    error_log("Error al contar empleados: " . $e->getMessage());
}

// 2. Manejar solicitudes AJAX para obtener datos del empleado
if (isset($_GET['get_employee_data']) && isset($_GET['id'])) {
    $employeeId = $_GET['id'];
    $response = ['success' => false];
    
    try {
        // Limpia cualquier salida previa
        ob_clean();
        
        $employeeQuery = "SELECT e.*, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, 
                         (SELECT usuario FROM USUARIO WHERE id_persona = e._id) as usuario
                         FROM EMPLEADO e
                         JOIN PERSONA p ON e._id = p._id
                         WHERE e._id = :id";
        $stmtEdit = $conn->prepare($employeeQuery);
        $stmtEdit->execute([':id' => $employeeId]);
        $employeeData = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        
        if ($employeeData) {
            // Procesamiento de imagen mejorado
            if (!empty($employeeData['foto'])) {
                if (is_resource($employeeData['foto'])) {
                    $employeeData['foto'] = stream_get_contents($employeeData['foto']);
                }
                
                // Elimina el BLOB original para evitar problemas con json_encode
                unset($employeeData['foto']);
                
                // Usa la imagen codificada
                $employeeData['imagen'] = 'data:image/jpeg;base64,'.base64_encode($employeeData['foto']);
            } else {
                $employeeData['imagen'] = DEFAULT_AVATAR;
            }
            
            $response = ['success' => true, 'employee' => $employeeData];
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
        error_log("Error en get_employee_data: " . $e->getMessage());
    }
    
    // Asegúrate de que no haya nada antes del JSON
    ob_clean();
    
    // Establece cabeceras y envía respuesta
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// 3. Procesar formularios (agregar/editar/despedir/recontratar)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    try {
        $employeeId = $_POST['employee_id'] ?? null;
        
        // Manejar acciones de despedir/recontratar
        if ($_POST['action'] == 'fire' || $_POST['action'] == 'rehire') {
            $newStatus = ($_POST['action'] == 'fire') ? 'Despedido' : 'Activo';
            $stmt = $conn->prepare("UPDATE EMPLEADO SET estado = :estado WHERE _id = :id");
            $stmt->execute([':estado' => $newStatus, ':id' => $employeeId]);
            header("Location: gestionEmpleados.php");
            exit;
        }
        
        // Procesar imagen si se subió
        $fotoBlob = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $detectedType = mime_content_type($_FILES['foto']['tmp_name']);
            
            if (!in_array($detectedType, $allowedTypes)) {
                throw new Exception("Solo se permiten imágenes JPG, PNG o GIF");
            }
            
            if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                throw new Exception("La imagen no debe exceder 2MB");
            }
            
            $fotoBlob = file_get_contents($_FILES['foto']['tmp_name']);
        }
        
        if ($_POST['action'] == 'add_employee') {
            // Insertar en PERSONA
            $stmtPersona = $conn->prepare("INSERT INTO PERSONA (nombre, apellido, telefono, email, documento_identidad) 
                                         VALUES (:nombre, :apellido, :telefono, :email, :documento)");
            $stmtPersona->execute([
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':telefono' => $_POST['telefono'] ?? null,
                ':email' => $_POST['email'] ?? null,
                ':documento' => $_POST['documento_identidad']
            ]);
            
            $nuevoId = $conn->lastInsertId();
            
            // Insertar en EMPLEADO
            $stmtEmpleado = $conn->prepare("INSERT INTO EMPLEADO (_id, cargo, salario, fecha_contratacion, id_rol, estado, foto) 
                                           VALUES (:id, :cargo, :salario, :fecha, :id_rol, 'Activo', :foto)");
            $stmtEmpleado->bindValue(':id', $nuevoId);
            $stmtEmpleado->bindValue(':cargo', $_POST['cargo']);
            $stmtEmpleado->bindValue(':salario', $_POST['salario']);
            $stmtEmpleado->bindValue(':fecha', $_POST['fecha_contratacion'] ?? date('Y-m-d'));
            $stmtEmpleado->bindValue(':id_rol', $_POST['id_rol'] ?? 2);
            $stmtEmpleado->bindValue(':foto', $fotoBlob, $fotoBlob ? PDO::PARAM_LOB : PDO::PARAM_NULL);
            $stmtEmpleado->execute();
            
            // Crear usuario si se proporcionó
            if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
                $hashedPassword = hash('sha256', $_POST['password']);
                $stmtUsuario = $conn->prepare("INSERT INTO USUARIO (id_persona, usuario, password, id_rol) 
                                              VALUES (:id_persona, :usuario, :password, :id_rol)");
                $stmtUsuario->execute([
                    ':id_persona' => $nuevoId,
                    ':usuario' => $_POST['usuario'],
                    ':password' => $hashedPassword,
                    ':id_rol' => $_POST['id_rol'] ?? 2
                ]);
            }
            
            header("Location: gestionEmpleados.php?success=1");
            
        } elseif ($_POST['action'] == 'edit_employee' && $employeeId) {
            // Actualizar PERSONA
            $stmtPersona = $conn->prepare("UPDATE PERSONA SET 
                                         nombre = :nombre, apellido = :apellido, 
                                         telefono = :telefono, email = :email, 
                                         documento_identidad = :documento 
                                         WHERE _id = :id");
            $stmtPersona->execute([
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':telefono' => $_POST['telefono'] ?? null,
                ':email' => $_POST['email'] ?? null,
                ':documento' => $_POST['documento_identidad'],
                ':id' => $employeeId
            ]);
            
            // Actualizar EMPLEADO
            $sql = "UPDATE EMPLEADO SET 
                   cargo = :cargo, salario = :salario, id_rol = :id_rol" . 
                   ($fotoBlob ? ", foto = :foto" : "") . " 
                   WHERE _id = :id";
            
            $stmtEmpleado = $conn->prepare($sql);
            $stmtEmpleado->bindValue(':cargo', $_POST['cargo']);
            $stmtEmpleado->bindValue(':salario', $_POST['salario']);
            $stmtEmpleado->bindValue(':id_rol', $_POST['id_rol'] ?? 2);
            $stmtEmpleado->bindValue(':id', $employeeId);
            
            if ($fotoBlob) {
                $stmtEmpleado->bindValue(':foto', $fotoBlob, PDO::PARAM_LOB);
            }
            
            $stmtEmpleado->execute();
            
            // Actualizar contraseña si se proporcionó
            if (!empty($_POST['password'])) {
                $hashedPassword = hash('sha256', $_POST['password']);
                $stmtUsuario = $conn->prepare("UPDATE USUARIO SET 
                                             password = :password 
                                             WHERE id_persona = :id_persona");
                $stmtUsuario->execute([
                    ':password' => $hashedPassword,
                    ':id_persona' => $employeeId
                ]);
            }
            
            header("Location: gestionEmpleados.php?updated=1");
        }
        exit;
        
    } catch (PDOException $e) {
        $error_message = "Error en la base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
// 4. Obtener datos para editar empleado (cuando se carga la página con ?edit=id)
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $employeeId = $_GET['edit'];
    try {
        $employeeQuery = "SELECT e.*, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, 
                         (SELECT usuario FROM USUARIO WHERE id_persona = e._id) as usuario
                         FROM EMPLEADO e
                         JOIN PERSONA p ON e._id = p._id
                         WHERE e._id = :id";
        $stmtEdit = $conn->prepare($employeeQuery);
        $stmtEdit->execute([':id' => $employeeId]);
        $employeeData = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        
        if ($employeeData) {
            $employeeData['imagen'] = !empty($employeeData['foto']) ? 
                'data:image/jpeg;base64,'.base64_encode($employeeData['foto']) : 
                DEFAULT_AVATAR;
        }
    } catch (PDOException $e) {
        $error_message = "Error al obtener datos del empleado: " . $e->getMessage();
    }
}

// 5. Obtener lista de empleados para mostrar
try {
    $stmt = $conn->prepare("SELECT e._id as id, p.nombre, p.apellido, e.cargo, e.salario, 
                           e.fecha_contratacion, p.telefono, p.email, e.estado, p.documento_identidad, e.foto
                           FROM EMPLEADO e JOIN PERSONA p ON e._id = p._id
                           ORDER BY p.nombre ASC");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($employees as &$employee) {
        if (!empty($employee['foto'])) {
            if (is_resource($employee['foto'])) {
                $employee['foto'] = stream_get_contents($employee['foto']);
            }
            
            // Determina el tipo MIME real de la imagen
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($employee['foto']);
            
            $employee['imagen'] = 'data:' . $mime . ';base64,' . base64_encode($employee['foto']);
        } else {
            $employee['imagen'] = DEFAULT_AVATAR;
        }
    }
    unset($employee);
    
} catch (PDOException $e) {
    die("Error al obtener empleados: " . $e->getMessage());
}

// Obtener datos del usuario logueado
try {
    $stmt = $conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                           FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                           WHERE p._id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $userData['foto'] = !empty($userData['foto']) ? 
        'data:image/jpeg;base64,'.base64_encode($userData['foto']) : 
        DEFAULT_AVATAR;
        
} catch (PDOException $e) {
    $userData = [
        'nombre' => 'Usuario',
        'apellido' => '',
        'foto' => DEFAULT_AVATAR,
        'cargo' => 'No especificado'
    ];
}

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
    <link rel="stylesheet" href="../public/css/gestionE.css">
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
                <img src="<?php echo htmlspecialchars($userData['foto']); ?>" alt="User">
            </div>
            <div class="user-info">
                <h5 class="user-name"><?php echo htmlspecialchars($userData['nombre'] . ' ' . $userData['apellido']); ?></h5>
                <p class="user-role"><?php echo htmlspecialchars($userData['cargo']); ?></p>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Panel administrativo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'gestionEmpleados.php') ? 'active' : ''; ?>"  href="gestionEmpleados.php">
                        <i class="bi bi-people"></i>
                        <span>Empleados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'catalogoA.php') ? 'active' : ''; ?>" href="catalogoA.php">
                      <i class="bi bi-bicycle"></i>
                        <span>Inventario de motos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'clientesA.php') ? 'active' : ''; ?>" href="clientesA.php">
                       <i class="bi bi-person me-2"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ventasA.php') ? 'active' : ''; ?>" href="ventasA.php">
                        <i class="bi bi-cash-coin"></i>
                        <span>Ventas</span>
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'creditosA.php') ? 'active' : ''; ?>" href="creditosA.php">
                        <i class="bi bi-cash-stack"></i>
                        <span>Créditos Directos</span>
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
                                      <button type="button" class="btn btn-fire" onclick="showFireAlert(<?php echo $employee['id']; ?>)">
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
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   maxlength="20" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                                   title="Solo letras (máximo 20 caracteres)" required>
                            <small class="form-text text-muted">Máximo 20 caracteres, solo letras</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                   maxlength="30" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                                   title="Solo letras (máximo 30 caracteres)" required>
                            <small class="form-text text-muted">Máximo 30 caracteres, solo letras</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="documento_identidad" class="form-label">Carnet de Identidad</label>
                            <input type="text" class="form-control" id="documento_identidad" name="documento_identidad"
                                inputmode="numeric" pattern="\d{5,10}" 
                                title="Solo números (hasta 10 dígitos)" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                inputmode="numeric" pattern="\d{7,8}" 
                                title="Solo números (8 dígitos)" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email"
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               title="Ejemplo: usuario@dominio.com">
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
                            <input type="number" class="form-control" id="salario" name="salario" 
                                   value="2500" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                            <input type="date" class="form-control" id="fecha_contratacion" 
                                   name="fecha_contratacion" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3" hidden>
                            <label for="id_rol" class="form-label">Rol</label>
                            <input type="number" class="form-control" id="id_rol" name="id_rol" value="2" readonly>
                            <small class="form-text text-muted">Rol asignado por defecto</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto del Empleado</label>
                        <input type="file" class="form-control" id="foto" name="foto" 
                               accept="image/jpeg, image/png, image/gif">
                        <small class="form-text text-muted">Formatos soportados: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
                    </div>
                    
                    <div class="mb-3 text-center">
                        <img id="foto-preview" class="upload-preview" src="data:image/png;base64,..." alt="Vista previa">
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario"
                                   maxlength="10" pattern="[A-Za-z0-9]+" 
                                   title="Solo letras y números (máximo 10 caracteres)">
                            <small class="form-text text-muted">Máximo 10 caracteres alfanuméricos</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="6" maxlength="20"
                                   title="Entre 6 y 20 caracteres">
                            <small class="form-text text-muted">Avise al empleado que debe cambiar su contraseña (6-20 caracteres)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Empleado</button>
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
                                <input type="text" class="form-control" id="edit_nombre" name="nombre"  maxlength="20" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                                   title="Solo letras (máximo 20 caracteres)" required>
                                <small class="form-text text-muted">Máximo 20 caracteres, solo letras</small>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" maxlength="30" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                                   title="Solo letras (máximo 30 caracteres)" required>
                            <small class="form-text text-muted">Máximo 30 caracteres, solo letras</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                            <label for="documento_identidad" class="form-label">Carnet de Identidad</label>
                            <input type="text" class="form-control" id="documento_identidad" name="documento_identidad"
                                inputmode="numeric" pattern="\d{5,10}" 
                                title="Solo números (hasta 10 dígitos)" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                inputmode="numeric" pattern="\d{7,8}" 
                                title="Solo números (8 dígitos)" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);">
                        </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="edit_email" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               title="Ejemplo: usuario@dominio.com">
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
                                <input type="number" class="form-control" id="edit_salario" name="salario" value="2500" min="0" step="0.01" required>
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
                            <img id="edit-foto-preview" class="upload-preview" src="data:image/png;base64,..." alt="Vista previa">
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
                                <input type="password" class="form-control" id="edit_password" name="password" readonly>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JavaScript -->
    <script src="../public/js/gestionE.js"></script>
    <script>
       function showFireAlert(employeeId, employeeName) {
    // Confirmación más elegante con opción de cancelar
    const confirmation = confirm(`⚠️ ¿Estás seguro de despedir a este empleado?`);
    
    if (!confirmation) {
        return; // Si el usuario cancela, no hacemos nada
    }

    // Crear formulario de manera más limpia
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'gestionEmpleados.php';
    form.style.display = 'none'; // Ocultamos el formulario

    // Función helper para crear inputs
    const createHiddenInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    };

    // Añadir campos al formulario
    form.appendChild(createHiddenInput('employee_id', employeeId));
    form.appendChild(createHiddenInput('action', 'fire'));

    // Añadir al documento y enviar
    document.body.appendChild(form);
    form.submit();
}
    </script>
</body>
</html>