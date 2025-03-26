<?php
session_start();
require_once '../config/conexion.php'; // Incluir el archivo de conexión

// Function to sanitize and validate input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store form data in session to preserve on reload
    $_SESSION['form_data'] = $_POST;

    // Sanitize and validate inputs
    $nombre = sanitizeInput($_POST['nombre']);
    $email = sanitizeInput($_POST['email']);
    $usuario = sanitizeInput($_POST['usuario']);
    $telefono = sanitizeInput($_POST['telefono'] ?? '');
    $documento_identidad = sanitizeInput($_POST['documento_identidad']);
    $password = $_POST['password'];

    // Validate inputs
    $errors = [];

    if (empty($nombre)) {
        $errors[] = "El nombre es requerido.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Correo electrónico inválido.";
    }

    if (!empty($telefono) && !preg_match('/^[0-9+\-\s]{8,20}$/', $telefono)) {
        $errors[] = "Número de teléfono inválido.";
    }

    if (empty($documento_identidad)) {
        $errors[] = "El documento de identidad es requerido.";
    }

    if (empty($usuario)) {
        $errors[] = "El nombre de usuario es requerido.";
    }

    try {
        // Check for existing username
        $check_user_stmt = $conn->prepare("SELECT * FROM USUARIO WHERE usuario = :usuario");
        $check_user_stmt->execute(['usuario' => $usuario]);
        if ($check_user_stmt->rowCount() > 0) {
            $errors[] = "El nombre de usuario ya está en uso.";
        }

        // Check for existing email
        $check_email_stmt = $conn->prepare("SELECT * FROM PERSONA WHERE email = :email");
        $check_email_stmt->execute(['email' => $email]);
        if ($check_email_stmt->rowCount() > 0) {
            $errors[] = "El correo electrónico ya está registrado.";
        }
    } catch (PDOException $e) {
        $errors[] = "Error de base de datos: " . $e->getMessage();
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Start a transaction
            $conn->beginTransaction();

            // Split nombre into nombre and apellido
            $nombreParts = explode(' ', $nombre, 2);
            $nombre = $nombreParts[0];
            $apellido = isset($nombreParts[1]) ? $nombreParts[1] : '';

            // Rol de cliente
            $rol_id = 3;

            // Insert into PERSONA table
            $stmt_persona = $conn->prepare("INSERT INTO PERSONA (nombre, apellido, email, telefono, documento_identidad) VALUES (:nombre, :apellido, :email, :telefono, :documento_identidad)");
            $stmt_persona->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':email' => $email,
                ':telefono' => $telefono,
                ':documento_identidad' => $documento_identidad
            ]);

            // Get the last inserted PERSONA id
            $persona_id = $conn->lastInsertId();

            // Insert into CLIENTE table
            $stmt_cliente = $conn->prepare("INSERT INTO CLIENTE (_id, id_rol) VALUES (:persona_id, :rol_id)");
            $stmt_cliente->execute([
                ':persona_id' => $persona_id,
                ':rol_id' => $rol_id
            ]);

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into USUARIO table
            $stmt_usuario = $conn->prepare("INSERT INTO USUARIO (id_persona, usuario, password, id_rol) VALUES (:persona_id, :usuario, :password, :rol_id)");
            $stmt_usuario->execute([
                ':persona_id' => $persona_id,
                ':usuario' => $usuario,
                ':password' => $hashed_password,
                ':rol_id' => $rol_id
            ]);

            // Commit the transaction
            $conn->commit();

            // Clear stored form data
            unset($_SESSION['form_data']);

            // Set success message
            $_SESSION['registro_success'] = true;

            // Redirect to index.php
            header("Location: /RepoProyectoSistemas2-JCAutoMotors/index.php");
            exit();

        } catch (PDOException $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            error_log("Error de registro: " . $e->getMessage()); // Log para administrador
            $errors[] = "No se pudo completar el registro. Intente nuevamente.";
        }
    }

    // If there are errors, store them in session
    $_SESSION['registro_errors'] = $errors;
    header("Location: registro.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto JC Motors - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/registro.css">
    <link rel="stylesheet" href="../public/transiciones.css">
    <script src="../public/transiciones.js"></script>
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    // Retrieve stored errors and form data
    $errors = isset($_SESSION['registro_errors']) ? $_SESSION['registro_errors'] : [];
    $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
    
    // Clear stored errors and form data
    unset($_SESSION['registro_errors']);
    unset($_SESSION['form_data']);
    ?>

    <div class="login-container">
        <div class="left-panel">
            <div class="right-content">
                <div class="session-title">Registro</div>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-container">
                        <?php foreach ($errors as $error): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="registro.php" method="POST" id="register-form">
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-person"></i></span>
                        <input type="text" class="input-field" placeholder="Nombre completo" name="nombre" required 
                               value="<?php echo isset($form_data['nombre']) ? htmlspecialchars($form_data['nombre']) : ''; ?>">
                    </div>
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="input-field" placeholder="Correo electrónico" name="email" required
                               value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                    </div>
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="input-field" placeholder="Usuario" name="usuario" required
                               value="<?php echo isset($form_data['usuario']) ? htmlspecialchars($form_data['usuario']) : ''; ?>">
                    </div>
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="input-field" placeholder="Teléfono" name="telefono"
                            value="<?php echo isset($form_data['telefono']) ? htmlspecialchars($form_data['telefono']) : ''; ?>">
                    </div>
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-card-text"></i></span>
                        <input type="text" class="input-field" placeholder="Documento de Identidad" name="documento_identidad" required
                            value="<?php echo isset($form_data['documento_identidad']) ? htmlspecialchars($form_data['documento_identidad']) : ''; ?>">
                    </div>

                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" class="input-field" placeholder="Contraseña" name="password" id="password" required>
                        <span class="eye-icon" id="eye-icon"><i class="bi bi-eye"></i></span>
                    </div>
                    
                    <div class="forgot-password"><a href="./login.php" id="to-login-link">¿Ya tienes cuenta?</a></div>
                    <button type="submit" class="login-btn">Registrarse</button>
                </form>
            </div>
        </div>

        <div class="right-panel">
            <div class="decorative-line line-1"></div>
            <div class="decorative-line line-2"></div>
            <div class="left-content">
                <img src="/RepoProyectoSistemas2-JCAutoMotors/public/logo.png" alt="Auto JC Motors Logo">
                <div class="welcome-text">Bienvenido</div>
                <div class="no-account"><a href="./login.php" id="to-login-link">¿Ya tienes cuenta?</a></div>
                <button class="register-btn" id="to-login-btn">Iniciar Sesión</button>
            </div>
        </div>
    </div>
<script>
    document.getElementById('eye-icon').addEventListener('click', function() {
    var passwordField = document.getElementById('password');
    var eyeIcon = document.getElementById('eye-icon');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.innerHTML = '<i class="bi bi-eye-slash"></i>'; // Cambia a icono de ojo cerrado
    } else {
        passwordField.type = "password";
        eyeIcon.innerHTML = '<i class="bi bi-eye"></i>'; // Vuelve al icono de ojo abierto
    }
});
</script>
</body>
</html>