<?php
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada</title>
    <style>
        :root {
            --primary: #a51314;        /* Rojo principal */
            --primary-dark: #701106;   /* Rojo oscuro */
            --primary-light: #e65657;  /* Rojo claro para hover y efectos */
            --dark: #050506;           /* Negro */
            --dark-gray: #1a1a1a;      /* Gris oscuro */
            --medium-gray: #333333;    /* Gris medio */
            --light: #f7f7f7;          /* Blanco */
            --success: #28a745;        /* Verde para estados completados */
            --warning: #ffc107;        /* Amarillo para estados pendientes */
            --info: #17a2b8;           /* Azul para info */
            --danger: #dc3545;         /* Rojo para alertas */
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

        /* Animación de moto perdida */
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

        .moto {
            position: absolute;
            width: 120px;
            height: 80px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%23a51314" d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V43.6C119 55.3 32 150.2 32 265.9V448c0 35.3 28.7 64 64 64H416c35.3 0 64-28.7 64-64V265.9c0-115.7-87-210.6-192-222.3V32zm0 96c0-17.7-14.3-32-32-32s-32 14.3-32 32v32c17.7 0 32 14.3 32 32s-14.3 32-32 32V288c0 17.7 14.3 32 32 32s32-14.3 32-32V224c53 0 96-43 96-96s-43-96-96-96V128z"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            animation: lostDrive 25s linear infinite;
            opacity: 0.7;
        }

        .moto:nth-child(2) {
            width: 80px;
            height: 50px;
            animation: lostDrive 30s linear infinite reverse;
            top: 70%;
            opacity: 0.5;
        }

        .moto:nth-child(3) {
            width: 60px;
            height: 40px;
            animation: lostDrive 20s linear infinite;
            top: 30%;
            opacity: 0.4;
        }

        @keyframes lostDrive {
            0% {
                transform: translate(-150px, -150px) rotate(0deg);
            }
            25% {
                transform: translate(calc(50vw - 60px), calc(50vh - 40px)) rotate(90deg);
            }
            50% {
                transform: translate(calc(100vw + 150px), calc(100vh + 150px)) rotate(180deg);
            }
            75% {
                transform: translate(calc(50vw - 60px), calc(50vh - 40px)) rotate(270deg);
            }
            100% {
                transform: translate(-150px, -150px) rotate(360deg);
            }
        }

        /* Señal de callejón sin salida */
        .dead-end {
            position: absolute;
            width: 100px;
            height: 100px;
            background-color: var(--light);
            border: 5px solid var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            animation: pulse 2s infinite;
        }

        .dead-end svg {
            width: 80%;
            height: 80%;
        }

        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                box-shadow: 0 0 0 rgba(165, 19, 20, 0.4);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.05);
                box-shadow: 0 0 20px rgba(165, 19, 20, 0.8);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efecto de neumático quemado */
        .skid-mark {
            position: absolute;
            width: 200px;
            height: 20px;
            background: linear-gradient(90deg, rgba(0,0,0,0.8), transparent);
            transform: rotate(5deg);
            opacity: 0;
            animation: skid 1s ease-out forwards;
        }

        @keyframes skid {
            0% {
                width: 0;
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            100% {
                width: 200px;
                opacity: 0;
            }
        }

        /* Efecto de mapa */
        .map {
            position: absolute;
            width: 300px;
            height: 300px;
            border: 3px dashed var(--primary-light);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="moto-container">
        <div class="map"></div>
        <div class="dead-end">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="var(--primary)" d="M18 1.83L16.17 0 12 4.17 7.83 0 6 1.83 10.17 6 6 10.17 7.83 12 12 7.83 16.17 12 18 10.17 13.83 6 18 1.83zM18 12l2.83 2.83L18 17.67 15.33 15l-2.83 2.83L12 18l2.83-2.83L12 12.33 14.67 15l2.83-2.83L18 12zM6 18l-2.83-2.83L6 12.33 8.67 15l2.83-2.83L12 12 9.17 14.83 12 17.67 9.33 15 6 18z"/>
            </svg>
        </div>
        <div class="moto"></div>
        <div class="moto"></div>
        <div class="moto"></div>
    </div>

   <div class="error-box">
    <h1>404</h1>
    <h2>Página no encontrada</h2>
    <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
    <p><small>Por favor, verifica la URL o navega desde nuestra página principal.</small></p>
    <a href="/RepoProyectoSistemas2-JCAutoMotors/" class="btn">Ir a la página principal</a>
</div>
    <script>
        // Efecto de neumático quemado aleatorio
        setInterval(() => {
            const skid = document.createElement('div');
            skid.className = 'skid-mark';
            skid.style.top = Math.random() * 80 + 10 + '%';
            skid.style.left = Math.random() * 80 + 10 + '%';
            document.body.appendChild(skid);
            
            // Eliminar después de la animación
            setTimeout(() => {
                skid.remove();
            }, 1000);
        }, 2000);

        // Crear motos adicionales dinámicamente
        for (let i = 0; i < 2; i++) {
            setTimeout(() => {
                const moto = document.createElement('div');
                moto.className = 'moto';
                moto.style.width = (Math.random() * 60 + 40) + 'px';
                moto.style.height = (Math.random() * 40 + 30) + 'px';
                moto.style.opacity = Math.random() * 0.5 + 0.3;
                moto.style.animationDuration = (Math.random() * 20 + 15) + 's';
                moto.style.top = Math.random() * 80 + 10 + '%';
                document.querySelector('.moto-container').appendChild(moto);
            }, i * 3000);
        }
    </script>
</body>
</html>