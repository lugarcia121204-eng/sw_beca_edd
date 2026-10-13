<template>
    <div class="flex h-svh bg-gray-50 overflow-hidden">
        <AppSidebar :menu="currentMenu" />

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
                            class="lg:hidden p-2 rounded-md text-gray-900 hover:bg-gray-200 transition-colors duration-200 cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" class="w-6 h-6">
                                <path d="M460-320v-320L300-480l160 160ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm440-80h120v-560H640v560Zm-80 0v-560H200v560h360Zm80 0h120-120Z"/>
                            </svg>
                        </button>

                        <!-- Desktop Toggle Button -->
                        <button
                            @click="sidebarCollapsed = !sidebarCollapsed"
                            class="hidden lg:block p-2 rounded-md text-gray-900 hover:bg-gray-200 transition-colors duration-200 cursor-pointer"
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
                    <NotificationsDropdown v-if="showNotifications" />
                </div>
                
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
// import AppSidebar from '@/components/Sidebar/AppSidebar.vue';
import { ref, onMounted, onUnmounted, onUpdated, nextTick, provide, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { menuConfigs } from '@/config/menu/menuConfig';
import AppSidebar from '@/components/Sidebar/AppSidebar.vue';
import NotificationsDropdown from '@/components/NotificationsDropdown.vue';

const page = usePage();
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const isMobile = ref(typeof window !== 'undefined' ? window.innerWidth < 1024 : false);

// Provide state to sidebar and other components
provide('sidebarOpen', sidebarOpen);
provide('sidebarCollapsed', sidebarCollapsed);
provide('isMobile', isMobile);

const currentMenu = computed(() => {
    const userRole = page.props.auth.primaryRole; // Use the shared prop 'primaryRole'
    // Assuming backend passes roles or we check permissions. 
    // Wait, UserResource provided 'role' (singular primary role).
    
    // If 'role' is not directly available on user object (it usually is if using Jetstream/Fortify/Inertia shared props)
    // Check if roles array exists or checks permissions.
    
    // Let's assume we can get the primary role name. 
    // In previous `UserResource.php` we added `role` field.
    
    // Mapping:
    if (userRole === 'Super Admin') return menuConfigs.superAdmin;
    if (userRole === 'Admin') return menuConfigs.admin;
    if (userRole === 'Docente') return menuConfigs.teacher;
    if (userRole === 'Evaluador') return menuConfigs.evaluator;
    
    return menuConfigs.superAdmin; // Default fallback
});

// Show notifications for Admin, Evaluador, Docente
const showNotifications = computed(() => {
    const userRole = page.props.auth.primaryRole;
    return ['Admin', 'Evaluador', 'Docente'].includes(userRole);
});

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

const applyMobileTableLabels = () => {
    const tables = document.querySelectorAll('main table');

    tables.forEach((table) => {
        const headers = Array.from(table.querySelectorAll('thead th')).map((th) =>
            (th.textContent || '').replace(/\s+/g, ' ').trim()
        );

        if (!headers.length || !table.querySelector('tbody')) return;

        table.setAttribute('data-mobile-stack', 'true');

        table.querySelectorAll('tbody tr').forEach((tr) => {
            const cells = Array.from(tr.children).filter((el) => el.tagName === 'TD');

            cells.forEach((td, index) => {
                const label = headers[index] || `Campo ${index + 1}`;
                td.setAttribute('data-label', label);

                if (/acciones/i.test(label)) {
                    td.classList.add('table-actions-cell');
                }
            });
        });
    });
};

const handleInertiaFinish = () => {
    nextTick(() => applyMobileTableLabels());
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('inertia:finish', handleInertiaFinish);
    nextTick(() => applyMobileTableLabels());
});

onUpdated(() => {
    nextTick(() => applyMobileTableLabels());
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('inertia:finish', handleInertiaFinish);
});
</script>

