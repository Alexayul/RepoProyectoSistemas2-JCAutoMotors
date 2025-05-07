<?php 
session_start(); 

$errors = isset($_SESSION['registro_errors']) ? $_SESSION['registro_errors'] : []; 
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
unset($_SESSION['registro_errors']); 
unset($_SESSION['form_data']); 
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
                
                <form action="../routes/registro.route.php" method="POST" id="register-form" novalidate>
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
