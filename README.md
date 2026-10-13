# Sistema EDD

Aplicación para gestionar convocatorias, expedientes docentes, evaluaciones Local, Estatal y Nacional, reconocimientos y documentos públicos.

## Contenido

- Backend Laravel e interfaz Vue con Inertia.
- Portal público y paneles por rol.
- Migraciones, catálogos, seeders y pruebas.
- Imágenes institucionales y documentación en `docs`.
- Configuración de despliegue y archivos de dependencias con sus versiones.

## Instalación local

Requiere PHP compatible con `composer.lock` (se recomienda PHP 8.4 o posterior), Composer, Node.js con npm y MySQL/MariaDB. Habilitar las extensiones requeridas por Composer y el controlador de base de datos.

```powershell
git clone https://github.com/lugarcia121204-eng/sw_beca_edd.git
cd sw_beca_edd
Copy-Item .env.example .env
composer install
npm ci
php artisan key:generate
```

Crear una base de datos vacía y configurar `DB_*` en `.env`. Después:

```powershell
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Para inicializar catálogos y cuentas de prueba, revisar los seeders antes de ejecutarlos. `DatabaseSeeder` limpia archivos de ciertas carpetas: usarlo únicamente en una instalación de prueba nueva, nunca sobre una instalación con expedientes que deban conservarse.

```powershell
php artisan db:seed
```

Para desarrollo, `npm run dev` activa Vite. El procesamiento de correos necesita `php artisan queue:work`; las tareas programadas requieren ejecutar periódicamente `php artisan schedule:run`.

## Servicios externos

Configurar las credenciales de correo, el token CURP y las rutas de firma en el entorno local. La plantilla usa correo de registro (`log`) para evitar envíos involuntarios. Los PDF firmados requieren la llave y certificado autorizados y las plantillas correspondientes. Consultar `docs` y `GUIA_DESPLIEGUE_CLOUDPANEL.md`.

## Datos de cada instalación

GitHub contiene el proyecto fuente. Las dependencias `vendor` y `node_modules` se instalan con Composer y npm. `.env`, sesiones de navegador, respaldos, registros y expedientes cargados se conservan fuera del repositorio. Las migraciones crean el esquema; no incluyen las cuentas o solicitudes de la base local en uso.
