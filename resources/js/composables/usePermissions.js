import { usePage } from "@inertiajs/vue3";

export const useCan = (permission) => !!usePage().props.auth.can[permission];
export const useRole = (role) => !!usePage().props.auth.roles[role];
export const verifyPermission = (permission) => useCan(permission);
