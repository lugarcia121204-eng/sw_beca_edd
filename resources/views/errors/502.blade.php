<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>502 - Error de Puerta de Enlace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(27, 57, 106, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            top: -200px;
            left: -200px;
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(27, 57, 106, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.5);
            padding: 80px 60px;
            text-align: center;
            max-width: 650px;
            width: 100%;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1B396A, #f59e0b, #1B396A);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .error-code {
            font-size: 140px;
            font-weight: 900;
            background: linear-gradient(135deg, #1B396A 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 24px;
            position: relative;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .error-code::after {
            content: attr(data-text);
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
            background: linear-gradient(135deg, rgba(27, 57, 106, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(15px);
        }

        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #1B396A;
            margin-bottom: 16px;
        }

        .error-description {
            font-size: 16px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 32px;
            padding: 0 20px;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 32px;
        }

        .info-badge svg {
            flex-shrink: 0;
        }

        .btn-home {
            display: inline-block;
            background: #1B396A;
            color: white;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(27, 57, 106, 0.2);
        }

        .btn-home:hover {
            background: #0f2347;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(27, 57, 106, 0.3);
        }

        .btn-home:active {
            transform: translateY(0);
        }

        .decorative-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.08;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #1B396A, #f59e0b);
            border-radius: 50%;
            top: -50px;
            right: -50px;
            filter: blur(40px);
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            background: #f59e0b;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            bottom: 40px;
            left: -30px;
            filter: blur(30px);
            animation: morphing 10s ease-in-out infinite;
        }

        @keyframes morphing {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
        }

        .shape-3 {
            width: 120px;
            height: 120px;
            border: 3px solid #1B396A;
            border-radius: 20px;
            top: 50%;
            left: 20px;
            transform: rotate(45deg);
        }

        @media (max-width: 640px) {
            .error-code {
                font-size: 100px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-description {
                font-size: 14px;
            }

            .error-container {
                padding: 50px 30px;
            }

            .shape-1, .shape-2, .shape-3 {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="decorative-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="error-code" data-text="502">502</div>
        <h1 class="error-title">Error de Puerta de Enlace</h1>
        <p class="error-description">
            El servidor recibió una respuesta no válida al procesar tu solicitud.
            El sistema puede estar en mantenimiento o experimentando un problema temporal.
        </p>
        <div class="info-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 -960 960 960" fill="currentColor">
                <path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
            Estamos trabajando para resolverlo
        </div>
        <br>
        <a href="{{ url('/') }}" class="btn-home" target="_top" rel="noopener noreferrer">Volver al Inicio</a>
    </div>
</body>
</html>
