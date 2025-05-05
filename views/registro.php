<?php 
session_start(); 
require_once '../config/conexion.php';

function sanitizeInput($input) { 
    $input = trim($input); 
    $input = stripslashes($input); 
    $input = htmlspecialchars($input); 
    return $input; 
} 

function isSecurePassword($password) {
    if (strlen($password) < 8) {
        return "La contraseña debe tener al menos 8 caracteres";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return "La contraseña debe contener al menos una letra mayúscula";
    }

    if (!preg_match('/[a-z]/', $password)) {
        return "La contraseña debe contener al menos una letra minúscula";
    }

    if (!preg_match('/[0-9]/', $password)) {
        return "La contraseña debe contener al menos un número";
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return "La contraseña debe contener al menos un carácter especial";
    }
    
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $errors = [];
    $form_data = [];

    $required_fields = ['nombre', 'email', 'usuario', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . ucfirst(str_replace('_', ' ', $field)) . " es obligatorio";
        } else {
            $form_data[$field] = sanitizeInput($_POST[$field]);
        }
    }
    
    if (empty($errors)) {
        // Validar formato de email
        if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del correo electrónico no es válido";
        }
        
        if (strlen($form_data['usuario']) < 4) {
            $errors[] = "El nombre de usuario debe tener al menos 4 caracteres";
        }
        
        $password_validation = isSecurePassword($form_data['password']);
        if ($password_validation !== true) {
            $errors[] = $password_validation;
        }
        
        if ($form_data['password'] !== $form_data['confirm_password']) {
            $errors[] = "Las contraseñas no coinciden";
        }

        $sql = "SELECT COUNT(*) FROM PERSONA WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $form_data['email']);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errors[] = "El correo electrónico ya está registrado";
        }

        $sql = "SELECT COUNT(*) FROM USUARIO WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $form_data['usuario']);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errors[] = "El nombre de usuario ya está en uso";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['registro_errors'] = $errors;
        $_SESSION['form_data'] = $form_data;
        header("Location: registro.php");
        exit;
    } else {
        try {
            $conn->exec('BEGIN');
            
            $nombre_completo = explode(' ', trim($form_data['nombre']), 2);
            $nombre = $nombre_completo[0];
            $apellido = isset($nombre_completo[1]) ? $nombre_completo[1] : '';
            
            $hashed_password = hash('sha256', $form_data['password']);
            
            $sql_persona = "INSERT INTO PERSONA (_id, nombre, apellido, telefono, email, documento_identidad) 
            VALUES (NULL, ?, ?, '', ?, NULL)";
        
            $stmt_persona = $conn->prepare($sql_persona);
            $stmt_persona->bindParam(1, $nombre);
            $stmt_persona->bindParam(2, $apellido);
            $stmt_persona->bindParam(3, $form_data['email']);
            $stmt_persona->execute();
        
            $id_persona = $conn->lastInsertId();

            $sql_usuario = "INSERT INTO USUARIO (_id, id_persona, usuario, password, id_rol) 
            VALUES (NULL, ?, ?, ?, 3)";
        
            $stmt_usuario = $conn->prepare($sql_usuario);
            $stmt_usuario->bindParam(1, $id_persona);
            $stmt_usuario->bindParam(2, $form_data['usuario']);
            $stmt_usuario->bindParam(3, $hashed_password);
            $stmt_usuario->execute();
            
            // Commit the transaction
            $conn->exec('COMMIT');

            $_SESSION['registro_exitoso'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
            header("Location: login.php");
            exit;
            
        } catch (Exception $e) {
            $conn->exec('ROLLBACK');

            $_SESSION['registro_errors'] = ["Error al registrar el usuario: " . $e->getMessage()];
            $_SESSION['form_data'] = $form_data;
            header("Location: registro.php");
            exit;
        }
    }
} 
?>
<!DOCTYPE html> 
<html lang="es"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Auto JC Motors - Registro</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> 
    <link rel="stylesheet" href="../public/css/registro.css"> 
    <link rel="stylesheet" href="../public/css/transiciones.css"> 
    <script src="../public/js/transiciones.js"></script> 
