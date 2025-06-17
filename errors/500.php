<?php
http_response_code(500);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>500 - Error del servidor</title>
    <style>
        :root {
            --primary: #a51314;        /* Rojo principal */
            --primary-dark: #701106;   /* Rojo oscuro */
            --primary-light: #e65657;  /* Rojo claro para hover y efectos */
            --dark: #050506;           /* Negro */
            --dark-gray: #1a1a1a;      /* Gris oscuro */
            --medium-gray: #333333;    /* Gris medio */
            --light: #f7f7f7;          /* Blanco */
            --warning: #ffc107;        /* Amarillo para advertencias */
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --hover-transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Arial', sans-serif;
            background: var(--dark-gray);
            color: var(--light);
            text-align: center;
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-image: radial-gradient(circle at 10% 20%, var(--dark) 0%, var(--dark-gray) 90%);
            overflow: hidden;
        }

        .error-box {
            background: var(--medium-gray);
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            position: relative;
            z-index: 2;
            border: 1px solid var(--primary);
            animation: fadeIn 0.8s ease-out;
        }

        h1 {
            color: var(--primary-light);
            font-size: 4em;
            margin-bottom: 10px;
            text-shadow: 0 0 10px rgba(229, 86, 87, 0.5);
        }

        h2 {
            color: var(--light);
            margin-top: 0;
            font-size: 1.8em;
        }

        p {
            margin: 15px 0;
            font-size: 1.1em;
        }

        small {
            color: #aaa;
            font-size: 0.9em;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 30px;
            margin-top: 25px;
            transition: var(--hover-transition);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid var(--primary);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        /* Animación de moto descompuesta */
        .moto-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .broken-moto {
            position: absolute;
            width: 120px;
            height: 80px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%23a51314" d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V43.6C119 55.3 32 150.2 32 265.9V448c0 35.3 28.7 64 64 64H416c35.3 0 64-28.7 64-64V265.9c0-115.7-87-210.6-192-222.3V32zm0 96c0-17.7-14.3-32-32-32s-32 14.3-32 32v32c17.7 0 32 14.3 32 32s-14.3 32-32 32V288c0 17.7 14.3 32 32 32s32-14.3 32-32V224c53 0 96-43 96-96s-43-96-96-96V128z"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.7;
            animation: brokenEngine 2s infinite;
        }

        .tool {
            position: absolute;
            width: 30px;
            height: 30px;
            background-size: contain;
            background-repeat: no-repeat;
            animation: floatTool 4s ease-in-out infinite;
        }

        .wrench {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffc107'%3E%3Cpath d='M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.5 7.1 1 10.1 3 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z'/%3E%3C/svg%3E");
            top: 40%;
            left: 40%;
            animation-delay: 0.5s;
        }

        .screwdriver {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffc107'%3E%3Cpath d='M18 1.83L16.17 0 12 4.17 7.83 0 6 1.83 10.17 6 6 10.17 7.83 12 12 7.83 16.17 12 18 10.17 13.83 6 18 1.83zM18 12l2.83 2.83L18 17.67 15.33 15l-2.83 2.83L12 18l2.83-2.83L12 12.33 14.67 15l2.83-2.83L18 12zM6 18l-2.83-2.83L6 12.33 8.67 15l2.83-2.83L12 12 9.17 14.83 12 17.67 9.33 15 6 18z'/%3E%3C/svg%3E");
            top: 60%;
            left: 60%;
            animation-delay: 1s;
        }

        .warning-sign {
            position: absolute;
            width: 80px;
            height: 80px;
            background-color: var(--warning);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 30%;
            right: 20%;
            animation: pulse 2s infinite;
        }

        .warning-sign svg {
            width: 60%;
            height: 60%;
        }

        @keyframes brokenEngine {
            0%, 100% { transform: translate(-50%, -50%) rotate(-5deg); }
            50% { transform: translate(-50%, -50%) rotate(5deg); }
        }

        @keyframes floatTool {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(10deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="moto-container">
        <div class="broken-moto"></div>
        <div class="tool wrench"></div>
        <div class="tool screwdriver"></div>
        <div class="warning-sign">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="#000" d="M12 2L1 21h22L12 2zm0 3.5L19.5 19h-15L12 5.5z"/>
                <path fill="#000" d="M12 16c.6 0 1-.4 1-1s-.4-1-1-1-1 .4-1 1 .4 1 1 1zm0-4c.6 0 1-.4 1-1V8c0-.6-.4-1-1-1s-1 .4-1 1v3c0 .6.4 1 1 1z"/>
            </svg>
        </div>
    </div>

<div class="error-box">
    <h1>500</h1>
    <h2>Error del servidor</h2>
    <p>Lo sentimos, ha ocurrido un error interno en el servidor.</p>
    <p><small>Nuestro equipo técnico ha sido notificado y está trabajando para solucionarlo.</small></p>
    <a href="/RepoProyectoSistemas2-JCAutoMotors/" class="btn">Volver a la página principal</a>
</div>

    <script>
        // Efecto de herramientas cayendo aleatoriamente
        setInterval(() => {
            const tools = ['wrench', 'screwdriver'];
            const tool = tools[Math.floor(Math.random() * tools.length)];
            
            const newTool = document.createElement('div');
            newTool.className = `tool ${tool}`;
            newTool.style.left = Math.random() * 80 + 10 + '%';
            newTool.style.top = '-30px';
            newTool.style.animation = `fallTool ${Math.random() * 2 + 2}s linear forwards`;
            
            document.querySelector('.moto-container').appendChild(newTool);
            
            setTimeout(() => {
                newTool.remove();
            }, 3000);
        }, 1500);

        // Añadir keyframes para caída de herramientas
        const styleSheet = document.styleSheets[0];
        styleSheet.insertRule(`
            @keyframes fallTool {
                to { transform: translateY(calc(100vh + 30px)) rotate(360deg); }
            }
        `, styleSheet.cssRules.length);
    </script>
</body>
</html>