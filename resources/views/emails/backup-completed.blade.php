<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo Completado</title>
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
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
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

        .header-subtitle {
            color: rgba(255, 255, 255, 0.65);
            font-size: 15px;
            margin-top: 12px;
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

        .backup-card {
            background: #f8fafc;
            border-radius: 24px;
            padding: 40px;
            margin: 0 0 45px 0;
            border: 2px solid #e2e8f0;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .backup-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: #10A558;
        }

        .backup-name {
            color: #374151;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            display: block;
        }

        .status-badge {
            display: inline-block;
            background: #D1FAE5;
            color: #065F46;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border: 1px solid #A7F3D0;
        }

        .type-badge {
            display: inline-block;
            background: #DBEAFE;
            color: #1E40AF;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            margin-left: 8px;
            border: 1px solid #BFDBFE;
        }

        .encrypted-badge {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 20px;
            margin-left: 8px;
            border: 1px solid #FDE68A;
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

        .alert-note {
            background: #eff6ff;
            border-left: 4px solid #1B396A;
            border-radius: 0 12px 12px 0;
            padding: 18px 24px;
            margin-bottom: 40px;
            text-align: left;
        }

        .alert-note p {
            color: #1e40af;
            font-size: 14px;
            line-height: 1.7;
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
            .content { padding: 30px 20px; }
            .header { padding: 40px 20px; }
            .header-title { font-size: 26px; }
            .backup-card { padding: 25px; }
            .info-grid { grid-template-columns: 1fr; }
            .cta-button { width: 100%; padding: 18px 20px; }
        }
    </style>
</head>

<body>
    <div class="email-container">

        <div class="header">
            <div class="header-badge">Respaldo Completado</div>
            <h1 class="header-title">{{ $backup->name }}</h1>
            <p class="header-subtitle">
                {{ $backup->type === 'scheduled' ? 'Respaldo automático generado por el sistema' : 'Respaldo manual ejecutado correctamente' }}
            </p>
        </div>

        <div class="content">
            <p class="greeting">Notificación del Sistema de Respaldos</p>

            <p class="message">
                El sistema ha generado exitosamente un <strong>respaldo de la base de datos</strong>.
                A continuación encontrarás los detalles del respaldo realizado.
            </p>

            <div class="backup-card">
                <span class="backup-name">{{ $backup->name }}</span>

                <div>
                    <span class="status-badge">✓ Completado</span>
                    <span class="type-badge">
                        {{ $backup->type === 'scheduled' ? 'Automático' : 'Manual' }}
                    </span>
                    @if ($backup->is_encrypted)
                        <span class="encrypted-badge">🔒 Cifrado AES-256</span>
                    @endif
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Tamaño del Archivo</span>
                        <span class="info-value">{{ $formattedSize }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha y Hora</span>
                        <span class="info-value">{{ $backup->created_at?->format('d/m/Y H:i') }} hrs.</span>
                    </div>
                    @if ($backup->description)
                        <div class="info-item" style="grid-column: span 2;">
                            <span class="info-label">Descripción</span>
                            <span class="info-value">{{ $backup->description }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="alert-note">
                <p>Este mensaje fue enviado automáticamente porque las <strong>notificaciones por correo</strong> están activadas en la configuración de respaldos. Puedes desactivarlas en cualquier momento desde la sección de <strong>Programación Automática</strong>.</p>
            </div>

            <a href="{{ url('/') }}" class="cta-button">Ir al Sistema</a>
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
</body>
</html>
