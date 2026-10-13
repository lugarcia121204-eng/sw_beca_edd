<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento no disponible</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            color: #374151;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            max-width: 400px;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-top: 4px solid #C9A800;
        }
        .icon {
            color: #9CA3AF;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.25rem;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 0.875rem;
            line-height: 1.5;
            color: #4B5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
        <h1>Aún no disponible</h1>
        <p>{{ $message ?? 'El documento solicitado aún no se ha generado o no hay una plantilla activa por el momento. Por favor, inténtelo de nuevo más tarde.' }}</p>
    </div>
</body>
</html>
