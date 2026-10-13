# 📁 Estructura del Proyecto Frontend

Este documento describe la organización de los archivos JavaScript/Vue del proyecto.

## 🗂️ Estructura de Carpetas

```
resources/js/
├── actions/              # Acciones y lógica de negocio
├── components/           # Componentes Vue reutilizables
├── composables/          # Composables de Vue 3 (lógica reutilizable)
├── config/               # Archivos de configuración
│   ├── menu/            # Configuración de menús
│   ├── colors.js        # Configuración de colores
│   └── styles.js        # Estilos globales
├── constants/            # Constantes de la aplicación
├── layouts/              # Layouts de la aplicación
├── pages/                # Páginas/Vistas de la aplicación
├── routes/               # Definición de rutas
├── Shared/               # Componentes compartidos entre módulos
├── stores/               # Stores de Pinia (estado global)
├── utils/                # Utilidades y helpers
├── wayfinder/            # Navegación
├── app.js                # Punto de entrada de la aplicación
├── bootstrap.js          # Inicialización de librerías
└── config.js             # Configuración principal
```

## 📂 Descripción de Carpetas

### `/composables`
Contiene funciones composables de Vue 3 que encapsulan lógica reutilizable:
- `usePermissions.js` - Manejo de permisos y roles
- `useFormato.js` - Formateo de fechas y números
- Otros composables específicos por funcionalidad

**Convención de nombres**: `use[Nombre].js`

### `/components`
Componentes Vue reutilizables organizados por tipo:
- Componentes base (BaseButton, BaseIcon, etc.)
- Componentes de formulario (FormControl, FormField, etc.)
- Componentes de UI (CardBox, Modal, etc.)
- Subcarpetas para componentes complejos (Charts/, Form/, Table/, ui/)

**Importar como**: `@/components/[Componente].vue`

### `/config`
Archivos de configuración centralizados:
- `colors.js` - Paleta de colores y utilidades
- `styles.js` - Configuraciones de estilos
- `menu/` - Configuración de menús (Aside, NavBar)

**Importar como**: `@/config/[archivo]`

### `/utils`
Funciones utilitarias y helpers:
- `image.js` - Constantes y utilidades de imágenes
- Otras utilidades generales

**Importar como**: `@/utils/[archivo]`

### `/stores`
Gestión del estado global con Pinia:
- `main.js` - Store principal
- `style.js` - Store de estilos y tema

### `/pages`
Páginas de la aplicación organizadas por módulo:
- `Security/` - Gestión de seguridad (Users, Roles, Permissions)
- `settings/` - Configuraciones
- Cada módulo puede tener sus propios composables locales

### `/layouts`
Layouts de la aplicación:
- `LayoutAuthenticated.vue` - Layout para usuarios autenticados
- Otros layouts según necesidad

### `/Shared`
Componentes compartidos entre múltiples módulos que no son suficientemente genéricos para `/components`:
- `Pagination.vue`
- Otros componentes compartidos

## 🎯 Convenciones de Importación

### Usar rutas absolutas con alias `@/`
```javascript
// ✅ Correcto
import { usePermissions } from '@/composables/usePermissions'
import BaseButton from '@/components/BaseButton.vue'
import { IMAGES } from '@/utils/image'
import { colorsBgLight } from '@/config/colors'

// ❌ Incorrecto (rutas antiguas)
import { usePermissions } from '@/Hooks/usePermissions'
import { IMAGES } from '@/Utils/Image'
import { colorsBgLight } from '@/colors'
```

### Importaciones relativas solo para componentes cercanos
```javascript
// ✅ En el mismo directorio
import './ComponenteCercano.vue'

// ✅ Para submódulos relacionados
import '../utils/helper.js'
```

## 🔄 Migración Realizada

Se reorganizó la estructura para seguir mejores prácticas:

| Antes | Después |
|-------|---------|
| `/Hooks/` | `/composables/` |
| `/Utils/Image.js` | `/utils/image.js` |
| `/colors.js` | `/config/colors.js` |
| `/styles.js` | `/config/styles.js` |
| `/menuAside.js` | `/config/menu/menuAside.js` |
| `/menuNavBar.js` | `/config/menu/menuNavBar.js` |
| `/menuConfig.js` | `/config/menu/menuConfig.js` |

## 📝 Nomenclatura

- **Componentes Vue**: PascalCase (ej: `BaseButton.vue`)
- **Composables**: camelCase con prefijo `use` (ej: `usePermissions.js`)
- **Utilidades**: camelCase (ej: `image.js`)
- **Configuración**: camelCase (ej: `colors.js`)
- **Carpetas**: camelCase o lowercase (ej: `composables/`, `config/`)

## 🚀 Buenas Prácticas

1. **Mantén los componentes pequeños y enfocados**
2. **Extrae lógica reutilizable a composables**
3. **Usa rutas absolutas (@/) para mejor mantenibilidad**
4. **Organiza por funcionalidad, no por tipo de archivo**
5. **Documenta composables y utilidades complejas**

## 🔍 Encontrar Código

### Por funcionalidad
- Permisos/Roles → `composables/usePermissions.js`
- Formateo → `composables/useFormato.js`
- Imágenes → `utils/image.js`
- Colores/Estilos → `config/colors.js`, `config/styles.js`
- Menús → `config/menu/`

### Por tipo
- Componentes reutilizables → `components/`
- Páginas → `pages/`
- Estado global → `stores/`
- Configuración → `config/`

---

**Última actualización**: Enero 2026
**Mantenido por**: Equipo de Desarrollo
