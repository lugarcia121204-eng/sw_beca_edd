<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <AppSidebar :menu="multiRoleMenu" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                <!-- Toggle Button and Notifications -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <!-- Mobile Menu Button -->
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-md text-gray-900 hover:bg-gray-200 transition-colors duration-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" class="w-6 h-6">
                                <path d="M460-320v-320L300-480l160 160ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm440-80h120v-560H640v560Zm-80 0v-560H200v560h360Zm80 0h120-120Z"/>
                            </svg>
                        </button>

                        <!-- Desktop Toggle Button -->
                        <button
                            @click="sidebarCollapsed = !sidebarCollapsed"
                            class="hidden lg:block p-2 rounded-md text-gray-900 hover:bg-gray-200 transition-colors duration-200"
                            :title="sidebarCollapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
                        >
                            <svg v-if="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" class="w-6 h-6">
                                <path d="M460-320v-320L300-480l160 160ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm440-80h120v-560H640v560Zm-80 0v-560H200v560h360Zm80 0h120-120Z"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" class="w-6 h-6">
                                <path d="M300-640v320l160-160-160-160ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm440-80h120v-560H640v560Zm-80 0v-560H200v560h360Zm80 0h120-120Z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Notifications Icon -->
                    <NotificationsDropdown />
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, provide } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { roleMenuItems, roleSectionLabels } from '@/config/menu/menuConfig';
import AppSidebar from '@/components/Sidebar/AppSidebar.vue';
import NotificationsDropdown from '@/components/NotificationsDropdown.vue';

const page = usePage();

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const isMobile = ref(typeof window !== 'undefined' ? window.innerWidth < 1024 : false);

provide('sidebarOpen', sidebarOpen);
provide('sidebarCollapsed', sidebarCollapsed);
provide('isMobile', isMobile);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

/**
 * Construye el menú dinámico a partir de los roles del usuario.
 * Cada rol aporta:
 *   - Un item de tipo 'section' con el label "Gestión de [Rol]"
 *   - Sus items de menú (incluyendo Inicio/Dashboard como primer item)
 */
const multiRoleMenu = computed(() => {
    const rolesList = page.props.auth?.roles_list ?? [];
    const menu = [];

    // Orden de visualización preferido
    const roleOrder = ['Admin', 'Evaluador', 'Docente'];
    const sortedRoles = roleOrder.filter(r => rolesList.includes(r));

    for (const role of sortedRoles) {
        const items = roleMenuItems[role];
        const label = roleSectionLabels[role];

        if (!items || !label) continue;

        // Encabezado de sección
        menu.push({ type: 'section', label });

        // Items del rol
        menu.push(...items);
    }

    return menu;
});
</script>

