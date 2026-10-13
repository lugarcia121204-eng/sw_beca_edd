<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevas Evaluaciones Asignadas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .email-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1B396A 0%, #0f2347 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .header-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .content {
            padding: 50px 60px;
            text-align: center;
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
            margin-bottom: 35px;
        }

        .info-box {
            background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
            border-radius: 16px;
            padding: 35px 30px;
            margin: 35px 0;
            border: 2px solid #D1D5DB;
            text-align: center;
        }

        .info-label {
            color: #1B396A;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
        }

        .info-value {
            background-color: #ffffff;
            border: 2px dashed #1B396A;
            border-radius: 12px;
            padding: 20px;
            font-size: 42px;
            font-weight: 800;
            color: #1B396A;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            min-width: 100px;
        }

        .warning-box {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 25px 0;
            text-align: left;
        }

        .warning-text {
            color: #92400E;
            font-size: 12px;
            margin: 0;
            line-height: 1.6;
        }

        .footer {
            padding: 30px 40px;
            background: linear-gradient(to right, #F9FAFB, #F3F4F6);
            text-align: center;
            border-top: 2px solid #E5E7EB;
        }

        .footer-text {
            color: #6B7280;
            font-size: 12px;
            line-height: 1.8;
            margin: 0;
        }

        /* Responsive Design para Móviles */
        @media only screen and (max-width: 600px) {
            body {
                padding: 15px 8px;
            }

            .email-container {
                max-width: 100%;
                border-radius: 10px;
            }

            .header {
                padding: 20px 12px;
            }

            .header-title {
                font-size: 15px;
                line-height: 1.3;
            }

            .content {
                padding: 25px 15px;
            }

            .greeting {
                font-size: 16px;
            }

            .message {
                font-size: 13px;
                line-height: 1.6;
            }

            .info-box {
                padding: 20px 12px;
                margin: 20px 0;
                border-radius: 12px;
            }

            .info-label {
                font-size: 11px;
                letter-spacing: 1px;
            }

            .info-value {
                padding: 15px;
                font-size: 28px;
            }

            .warning-box {
                padding: 12px;
                margin: 15px 0;
            }

            .warning-text {
                font-size: 11px;
            }

            .footer {
                padding: 18px 12px;
            }

            .footer-text {
                font-size: 10px;
                line-height: 1.6;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="header-title">Nuevas Evaluaciones Asignadas</h1>
        </div>

        <div class="content">
            <p class="greeting">Hola {{ $evaluatorName }},</p>

            <p class="message">
                Se te han asignado nuevas evaluaciones en el Sistema de Becas TecNM.<br>
                Por favor, revisa tu panel de evaluaciones para comenzar con la revisión.
            </p>

            <div class="info-box">
                <span class="info-label">Evaluaciones asignadas</span>
                <div class="info-value">{{ $evaluationsCount }}</div>
            </div>

            <div class="warning-box">
                <p class="warning-text">
                    ⏰ Tienes {{ $daysLimit }} días hábiles para completar las evaluaciones a partir de hoy.<br>
                    Pasado este tiempo, las asignaciones serán removidas automáticamente.
                </p>
            </div>

            <p class="message">
                Te recomendamos iniciar sesión lo antes posible para revisar las solicitudes asignadas.
            </p>
        </div>

        <div class="footer">
            <p class="footer-text">
                <strong>Programa de Estímulos al Desempeño del Personal Docente - TecNM</strong><br>
                Este es un mensaje automático del Sistema de Becas TecNM.<br>
                Por favor, no respondas a este correo.<br>
                © {{ date('Y') }} Tecnológico Nacional de México. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
