import {
    mdiHome,
    mdiSecurity,
    mdiViewModule,
    mdiLockCheckOutline,
    mdiAccountSupervisor,
    mdiAccount,
    mdiFileDocumentMultiple,
    mdiBullhorn,
    mdiBookOpenPageVariant,
    mdiOfficeBuilding,
    mdiLightbulbOnOutline,
    mdiCalendar,
    mdiClipboardTextOutline,
    mdiSchool,
    mdiStar,
    mdiFileTree,
    mdiBell,
    mdiDatabase,
} from "@mdi/js";

// Helper to create consistent security items with dynamic route prefix
const getSecurityItems = (routePrefix) => [
    {
        label: "Módulos",
        route: `${routePrefix}modules.index`,
        icon: mdiViewModule,
        permission: "modules.index",
    },
    {
        label: "Permisos",
        route: `${routePrefix}permissions.index`,
        icon: mdiLockCheckOutline,
        permission: "permissions.index",
    },
    {
        label: "Roles",
        route: `${routePrefix}roles.index`,
        icon: mdiAccountSupervisor,
        permission: "roles.index",
    },
    {
        label: "Usuarios",
        route: `${routePrefix}users.index`,
        icon: mdiAccount,
        permission: "users.index",
    },
];

// Helper for Catalog items
const getCatalogItems = (routePrefix) => [
    {
        label: "Instituciones",
        route: `${routePrefix}institutions.index`,
        icon: mdiOfficeBuilding,
        permission: "institutions.index",
    },
    {
        label: "Áreas Prioritarias",
        route: `${routePrefix}priority-areas.index`,
        icon: mdiLightbulbOnOutline,
        permission: "priority_areas.index",
    },
    {
        label: "Sub Áreas",
        route: `${routePrefix}sub-areas.index`,
        icon: mdiFileTree,
        permission: "sub_areas.index",
    },
    {
        label: "Documentos",
        route: `${routePrefix}documents.index`,
        icon: mdiFileDocumentMultiple,
        permission: "documents.index",
    },
    {
        label: "Rúbrica",
        route: `${routePrefix}rubrics.index`,
        icon: mdiClipboardTextOutline,
        permission: "rubrics.index",
    },
    {
        label: "Plantillas",
        route: `${routePrefix}templates.index`,
        icon: mdiFileDocumentMultiple,
        permission: "templates.index",
    },
];

// ─────────────────────────────────────────────────────────────────────────────
// Items por rol para el modo multi-rol (idénticos a menuConfigs, sin secciones)
// Incluye Dashboard como primer item de cada sección.
// ─────────────────────────────────────────────────────────────────────────────
export const roleMenuItems = {
    Admin: [
        {
            label: "Inicio",
            route: "admin.dashboard",
            icon: mdiHome,
            permission: "admin.dashboard",
        },
        {
            label: "Solicitudes",
            route: "admin.applications.index",
            icon: mdiFileDocumentMultiple,
            permission: "applications.index",
        },
        {
            label: "Seguridad",
            icon: mdiSecurity,
            items: [
                {
                    label: "Usuarios",
                    route: "admin.evaluators.index",
                    icon: mdiAccount,
                    permission: "users.index",
                },
            ],
        },
        {
            label: "Reconocimiento",
            route: "admin.recognitions.index",
            icon: mdiStar,
            permission: "recognitions.index",
        },
    ],
    Evaluador: [
        {
            label: "Inicio",
            route: "evaluator.dashboard",
            icon: mdiHome,
        },
        {
            label: "Evaluaciones",
            route: "evaluator.evaluations.index",
            icon: mdiSchool,
            permission: "evaluator.evaluations.index",
        },
        {
            label: "Reconocimientos",
            route: "evaluator.recognitions.index",
            icon: mdiStar,
            permission: "evaluator.recognitions.index",
        },
    ],
    Docente: [
        {
            label: "Inicio",
            route: "teacher.dashboard",
            icon: mdiHome,
        },
        {
            label: "Convocatorias",
            route: "teacher.announcements.index",
            icon: mdiBullhorn,
            permission: "announcements.index",
        },
        {
            label: "Reconocimientos",
            route: "teacher.recognitions.index",
            icon: mdiStar,
            permission: "teacher.recognitions.index",
        },
    ],
};

// Etiquetas de sección para el sidebar multi-rol
export const roleSectionLabels = {
    Admin: "Gestión de Administrador",
    Evaluador: "Gestión de Evaluador",
    Docente: "Gestión de Docente",
};

