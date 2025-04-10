<?php 
session_start(); 
require_once '../config/conexion.php';

function sanitizeInput($input) { 
    $input = trim($input); 
    $input = stripslashes($input); 
    $input = htmlspecialchars($input); 
    return $input; 
} 

// Función para validar contraseña segura
function isSecurePassword($password) {
    // Mínimo 8 caracteres
    if (strlen($password) < 8) {
        return "La contraseña debe tener al menos 8 caracteres";
    }
    
    // Debe contener al menos una letra mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        return "La contraseña debe contener al menos una letra mayúscula";
    }
    
    // Debe contener al menos una letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        return "La contraseña debe contener al menos una letra minúscula";
    }
    
    // Debe contener al menos un número
    if (!preg_match('/[0-9]/', $password)) {
        return "La contraseña debe contener al menos un número";
    }
    
    // Debe contener al menos un carácter especial
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return "La contraseña debe contener al menos un carácter especial";
    }
    
    return true; // La contraseña cumple con todos los requisitos
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $errors = [];
    $form_data = [];
    
    // Validar campos obligatorios
    $required_fields = ['nombre', 'email', 'usuario', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . ucfirst(str_replace('_', ' ', $field)) . " es obligatorio";
        } else {
            $form_data[$field] = sanitizeInput($_POST[$field]);
        }
    }
    
    // Si no hay errores de campos obligatorios, procedemos con otras validaciones
    if (empty($errors)) {
        // Validar formato de email
        if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del correo electrónico no es válido";
        }
        
        // Validar longitud de nombre de usuario
        if (strlen($form_data['usuario']) < 4) {
            $errors[] = "El nombre de usuario debe tener al menos 4 caracteres";
        }
        
        // Validar contraseña segura
        $password_validation = isSecurePassword($form_data['password']);
        if ($password_validation !== true) {
            $errors[] = $password_validation;
        }
        
        // Validar coincidencia de contraseñas
        if ($form_data['password'] !== $form_data['confirm_password']) {
            $errors[] = "Las contraseñas no coinciden";
        }
        
        // Aquí puedes agregar validaciones adicionales como verificar si el usuario o email ya existen
        // Por ejemplo:
        /*
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $form_data['email']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors[] = "El correo electrónico ya está registrado";
        }
        
        $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $form_data['usuario']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors[] = "El nombre de usuario ya está en uso";
        }
        */
    }
    
    // Si hay errores, guardarlos en la sesión y redirigir
    if (!empty($errors)) {
        $_SESSION['registro_errors'] = $errors;
        $_SESSION['form_data'] = $form_data;
        header("Location: registro.php");
        exit;
    } else {
        // Procesar el registro si no hay errores
        // Aquí iría tu código para insertar el usuario en la base de datos
        
        // Después del registro exitoso, redirigir a la página de inicio de sesión
        $_SESSION['registro_exitoso'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
        header("Location: login.php");
        exit;
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
    <link rel="stylesheet" href="../public/registro.css"> 
    <link rel="stylesheet" href="../public/transiciones.css"> 
    <script src="../public/transiciones.js"></script> 
    <style> 
        /* Estilo Tailwind para alertas */
        .alert-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            max-width: 24rem;
        }
        
        .alert-tailwind {
            display: flex;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            align-items: center;
            overflow: hidden;
            animation: slideIn 0.5s forwards;
        }
        
        .alert-tailwind-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            background-color: #FEE2E2;
            border-radius: 0.375rem;
            margin-right: 0.75rem;
        }
        
        .alert-tailwind-icon i {
            font-size: 1.25rem;
            color: #DC2626;
        }
        
        .alert-tailwind-content {
            flex-grow: 1;
        }
        
        .alert-tailwind-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #1F2937;
            margin: 0;
        }
        
        .alert-tailwind-message {
            font-size: 0.875rem;
            color: #6B7280;
            margin: 0.25rem 0 0 0;
        }
        
        .alert-tailwind-close {
            flex-shrink: 0;
            margin-left: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            font-size: 1.25rem;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .alert-tailwind-close:hover {
            color: #4B5563;
        }
        
        /* Animaciones */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        /* Estilos para campos con error */
        .input-container.error input {
            border-color: #DC2626;
            background-color: #FEF2F2;
        }
        
        .error-tooltip {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            font-size: 0.75rem;
            color: #DC2626;
            margin-top: 0.25rem;
            display: none;
        }
        
        .input-container.error .error-tooltip {
            display: block;
        }
        
        /* Indicador de fortaleza de contraseña */
        .password-strength {
            height: 5px;
            width: 100%;
            background-color:rgb(23, 23, 23);
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0%;
            border-radius: 3px;
        }
        
        .password-requirements {
            margin-top: 0;
            font-size: 0.75rem;
            color: #6B7280;
            line-height: 1.2; /* Reduce el espacio entre líneas */
        }
                #password {
            background-color: transparent !important; /* O el color que deseas mantener */
        }

        #password:focus {
            background-color: transparent !important;
        }

        #password:active, #password:hover {
            background-color: transparent !important;
        }
        /* Estilo base para el campo de confirmación */
        #confirm_password {
            background-color: transparent !important;
        }

        /* Estilo al enfocar (focus) */
        #confirm_password:focus {
            background-color: transparent !important;
        }

        /* Estilo al interactuar (hover/active) */
        #confirm_password:hover,
        #confirm_password:active {
            background-color: transparent !important;
        }

        .requirement {
            display: flex;
            align-items: center;
        }

        .requirement i {
            margin-right: 4px; /* Reduce el espacio entre el ícono y el texto */
            font-size: 0.8rem; /* Ajusta el tamaño del ícono para que coincida con el texto */
        }
        
        .requirement.valid {
            color: #059669;
        }
        
        .requirement.invalid {
            color: #9CA3AF;
        }
        @media (max-width: 768px) {
            .alert-container {
                left: 1rem;
                right: 1rem;
                max-width: none;
            }
        }
    </style> 
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

    <script>
        // Función para cerrar alertas
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.animation = 'slideOut 0.5s forwards';
                setTimeout(() => alert.remove(), 500);
                
                // Verificar si quedan alertas
                setTimeout(() => {
                    const container = document.getElementById('alertContainer');
                    if (container && container.children.length === 0) {
                        container.remove();
                    }
                }, 600);
            }
        }
        
        // Cerrar alertas automáticamente después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-tailwind');
            let delay = 0;
            
            alerts.forEach((alert) => {
                setTimeout(() => {
                    if (alert && alert.id) {
                        closeAlert(alert.id);
                    }
                }, 5000 + delay);
                delay += 500; // Escalonar el cierre de múltiples alertas
            });
            
            // Inicializar validaciones
            initializeFormValidation();
        });
        
        // Función para alternar la visibilidad de la contraseña
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<i class="bi bi-eye"></i>';
            }
        }

        // Event listeners para los iconos de ojo
        document.getElementById('eye-icon-password').addEventListener('click', function() {
            togglePasswordVisibility('password', 'eye-icon-password');
        });

        document.getElementById('eye-icon-confirm').addEventListener('click', function() {
            togglePasswordVisibility('confirm_password', 'eye-icon-confirm');
        });
        
        // Función para mostrar un mensaje de error en un campo específico
        function showError(fieldId, message) {
            const container = document.getElementById(fieldId + '-container');
            const errorElement = document.getElementById(fieldId + '-error');
            
            if (container && errorElement) {
                container.classList.add('error');
                errorElement.textContent = message;
            }
        }
        
        // Función para limpiar el error de un campo
        function clearError(fieldId) {
            const container = document.getElementById(fieldId + '-container');
            const errorElement = document.getElementById(fieldId + '-error');
            
            if (container && errorElement) {
                container.classList.remove('error');
                errorElement.textContent = '';
            }
        }
        
        // Función para mostrar alerta de error
        function showAlertError(message) {
            const container = document.getElementById('alertContainer') || createAlertContainer();
            const alertId = 'errorAlert-' + Date.now();
            
            const alertElement = document.createElement('div');
            alertElement.id = alertId;
            alertElement.className = 'alert-tailwind';
            alertElement.innerHTML = `
                <div class="alert-tailwind-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-tailwind-content">
                    <p class="alert-tailwind-title">Error</p>
                    <p class="alert-tailwind-message">${message}</p>
                </div>
                <button type="button" class="alert-tailwind-close" onclick="closeAlert('${alertId}')">
                    <i class="bi bi-x"></i>
                </button>
            `;
            
            container.appendChild(alertElement);
            
            // Cerrar automáticamente después de 5 segundos
            setTimeout(() => closeAlert(alertId), 5000);
        }
        
        // Función auxiliar para crear el contenedor de alertas si no existe
        function createAlertContainer() {
            const container = document.createElement('div');
            container.id = 'alertContainer';
            container.className = 'alert-container';
            document.body.appendChild(container);
            return container;
        }
        
        // Verificar requisitos de contraseña y actualizar indicadores
        function checkPasswordRequirements(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // Actualizar indicadores visuales
            updateRequirementUI('req-length', requirements.length);
            updateRequirementUI('req-uppercase', requirements.uppercase);
            updateRequirementUI('req-lowercase', requirements.lowercase);
            updateRequirementUI('req-number', requirements.number);
            updateRequirementUI('req-special', requirements.special);
            
            // Calcular puntuación de fortaleza (0-5)
            const strength = Object.values(requirements).filter(Boolean).length;
            
            // Actualizar medidor de fortaleza
            const meter = document.getElementById('password-strength-meter');
            meter.style.width = `${strength * 20}%`;
            
            // Asignar color según fortaleza
            if (strength <= 2) {
                meter.style.backgroundColor = '#EF4444'; // Rojo - débil
            } else if (strength <= 3) {
                meter.style.backgroundColor = '#F59E0B'; // Ámbar - medio
            } else if (strength <= 4) {
                meter.style.backgroundColor = '#10B981'; // Verde - fuerte
            } else {
                meter.style.backgroundColor = '#059669'; // Verde oscuro - muy fuerte
            }
            
            return requirements;
        }
        
        // Actualizar UI de requisitos
        function updateRequirementUI(reqId, isValid) {
            const reqElement = document.getElementById(reqId);
            if (reqElement) {
                if (isValid) {
                    reqElement.classList.add('valid');
                    reqElement.classList.remove('invalid');
                    reqElement.querySelector('i').className = 'bi bi-check-circle';
                } else {
                    reqElement.classList.add('invalid');
                    reqElement.classList.remove('valid');
                    reqElement.querySelector('i').className = 'bi bi-x-circle';
                }
            }
        }
        
        // Inicializar validaciones de formulario
        function initializeFormValidation() {
            const form = document.getElementById('register-form');
            
            // Validación para el campo nombre
            const nombreInput = document.getElementById('nombre');
            nombreInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError('nombre', 'El nombre completo es obligatorio');
                } else {
                    clearError('nombre');
                }
            });
            
            // Validación para el campo email
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError('email', 'El correo electrónico es obligatorio');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    showError('email', 'El formato del correo electrónico no es válido');
                } else {
                    clearError('email');
                }
            });
            
            // Validación para el campo usuario
            const usuarioInput = document.getElementById('usuario');
            usuarioInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError('usuario', 'El nombre de usuario es obligatorio');
                } else if (this.value.length < 4) {
                    showError('usuario', 'El nombre de usuario debe tener al menos 4 caracteres');
                } else {
                    clearError('usuario');
                }
            });
            
            // Validación para la contraseña
            const passwordInput = document.getElementById('password');
            passwordInput.addEventListener('input', function() {
                const requirements = checkPasswordRequirements(this.value);
                const allValid = Object.values(requirements).every(Boolean);
                
                if (!this.value) {
                    showError('password', 'La contraseña es obligatoria');
                } else if (!allValid) {
                    showError('password', 'La contraseña no cumple con todos los requisitos');
                } else {
                    clearError('password');
                }
                
                // Validar coincidencia si confirm_password tiene valor
                const confirmPassword = document.getElementById('confirm_password');
                if (confirmPassword.value) {
                    validatePasswordMatch();
                }
            });
            
            // Validación para confirmación de contraseña
            const confirmPasswordInput = document.getElementById('confirm_password');
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
            
            function validatePasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (!confirmPassword) {
                    showError('confirm-password', 'Debe confirmar su contraseña');
                } else if (password !== confirmPassword) {
                    showError('confirm-password', 'Las contraseñas no coinciden');
                } else {
                    clearError('confirm-password');
                }
            }
            
            // Validación del formulario completo al enviar
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validar todos los campos
                let isValid = true;
                
                // Nombre
                if (!nombreInput.value.trim()) {
                    showError('nombre', 'El nombre completo es obligatorio');
                    isValid = false;
                }
                
                // Email
                if (!emailInput.value.trim()) {
                    showError('email', 'El correo electrónico es obligatorio');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                    showError('email', 'El formato del correo electrónico no es válido');
                    isValid = false;
                }
                
                // Usuario
                if (!usuarioInput.value.trim()) {
                    showError('usuario', 'El nombre de usuario es obligatorio');
                    isValid = false;
                } else if (usuarioInput.value.length < 4) {
                    showError('usuario', 'El nombre de usuario debe tener al menos 4 caracteres');
                    isValid = false;
                }
                
                // Contraseña
                if (!passwordInput.value) {
                    showError('password', 'La contraseña es obligatoria');
                    isValid = false;
                } else {
                    const requirements = checkPasswordRequirements(passwordInput.value);
                    const allValid = Object.values(requirements).every(Boolean);
                    
                    if (!allValid) {
                        showError('password', 'La contraseña no cumple con todos los requisitos');
                        isValid = false;
                    }
                }
                
                // Confirmar contraseña
                if (!confirmPasswordInput.value) {
                    showError('confirm-password', 'Debe confirmar su contraseña');
                    isValid = false;
                } else if (passwordInput.value !== confirmPasswordInput.value) {
                    showError('confirm-password', 'Las contraseñas no coinciden');
                    isValid = false;
                }
                
                // Si el formulario es válido, enviarlo
                if (isValid) {
                    this.submit();
                } else {
                    // Mostrar alerta general
                    showAlertError('Por favor, corrija los errores en el formulario antes de continuar');
                    
                    // Hacer scroll al primer error
                    const firstErrorField = document.querySelector('.input-container.error');
                    if (firstErrorField) {
                        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
    </script>
</body>
</html>