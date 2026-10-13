<template>
    <!-- Mobile Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="sidebarOpen && isMobile"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        ></div>
    </Transition>

    <!-- Sidebar -->
    <aside
        :class="[
            { 'supreme-sidebar': supremeSidebar, 'administrative-sidebar': administrativeSidebar, 'sidebar-mobile': isMobile, 'supreme-collapsed': sidebarCollapsed && !isMobile },
            'bg-[var(--institutional-primary)] text-white flex flex-col shadow-lg transition-all duration-300 z-50',
            sidebarCollapsed && !isMobile
                ? 'w-20'
                : 'w-64',
            isMobile
                ? 'fixed inset-y-0 left-0 transform ' + (sidebarOpen ? 'translate-x-0' : '-translate-x-full')
                : 'relative'
        ]"
    >
        <!-- Logo Section -->
        <div v-if="administrativeSidebar" class="supreme-brand">
            <img src="/images/TecnmBlanco.png" alt="TecNM" />
            <div v-if="!sidebarCollapsed || isMobile"><strong>Sistema EDD</strong><span>Tecnológico Nacional de México</span></div>
        </div>
        <div v-else class="p-6 flex items-center justify-center border-b border-[var(--institutional-primary-light)] min-h-[80px]">
            <Transition
                mode="out-in"
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <img
                    v-if="!sidebarCollapsed || isMobile"
                    key="full-logo"
                    src="/img/LogoTecNMCompleto.png"
                    alt="TecNM Logo"
                    class="w-full h-auto max-w-[180px]"
                />
                <img
                    v-else
                    key="small-logo"
                    src="/img/LogoTecNMCerrado.png"
                    alt="TecNM"
                    class="w-10"
                />
            </Transition>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
            <SidebarContent :items="menu" />
        </nav>

        <!-- User Profile -->
        <div class="sidebar-profile relative shrink-0 p-4 border-t border-[var(--institutional-primary-light)]">
            <button
                @click="profileMenuOpen = !profileMenuOpen"
                class="sidebar-profile-button w-full flex items-center space-x-3 hover:bg-white/5 p-2 rounded transition-colors duration-200 cursor-pointer"
            >
                <div class="sidebar-avatar w-10 h-10 rounded-full bg-[var(--institutional-primary-dark)] flex items-center justify-center text-white font-semibold flex-shrink-0">
                    {{ userInitials }}
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-sm font-medium truncate">{{ supremeSidebar ? 'Administrador Supremo' : userName }}</p>
                    <p v-if="!supremeSidebar" class="text-xs text-gray-400 truncate">{{ userEmail }}</p>
                </div>
                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': profileMenuOpen }">
                    <path fill="currentColor" d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"/>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-show="profileMenuOpen"
                    class="absolute bottom-full left-4 mb-2 bg-white rounded-xl shadow-xl z-50 w-56"
                >
                    <!-- User Info Header -->
                    <div class="px-4 py-4 bg-gradient-to-r from-[var(--institutional-primary)] to-[var(--institutional-primary-light)] flex rounded-t-lg items-center gap-3">
                        <img
                            src="/img/LogoTecNMCerrado.png"
                            alt="TecNM"
                            class="w-10 p-2"
                        />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ userName }}</p>
                            <p class="text-xs text-gray-300 truncate">{{ userEmail }}</p>
                        </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="py-2">
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
                            @click="profileMenuOpen = false; if (isMobile) sidebarOpen = false"
                        >
                            <svg viewBox="0 0 24 24" class="w-4 h-4 mr-3 text-[var(--institutional-primary)]">
                                <path fill="currentColor" :d="mdiAccount"/>
                            </svg>
                            <span class="font-medium">Perfil</span>
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition-colors duration-150 border-t border-gray-100 cursor-pointer"
                            @click="profileMenuOpen = false; if (isMobile) sidebarOpen = false"
                        >
                            <svg viewBox="0 0 24 24" class="w-4 h-4 mr-3 text-red-600">
                                <path fill="currentColor" :d="mdiLogout"/>
                            </svg>
                            <span class="font-medium">Cerrar sesión</span>
                        </Link>
                    </div>
                </div>
            </Transition>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject, ref, watch, onUnmounted, provide } from 'vue';
import { mdiAccount, mdiLogout } from "@mdi/js";
import SidebarContent from './SidebarContent.vue';

defineProps({
    menu: {
        type: Array,
        required: true,
        default: () => []
    }
});

const page = usePage();
const supremeSidebar = computed(() => page.props.auth?.primaryRole === 'Super Admin');
provide('supremeSidebar', supremeSidebar);
const administrativeSidebar = computed(() => ['Super Admin', 'Admin', 'Docente', 'Evaluador'].includes(page.props.auth?.primaryRole));
provide('administrativeSidebar', administrativeSidebar);
const sidebarOpen = inject('sidebarOpen', ref(false));
const sidebarCollapsed = inject('sidebarCollapsed', ref(false));
const isMobile = inject('isMobile', ref(false));
const profileMenuOpen = ref(false);
let savedScrollY = 0;

const lockBodyScroll = () => {
    if (typeof window === 'undefined') return;

    savedScrollY = window.scrollY || window.pageYOffset || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${savedScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
};

const unlockBodyScroll = () => {
    if (typeof window === 'undefined') return;

    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';

    window.scrollTo(0, savedScrollY);
};

const userName = computed(() => page.props.auth?.user?.name || 'Usuario');
const userEmail = computed(() => page.props.auth?.user?.email || '');
const userInitials = computed(() => {
    if (supremeSidebar.value) return 'AS';
    const name = userName.value;
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

watch(
    () => sidebarOpen.value && isMobile.value,
    (isOpen) => {
        if (isOpen) {
            lockBodyScroll();
            return;
        }

        unlockBodyScroll();
    },
    { immediate: true }
);

onUnmounted(() => {
    unlockBodyScroll();
});
</script>

<style scoped>
.supreme-sidebar { width:256px; flex-shrink:0; }
.supreme-sidebar.supreme-collapsed { width:80px; }
.supreme-brand { display:flex; align-items:center; gap:12px; min-height:80px; padding:16px 20px; border-bottom:1px solid #ffffff16; flex-shrink:0; }
.supreme-brand img { width:34px; height:46px; object-fit:contain; flex-shrink:0; }
.supreme-brand strong { display:block; font-size:14px; font-weight:700; line-height:1.3; }
.supreme-brand span { display:block; font-size:11px; line-height:1.4; color:#dbeafe; }
.supreme-sidebar .sidebar-profile { border-color:#ffffff16; padding:12px 16px; }
.supreme-sidebar .sidebar-avatar { background:#f8fafc; color:#102344; border-radius:10px; font-weight:400; }
.supreme-sidebar .sidebar-profile-button p { font-size:16px; }
.administrative-sidebar { width:16rem; height:100svh; font-family:'Instrument Sans',sans-serif; flex-shrink:0; }
.administrative-sidebar.supreme-collapsed { width:80px; }
.administrative-sidebar.sidebar-mobile { width:min(18rem,100vw); }
.administrative-sidebar :deep(nav .font-semibold) { font-weight:700; }

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.2) transparent; }
</style>

<style scoped>
.administrative-sidebar { background:var(--institutional-primary, #1b396a); }
</style>
