<?php
session_start();

// Obtener mensaje de error si existe
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
// Limpiar mensaje de error para que no persista en futuras cargas
if (isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
}

require_once '../config/conexion.php';

function loginError($mensaje = "Usuario o contraseña incorrectos") {
    $_SESSION['login_error'] = $mensaje;
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM USUARIO WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $dbPassword = $user['password'];

            $hashedInputPassword = hash('sha256',$password);

            if ($hashedInputPassword === $dbPassword) {
                iniciarSesion($user);
            } else {
                loginError();
            }
        } else {
            loginError();
        }
    } catch(PDOException $e) {
        loginError("Error en la base de datos: " . $e->getMessage());
    }
}

function iniciarSesion($user) {
    $_SESSION['user'] = [
        'id' => $user['_id'],
        'usuario' => $user['usuario'],
        'nombre' => $user['nombre'],
        'apellido' => $user['apellido'],
        'rol_id' => $user['id_rol'],
        'rol_nombre' => $user['rol_nombre']
    ];

    switch ($user['id_rol']) {
        case 1: header("Location: admin.php"); break;
        case 2: header("Location: empleado.php"); break;
        case 3: header("Location: ../index.php"); break;
        default: header("Location: ../index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto JC Motors - Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> 
    <link rel="stylesheet" href="../public/login.css"> 
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

        /* Variante de error */
        .alert-tailwind-error .alert-tailwind-icon {
            background-color: #FEE2E2;
        }

        .alert-tailwind-error .alert-tailwind-icon i {
            color: #DC2626;
        }

        /* Variante de éxito */
        .alert-tailwind-success .alert-tailwind-icon {
            background-color: #D1FAE5;
        }

        .alert-tailwind-success .alert-tailwind-icon i {
            color: #059669;
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

        /* Responsive */
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
<?php if (!empty($login_error)): ?>
    <div class="alert-container">
        <div class="alert-tailwind" id="errorAlert">
            <div class="alert-tailwind-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-tailwind-content">
                <p class="alert-tailwind-title">Error</p>
                <p class="alert-tailwind-message"><?php echo htmlspecialchars($login_error); ?></p>
            </div>
            <button type="button" class="alert-tailwind-close" onclick="closeAlert()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

    <div class="login-container">
        <div class="left-panel">
            <div class="left-content">
                <img src="../public/logo.png" alt="Auto JC Motors Logo">
                <div class="welcome-text">Bienvenido</div>
                <div class="no-account"><a href="registro.php">¿Aún no tienes cuenta?</a></div>
                <button class="register-btn" id="to-register-btn">Registrarse</button>
            </div>
        </div>
        <div class="right-panel">
            <div class="right-content">
                <div class="session-title">Inicio de Sesión</div>
                <form action="login.php" method="POST" autocomplete="off">
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-person"></i></span>
                        <input type="text" class="input-field" placeholder="Usuario" name="usuario" required>
                    </div>
                    <div class="input-container">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" class="input-field" placeholder="Contraseña" name="password" id="password" required>
                        <span class="eye-icon" id="eye-icon"><i class="bi bi-eye"></i></span>
                    </div>
                    
                    <div class="forgot-password"><a href="../index.php">Entrar como invitado</a></div>
                    <div class="forgot-password"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></div>
                    <button type="submit" class="login-btn">Iniciar sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle para mostrar/ocultar contraseña
        document.getElementById('eye-icon').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });

        // Cerrar alerta automáticamente después de 5 segundos
        setTimeout(() => {
            const alert = document.getElementById('errorAlert');
            if (alert) {
                alert.style.animation = 'slideOut 0.5s forwards';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
        
        // Función para cerrar manualmente
        function closeAlert() {
            const alert = document.getElementById('errorAlert');
            if (alert) {
                alert.style.animation = 'slideOut 0.5s forwards';
                setTimeout(() => alert.remove(), 500);
            }
        }
        
        // Redirección al registro
        document.getElementById('to-register-btn').addEventListener('click', function() {
            window.location.href = 'registro.php';
        });
        function closeAlert() {
    
            const alert = document.getElementById('errorAlert');
    if (alert) {
        alert.style.animation = 'slideOut 0.5s forwards';
        setTimeout(() => alert.remove(), 500);
    }
}
    </script>
</body>
</html>