<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/ClienteController.php';
require_once __DIR__ . '/../models/Usuario.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario logueado
$usuario_logueado = $_SESSION['user'];
$id_usuario = $_SESSION['user']['id'];

// Obtener datos extendidos del usuario para el sidebar
function obtenerDatosUsuario($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                            FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                            WHERE p._id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Procesar la imagen si existe
    if (!empty($userData['foto'])) {
        $userData['foto'] = 'data:image/jpeg;base64,' . base64_encode($userData['foto']);
    } else {
        $userData['foto'] = 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png';
    }

    return $userData;
}
$userData = obtenerDatosUsuario($conn, $id_usuario);
$current_page = basename($_SERVER['PHP_SELF']);
$controller = new ClienteController($conn);
$usuarioModel = new Usuario($conn);

// Manejo de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'crear') {
        $persona = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'telefono' => $_POST['telefono'],
            'email' => $_POST['email'],
            'documento_identidad' => $_POST['documento_identidad']
        ];
        $cliente = [
            'croquis_domicilio' => null,
            'factura_servicio' => null,
            'id_rol' => 3
        ];
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        // Validación de duplicidad
        if ($controller->existeDocumento($persona['documento_identidad'])) {
            $_SESSION['mensaje_error'] = "El documento de identidad ya está registrado.";
            header("Location: clientesA.php");
            exit;
        }
        if ($controller->existeEmail($persona['email'])) {
            $_SESSION['mensaje_error'] = "El correo electrónico ya está registrado.";
            header("Location: clientesA.php");
            exit;
        }

        try {
            $controller->store(['persona' => $persona, 'cliente' => $cliente, 'usuario' => $usuario, 'password' => $password]);
            $_SESSION['mensaje'] = "Cliente creado exitosamente.";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['mensaje_error'] = "El documento de identidad o correo ya está registrado.";
            } else {
                $_SESSION['mensaje_error'] = "Error al crear el cliente.";
            }
        }
        header("Location: clientesA.php");
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'editar') {
        $id = $_POST['id'];
        $persona = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'telefono' => $_POST['telefono'],
            'email' => $_POST['email'],
            'documento_identidad' => $_POST['documento_identidad']
        ];
        $cliente = [
            'croquis_domicilio' => null,
            'factura_servicio' => null,
            'id_rol' => 3
        ];
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        // Validación de duplicidad (excluyendo el propio ID)
        if ($controller->existeDocumento($persona['documento_identidad'], $id)) {
            $_SESSION['mensaje_error'] = "El documento de identidad ya está registrado.";
            header("Location: clientesA.php");
            exit;
        }
        if ($controller->existeEmail($persona['email'], $id)) {
            $_SESSION['mensaje_error'] = "El correo electrónico ya está registrado.";
            header("Location: clientesA.php");
            exit;
        }

        try {
            $controller->update($id, ['persona' => $persona, 'cliente' => $cliente, 'usuario' => $usuario, 'password' => $password]);
            $_SESSION['mensaje'] = "Cliente actualizado exitosamente.";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['mensaje_error'] = "El documento de identidad o correo ya está registrado.";
            } else {
                $_SESSION['mensaje_error'] = "Error al actualizar el cliente.";
            }
        }
        header("Location: clientesA.php");
        exit;
    }
}

// Filtros de búsqueda para clientes
$filtros = [];
if (isset($_GET['action']) && $_GET['action'] === 'filter') {
    if (!empty($_GET['documento'])) {
        $filtros['documento_identidad'] = $_GET['documento'];
    }
    if (!empty($_GET['nombre'])) {
        $filtros['nombre'] = $_GET['nombre'];
    }
    if (!empty($_GET['telefono'])) {
        $filtros['telefono'] = $_GET['telefono'];
    }
}

$clientes_all = $controller->index();
if (!empty($filtros)) {
    $clientes = array_filter($clientes_all, function($c) use ($filtros) {
        $ok = true;
        if (isset($filtros['documento_identidad'])) {
            $ok = $ok && stripos($c['documento_identidad'], $filtros['documento_identidad']) !== false;
        }
        if (isset($filtros['nombre'])) {
            $nombreCompleto = $c['nombre'] . ' ' . $c['apellido'];
            $ok = $ok && (stripos($c['nombre'], $filtros['nombre']) !== false || stripos($c['apellido'], $filtros['nombre']) !== false || stripos($nombreCompleto, $filtros['nombre']) !== false);
        }
        if (isset($filtros['telefono'])) {
            $ok = $ok && stripos($c['telefono'], $filtros['telefono']) !== false;
        }
        return $ok;
    });
    $clientes = array_values($clientes); 
} else {
    $clientes = $clientes_all;
}

