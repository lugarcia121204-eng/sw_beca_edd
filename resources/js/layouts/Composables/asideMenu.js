import {
    mdiMonitorDashboard,
} from "@mdi/js";
import securityMenu from "./Menus/securityMenu";

export const baseMenu = [
    {
        labelGroup: "Inicio",
        items: [
            {
                label: "Dashboard",
                route: "dashboard",
                icon: mdiMonitorDashboard,
            },
        ],
    },
    {
        labelGroup: "Administración",
        items: [
            ...securityMenu,
        ],
    },
];

export const useAside = () => {
    return {
        asideMenu: baseMenu,
    };
};
