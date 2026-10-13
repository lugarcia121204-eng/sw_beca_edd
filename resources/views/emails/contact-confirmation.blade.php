<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Mensaje</title>
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

        .confirmation-section {
            background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
            border-radius: 16px;
            padding: 35px 30px;
            margin: 35px 0;
            border: 2px solid #D1D5DB;
        }

        .confirmation-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirmation-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .confirmation-label {
            color: #1B396A;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .confirmation-text {
            color: #4B5563;
            font-size: 14px;
            line-height: 1.6;
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

        .info-title {
            color: #1B396A;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 14px;
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

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #D1D5DB, transparent);
            margin: 30px 0;
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
                font-size: 20px;
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

            .confirmation-section {
                padding: 20px 15px;
                margin: 20px 0;
                border-radius: 12px;
            }

            .confirmation-icon {
                width: 60px;
                height: 60px;
            }

            .confirmation-icon svg {
                width: 35px;
                height: 35px;
            }

            .confirmation-label {
                font-size: 16px;
            }

            .confirmation-text {
                font-size: 12px;
            }

            .info-box {
                padding: 12px 15px;
                margin: 15px 0;
            }

            .info-box p {
                font-size: 11px;
            }

            .info-title {
                font-size: 12px;
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
            <h1 class="header-title">Hemos Recibido tu Mensaje</h1>
        </div>

        <div class="content">
            <p class="greeting">¡Hola {{ $data['name'] }}!</p>
            
            <p class="message">
                Gracias por ponerte en contacto con nosotros.<br>
                Hemos recibido tu mensaje y nuestro equipo lo revisará a la brevedad.
            </p>

            <div class="confirmation-section">
                <p class="confirmation-label">Mensaje Recibido</p>
                <p class="confirmation-text">
                    Te responderemos en un plazo máximo de 48 horas hábiles.<br>
                    Si tu consulta es urgente, por favor menciona "URGENTE" en tu mensaje.
                </p>
            </div>

            <div class="info-box">
                <p class="info-title">Detalles de tu mensaje:</p>
                <p>
                    <strong>Institución:</strong> {{ $data['institucion'] }}<br>
                    <strong>Correo:</strong> {{ $data['email'] }}
                </p>
            </div>

            <div class="divider"></div>

            <p class="message">
                Si no enviaste este mensaje, por favor ignora este correo.<br>
                Para cualquier otra consulta, puedes volver a contactarnos a través del formulario.
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>Programa de Estímulos al Desempeño del Personal Docente - TecNM</strong><br>
                Este es un correo automático, por favor no respondas a este mensaje.<br>
                © {{ date('Y') }} Tecnológico Nacional de México. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