</head>
<body>
<?php 
$errors = isset($_SESSION['registro_errors']) ? $_SESSION['registro_errors'] : []; 
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
unset($_SESSION['registro_errors']); 
unset($_SESSION['form_data']); 
?> 

<?php if (!empty($errors)): ?>
    <div class="alert-container" id="alertContainer">
        <?php foreach ($errors as $index => $error): ?>
            <div class="alert-tailwind" id="errorAlert-<?php echo $index; ?>">
                <div class="alert-tailwind-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-tailwind-content">
                    <p class="alert-tailwind-title">Error</p>
                    <p class="alert-tailwind-message"><?php echo htmlspecialchars($error); ?></p>
                </div>
                <button type="button" class="alert-tailwind-close" onclick="closeAlert('errorAlert-<?php echo $index; ?>')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="login-container">
        <div class="left-panel">
            <div class="right-content">
                <div class="session-title">Registro</div>
                
                <form action="registro.php" method="POST" id="register-form" novalidate>
                    <div class="input-container" id="nombre-container">
                        <span class="input-icon"><i class="bi bi-person"></i></span>
                        <input type="text" class="input-field" placeholder="Nombre completo" name="nombre" id="nombre" required 
                               value="<?php echo isset($form_data['nombre']) ? htmlspecialchars($form_data['nombre']) : ''; ?>">
                        <div class="error-tooltip" id="nombre-error"></div>
                    </div>
                    
                    <div class="input-container" id="email-container">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="input-field" placeholder="Correo electrónico" name="email" id="email" required
                               value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                        <div class="error-tooltip" id="email-error"></div>
                    </div>
                    
                    <div class="input-container" id="usuario-container">
                        <span class="input-icon"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="input-field" placeholder="Usuario" name="usuario" id="usuario" required
                               value="<?php echo isset($form_data['usuario']) ? htmlspecialchars($form_data['usuario']) : ''; ?>">
                        <div class="error-tooltip" id="usuario-error"></div>
                    </div>
                    
                    <div class="input-container" id="password-container" style="margin-bottom: 0;">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" class="input-field" placeholder="Contraseña" name="password" id="password" required>
                        <span class="eye-icon" id="eye-icon-password"><i class="bi bi-eye"></i></span>
                        <div class="error-tooltip" id="password-error"></div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="password-strength-meter"></div>
                        </div>
                    </div>
                    <div class="password-strength-container">
                        <div class="password-requirements" style="margin-top: 8px; margin-left: 15px;">
                            <div class="requirement" id="req-length" style="margin-bottom: 3px;">
                                <i class="bi bi-x-circle"></i> Mínimo 8 caracteres
                            </div>
                            <div class="requirement" id="req-uppercase" style="margin-bottom: 3px;">
                                <i class="bi bi-x-circle"></i> Al menos una mayúscula
                            </div>
                            <div class="requirement" id="req-lowercase" style="margin-bottom: 3px;">
                                <i class="bi bi-x-circle"></i> Al menos una minúscula
                            </div>
                            <div class="requirement" id="req-number" style="margin-bottom: 3px;">
                                <i class="bi bi-x-circle"></i> Al menos un número
                            </div>
                            <div class="requirement" id="req-special" style="margin-bottom: 3px;">
                                <i class="bi bi-x-circle"></i> Al menos un carácter especial
                            </div>
                            <br>
                        </div>
                    </div>
                    <div class="input-container" id="confirm-password-container">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" class="input-field" placeholder="Confirmar Contraseña" name="confirm_password" id="confirm_password" required>
                        <span class="eye-icon" id="eye-icon-confirm"><i class="bi bi-eye"></i></span>
                        <div class="error-tooltip" id="confirm-password-error"></div>
                    </div>
                    
                    <button type="submit" class="login-btn" id="submit-btn">Registrarse</button>
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
                <button class="register-btn" id="to-login-btn" onclick="window.location.href='login.php'">Iniciar Sesión</button>
            </div>
        </div>
    </div>
    <script src="../public/js/registro.js"></script> 
</body>
</html>