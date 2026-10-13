import {
    mdiAccount,
    mdiCogOutline,
    mdiEmail,
    mdiLogout,
    mdiThemeLightDark,
} from "@mdi/js";

export default [
    {
        items: [
            {
                label: "Cuenta",
                icon: mdiAccount,
                route: "profile.edit",
            },
        ],
    },
    {
        isSeparator: true,
    },
    {
        items: [
            {
                label: "Cerrar Sesión",
                icon: mdiLogout,
                route: "logout",
                method: "post",
            },
        ],
    },
];
