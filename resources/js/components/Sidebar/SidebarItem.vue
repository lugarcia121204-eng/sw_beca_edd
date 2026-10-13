<template>
    <div v-if="isVisible">
        <template v-if="supremeSidebar || administrativeSidebar || item.adminGroup">
            <template v-if="item.items?.length">
                <button v-if="!collapsed || isMobile" type="button" class="supreme-group" :aria-expanded="isExpanded" @click="toggleGroup">
                    <component :is="supremeIcon" :size="20" :stroke-width="1.7" class="supreme-group-icon" />
                    <span>{{ item.label }}</span><ChevronDown v-if="isExpanded" :size="17" /><ChevronRight v-else :size="17" />
                </button>
                <Link v-else :href="resolveRoute(firstVisibleChild?.route)" class="supreme-single supreme-icon-only" :title="item.label" @click="closeSidebarOnMobileNavigate"><component :is="supremeIcon" :size="20" /></Link>
                <div v-if="!collapsed || isMobile" v-show="isExpanded" class="supreme-submenu">
                    <SidebarItem v-for="(child, index) in item.items" :key="child.route || index" :item="child" :is-child="true" />
                </div>
            </template>
            <Link v-else :href="resolveRoute(item.route)" :aria-current="isActive ? 'page' : undefined" :title="item.label" :class="['supreme-single', { 'supreme-child': isChild, 'supreme-large': largeMenuOption, 'supreme-active': isActive, 'supreme-icon-only': collapsed && !isMobile }]" @click="closeSidebarOnMobileNavigate">
                <svg v-if="!supremeSidebar && item.icon && !item.iconName" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" :d="item.icon" /></svg>
                <component v-else :is="supremeIcon" :size="20" :stroke-width="1.7" />
                <span v-if="!collapsed || isMobile">{{ item.label }}</span>
            </Link>
        </template>
        <template v-else>
        <!-- Group with Subitems -->
        <div v-if="item.items && item.items.length > 0">
            <!-- Expanded / Desktop View -->
            <div v-if="!collapsed || isMobile">
                <button
                    @click="toggleGroup"
                    :class="[
                        'w-full flex items-center justify-between px-6 py-3 transition-all duration-200 relative',
                        'before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-[80%] before:w-[4px] before:rounded-r-lg before:transition-colors',
                        isExpanded || isActive
                            ? 'bg-white/10 text-white before:bg-white'
                            : 'text-white/80 hover:bg-white/5 hover:text-white before:bg-transparent'
                    ]"
                >
                    <div class="flex items-center min-w-0 flex-1">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0">
                            <path fill="currentColor" :d="item.icon || mdiViewModule"/>
                        </svg>
                        <span class="ml-3 text-sm font-medium truncate" :style="administrativeSidebar ? { fontSize: '11px', fontWeight: 700 } : undefined" :title="item.label">{{ item.label }}</span>
                    </div>
                    <svg
                        viewBox="0 0 24 24"
                        class="w-3.5 h-3.5 transition-transform duration-200"
                        :class="{ 'rotate-90': isExpanded }"
                    >
                        <path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </button>

                <!-- Recursive Submenu -->
                <div v-show="isExpanded" class="bg-[var(--institutional-primary-dark)] py-1">
                    <SidebarItem
                        v-for="(subItem, index) in item.items"
                        :key="index"
                        :item="subItem"
                        :is-child="true"
                    />
                </div>
            </div>

            <!-- Collapsed Icon View -->
            <div v-else class="relative group">
                 <Link
                    v-if="item.items.length > 0"
                    :href="route(item.items[0].route)" 
                    @click="closeSidebarOnMobileNavigate"
                    :class="[
                        'flex items-center py-3 px-4 justify-center transition-all duration-200 relative',
                        'before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-[80%] before:w-[4px] before:rounded-r-lg before:transition-colors',
                        isActive
                            ? 'bg-white/10 text-white before:bg-white'
                            : 'text-white/80 hover:bg-white/5 hover:text-white before:bg-transparent'
                    ]"
                    :title="item.label"
                >
                    <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0">
                        <path fill="currentColor" :d="item.icon || mdiViewModule"/>
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Single Link Item -->
        <Link
            v-else
            :href="resolveRoute(item.route)"
            @click="closeSidebarOnMobileNavigate"
            :class="[
                'flex items-center py-3 transition-all duration-200 relative',
                 'before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-[80%] before:w-[4px] before:rounded-r-lg before:transition-colors',
                isChild 
                    ? 'px-4 py-2.5 pl-12 text-gray-300 hover:bg-[var(--institutional-primary)] hover:text-white' 
                    : (collapsed && !isMobile ? 'px-4 justify-center' : 'px-6'),
                !isChild && (isActive 
                    ? 'bg-white/10 text-white before:bg-white' 
                    : 'text-white/80 hover:bg-white/5 hover:text-white before:bg-transparent'),
                isChild && isActive ? 'bg-[var(--institutional-primary)] text-white font-medium' : ''
            ]"
            :title="collapsed && !isMobile ? item.label : ''"
        >
            <svg v-if="item.icon" viewBox="0 0 24 24" :class="[isChild ? 'w-3.5 h-3.5' : 'w-4 h-4', 'flex-shrink-0']">
                <path fill="currentColor" :d="item.icon"/>
            </svg>
            <span v-show="!collapsed || isMobile || isChild" class="ml-3 text-sm font-medium truncate" :style="administrativeSidebar ? { fontSize: largeMenuOption ? '14px' : '12px' } : undefined" :title="item.label">{{ item.label }}</span>
        </Link>
        </template>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject, ref, onMounted } from 'vue';
