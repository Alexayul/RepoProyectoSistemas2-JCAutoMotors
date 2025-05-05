<?php
session_start();

$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
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
    <link rel="stylesheet" href="../public/css/login.css"> 
    <link rel="stylesheet" href="../public/css/transiciones.css"> 
    <script src="../public/js/transiciones.js"></script> 
    <style>
        
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
    <script src="../public/js/login.js"></script> 
</body>
</html>