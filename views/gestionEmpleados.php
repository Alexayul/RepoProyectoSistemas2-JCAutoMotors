<?php
// Simulated database connection and data retrieval
$employees = [
    [
        'id' => 3,
        'nombre' => 'Mateo',
        'apellido' => 'Torrez',
        'cargo' => 'Vendedor',
        'salario' => 3000.00,
        'fecha_contratacion' => '2024-05-19',
        'telefono' => '76543210',
        'email' => 'mateo@gmail.com',
        'imagen' => 'https://i.pinimg.com/736x/75/56/16/755616a4aa93f0f993ebd7e1e6c68234.jpg'
    ],
    [
        'id' => 4,
        'nombre' => 'Henry',
        'apellido' => 'Rojas',
        'cargo' => 'Administrador',
        'salario' => 1800.00,
        'fecha_contratacion' => '2023-07-10',
        'telefono' => '74569832',
        'email' => 'henry@gmail.com',
        'imagen' => 'https://i.pinimg.com/736x/75/56/16/755616a4aa93f0f993ebd7e1e6c68234.jpg'
    ],
    [
        'id' => 7,
        'nombre' => 'Mauricio',
        'apellido' => 'Marces',
        'cargo' => 'Administrador',
        'salario' => 2800.00,
        'fecha_contratacion' => '2023-04-15',
        'telefono' => '70123456',
        'email' => 'mauricio@gmail.com',
        'imagen' => 'https://i.pinimg.com/736x/75/56/16/755616a4aa93f0f993ebd7e1e6c68234.jpg'
    ],
    [
        'id' => 8,
        'nombre' => 'Alexandra',
        'apellido' => 'Mamani',
        'cargo' => 'Vendedor',
        'salario' => 1800.00,
        'fecha_contratacion' => '2025-03-21',
        'telefono' => '71234567',
        'email' => 'alexayul@gmail.com',
        'imagen' => 'https://i.pinimg.com/736x/75/56/16/755616a4aa93f0f993ebd7e1e6c68234.jpg'
    ],
    [
        'id' => 11,
        'nombre' => 'Empleado',
        'apellido' => '5',
        'cargo' => 'Vendedor',
        'salario' => 1800.00,
        'fecha_contratacion' => '2025-03-21',
        'telefono' => '71234567',
        'email' => 'null@gmail.com',
        'imagen' => 'https://i.pinimg.com/736x/75/56/16/755616a4aa93f0f993ebd7e1e6c68234.jpg'
    ]
];
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
    <link rel="stylesheet" href="/RepoProyectoSistemas2-JCAutoMotors/public/gestionEmpleados.css">
</head>

<header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="bi bi-list text-light fs-2"></i>
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
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
                            <a class="nav-link" href="../index.php">
                            <i class="bi bi-box-arrow-in-left"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

<body>

    <div class="container-fluid">
        <div class="employee-grid">
            <?php foreach ($employees as $employee): ?>
            <div class="employee-card animate__animated animate__fadeIn">
                <div class="employee-card-header">
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
                        <button class="btn btn-fire" disabled>
                            <i class="bi bi-person-dash"></i> Despedir
                        </button>
                        <button class="btn btn-rehire" disabled>
                            <i class="bi bi-person-plus"></i> Recontratar
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="public/index.js"></script>
        
</body>
</html>
</php>