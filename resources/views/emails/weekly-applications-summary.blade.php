<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Semanal de Solicitudes</title>
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

        .summary-section {
            background: #f3f6fb;
            border-radius: 14px;
            padding: 28px 20px;
            margin: 0 0 36px 0;
            border: 1.5px solid #dbeafe;
        }

        .summary-title {
            color: #1B396A;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 22px;
            letter-spacing: 0.5px;
            text-align: left;
        }

        .weekday-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 12px;
            border: 1px solid #e0e7ef;
            box-shadow: 0 2px 8px rgba(27,57,106,0.04);
        }

        .weekday-item:last-child {
            margin-bottom: 0;
        }

        .weekday-name {
            color: #1B396A;
            font-size: 15px;
            font-weight: 600;
            text-align: left;
        }

        .weekday-count {
            color: #fff;
            background: #1B396A;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            text-align: right;
        }

        .total-section {
            background: #1B396A;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
            text-align: center;
        }

        .total-label {
            color: #c7d2fe;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .total-number {
            color: #fff;
            font-size: 48px;
            font-weight: 800;
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

            .summary-section {
                padding: 15px 8px;
            }

            .weekday-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
                gap: 10px;
            }

            .weekday-name,
            .weekday-count {
                flex: unset;
                width: 100%;
                text-align: left;
                box-sizing: border-box;
            }

            .weekday-count {
                font-size: 14px;
                padding: 6px 10px;
            }

            .total-number {
                font-size: 32px;
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
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="header-title">Resumen Semanal de Solicitudes</h1>
        </div>

        <div class="content">
            <p class="greeting">¡Hola Administrador!</p>
            
            <p class="message">
                Este es tu resumen semanal de solicitudes recibidas durante la convocatoria activa.<br>
                A continuación encontrarás el desglose por día de la semana.
            </p>

            <div class="summary-section">
                <p class="summary-title">Solicitudes por Día</p>
                
                @foreach($weeklyData as $day => $count)
                <div class="weekday-item">
                    <span class="weekday-name">{{ $day }}</span>
                    <span class="weekday-count">{{ $count }}</span>
                </div>
                @endforeach

                <div class="total-section">
                    <div class="total-label">Total Semanal</div>
                    <div class="total-number">{{ $totalApplications }}</div>
                </div>
            </div>

            <div class="info-box">
                <p>
                    <span class="info-icon">i</span>
                    <strong>¿Qué significa esto?</strong><br>
                    Este resumen se genera automáticamente cada viernes para mantener informados a los administradores sobre la actividad de solicitudes durante la semana.
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
