<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Reconocimiento está disponible</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1B396A 0%, #2d5aa0 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1B396A 0%, #2d5aa0 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .header-icon {
            font-size: 52px;
            margin-bottom: 12px;
            display: block;
        }

        .header-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin-top: 8px;
        }

        .content {
            padding: 40px 50px;
        }

        .greeting {
            color: #1B396A;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .message {
            color: #4B5563;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .recognition-box {
            background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
            border-radius: 12px;
            padding: 30px;
            margin: 25px 0;
            text-align: center;
            border-left: 4px solid #1B396A;
        }

        .recognition-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .recognition-title {
            color: #1B396A;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .recognition-subtitle {
            color: #3B82F6;
            font-size: 15px;
            font-weight: 500;
        }

        .announcement-box {
            background: #F3F4F6;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }

        .announcement-label {
            color: #6B7280;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .announcement-title {
            color: #1B396A;
            font-size: 16px;
            font-weight: 600;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1B396A 0%, #2d5aa0 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            margin: 10px 0 25px;
            letter-spacing: 0.3px;
        }

        .divider {
            border: none;
            border-top: 1px solid #E5E7EB;
            margin: 25px 0;
        }

        .footer {
            background-color: #F9FAFB;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .footer-text {
            color: #6B7280;
            font-size: 12px;
            line-height: 1.5;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <span class="header-icon">🏅</span>
            <h1 class="header-title">Reconocimiento Disponible</h1>
            <p class="header-subtitle">Sistema de Becas TecNM</p>
        </div>

        <div class="content">
            <p class="greeting">¡Felicidades, {{ $teacherName }}!</p>

            <p class="message">
                Nos complace informarte que tu reconocimiento por participación en la convocatoria
                de becas ya se encuentra disponible para su descarga.
            </p>

            <div class="announcement-box">
                <p class="announcement-label">Convocatoria</p>
                <p class="announcement-title">{{ $announcementTitle }}</p>
            </div>

            <div class="recognition-box">
                <span class="recognition-icon">📄</span>
                <h2 class="recognition-title">¡Tu reconocimiento está listo!</h2>
                <p class="recognition-subtitle">Ya puedes descargarlo desde tu cuenta</p>
            </div>

            <p class="message">
                Ingresa a tu cuenta en el Sistema de Becas TecNM, dirígete a la sección de
                <strong>Reconocimientos</strong> y descarga tu documento.
            </p>

            <hr class="divider">

            <p class="message" style="font-size: 13px; color: #6B7280;">
                Si tienes alguna duda, contacta a la administración del programa de becas.
            </p>
        </div>

        <div class="footer">
            <p class="footer-text">
                Este es un mensaje automático del Sistema de Becas TecNM.<br>
                Por favor, no respondas a este correo.
            </p>
        </div>
    </div>
</body>
</html>