$total_clientes = count($clientes);
$clientes_nuevos = count(array_filter($clientes, function($c) {
    $fecha_registro = strtotime($c['fecha_registro'] ?? date('Y-m-d'));
    return (time() - $fecha_registro) < (30 * 24 * 60 * 60);
}));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Automotors - Gestión de Clientes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/clienteA.css">
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
                    <a class="nav-link <?php echo ($current_page == 'mantenimientosA.php') ? 'active' : ''; ?>" href="mantenimientosA.php">
                        <i class="bi bi-wrench"></i>
                        <span>Mantenimientos</span>
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
    <div class="main-content">
    <div class="content-header">
                <div>
                    <h1 class="h2">
                         <i class="bi bi-people"> </i>Listado de Clientes
                    </h1>
                    <div class="breadcrumbs">
                        <i class="bi bi-house-door me-1"></i> Inicio / Clientes
                    </div>

                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalCrearCliente">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
                    </button>
                    <a href="../helpers/ReporteClientesA.php" target="_blank" class="btn btn-dark">
                        <i class="bi bi-upload me-1"></i> Exportar
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['mensaje_error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['mensaje_error']); ?>
<?php endif; ?>

            <div class="row mb-4 g-3">
                <!-- Total Clientes -->
                <div class="col-md-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 d-flex align-items-center">
                                        <i class="bi bi-people me-2 fs-5 text-muted"></i> Total Clientes
                                    </h6>
                                    <h3 class="mb-0 text-primary">
                                        <?= $total_clientes ?>
                                    </h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people-fill fs-3 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 d-flex align-items-center">
                                        <i class="bi bi-person-plus me-2 fs-5 text-muted"></i> Nuevos (30 días)
                                    </h6>
                                    <h3 class="mb-0 text-success">
                                        <?= $clientes_nuevos ?>
                                    </h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-plus-fill fs-3 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Filtros Avanzados de Clientes -->
            <div class="filter-section mb-4">
                <h5 class="mb-4">
                    <i class="bi bi-funnel-fill text-primary me-2"></i> Buscar Clientes
                </h5>
                <form method="GET" class="row g-3">
                    <input type="hidden" name="action" value="filter">
                    
                    <div class="col-md-4">
                        <label for="documento" class="form-label">
                            <i class="bi bi-card-text text-primary me-1"></i> Documento de Identidad
                        </label>
                        <input type="text" class="form-control" id="documento" name="documento" 
                            value="<?= htmlspecialchars($_GET['documento'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="nombre" class="form-label">
                            <i class="bi bi-person text-primary me-1"></i> Nombre o Apellido
                        </label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                            value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="telefono" class="form-label">
                            <i class="bi bi-telephone text-primary me-1"></i> Teléfono
                        </label>
                        <input type="text" class="form-control" id="telefono" name="telefono" 
                            value="<?= htmlspecialchars($_GET['telefono'] ?? '') ?>">
                    </div>
                    
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                        <a href="clientesA.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Carnet de Identidad</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Domicilio</th>
                                    <th>Factura Servicio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($clientes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted">No se encontraron clientes con los criterios especificados</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($clientes as $c): 
    $usuario_cliente = $usuarioModel->obtenerUsuarioPorPersona($c['_id']);
?>
<tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido']) ?></div>
                                                    <?php if (isset($c['fecha_registro'])): ?>
                                                        <small class="text-muted">Desde: <?= htmlspecialchars($c['fecha_registro']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($c['documento_identidad']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($c['telefono']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($c['email']) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['croquis_domicilio'])): ?>
                                                <span class="badge" style="background:#701106; color:#fff;">Entregado</span>
                                            <?php else: ?>
                                                <span class="badge" style="background:#050506; color:#fff;">No entregado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['factura_servicio'])): ?>
                                                <span class="badge" style="background:#701106; color:#fff;">Entregado</span>
                                            <?php else: ?>
                                                <span class="badge" style="background:#050506; color:#fff;">No entregado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarCliente"
                                                    data-id="<?= $c['_id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                                                    data-apellido="<?= htmlspecialchars($c['apellido']) ?>"
                                                    data-telefono="<?= htmlspecialchars($c['telefono']) ?>"
                                                    data-email="<?= htmlspecialchars($c['email']) ?>"
                                                    data-documento="<?= htmlspecialchars($c['documento_identidad']) ?>"
                                                    data-usuario="<?= htmlspecialchars($usuario_cliente) ?>"
                                                    data-croquis="<?= !empty($c['croquis_domicilio']) ? '1' : '0' ?>"
                                                    data-factura="<?= !empty($c['factura_servicio']) ? '1' : '0' ?>"
                                                    title="Editar">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
        </div>
    </div>
</div>

   <!-- Modal Crear Cliente -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" aria-labelledby="modalCrearClienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content" id="formCrearCliente">
            <input type="hidden" name="action" value="crear">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCrearClienteLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Campos de persona y cliente -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person me-1 text-primary"></i>Nombre
                        </label>
                        <input type="text" name="nombre" class="form-control" required 
                               maxlength="50" 
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 50 caracteres (solo letras)">
                        <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras, máximo 50 caracteres)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person me-1 text-primary"></i>Apellido
                        </label>
                        <input type="text" name="apellido" class="form-control" required
                               maxlength="50"
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 50 caracteres (solo letras)">
                        <div class="invalid-feedback">Por favor ingrese un apellido válido (solo letras, máximo 50 caracteres)</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-telephone me-1 text-primary"></i>Teléfono
                        </label>
                        <input type="text" name="telefono" class="form-control" required
                               maxlength="8"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 8 dígitos (solo números)">
                        <div class="invalid-feedback">Por favor ingrese un teléfono válido (8 dígitos numéricos)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-envelope me-1 text-primary"></i>Email
                        </label>
                        <input type="email" name="email" class="form-control" required
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Debe ser un email válido (ejemplo@dominio.com)">
                        <div class="invalid-feedback">Por favor ingrese un email válido</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-card-text me-1 text-primary"></i>Documento Identidad
                    </label>
                    <input type="text" name="documento_identidad" class="form-control" required
                           maxlength="10"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Máximo 10 dígitos (solo números)">
                    <div class="invalid-feedback">Por favor ingrese un documento válido (10 dígitos numéricos)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-geo-alt me-1 text-primary"></i>Croquis Domicilio (Imagen)
                    </label>
                    <input type="file" name="croquis_domicilio" class="form-control" accept="image/*"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Formatos aceptados: JPG, PNG, etc.">
                    <small class="text-muted">Suba una imagen del croquis de domicilio (JPG, PNG, etc.)</small>
                    <div class="invalid-feedback">Por favor seleccione una imagen válida</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-receipt me-1 text-primary"></i>Factura Servicio (Imagen)
                    </label>
                    <input type="file" name="factura_servicio" class="form-control" accept="image/*"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Formatos aceptados: JPG, PNG, etc.">
                    <small class="text-muted">Suba una imagen de la factura de servicio (JPG, PNG, etc.)</small>
                    <div class="invalid-feedback">Por favor seleccione una imagen válida</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person-badge me-1 text-primary"></i>Usuario
                        </label>
                        <input type="text" name="usuario" class="form-control" required maxlength="30"
                               pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guiones bajos">
                        <div class="invalid-feedback">Ingrese un usuario válido (solo letras, números y guiones bajos)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-lock me-1 text-primary"></i>Contraseña
                        </label>
                        <div class="input-group">
        <input type="password" name="password" id="crear-password" class="form-control" required minlength="8
               title="Mínimo 8 caracteres">
        <span class="input-group-text" id="eye-icon-crear-password" style="cursor:pointer;">
            <i class="bi bi-eye"></i>
        </span>
    </div>
    <div class="password-strength">
        <div class="password-strength-meter" id="crear-password-strength-meter"></div>
    </div>
    <div class="password-strength-container">
        <div class="password-requirements" style="margin-top: 8px;">
            <div class="requirement" id="crear-req-length"><i class="bi bi-x-circle"></i> Mínimo 8 caracteres</div>
            <div class="requirement" id="crear-req-uppercase"><i class="bi bi-x-circle"></i> Al menos una mayúscula</div>
            <div class="requirement" id="crear-req-lowercase"><i class="bi bi-x-circle"></i> Al menos una minúscula</div>
            <div class="requirement" id="crear-req-number"><i class="bi bi-x-circle"></i> Al menos un número</div>
            <div class="requirement" id="crear-req-special"><i class="bi bi-x-circle"></i> Al menos un carácter especial</div>
        </div>
    </div>
    <div class="invalid-feedback">Ingrese una contraseña válida</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content" id="formEditarCliente">
            <input type="hidden" name="action" value="editar">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalEditarClienteLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Campos de persona y cliente -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person me-1 text-primary"></i>Nombre
                        </label>
                        <input type="text" name="nombre" id="edit-nombre" class="form-control" required 
                               maxlength="50" 
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 50 caracteres (solo letras)">
                        <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras, máximo 50 caracteres)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person me-1 text-primary"></i>Apellido
                        </label>
                        <input type="text" name="apellido" id="edit-apellido" class="form-control" required
                               maxlength="50"
                               oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 50 caracteres (solo letras)">
                        <div class="invalid-feedback">Por favor ingrese un apellido válido (solo letras, máximo 50 caracteres)</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-telephone me-1 text-primary"></i>Teléfono
                        </label>
                        <input type="text" name="telefono" id="edit-telefono" class="form-control" required
                               maxlength="8"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Máximo 8 dígitos (solo números)">
                        <div class="invalid-feedback">Por favor ingrese un teléfono válido (8 dígitos numéricos)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-envelope me-1 text-primary"></i>Email
                        </label>
                        <input type="email" name="email" id="edit-email" class="form-control" required
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Debe ser un email válido (ejemplo@dominio.com)">
                        <div class="invalid-feedback">Por favor ingrese un email válido</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-card-text me-1 text-primary"></i>Documento Identidad
                    </label>
                    <input type="text" name="documento_identidad" id="edit-documento" class="form-control" required
                           maxlength="10"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Máximo 10 dígitos (solo números)">
                    <div class="invalid-feedback">Por favor ingrese un documento válido (10 dígitos numéricos)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-geo-alt me-1 text-primary"></i>Croquis Domicilio (Imagen)
                    </label>
                    <input type="file" name="croquis_domicilio" class="form-control" accept="image/*"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Formatos aceptados: JPG, PNG, etc.">
                    <small class="text-muted">Actualice la imagen del croquis si es necesario</small>
                    <div class="mt-2">
                        <p class="mb-1"><small>Estado actual:</small></p>
                        <span id="croquis-status" class="badge"></span>
                    </div>
                    <div class="invalid-feedback">Por favor seleccione una imagen válida</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-receipt me-1 text-primary"></i>Factura Servicio (Imagen)
                    </label>
                    <input type="file" name="factura_servicio" class="form-control" accept="image/*"
                           data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Formatos aceptados: JPG, PNG, etc.">
                    <small class="text-muted">Actualice la imagen de la factura si es necesario</small>
                    <div class="mt-2">
                        <p class="mb-1"><small>Estado actual:</small></p>
                        <span id="factura-status" class="badge"></span>
                    </div>
                    <div class="invalid-feedback">Por favor seleccione una imagen válida</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person-badge me-1 text-primary"></i>Usuario
                        </label>
                        <input type="text" name="usuario" id="edit-usuario" class="form-control" required maxlength="30"
                               pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guiones bajos">
                        <div class="invalid-feedback">Ingrese un usuario válido (solo letras, números y guiones bajos)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-lock me-1 text-primary"></i>Contraseña
                        </label>
                        <div class="input-group">
        <input type="password" name="password" id="edit-password" class="form-control" minlength="8"
               title="Mínimo 8 caracteres">
        <span class="input-group-text" id="eye-icon-edit-password" style="cursor:pointer;">
            <i class="bi bi-eye"></i>
        </span>
    </div>
    <div class="password-strength">
        <div class="password-strength-meter" id="edit-password-strength-meter"></div>
    </div>
    <div class="password-strength-container">
        <div class="password-requirements" style="margin-top: 8px;">
            <div class="requirement" id="edit-req-length"><i class="bi bi-x-circle"></i> Mínimo 8 caracteres</div>
            <div class="requirement" id="edit-req-uppercase"><i class="bi bi-x-circle"></i> Al menos una mayúscula</div>
            <div class="requirement" id="edit-req-lowercase"><i class="bi bi-x-circle"></i> Al menos una minúscula</div>
            <div class="requirement" id="edit-req-number"><i class="bi bi-x-circle"></i> Al menos un número</div>
            <div class="requirement" id="edit-req-special"><i class="bi bi-x-circle"></i> Al menos un carácter especial</div>
        </div>
    </div>
    <div class="invalid-feedback">Ingrese una contraseña válida</div>
    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save me-1"></i>Actualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>


    <!-- Modal Imagen -->
    <div class="modal fade" id="modalImagen" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalImagenLabel">Imagen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center modal-image-container">
                    <img id="imagen-modal" src="" class="img-fluid" alt="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="descargarImagen()">
                        <i class="bi bi-download me-1"></i> Descargar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/registro.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración del modal de edición
        var modalEditar = document.getElementById('modalEditarCliente');
        modalEditar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-nombre').value = button.getAttribute('data-nombre');
            document.getElementById('edit-apellido').value = button.getAttribute('data-apellido');
            document.getElementById('edit-telefono').value = button.getAttribute('data-telefono');
            document.getElementById('edit-email').value = button.getAttribute('data-email');
            document.getElementById('edit-documento').value = button.getAttribute('data-documento');
            document.getElementById('edit-usuario').value = button.getAttribute('data-usuario');
            
            // Actualizar estados
            var croquisStatus = document.getElementById('croquis-status');
            var facturaStatus = document.getElementById('factura-status');
            
            if (button.getAttribute('data-croquis') === '1') {
                croquisStatus.className = 'badge badge-entregado rounded-pill';
                croquisStatus.textContent = 'Entregado';
            } else {
                croquisStatus.className = 'badge bg-secondary rounded-pill';
                croquisStatus.textContent = 'No entregado';
            }
            
            if (button.getAttribute('data-factura') === '1') {
                facturaStatus.className = 'badge badge-entregado rounded-pill';
                facturaStatus.textContent = 'Entregado';
            } else {
                facturaStatus.className = 'badge bg-secondary rounded-pill';
                facturaStatus.textContent = 'No entregado';
            }
        });
        
        // Configuración del modal de imagen
        var modalImagen = document.getElementById('modalImagen');
        modalImagen.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var imagenBase64 = button.getAttribute('data-imagen');
            var titulo = button.getAttribute('data-titulo');
            
            document.getElementById('modalImagenLabel').textContent = titulo;
            document.getElementById('imagen-modal').src = "data:image/jpeg;base64," + imagenBase64;
            document.getElementById('imagen-modal').dataset.imagen = imagenBase64;
        });
        
        // Mostrar/ocultar contraseña en edición
    const editPassword = document.getElementById('edit-password');
    const eyeIconEdit = document.getElementById('eye-icon-edit-password');
    if (editPassword && eyeIconEdit) {
        eyeIconEdit.addEventListener('click', function() {
            if (editPassword.type === 'password') {
                editPassword.type = 'text';
                eyeIconEdit.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                editPassword.type = 'password';
                eyeIconEdit.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    }

    // Validación visual de contraseña en edición
    if (editPassword) {
        editPassword.addEventListener('input', function() {
            const val = editPassword.value;
            document.getElementById('edit-req-length').innerHTML =
                (val.length >= 8 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Mínimo 8 caracteres';
            document.getElementById('edit-req-uppercase').innerHTML =
                (/[A-Z]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos una mayúscula';
            document.getElementById('edit-req-lowercase').innerHTML =
                (/[a-z]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos una minúscula';
            document.getElementById('edit-req-number').innerHTML =
                (/[0-9]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos un número';
            document.getElementById('edit-req-special').innerHTML =
                (/[^A-Za-z0-9]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos un carácter especial';
        });
    }

    // Mostrar/ocultar contraseña
    const crearPassword = document.getElementById('crear-password');
    const eyeIconCrear = document.getElementById('eye-icon-crear-password');
    if (crearPassword && eyeIconCrear) {
        eyeIconCrear.addEventListener('click', function() {
            if (crearPassword.type === 'password') {
                crearPassword.type = 'text';
                eyeIconCrear.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                crearPassword.type = 'password';
                eyeIconCrear.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    }

    // Validación visual de contraseña (puedes copiar la función de registro.js o adaptarla aquí)
    if (crearPassword) {
        crearPassword.addEventListener('input', function() {
            const val = crearPassword.value;
            document.getElementById('crear-req-length').innerHTML =
                (val.length >= 8 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Mínimo 8 caracteres';
            document.getElementById('crear-req-uppercase').innerHTML =
                (/[A-Z]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos una mayúscula';
            document.getElementById('crear-req-lowercase').innerHTML =
                (/[a-z]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos una minúscula';
            document.getElementById('crear-req-number').innerHTML =
                (/[0-9]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos un número';
            document.getElementById('crear-req-special').innerHTML =
                (/[^A-Za-z0-9]/.test(val) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle"></i>') + ' Al menos un carácter especial';
        });
    }
});    // Función para descargar imagen
    function descargarImagen() {
        const imagenData = document.getElementById('imagen-modal').dataset.imagen;
        const titulo = document.getElementById('modalImagenLabel').textContent;
        const link = document.createElement('a');
        link.href = "data:image/jpeg;base64," + imagenData;
        link.download = titulo.toLowerCase().replace(/ /g, '_') + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</body>
</html>