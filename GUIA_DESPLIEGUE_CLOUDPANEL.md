# Guía Completa de Despliegue en CloudPanel

## Objetivo
Esta guía documenta el flujo completo para:

1. Despliegue inicial del proyecto en CloudPanel.
2. Configuración persistente de Scheduler y Queue Worker.
3. Flujo de despliegue para cambios futuros.
4. Checklist de verificación final.

---

## 1. Requisitos previos

- Sitio creado en CloudPanel.
- Dominio apuntando al servidor.
- Acceso SSH.
- Repositorio en GitHub.
- Base de datos creada en CloudPanel.

Ruta de proyecto usada en este sistema:

/home/tecnm-edd-cenidet/htdocs/edd.cenidet.tecnm.mx

---

## 2. Despliegue inicial

### 2.1 Entrar al proyecto

    cd /home/tecnm-edd-cenidet/htdocs/edd.cenidet.tecnm.mx

### 2.2 Conectar repo y sincronizar código

Si la carpeta no tiene repo o está desalineada:

    git init
    git remote add origin https://github.com/Viter-Lopez-Caballero/sw_BecaDesempeno.git
    git fetch origin
    git branch -M main
    git reset --hard origin/main

Si ya existe remoto:

    git remote -v
    git pull origin main

### 2.3 Instalar dependencias

    composer install --no-dev --optimize-autoloader
    npm install
    npm run build

### 2.4 Configurar variables de entorno

    cp .env.example .env
    php artisan key:generate

Editar .env para producción:

- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://edd.cenidet.tecnm.mx
- DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_* de producción
- QUEUE_CONNECTION=database

### 2.5 Permisos

    chown -R tecnm-edd-cenidet:tecnm-edd-cenidet .
    chmod -R 775 storage bootstrap/cache
    find storage -type f -exec chmod 664 {} \;
    find bootstrap/cache -type f -exec chmod 664 {} \;

### 2.6 Enlace publico de archivos (obligatorio)

Laravel publica archivos del disco public por medio de un symlink en public/storage.

    php artisan storage:link

Si ya existe un enlace roto o incorrecto:

    rm -f public/storage
    php artisan storage:link

### 2.7 Migraciones y optimización

    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:clear
    php artisan view:cache

---

## 3. Configuración persistente (una sola vez)

## 3.1 Scheduler (Cron Job)

En CloudPanel > sitio > Cron Jobs, agregar:

    * * * * * cd /home/tecnm-edd-cenidet/htdocs/edd.cenidet.tecnm.mx ; php artisan schedule:run >> /home/tecnm-edd-cenidet/logs/laravel-scheduler.log 2>&1

Esto ejecuta tareas programadas cada minuto.

## 3.2 Queue Worker con Supervisor

Crear archivo:

    sudo nano /etc/supervisor/conf.d/laravel-worker.conf

Contenido completo:

    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /home/tecnm-edd-cenidet/htdocs/edd.cenidet.tecnm.mx/artisan queue:work database --sleep=3 --tries=3 --timeout=120 --queue=default
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=tecnm-edd-cenidet
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/home/tecnm-edd-cenidet/logs/laravel-worker.log
    stopwaitsecs=3600

Activar Supervisor:

    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start laravel-worker:*
    sudo supervisorctl status

Debe aparecer RUNNING.

Si falta carpeta de logs:

    mkdir -p /home/tecnm-edd-cenidet/logs
    chown -R tecnm-edd-cenidet:tecnm-edd-cenidet /home/tecnm-edd-cenidet/logs

---

## 4. Flujo para próximos cambios (siempre)

## 4.1 En local (tu máquina)

    git add .
    git commit -m "mensaje del cambio"
    git push origin main

## 4.2 En servidor (deploy)

    git push origin feature/responsive
    git push viter feature/responsive:dev
    su - tecnm-edd-cenidet
    cd /home/tecnm-edd-cenidet/htdocs/edd.cenidet.tecnm.mx
    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm install
    npm run build
    php artisan storage:link
    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:clear
    php artisan view:cache
    php artisan queue:restart

Importante:

- El comando queue:restart es obligatorio al final del despliegue para que los workers tomen el nuevo código sin parar Supervisor.

---

## 5. Comandos de diagnóstico rápido

## 5.1 Estado de colas

    sudo supervisorctl status
    php artisan queue:failed

## 5.2 Logs Laravel

    tail -n 120 storage/logs/laravel.log

## 5.3 Logs Scheduler y Worker

    tail -n 120 /home/tecnm-edd-cenidet/logs/laravel-scheduler.log
    tail -n 120 /home/tecnm-edd-cenidet/logs/laravel-worker.log

## 5.4 Si falla git pull por cambios locales

    git reset --hard origin/main

---

## 6. Errores comunes y solución

## 6.1 Unable to locate file in Vite manifest

Causa:
- Build desactualizado o configuración de Vite incorrecta.

Solución:

    npm run build
    php artisan optimize:clear
    php artisan view:cache

Y verificar que en app.blade.php se use:

    @vite(['resources/js/app.js'])

## 6.4 404 en archivos /storage/*

Causa:
- Falta symlink public/storage -> storage/app/public.
- Permisos insuficientes en storage/app/public.

Solucion:

    rm -f public/storage
    php artisan storage:link
    chown -R tecnm-edd-cenidet:tecnm-edd-cenidet storage public/storage
    chmod -R 775 storage/app/public
    find storage/app/public -type f -exec chmod 664 {} \;

Validacion:

    ls -la public | grep storage
    ls -la storage/app/public/announcements

## 6.2 Page not found en Inertia (frontend)

Causa:
- Rutas de páginas no encontradas por diferencias de mayúsculas/minúsculas en Linux.

Solución:
- Corregir imports y resolver de Inertia.
- Recompilar:

    npm run build

## 6.3 Duplicate route name en route:cache

Causa:
- Dos rutas con mismo name.

Solución:
- Renombrar ruta duplicada y luego:

    php artisan route:clear

---

## 7. Checklist de salida (Go Live)

Antes de cerrar despliegue, confirmar:

1. Sitio responde sin 500.
2. npm run build terminó sin errores.
3. php artisan migrate --force ejecutado.
4. Supervisor en RUNNING.
5. Cron Scheduler creado.
6. queue:restart ejecutado tras deploy.
7. APP_ENV=production y APP_DEBUG=false.
8. Logs sin errores críticos.

---

## 8. Flujo corto (resumen rápido)

Para cada deploy futuro:

    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm install
    npm run build
    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:clear
    php artisan view:cache
    php artisan queue:restart

Con eso el sistema queda actualizado y estable.

Ya me quiero ir ahhh