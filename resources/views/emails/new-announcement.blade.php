<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Nueva Convocatoria Disponible!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1B396A 0%, #0f2347 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .email-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #1B396A 0%, #0f2347 100%);
            padding: 60px 40px;
            text-align: center;
            position: relative;
        }

        .header-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-title {
            color: #ffffff;
            font-size: 36px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .content {
            padding: 60px;
            text-align: center;
        }

        .greeting {
            color: #1B396A;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .message {
            color: #4B5563;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .announcement-card {
            background: #f8fafc;
            border-radius: 24px;
            padding: 40px;
            margin: 0 0 45px 0;
            border: 2px solid #e2e8f0;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: #1B396A;
        }

        .announcement-name {
            color: #1B396A;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            display: block;
        }

        .announcement-description {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .info-value {
            color: #334155;
            font-size: 15px;
            font-weight: 600;
        }

        .cta-button {
            display: inline-block;
            background: #1B396A;
            color: #ffffff;
            padding: 20px 45px;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(27, 57, 106, 0.3);
        }

        .footer {
            padding: 40px;
            background: #f1f5f9;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-text {
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
            max-width: 500px;
            margin: 0 auto;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 40px 20px;
            }

            .header-title {
                font-size: 26px;
            }

            .announcement-card {
                padding: 25px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .cta-button {
                width: 100%;
                padding: 18px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <div class="header-badge">Nueva Oportunidad</div>
            <h1 class="header-title">¡La convocatoria ya está abierta!</h1>
        </div>

        <div class="content">
            <p class="greeting">Hola {{ $userName }},</p>

            <p class="message">
                Nos complace informarte que una nueva convocatoria ha sido publicada en nuestro sistema. Ya puedes
                comenzar con tu proceso de registro.
            </p>

            <div class="announcement-card">
                <span class="announcement-name">{{ $announcement->name }}</span>
                <p class="announcement-description">
                    {{ \Illuminate\Support\Str::limit($announcement->description, 180) }}
                </p>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Periodo de Registro</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($announcement->calendar->registration_start)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($announcement->calendar->registration_end)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ url('/') }}" class="cta-button">Ver Detalles en el Sistema</a>
        </div>

        <div class="footer">
            <p class="footer-text">
                <strong>Programa de Estímulos al Desempeño del Personal Docente - TecNM</strong><br>
                Este es un mensaje automático del Sistema de Becas TecNM.<br>
                © {{ date('Y') }} Tecnológico Nacional de México.
            </p>
        </div>
    </div>
</body>

</html>