<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Fechas en Convocatoria</title>
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

        .changes-box {
            background: #f3f6fb;
            border-radius: 14px;
            padding: 28px 20px;
            margin: 0 0 36px 0;
            border: 1.5px solid #dbeafe;
        }

        .changes-title {
            color: #1B396A;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 22px;
            letter-spacing: 0.5px;
            text-align: left;
        }

        .change-item {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .change-item:last-child {
            margin-bottom: 0;
        }

        .change-label {
            color: #1e293b;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            display: block;
            text-align: center;
            background: #f8fafc;
            padding: 8px;
            border-radius: 6px;
        }

        .comparison-grid {
            display: table;
            margin: 0 auto;
            border-spacing: 15px 0;
            width: auto;
        }

        .date-card {
            display: table-cell;
            vertical-align: top;
            width: 200px;
            text-align: center;
        }

        .date-tag {
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 12px;
            display: block;
            letter-spacing: 1.5px;
            text-align: center;
        }

        .tag-old {
            color: #94a3b8;
        }

        .tag-new {
            color: #1B396A;
        }

        .date-box {
            padding: 12px 10px;
            border-radius: 8px;
            font-size: 14px;
            min-height: 45px;
            display: block;
            text-align: center;
            line-height: 45px;
        }

        .box-old {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .box-new {
            background: #1B396A;
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 4px 6px -1px rgba(27, 57, 106, 0.2);
            border: 1px solid #1B396A;
        }

        .arrow-container {
            display: table-cell;
            vertical-align: middle;
            padding-top: 22px;
            text-align: center;
            width: 60px;
        }

        .arrow-icon {
            color: #1B396A;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -2px;
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
                font-size: 18px;
            }

            .content {
                padding: 25px 15px;
            }

            .date-card {
                min-width: auto;
                width: 100%;
            }

            .arrow-container {
                padding: 0;
                transform: rotate(90deg);
                margin: 10px 0;
                display: flex;
                justify-content: center;
                width: 100%;
            }

            .date-box {
                font-size: 13px;
                padding: 10px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1 class="header-title">Cambio de Fechas en Convocatoria</h1>
        </div>

        <div class="content">
            <p class="greeting">Hola {{ $userName }},</p>

            <p class="message">
                Te informamos que se han actualizado las fechas de la siguiente convocatoria:
            </p>

            <div class="announcement-box">
                <p class="announcement-label">Convocatoria</p>
                <p class="announcement-title">{{ $announcementTitle }}</p>
            </div>

            <div class="changes-box">
                <p class="changes-title">Detalle de los cambios:</p>
                @foreach($changes as $change)
                    <div class="change-item">
                        <span class="change-label">{{ $change['label'] }}</span>

                        <div class="comparison-grid">
                            <div class="date-card">
                                <span class="date-tag tag-old">ANTERIOR</span>
                                <div class="date-box box-old">
                                    {{ \Carbon\Carbon::parse($change['old'])->translatedFormat('d \d\e F, Y') }}
                                </div>
                            </div>

                            <div class="arrow-container">
                                <span class="arrow-icon">&gt;&gt;&gt;&gt;</span>
                            </div>

                            <div class="date-card">
                                <span class="date-tag tag-new">NUEVA</span>
                                <div class="date-box box-new">
                                    {{ \Carbon\Carbon::parse($change['new'])->translatedFormat('d \d\e F, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="message">
                Por favor, toma nota de estos cambios y ajusta tus plazos en consecuencia.
                Puedes consultar el calendario completo ingresando al sistema.
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