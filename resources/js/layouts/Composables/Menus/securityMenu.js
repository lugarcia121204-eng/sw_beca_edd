import {
    mdiViewModule,
    mdiLockCheckOutline,
    mdiAccountSupervisor,
    mdiAccount,
    mdiSecurity,
} from "@mdi/js";

export default [
    {
        label: "Seguridad",
        permission: "menu.security",
        icon: mdiSecurity,
        menu: [
            {
                label: "Módulos",
                route: "modules.index",
                icon: mdiViewModule,
                permission: "modules.index",
            },
            {
                label: "Permisos",
                route: "permissions.index",
                icon: mdiLockCheckOutline,
                permission: "permissions.index",
            },
            {
                label: "Roles",
                route: "roles.index",
                icon: mdiAccountSupervisor,
                permission: "roles.index",
            },
            {
                label: "Usuarios",
                route: "users.index",
                icon: mdiAccount,
                permission: "users.index",
            },
        ],
    },
]
