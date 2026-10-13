<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { ref } from 'vue';
import { mdiDatabaseOutline, mdiLock, mdiLockOff } from '@mdi/js';
import { alertaCargando, alertaExito, alertaError, cerrarAlerta } from '@/utils/alerts.js';
import { useCan } from '@/composables/usePermissions';

const canCreate = useCan('backup.create');

const props = defineProps({
    hasFullBackup: { type: Boolean, default: false },
});

const showOrderBanner = ref(false);

const trySelectIncremental = () => {
    if (!props.hasFullBackup) {
        showOrderBanner.value = true;
        return;
    }
    form.backup_mode = 'incremental';
};

const descLength = ref(0);
const showInfo   = ref(true);

const form = useForm({
    name:        '',
    description: '',
    backup_mode: 'full',
    encrypted:   false,
});

const submit = () => {
    alertaCargando('Procesando...', 'Ejecutando respaldo de la base de datos, esto puede tomar unos segundos.');
    form.post(route('superadmin.backup.store'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('Respaldo completado', 'El respaldo se ha generado exitosamente.');
        },
        onError: (errors) => {
            cerrarAlerta();
            const msg = Object.values(errors)[0] ?? 'Ocurrió un error al ejecutar el respaldo.';
            alertaError('Error', msg);
        },
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Respaldo Manual" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Respaldo Manual</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiDatabaseOutline"/>
                        </svg>
                        <Link :href="route('superadmin.backup.index')" class="text-gray-700 font-medium hover:underline">Respaldos</Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Respaldo Manual</span>
                    </div>
                </div>
                <Link :href="route('superadmin.backup.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Form card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Nombre del Respaldo: <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Ej: Respaldo pre-actualización"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': form.errors.name }"
                            />
                            <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce el nombre del respaldo</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Descripción:</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                maxlength="255"
                                placeholder="Describe el motivo o contexto de este respaldo..."
                                @input="descLength = form.description.length"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)] resize-none"
                                :class="{ 'border-b-red-500': form.errors.description }"
                            ></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <div v-if="!form.errors.description" class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Describe el motivo o contexto de este respaldo</span>
                                </div>
                                <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                                <span class="text-gray-400 text-sm">{{ descLength }}/255</span>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta orden de respaldos -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                    <div v-if="showOrderBanner" class="flex items-start gap-4 px-5 py-4 bg-white border border-gray-200 border-l-4 border-l-amber-500 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F59E0B" class="flex-shrink-0 mt-0.5">
                            <path d="m40-120 440-760 440 760H40Zm138-80h604L480-720 178-200Zm302-40q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240Zm-40-120h80v-200h-80v200Zm40-100Z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600 mb-0.5">Orden de respaldos</p>
                            <p class="text-sm text-gray-800 font-medium">
                                Para que los respaldos funcionen correctamente, se debe realizar primero un <strong>Respaldo Completo (Full)</strong> como base.
                                Los respaldos <strong>Incrementales</strong> solo guardan los cambios desde ese Full previo, por lo que <span class="text-amber-600 font-semibold">no es posible restaurar un incremental sin un completo previo.</span>
                            </p>
                        </div>
                        <button type="button" @click="showOrderBanner = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                            </svg>
                        </button>
                    </div>
                    </Transition>
                    <div>
                        <label class="block mb-3 text-base text-[var(--institutional-primary)] font-medium">Tipo de Respaldo:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Completo -->
                            <button
                                type="button"
                                @click="form.backup_mode = 'full'"
                                :class="form.backup_mode === 'full'
                                    ? 'border-[var(--institutional-primary)] bg-[var(--institutional-primary)]/5 ring-2 ring-[var(--institutional-primary)]/30'
                                    : 'border-gray-200 bg-gray-50 hover:border-gray-300'"
                                class="flex items-start gap-4 p-4 rounded-lg border-2 text-left transition-all cursor-pointer"
                            >
                                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5"
                                    :class="form.backup_mode === 'full' ? 'bg-[var(--institutional-primary)]' : 'bg-gray-200'">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                                        :fill="form.backup_mode === 'full' ? 'white' : '#9CA3AF'">
                                        <path d="M160-160v-80h80v80h-80Zm160 0v-80h80v80h-80Zm-160-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm160 320v-80h80v80h-80Zm160 0v-80h80v80h-80Zm160 160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80ZM320-640v-80h80v80h-80Zm160 480v-80h80v80h-80Zm-160 0v-80h80v80h-80Zm160-320v-80h80v80h-80Zm160 0v-80h80v80h-80Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Completo (Full)</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Esquema completo + todos los datos, rutinas, triggers y eventos. Recomendado para respaldo general.</p>
                                </div>
                            </button>
                            <!-- Incremental -->
                            <button
                                type="button"
                                @click="trySelectIncremental()"
                                :class="!hasFullBackup
                                    ? 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed'
                                    : form.backup_mode === 'incremental'
                                        ? 'border-[var(--institutional-primary)] bg-[var(--institutional-primary)]/5 ring-2 ring-[var(--institutional-primary)]/30 cursor-pointer'
                                        : 'border-gray-200 bg-gray-50 hover:border-gray-300 cursor-pointer'"
                                class="relative flex items-start gap-4 p-4 rounded-lg border-2 text-left transition-all"
                            >
                                <div v-if="!hasFullBackup" class="absolute top-2 right-2 w-5 h-5 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#6B7280">
                                        <path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Zm-120 480v-400 400Z"/>
                                    </svg>
                                </div>
                                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5"
                                    :class="form.backup_mode === 'incremental' ? 'bg-[var(--institutional-primary)]' : 'bg-gray-200'">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                                        :fill="form.backup_mode === 'incremental' ? 'white' : '#9CA3AF'">
                                        <path d="M480-120 200-272v-200L80-536l400-224 400 224v320h-80v-276l-120 67v200L480-120Zm0-332 274-153-274-154-274 154 274 153Zm0 241 160-89v-151L480-321 320-411v151l160 89Zm0-241Zm0 90Zm0 0Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Incremental</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Solo los registros nuevos o modificados desde el último respaldo completo. Requiere un Full previo.</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="form.errors.backup_mode" class="mt-1 text-sm text-red-600">{{ form.errors.backup_mode }}</p>
                    </div>

                    <!-- Cifrado del Archivo -->
                    <div class="flex items-center justify-between py-4 px-5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="form.encrypted ? 'bg-[var(--institutional-primary)]' : 'bg-gray-200'">
                                <svg viewBox="0 0 24 24" class="w-5 h-5" :style="{ fill: form.encrypted ? 'white' : '#6B7280' }">
                                    <path :d="form.encrypted ? mdiLock : mdiLockOff"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Cifrado del Archivo</p>
                                <p class="text-xs text-gray-500">{{ form.encrypted ? 'El archivo será cifrado' : 'Sin cifrado (SQL estándar)' }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="form.encrypted = !form.encrypted"
                            class="relative inline-flex w-14 h-7 items-center rounded-full transition-colors focus:outline-none cursor-pointer"
                            :class="form.encrypted ? 'bg-[var(--institutional-primary)]' : 'bg-gray-300'"
                        >
                            <span
                                class="inline-block w-5 h-5 bg-white rounded-full shadow-md transform transition-transform"
                                :class="form.encrypted ? 'translate-x-8' : 'translate-x-1'"
                            />
                        </button>
                    </div>

                    <!-- Info note -->
                    <div v-if="showInfo" class="flex items-start gap-4 px-5 py-4 bg-white border border-gray-200 border-l-4 border-l-[var(--institutional-primary)] rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A" class="flex-shrink-0 mt-0.5">
                            <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--institutional-primary)] mb-0.5">Instrucciones</p>
                            <p v-if="form.backup_mode === 'full'" class="text-sm text-gray-800 font-medium">El respaldo incluye la <strong>base de datos completa</strong>: esquema, datos, rutinas almacenadas, triggers y eventos. El proceso puede tardar unos momentos dependiendo del tamaño de los datos.</p>
                            <p v-else class="text-sm text-gray-800 font-medium">Solo se respaldarán los <strong>registros nuevos o modificados</strong> desde el último respaldo completo. <span class="text-amber-600 font-semibold">Requiere que exista un respaldo Completo previo</span> como línea base.</p>
                        </div>
                        <button type="button" @click="showInfo = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route('superadmin.backup.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </Link>
                        <button
                            v-if="canCreate"
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 cursor-pointer bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium"
                        >
                            <span v-if="!form.processing">Ejecutar Respaldo Ahora</span>
                            <span v-else>Ejecutando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

