<?php
session_start();
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto JC Motors - Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/login.css"> 
    <link rel="stylesheet" href="../public/css/transiciones.css"> 
    <script src="../public/js/transiciones.js"></script> 
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
            <form action="../routes/login.route.php" method="POST" autocomplete="off">
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