import { Award, Home, Building2, Shield, Folder, Database, Layers, Lock, KeyRound, Users, Lightbulb, ListTree, FileText, ClipboardList, Files, Megaphone, ChevronDown, ChevronRight } from 'lucide-vue-next';
import { mdiViewModule } from "@mdi/js";

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isChild: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const supremeSidebar = inject('supremeSidebar', ref(false));
const administrativeSidebar = inject('administrativeSidebar', ref(false));
const largeMenuOption = computed(() => ['Mensajes de contacto', 'Control de Solicitudes', 'Convocatorias', 'Respaldos'].includes(props.item.label));
const supremeIcons = { Award, Home, Building2, Shield, Folder, Database, Layers, Lock, KeyRound, Users, Lightbulb, ListTree, FileText, ClipboardList, Files, Megaphone };
const supremeIcon = computed(() => supremeIcons[props.item.iconName] || FileText);
const firstVisibleChild = computed(() => props.item.items?.find(child => hasPermission(child.permission)));
const collapsed = inject('sidebarCollapsed', ref(false));
const isMobile = inject('isMobile', ref(false));
const sidebarOpen = inject('sidebarOpen', ref(false));

const isExpanded = ref(false);

const resolveRoute = (routeName) => {
    try {
        return route(routeName);
    } catch (e) {
        return '#';
    }
};

const hasPermission = (permission) => {
    if (!permission) return true;
    return page.props.auth?.can?.[permission] || false;
};

// Check if item or any of its children matches current route
const isActive = computed(() => {
    if (props.item.items) {
        return props.item.items.some(sub => {
            try {
                return route().current(sub.route) || route().current(`${sub.route}.*`);
            } catch { return false; }
        });
    }
    
    try {
        return route().current(props.item.route) || route().current(`${props.item.route}.*`);
    } catch { return false; }
});

// Determine visibility based on permission vs children permissions
const isVisible = computed(() => {
    // If it's a group
    if (props.item.items && props.item.items.length > 0) {
        // Show if ANY child is visible
        return props.item.items.some(sub => hasPermission(sub.permission));
    }
    // If single item
    return hasPermission(props.item.permission);
});

const toggleGroup = () => {
    isExpanded.value = !isExpanded.value;
};

const closeSidebarOnMobileNavigate = () => {
    if (isMobile.value) {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    if (isActive.value) {
        isExpanded.value = true;
    }
});
</script>

<style scoped>
.supreme-group { display:flex; align-items:center; gap:12px; width:100%; padding:21px 24px; color:#e2e8f0; cursor:pointer; text-align:left; transition:background .15s; }
.supreme-group > span { flex:1; font-size:11px; font-weight:700; text-transform:uppercase; }
.supreme-group-icon { color:var(--institutional-gold); flex-shrink:0; }
.supreme-group:hover { background:#ffffff08; }
.supreme-submenu { margin:0 14px 12px 24px; border-left:1px solid #ffffff25; padding:0 0 0 14px; }
.supreme-single { display:flex; align-items:center; gap:14px; padding:17px 30px; color:#dbeafe; font-size:14px; font-weight:550; min-width:0; border-radius:6px; }
.supreme-single svg { flex-shrink:0; }
.supreme-single span { min-width:0; overflow-wrap:anywhere; }
.supreme-child { padding:11px 10px; font-size:12px; }
.supreme-large { font-size:14px; }
.supreme-single:hover,.supreme-active { background:#ffffff07; color:white; }
.supreme-icon-only { justify-content:center; padding:20px 12px; }
.supreme-group:focus-visible,.supreme-single:focus-visible { outline:2px solid var(--institutional-gold); outline-offset:-3px; }
</style>

<style scoped>
.supreme-submenu .supreme-child { margin-bottom:5px; border-radius:9px; color:#fff; font-size:12px; font-weight:650; padding:14px 12px; }
.supreme-submenu .supreme-child:hover,.supreme-submenu .supreme-active { background:#ffffff08; }
</style>
