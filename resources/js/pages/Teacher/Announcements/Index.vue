<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TeacherLayout from '@/layouts/TeacherLayout.vue';
import { mdiBullhorn } from '@mdi/js';
import { computed, ref } from 'vue';

const props = defineProps({
    announcements: Object,
    has_active_application: Boolean,
});

const expandedDescriptions = ref({});
const DESCRIPTION_LIMIT = 220;

const toggleDescription = (id) => {
    expandedDescriptions.value[id] = !expandedDescriptions.value[id];
};

const showDocumentModal = ref(false);
const currentDocumentUrl = ref('');
const currentDocumentTitle = ref('');
const currentDocumentFileUrl = ref('');

const openDocumentModal = (url, title, fileUrl) => {
    if (!url) return;
    currentDocumentUrl.value = url;
    currentDocumentFileUrl.value = fileUrl || url;
    currentDocumentTitle.value = title;
    showDocumentModal.value = true;
};

const closeDocumentModal = () => {
    showDocumentModal.value = false;
    currentDocumentUrl.value = '';
    currentDocumentFileUrl.value = '';
    currentDocumentTitle.value = '';
};

const isPdfResource = (url) => /\.pdf($|\?)/i.test(url || '');
const isImageResource = (url) => /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(url || '');

const currentDocumentIsPdf = computed(() => isPdfResource(currentDocumentFileUrl.value));
const currentDocumentIsImage = computed(() => isImageResource(currentDocumentFileUrl.value));

// Helper para parsear fecha "YYYY-MM-DD", "YYYY-MM-DD HH:MM:SS" o ISO "YYYY-MM-DDTHH:mm:ss"
const parseDateLocal = (dateString) => {
    if (!dateString) return null;
    // Tomamos los primeros 10 caracteres (YYYY-MM-DD)
    const datePart = dateString.substring(0, 10);
    const [year, month, day] = datePart.split('-').map(Number);
    
    if (!year || !month || !day) return null;

    return new Date(year, month - 1, day);
};

const getFase = (announcement) => {
    if (announcement.status !== 'activa') {
        return { 
            nombre: 'Cerrada', 
            color: 'bg-red-100 text-red-800', 
            canRegister: false 
        };
    }

    let canRegister = false;
    let registrationStatus = 'upcoming';
    
    if (announcement.calendar) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const regInicio = parseDateLocal(announcement.calendar.registration_start);
        const regFin = parseDateLocal(announcement.calendar.registration_end);
        
        if (regInicio && regFin) {
            if (today >= regInicio && today <= regFin) {
                canRegister = true;
                registrationStatus = 'open';
            } else if (today > regFin) {
                registrationStatus = 'ended';
            } else {
                registrationStatus = 'upcoming';
            }
        }
    }

    return { 
        nombre: 'Activa', 
        color: 'bg-green-100 text-green-800', 
        canRegister: canRegister,
        registrationStatus: registrationStatus,
    };
};
</script>

