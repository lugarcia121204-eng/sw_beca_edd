<template>
    <div class="relative" ref="dropdownRef">
        <!-- Notifications Button -->
        <button
            @click="toggleDropdown"
            class="p-2 rounded-md text-gray-900 hover:bg-gray-200 transition-colors duration-200 relative cursor-pointer"
            title="Notificaciones"
        >
            <svg viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor;">
                <path :d="mdiBellOutline"/>
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center border-2 border-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Modal -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 max-h-[500px] overflow-hidden flex flex-col"
            >
                <!-- Header with Brand Gradient -->
                <div class="px-5 py-4 bg-gradient-to-r from-[var(--institutional-primary)] to-[#2d5a9e] flex items-center justify-between shadow-sm">
                    <h3 class="text-white font-bold text-base flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" style="fill: currentColor;">
                            <path :d="mdiBellOutline"/>
                        </svg>
                        Notificaciones
                    </h3>
                    <button
                        v-if="notifications.length > 0 && hasUnread"
                        @click="markAllAsRead"
                        class="text-blue-100 text-xs font-semibold hover:text-white transition underline underline-offset-2 cursor-pointer"
                    >
                        Marcar todas
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="overflow-y-auto flex-1 bg-gray-50/50">
                    <div v-if="loading" class="p-10 text-center text-gray-400">
                        <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-[var(--institutional-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div v-else-if="notifications.length === 0" class="p-10 text-center text-gray-400">
                        <p class="text-sm font-medium">Bandeja vacía</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="p-4 transition group bg-white hover:bg-gray-50 relative"
                            :class="{ 'bg-blue-50/30': !notification.read_at }"
                        >
                            <!-- Unread Indicator Marker -->
                            <div v-if="!notification.read_at" class="absolute left-0 top-0 bottom-0 w-1 bg-[var(--institutional-primary)]"></div>

                            <div class="flex items-start gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm truncate leading-tight">
                                            {{ notification.title }}
                                        </h4>
                                    </div>
                                    
                                    <p class="text-[13px] text-gray-700 leading-snug line-clamp-2 mb-1.5">
                                        {{ getShortMessage(notification) }}
                                    </p>

                                    <div class="flex items-center gap-2">
                                        <p class="text-[11px] text-gray-400 font-medium">
                                            {{ formatDate(notification.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 self-center ml-2">
                                    <button
                                        v-if="!notification.read_at"
                                        @click.stop="markAsRead(notification.id)"
                                        class="p-1 text-gray-400 hover:text-green-600 transition cursor-pointer"
                                        title="Leída"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 -960 960 960" fill="currentColor">
                                            <path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="deleteNotification(notification.id)"
                                        class="p-1 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100 cursor-pointer"
                                        title="Eliminar"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 -960 960 960" fill="currentColor">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { 
    mdiBellOutline, 
    mdiBellOffOutline
} from '@mdi/js';

const page = usePage();
const isOpen = ref(false);
const loading = ref(false);
const notifications = ref([]);
const dropdownRef = ref(null);

// Unread count from global props
const unreadCount = computed(() => page.props.unreadNotifications || 0);

// Check if there are unread notifications
const hasUnread = computed(() => notifications.value.some(n => !n.read_at));

// Get notification route based on user role
const getNotificationRoute = () => {
    const primaryRole = page.props.auth?.primaryRole;
    const userRoles = page.props.auth?.roles;
    
    let prefix = 'admin';
    
    if (primaryRole === 'Admin' || (userRoles && userRoles.includes && userRoles.includes('Admin'))) {
        prefix = 'admin';
    } else if (primaryRole === 'Evaluador' || (userRoles && userRoles.includes && userRoles.includes('Evaluador'))) {
        prefix = 'evaluator';
    } else if (primaryRole === 'Docente' || (userRoles && userRoles.includes && userRoles.includes('Docente'))) {
        prefix = 'teacher';
    }
    
    return prefix;
};

// Toggle dropdown
const toggleDropdown = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await loadNotifications();
    }
};

// Load notifications
const loadNotifications = async () => {
    loading.value = true;
    try {
        const prefix = getNotificationRoute();
        const response = await axios.get(route(`${prefix}.notifications.index`));
        notifications.value = response.data.notifications?.data || [];
    } catch (error) {
        console.error('Error loading notifications:', error);
    } finally {
        loading.value = false;
    }
};

// Mark as read
const markAsRead = async (id) => {
    try {
        const prefix = getNotificationRoute();
        await axios.post(route(`${prefix}.notifications.mark-as-read`, id));
        
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.read_at = new Date().toISOString();
        }
        
        router.reload({ only: ['unreadNotifications'] });
    } catch (error) {
        console.error('Error marking as read:', error);
    }
};

// Mark all as read
const markAllAsRead = async () => {
    try {
        const prefix = getNotificationRoute();
        await axios.post(route(`${prefix}.notifications.mark-all-as-read`));
        
        notifications.value.forEach(n => {
            n.read_at = new Date().toISOString();
        });
        
        router.reload({ only: ['unreadNotifications'] });
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

// Delete notification
const deleteNotification = async (id) => {
    try {
        const prefix = getNotificationRoute();
        await axios.delete(route(`${prefix}.notifications.destroy`, id));
        
        notifications.value = notifications.value.filter(n => n.id !== id);
        router.reload({ only: ['unreadNotifications'] });
    } catch (error) {
        console.error('Error deleting notification:', error);
        alertaError('Error', 'No se pudo eliminar la notificación');
    }
};

// Get simplified message
const getShortMessage = (notification) => {
    if (!notification.data) return '';
    
    if (notification.type === 'weekly_summary') {
        return `Se han registrado ${notification.data.total || 0} nuevas solicitudes esta semana.`;
    }
    
    return notification.data.message || '';
};

// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
    
    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        return diffInMinutes <= 1 ? 'Ahora' : `Hace ${diffInMinutes} m`;
    }
    if (diffInHours < 24) {
        return `Hace ${diffInHours} h`;
    }
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 1) return 'Hoy';
    if (diffInDays === 1) return 'Ayer';
    if (diffInDays < 7) {
        return `Hace ${diffInDays} d`;
    }
    
    return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
