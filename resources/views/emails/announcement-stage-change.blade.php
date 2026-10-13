<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Etapa en Convocatoria</title>
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

        .announcement-box {
            background: linear-gradient(120deg, #f8fafc 60%, #e0e7ef 100%);
            border-radius: 14px;
            padding: 28px 24px;
            margin: 28px 0 36px 0;
            border: 1.5px solid #dbeafe;
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

        .stage-box {
            background: #f3f6fb;
            border-radius: 14px;
            padding: 28px 20px;
            margin: 0 0 36px 0;
            border: 1.5px solid #dbeafe;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .stage-label {
            color: #1B396A;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            flex: 1 1 180px;
            text-align: left;
        }

        .stage-name {
            color: #fff;
            background: #1B396A;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            flex: 1 1 180px;
            text-align: center;
        }

        .stage-date {
            color: #1B396A;
            background: #e0e7ef;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 15px;
            font-weight: 600;
            flex: 1 1 180px;
            text-align: right;
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

            .announcement-box {
                padding: 15px;
                margin: 15px 0;
            }

            .stage-box {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
                gap: 12px;
            }

            .stage-label,
            .stage-name,
            .stage-date {
                flex: unset;
                width: 100%;
                text-align: left;
                box-sizing: border-box;
            }

            .stage-name,
            .stage-date {
                font-size: 14px;
                padding: 8px 12px;
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
            <h1 class="header-title">Cambio de Etapa en Convocatoria</h1>
        </div>

        <div class="content">
            <p class="greeting">Hola {{ $userName }},</p>

            <p class="message">
                Te informamos que ha ocurrido un cambio de etapa en la siguiente convocatoria:
            </p>

            <div class="announcement-box">
                <p class="announcement-label">Convocatoria</p>
                <p class="announcement-title">{{ $announcementTitle }}</p>
            </div>

            <div class="stage-box">
                <span class="stage-label">Nueva Etapa</span>
                <span class="stage-name">{{ $newStage }}</span>
                @if($stageDate)
                    <span class="stage-date">{{ \Carbon\Carbon::parse($stageDate)->translatedFormat('d \d\e F \d\e\l Y') }}</span>
                @endif
            </div>

            <p class="message">
                Por favor, ingresa al sistema para conocer más detalles sobre esta etapa y las acciones que debes realizar.
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