<template>
    <TeacherLayout>
        <Head title="Convocatorias" />
        <div class="space-y-6">
             <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Convocatoria Disponible</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBullhorn"/>
                        </svg>
                         <span class="text-gray-900 font-semibold">Convocatorias</span>
                    </div>
                </div>
            </div>

            <!-- Diseño "Featured Card" para convocatoria única/principal -->
            <div v-if="announcements.data && announcements.data.length > 0" class="max-w-5xl mx-auto space-y-8">
                <div 
                    v-for="announcement in announcements.data" 
                    :key="announcement.id"
                    :class="[
                        'rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 flex flex-col md:flex-row',
                        announcement.status === 'activa' 
                            ? 'bg-white transform hover:-translate-y-1' 
                            : 'bg-white shadow-md border border-gray-200 opacity-90'
                    ]"
                >
                    <!-- Imagen (Izquierda en desktop, Arriba en mobile) -->
                    <div class="md:w-2/5 relative h-64 md:h-auto overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent z-10 md:hidden"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-transparent z-10 hidden md:block"></div>
                        
                        <img 
                            :src="announcement.image_url || 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&q=80'" 
                            alt="Imagen de convocatoria" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                        
                        <!-- Fase Badge (Desktop: Over image top-left) -->
                    </div>
                    
                    <!-- Contenido (Derecha en desktop) -->
                    <div class="p-8 md:w-3/5 flex flex-col justify-center">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-sm font-semibold text-gray-500 uppercase tracking-widest">
                                Convocatoria {{ new Date(announcement.created_at).getFullYear() }}
                            </span>
                            <div 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-white shadow-sm"
                                :class="{
                                    'text-green-700': getFase(announcement).nombre === 'Activa',
                                    'text-red-700': getFase(announcement).nombre === 'Cerrada'
                                }"
                            >
                                <span 
                                    class="w-2.5 h-2.5 rounded-full"
                                    :class="{
                                        'bg-green-500 animate-pulse': getFase(announcement).nombre === 'Activa',
                                        'bg-red-500': getFase(announcement).nombre === 'Cerrada'
                                    }"
                                ></span>
                                <span class="text-[13px] font-bold uppercase tracking-wider">
                                    {{ getFase(announcement).nombre }}
                                </span>
                            </div>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-0 mb-3 leading-tight">
                            {{ announcement.name }}
                        </h2>
                        <div class="w-20 h-1.5 bg-[var(--institutional-primary)] rounded-full mb-4"></div>
                        
                        <div class="text-gray-600 text-lg mb-6 leading-relaxed">
                            <span>{{ (() => {
                                const desc = announcement.description || 'Sin descripción disponible para esta convocatoria.';
                                return expandedDescriptions[announcement.id] || desc.length <= DESCRIPTION_LIMIT
                                    ? desc
                                    : desc.substring(0, DESCRIPTION_LIMIT) + '...';
                            })() }}</span>
                            <button
                                v-if="(announcement.description || '').length > DESCRIPTION_LIMIT"
                                @click="toggleDescription(announcement.id)"
                                class="ml-1.5 text-[var(--institutional-primary)] font-semibold text-base hover:underline cursor-pointer focus:outline-none"
                            >
                                {{ expandedDescriptions[announcement.id] ? 'Ver menos' : 'Ver más' }}
                            </button>
                        </div>

                        <!-- Fechas Importantes (Si está activa) -->
                        <div v-if="announcement.calendar && announcement.status === 'activa'" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Inicio de Registro</p>
                                <p class="text-gray-800 font-medium font-mono">
                                    {{ parseDateLocal(announcement.calendar.registration_start)?.toLocaleDateString() || 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Fin de Registro</p>
                                <p class="text-red-600 font-medium font-mono">
                                    {{ parseDateLocal(announcement.calendar.registration_end)?.toLocaleDateString() || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 mt-auto items-center">
                             <!-- Document Link -->
                            <button
                                v-if="announcement.file_url"
                                type="button"
                                @click="openDocumentModal(route('teacher.announcements.preview', announcement.id), announcement.name, announcement.file_url)"
                                class="text-[var(--institutional-primary)] font-semibold hover:underline flex items-center gap-2 group/link cursor-pointer"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/link:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ver Convocatoria (PDF)
                            </button>

                             <!-- Botón Solicitar / Estado -->
                             <div class="flex-1 w-full sm:w-auto">
                                <!-- Estado 1: Ya aplicó a ESTA convocatoria -->
                                <div v-if="announcement.user_application" class="w-full text-center py-3 bg-gray-100 border border-gray-200 rounded-lg text-gray-600 font-medium px-4 cursor-not-allowed">
                                    <span class="flex items-center justify-center gap-2">
                                        <!-- Icono Check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Beca solicitada
                                    </span>
                                </div>
                                
                                <!-- Estado 2: Tiene revisión global pendiente (no puede aplicar a OTRAS) -->
                                <div v-else-if="has_active_application" class="w-full text-center py-3 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 font-medium px-4">
                                    <span class="flex items-center justify-center gap-2 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                                        </svg>
                                        Otra revisión en curso
                                    </span>
                                </div>
                                
                                <!-- Estado 3: Puede aplicar -->
                                <Link 
                                    v-else-if="getFase(announcement).canRegister" 
                                    :href="route('teacher.announcements.apply', announcement.id)"
                                    class="w-full flex items-center justify-center gap-2 py-3.5 bg-[var(--institutional-primary)] text-white rounded-lg font-bold hover:bg-[var(--institutional-primary-dark)] transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 px-6 cursor-pointer"
                                >
                                    <span>Solicitar Beca</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>

                                <button 
                                    v-else 
                                    disabled
                                    class="w-full py-3.5 bg-gray-100 text-gray-400 border border-gray-300 rounded-lg font-medium cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <template v-if="announcement.status === 'activa'">
                                        {{ getFase(announcement).registrationStatus === 'upcoming' ? 'Registro Próximamente' : 'Fuera de Periodo' }}
                                    </template>
                                    <template v-else>Convocatoria Inactiva</template>
                                </button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
             <div v-else class="max-w-3xl mx-auto py-16 flex flex-col items-center justify-center text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-xl font-bold text-gray-600">No hay convocatorias activas</p>
                <p class="text-gray-500 mt-2">Mantente al pendiente para futuras oportunidades.</p>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDocumentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[85vh] sm:h-[90vh] flex flex-col">
                        <div class="flex items-center justify-between p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">{{ currentDocumentTitle }}</h2>
                            <button @click="closeDocumentModal" class="text-gray-400 hover:text-gray-800 transition-colors cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-hidden bg-gray-100">
                            <iframe
                                v-if="currentDocumentIsPdf"
                                :src="currentDocumentUrl"
                                class="w-full h-full"
                                :title="currentDocumentTitle"
                                frameborder="0"
                            ></iframe>

                            <div
                                v-else-if="currentDocumentIsImage"
                                class="w-full h-full overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentDocumentUrl"
                                    :alt="currentDocumentTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>

                            <div v-else class="w-full h-full flex items-center justify-center p-8">
                                <a
                                    :href="currentDocumentUrl"
                                    target="_blank"
                                    class="px-4 py-2 rounded-md bg-[var(--institutional-primary)] text-white font-medium hover:bg-[var(--institutional-primary-dark)]"
                                >
                                    Abrir documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TeacherLayout>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}
</style>