// ─────────────────────────────────────────────────────────────────────────────
// Configuraciones completas para layouts de un solo rol (sin cambios)
// ─────────────────────────────────────────────────────────────────────────────
export const menuConfigs = {
    superAdmin: [
        { label: "Inicio", icon: mdiHome, iconName: "Home", items: [{ label: "Panel principal", route: "superadmin.dashboard", icon: mdiHome, iconName: "Home" }] },
        { label: "Administración", icon: mdiOfficeBuilding, iconName: "Building2", items: [
            { label: "Mensajes de contacto", route: "superadmin.contact-messages.index", icon: mdiBell, iconName: "Mail" },
            { label: "Control de Solicitudes", route: "superadmin.control-applications", icon: mdiFileDocumentMultiple, iconName: "FileText" },
            { label: "Convocatorias", route: "announcements.index", icon: mdiBullhorn, iconName: "Megaphone", permission: "announcements.index" },
        ] },
        { label: "Seguridad", icon: mdiSecurity, iconName: "Shield", items: getSecurityItems("security.").map((item, index) => ({ ...item, iconName: ["Layers", "Lock", "KeyRound", "Users"][index] })) },
        { label: "Catálogos", icon: mdiBookOpenPageVariant, iconName: "Folder", items: [
            ...getCatalogItems("catalog.").map((item, index) => ({ ...item, label: index === 4 ? "Rúbricas" : item.label, iconName: ["Building2", "Lightbulb", "ListTree", "FileText", "ClipboardList", "Files"][index] })),
            { label: "Documentos de Trabajo", route: "superadmin.work-documents.index", icon: mdiFileDocumentMultiple, iconName: "FileText" },
        ] },
        { label: "Respaldos", route: "superadmin.backup.index", icon: mdiDatabase, iconName: "Database" },
    ],
    admin: [
        { label: "Inicio", icon: mdiHome, iconName: "Home", adminGroup: true, items: [
            { label: "Panel principal", route: "admin.dashboard", iconName: "Home", icon: mdiHome, permission: "admin.dashboard" },
        ] },
        { label: "Gestión", icon: mdiOfficeBuilding, iconName: "Building2", adminGroup: true, items: [
            { label: "Solicitudes", route: "admin.applications.index", iconName: "FileText", icon: mdiFileDocumentMultiple, permission: "applications.index" },
            { label: "Reconocimiento", route: "admin.recognitions.index", iconName: "Award", icon: mdiStar, permission: "recognitions.index" },
            { label: "Respaldos", route: "superadmin.backup.index", iconName: "Database", icon: mdiDatabase, permission: "backup.index" },
        ] },
        { label: "Seguridad", icon: mdiSecurity, iconName: "Shield", adminGroup: true, items: [
            { label: "Usuarios", route: "admin.evaluators.index", iconName: "Users", icon: mdiAccount, permission: "users.index" },
        ] },
    ],
    teacher: [
        { label: "Inicio", icon: mdiHome, iconName: "Home", items: [
            { label: "Panel principal", route: "teacher.dashboard", icon: mdiHome, iconName: "Home" },
        ] },
        { label: "Gestión", icon: mdiOfficeBuilding, iconName: "Building2", items: [
            { label: "Convocatorias", route: "teacher.announcements.index", icon: mdiBullhorn, iconName: "Megaphone", permission: "announcements.index" },
            { label: "Reconocimientos", route: "teacher.recognitions.index", icon: mdiStar, iconName: "Award", permission: "teacher.recognitions.index" },
        ] },
        { label: "Seguridad", icon: mdiSecurity, iconName: "Shield", items: getSecurityItems("security.").map((item, index) => ({ ...item, iconName: ["Layers", "Lock", "KeyRound", "Users"][index] })) },
        { label: "Catálogo", icon: mdiBookOpenPageVariant, iconName: "Folder", items: getCatalogItems("catalog.").map((item, index) => ({ ...item, iconName: ["Building2", "Lightbulb", "ListTree", "FileText", "ClipboardList", "Files"][index] })) },
    ],
    evaluator: [
        { label: "Inicio", icon: mdiHome, iconName: "Home", items: [
            { label: "Panel principal", route: "evaluator.dashboard", icon: mdiHome, iconName: "Home" },
        ] },
        { label: "Gestión", icon: mdiOfficeBuilding, iconName: "Building2", items: [
            { label: "Evaluaciones", route: "evaluator.evaluations.index", icon: mdiSchool, iconName: "ClipboardList", permission: "evaluator.evaluations.index" },
            { label: "Reconocimientos", route: "evaluator.recognitions.index", icon: mdiStar, iconName: "Award", permission: "evaluator.recognitions.index" },
        ] },
        { label: "Seguridad", icon: mdiSecurity, iconName: "Shield", items: getSecurityItems("security.").map((item, index) => ({ ...item, iconName: ["Layers", "Lock", "KeyRound", "Users"][index] })) },
        { label: "Catálogo", icon: mdiBookOpenPageVariant, iconName: "Folder", items: getCatalogItems("catalog.").map((item, index) => ({ ...item, iconName: ["Building2", "Lightbulb", "ListTree", "FileText", "ClipboardList", "Files"][index] })) },
    ],
};

