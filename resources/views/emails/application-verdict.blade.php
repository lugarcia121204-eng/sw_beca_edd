<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de tu Solicitud</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, {{ $status === 'approved' ? '#10B981' : '#EF4444' }} 0%, {{ $status === 'approved' ? '#059669' : '#DC2626' }} 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .header-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
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

        .result-box {
            background: linear-gradient(to bottom right, 
                {{ $status === 'approved' ? '#D1FAE5, #A7F3D0' : '#FEE2E2, #FECACA' }});
            border-radius: 12px;
            padding: 30px;
            margin: 25px 0;
            text-align: center;
            border-left: 4px solid {{ $status === 'approved' ? '#10B981' : '#EF4444' }};
        }

        .result-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .result-title {
            color: {{ $status === 'approved' ? '#065F46' : '#991B1B' }};
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .result-subtitle {
            color: {{ $status === 'approved' ? '#047857' : '#B91C1C' }};
            font-size: 16px;
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
            <h1 class="header-title">
                @if($status === 'approved')
                    Solicitud Aprobada
                @else
                    Resultado de tu Solicitud
                @endif
            </h1>
        </div>

        <div class="content">
            <p class="greeting">Hola {{ $teacherName }},</p>

            <p class="message">
                Te informamos sobre el resultado de tu solicitud de beca en el Sistema TecNM.
            </p>

            <div class="announcement-box">
                <p class="announcement-label">Convocatoria</p>
                <p class="announcement-title">{{ $announcementTitle }}</p>
            </div>

            <div class="result-box">
                <div class="result-icon">
                    @if($status === 'approved')
                        ✅
                    @else
                        ℹ️
                    @endif
                </div>
                <h2 class="result-title">
                    @if($status === 'approved')
                        ¡Felicidades!
                    @else
                        Resultado de Evaluación
                    @endif
                </h2>
                <p class="result-subtitle">
                    @if($status === 'approved')
                        Tu solicitud ha sido aprobada
                    @else
                        Tu solicitud no fue aceptada en esta ocasión
                    @endif
                </p>
            </div>

            <p class="message">
                @if($status === 'approved')
                    Tu solicitud ha cumplido con todos los requisitos. Próximamente recibirás más información sobre los siguientes pasos.
                @else
                    Lamentamos informarte que tu solicitud no cumplió con todos los criterios de evaluación en esta convocatoria. 
                    Te invitamos a participar en futuras convocatorias.
                @endif
            </p>

            <p class="message">
                Para más detalles, puedes ingresar a tu cuenta en el Sistema de Becas TecNM.
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
