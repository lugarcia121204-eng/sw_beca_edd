<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <AppSidebar :menu="activeMenu" />

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
import { ref, computed, onMounted, onUnmounted, provide, watch } from 'vue';
import { menuConfigs, roleMenuItems, roleSectionLabels } from '@/config/menu/menuConfig';
import AppSidebar from '@/components/Sidebar/AppSidebar.vue';
import NotificationsDropdown from '@/components/NotificationsDropdown.vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

const activeMenu = computed(() => {
    if (page.props.auth?.layoutName === 'MultiRoleLayout') {
        const rolesList = page.props.auth?.roles_list ?? [];
        const roleOrder = ['Admin', 'Evaluador', 'Docente'];
        const menu = [];
        for (const role of roleOrder.filter(r => rolesList.includes(r))) {
            const items = roleMenuItems[role];
            const label = roleSectionLabels[role];
            if (!items || !label) continue;
            menu.push({ type: 'section', label });
            menu.push(...items);
        }
        return menu;
    }
    return menuConfigs.evaluator;
});

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const isMobile = ref(typeof window !== 'undefined' ? window.innerWidth < 1024 : false);

// Provide state to sidebar and other components
provide('sidebarOpen', sidebarOpen);
provide('sidebarCollapsed', sidebarCollapsed);
provide('isMobile', isMobile);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

// Watch for flash messages

watch(() => page.props.flash?.success, (newValue) => {
    if (newValue) {
        Swal.fire({
            title: newValue,
            icon: 'success',
            confirmButtonColor: '#1B396A',
        });
    }
});

watch(() => page.props.flash?.error, (newValue) => {
    if (newValue) {
        Swal.fire({
            title: 'Error',
            text: newValue,
            icon: 'error',
            confirmButtonColor: '#d33',
        });
    }
});

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});
</script>

