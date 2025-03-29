<?php
session_start();
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
        /* Alertas */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
            width: 100%;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s forwards;
        }
        
        .alert-error {
            background-color: #FFEBEE;
            border-left: 4px solid #F44336;
            color: #D32F2F;
        }
        
        .alert-icon {
            margin-right: 15px;
            font-size: 24px;
        }
        
        .alert-message {
            flex-grow: 1;
        }
        
        .alert-close {
            margin-left: 15px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .alert-close:hover {
            opacity: 1;
        }
        
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
            .login-container {
                flex-direction: column;
                height: auto;
                width: 100%;
                max-width: 100%;
            }
            
            .left-panel {
                display: none;
            }
            
            .right-panel {
                padding: 30px;
            }
            
            .alert-container {
                max-width: calc(100% - 40px);
                left: 20px;
                right: auto;
            }
        }
    </style>
</head>
<body>
<?php if (!empty($login_error)): ?>
    <div class="alert-container">
        <div class="alert alert-error" id="errorAlert">
            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
            <div class="alert-message"><?php echo htmlspecialchars($login_error); ?></div>
            <i class="bi bi-x alert-close" onclick="closeAlert()"></i>
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
    </script>
</body>
</html>