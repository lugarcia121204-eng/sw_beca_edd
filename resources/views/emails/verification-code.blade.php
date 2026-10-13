<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu Correo Electrónico</title>
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

        .code-section {
            background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
            border-radius: 16px;
            padding: 35px 30px;
            margin: 35px 0;
            border: 2px solid #D1D5DB;
        }

        .code-label {
            color: #1B396A;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .code-display {
            background-color: #ffffff;
            border: 2px dashed #1B396A;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
        }

        .code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #1B396A;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .expiry {
            color: #DC2626;
            font-size: 13px;
            margin-top: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .expiry svg {
            width: 16px;
            height: 16px;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #1B396A 0%, #0f2347 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            margin: 25px 0;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(27, 57, 106, 0.3);
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(27, 57, 106, 0.4);
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

        .info-icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            background-color: #1B396A;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 8px;
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

        .security-note {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 25px 0;
            text-align: left;
        }

        .security-note p {
            color: #92400E;
            font-size: 12px;
            margin: 0;
            line-height: 1.6;
        }

        .security-title {
            color: #B45309;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
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

            .logos {
                flex-direction: column;
                gap: 12px;
                margin-bottom: 15px;
            }

            .logo-tecnm {
                max-width: 200px;
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

            .code-section {
                padding: 20px 12px;
                margin: 20px 0;
                border-radius: 12px;
            }

            .code {
                font-size: 28px;
                letter-spacing: 6px;
            }

            .code-display {
                padding: 15px;
            }

            .code-label {
                font-size: 11px;
                letter-spacing: 1px;
            }

            .expiry {
                font-size: 11px;
            }

            .expiry svg {
                width: 14px;
                height: 14px;
            }

            .button {
                padding: 12px 25px;
                font-size: 13px;
                width: 100%;
                box-sizing: border-box;
            }

            .info-box,
            .security-note {
                padding: 12px;
                margin: 15px 0;
            }

            .info-box p,
            .security-note p {
                font-size: 11px;
            }

            .info-icon {
                width: 16px;
                height: 16px;
                line-height: 16px;
                font-size: 11px;
            }

            .security-title {
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
            <h1 class="header-title">Confirma tu Correo Electrónico</h1>
        </div>

        <div class="content">
            <p class="greeting">¡Hola {{ $userName }}!</p>
            
            <p class="message">
                Gracias por registrarte en nuestro sistema de becas.<br>
                Para activar tu cuenta, necesitamos verificar tu dirección de correo electrónico.<br>
                Por favor ingresa el siguiente código de verificación en la página correspondiente.
            </p>

            <div class="code-section">
                <p class="code-label">Tu Código de Verificación</p>
                <div class="code-display">
                    <div class="code">{{ $code }}</div>
                </div>
                <p class="expiry">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Este código expira en 24 horas
                </p>
            </div>
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
