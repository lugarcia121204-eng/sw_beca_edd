<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensaje de Contacto</title>
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

        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-top: 10px;
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

        .field-section {
            background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
            border-radius: 16px;
            padding: 35px 30px;
            margin: 35px 0;
            border: 2px solid #D1D5DB;
        }

        .field {
            margin-bottom: 25px;
        }

        .field:last-child {
            margin-bottom: 0;
        }

        .field-label {
            color: #1B396A;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
        }

        .field-value {
            background-color: #ffffff;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 15px 18px;
            color: #1F2937;
            font-size: 15px;
            line-height: 1.6;
        }

        .message-box {
            background-color: #ffffff;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            color: #1F2937;
            font-size: 15px;
            line-height: 1.8;
            min-height: 120px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #D1D5DB, transparent);
            margin: 30px 0;
        }

        .info-box {
            background-color: #EFF6FF;
            border-left: 4px solid #1B396A;
            padding: 18px 20px;
            margin: 25px 0;
            text-align: left;
            border-radius: 8px;
        }

        .info-box p {
            color: #1E40AF;
            font-size: 13px;
            margin: 0;
            line-height: 1.6;
        }

        .footer {
            padding: 30px 40px;
            background: linear-gradient(to right, #F9FAFB, #F3F4F6);
            text-align: center;
            border-top: 2px solid #E5E7EB;
        }

        .footer p {
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

            .header-subtitle {
                font-size: 11px;
            }

            .content {
                padding: 25px 15px;
            }

            .greeting {
                font-size: 16px;
            }

            .field-section {
                padding: 20px 15px;
                margin: 20px 0;
                border-radius: 12px;
            }

            .field {
                margin-bottom: 20px;
            }

            .field-label {
                font-size: 11px;
                letter-spacing: 0.5px;
            }

            .field-value {
                padding: 12px 14px;
                font-size: 13px;
            }

            .message-box {
                padding: 15px;
                font-size: 13px;
                min-height: 100px;
            }

            .info-box {
                padding: 12px 15px;
                margin: 15px 0;
            }

            .info-box p {
                font-size: 11px;
            }

            .footer {
                padding: 18px 12px;
            }

            .footer p {
                font-size: 10px;
                line-height: 1.6;
            }

            .divider {
                margin: 20px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="header-title">Nuevo Mensaje</h1>
            <p class="header-subtitle">Programa de Estímulos al Desempeño del Personal Docente - TecNM</p>
        </div>

        <div class="content">
            <p class="greeting">Se ha recibido un nuevo mensaje de contacto con los siguientes detalles:</p>

            <div class="field-section">
                <div class="field">
                    <span class="field-label">Nombre</span>
                    <div class="field-value">{{ $data['name'] }}</div>
                </div>

                <div class="field">
                    <span class="field-label">Correo Electrónico</span>
                    <div class="field-value">{{ $data['email'] }}</div>
                </div>

                <div class="field">
                    <span class="field-label">Institución</span>
                    <div class="field-value">{{ $data['institucion'] }}</div>
                </div>

                <div class="divider"></div>

                <div class="field">
                    <span class="field-label">Mensaje</span>
                    <div class="message-box">{{ $data['message'] }}</div>
                </div>
            </div>

            <div class="info-box">
                <p>
                    Este mensaje fue enviado desde el formulario de contacto del sistema de Programa de Estímulos al Desempeño del Personal Docente.<br>
                    Por favor, responde directamente al correo electrónico proporcionado.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>Programa de Estímulos al Desempeño del Personal Docente - TecNM</strong><br>
                Este es un correo automático generado por el sistema.<br>
                © {{ date('Y') }} Tecnológico Nacional de México. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
