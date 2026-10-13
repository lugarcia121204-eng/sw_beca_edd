<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { mdiDatabaseOutline, mdiBell, mdiBellOff, mdiCalendarClock } from '@mdi/js';
import { alertaCargando, alertaExito, alertaError, cerrarAlerta } from '@/utils/alerts.js';
import { useCan } from '@/composables/usePermissions';

const canSchedule = useCan('backup.schedule');

const props = defineProps({
    schedule:      { type: Object,  required: true },
    hasFullBackup: { type: Boolean, default: false },
});

const frequencyOptions = [
    { label: 'Diario',   value: 'daily'   },
    { label: 'Semanal',  value: 'weekly'  },
    { label: 'Mensual',  value: 'monthly' },
];

const form = useForm({
    frequency:            props.schedule.frequency    ?? 'daily',
    run_time:             props.schedule.run_time     ?? '02:00',
    backup_mode:          props.hasFullBackup ? (props.schedule.backup_mode ?? 'full') : 'full',
    email_notifications:  props.schedule.email_notifications ?? false,
    is_active:            props.schedule.is_active    ?? true,
});

const frequencyLabel = computed(() => {
    return frequencyOptions.find(o => o.value === form.frequency)?.label ?? '';
});

const showScheduleInfo = ref(true);
const showNextRunInfo  = ref(true);

const showOrderBanner = ref(false);

const timeHour = computed({
    get: () => (form.run_time || '02:00').split(':')[0],
    set: (val) => { form.run_time = `${val}:${timeMinute.value}`; }
});
const timeMinute = computed({
    get: () => (form.run_time || '02:00').split(':')[1],
    set: (val) => { form.run_time = `${timeHour.value}:${val}`; }
});
const minuteOptions = Array.from({ length: 12 }, (_, i) => String(i * 5).padStart(2, '0'));

const timePickerOpen = ref(false);
const timePickerRef  = ref(null);

const selectHour = (h) => { timeHour.value = h; };
const selectMinute = (m) => { timeMinute.value = m; timePickerOpen.value = false; };

const handleOutsideClick = (e) => {
    if (timePickerRef.value && !timePickerRef.value.contains(e.target)) {
        timePickerOpen.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick));

const trySelectIncremental = () => {
    if (!props.hasFullBackup) {
        showOrderBanner.value = true;
        return;
    }
    form.backup_mode = 'incremental';
};

const submit = () => {
    alertaCargando('Guardando...', 'Por favor espera.');
    form.post(route('superadmin.backup.update-schedule'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('Configuración guardada', 'La programación de respaldos ha sido actualizada correctamente.');
        },
        onError: (errors) => {
            cerrarAlerta();
            const msg = Object.values(errors)[0] ?? 'Ocurrió un error al guardar la configuración.';
            alertaError('Error', msg);
        },
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Programación Automática" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Programación Automática</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiDatabaseOutline"/>
                        </svg>
                        <Link :href="route('superadmin.backup.index')" class="text-gray-700 font-medium hover:underline">Respaldos</Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Programación Automática</span>
                    </div>
                </div>
                <Link :href="route('superadmin.backup.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Current schedule preview -->
            <div v-if="schedule.next_run_at && showScheduleInfo"
                 class="flex items-start gap-4 px-5 py-4 bg-white border border-gray-200 border-l-4 border-l-[var(--institutional-primary)] rounded-lg shadow-sm">
                <svg viewBox="0 0 24 24" class="w-5 h-5 flex-shrink-0 mt-0.5" style="fill:#1B396A">
                    <path :d="mdiCalendarClock"/>
                </svg>
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--institutional-primary)] mb-0.5">Próximo Respaldo</p>
                    <p class="text-sm text-gray-800 font-medium">
                        Próximo respaldo programado:
                        <strong>{{ schedule.next_run_at }}</strong>
                        — Frecuencia actual: <strong>{{ schedule.frequency_label }}</strong>
                    </p>
                </div>
                <button type="button" @click="showScheduleInfo = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                    </svg>
                </button>
            </div>

            <!-- Form card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Frecuencia -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Frecuencia: <span class="text-red-500">*</span></label>
                            <VueSelect
                                v-model="form.frequency"
                                :options="frequencyOptions"
                                :reduce="o => o.value"
                                :searchable="false"
                                :clearable="false"
                                placeholder="Selecciona frecuencia"
                                class="vue-select-form"
                            />
                            <div v-if="!form.errors.frequency" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Selecciona con qué frecuencia se ejecutarán los respaldos</span>
                            </div>
                            <p v-if="form.errors.frequency" class="mt-1 text-sm text-red-600">{{ form.errors.frequency }}</p>
                        </div>

                        <!-- Hora de Ejecución -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Hora de Ejecución: <span class="text-red-500">*</span></label>
                            <div class="relative" ref="timePickerRef">
                                <!-- Trigger -->
                                <button
                                    type="button"
                                    @click="timePickerOpen = !timePickerOpen"
                                    class="w-full flex items-center gap-3 rounded-lg bg-[#F3F4F6] border-b-2 transition-colors duration-200 h-[44px] px-3 text-left cursor-pointer"
                                    :class="timePickerOpen ? 'border-b-[var(--institutional-primary)] bg-white' : (form.errors.run_time ? 'border-b-red-500' : 'border-b-gray-300')"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 transition-colors" :class="timePickerOpen ? 'text-[var(--institutional-primary)]' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="flex-1 font-base text-gray-900 text-base tracking-widest">
                                        {{ timeHour }}<span class="text-[var(--institutional-primary)]">:</span>{{ timeMinute }}
                                    </span>
                                    <span
                                        class="text-xs font-medium px-2 py-0.5 rounded-lg transition-colors shadow-sm"
                                        :class="parseInt(timeHour) >= 12 ? 'text-gray-700' : 'bg-white text-gray-700'"
                                    >
                                        {{ parseInt(timeHour) >= 12 ? 'p.m.' : 'a.m.' }}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="timePickerOpen ? 'rotate-180 text-[var(--institutional-primary)]' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown panel -->
                                <Transition
                                    enter-active-class="transition-all duration-200 ease-out"
                                    enter-from-class="opacity-0 translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition-all duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 translate-y-1"
                                >
                                    <div v-if="timePickerOpen"
                                        class="absolute z-50 top-full left-0 right-0 mt-1 bg-white rounded-xl border border-gray-200 shadow-2xl overflow-hidden"
                                    >
                                        <!-- Header -->
                                        <div class="flex items-center justify-between px-5 py-3 bg-[var(--institutional-primary)]">
                                            <span class="text-xs font-bold text-white/60 uppercase tracking-widest">Horas</span>
                                            <span class="text-2xl font-medium text-white tracking-widest">{{ timeHour }}<span class="opacity-50">:</span>{{ timeMinute }}</span>
                                            <span class="text-xs font-bold text-white/60 uppercase tracking-widest">Minutos</span>
                                        </div>
                                        <!-- Two scrollable columns -->
                                        <div class="flex h-44 divide-x divide-gray-100">
                                            <!-- Hours -->
                                            <div class="flex-1 overflow-y-auto">
                                                <button
                                                    v-for="h in 24" :key="h - 1"
                                                    type="button"
                                                    @click="selectHour(String(h - 1).padStart(2, '0'))"
                                                    class="w-full py-2.5 text-center text-sm font-medium transition-colors cursor-pointer"
                                                    :class="timeHour === String(h - 1).padStart(2, '0')
                                                        ? 'bg-[var(--institutional-primary)] text-white'
                                                        : 'text-gray-700 hover:bg-[var(--institutional-primary)]/10 hover:text-[var(--institutional-primary)]'"
                                                >
                                                    {{ String(h - 1).padStart(2, '0') }}
                                                </button>
                                            </div>
                                            <!-- Minutes -->
                                            <div class="flex-1 overflow-y-auto">
                                                <button
                                                    v-for="m in minuteOptions" :key="m"
                                                    type="button"
                                                    @click="selectMinute(m)"
                                                    class="w-full py-2.5 text-center text-sm font-medium transition-colors cursor-pointer"
                                                    :class="timeMinute === m
                                                        ? 'bg-[var(--institutional-primary)] text-white'
                                                        : 'text-gray-700 hover:bg-[var(--institutional-primary)]/10 hover:text-[var(--institutional-primary)]'"
                                                >
                                                    {{ m }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                            <div v-if="!form.errors.run_time" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Hora del día en que se ejecutará el respaldo</span>
                            </div>
                            <p v-if="form.errors.run_time" class="mt-1 text-sm text-red-600">{{ form.errors.run_time }}</p>
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
                                    <p class="text-xs text-gray-500 mt-0.5">Esquema completo + todos los datos, rutinas, triggers y eventos.</p>
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
                                    <p class="text-xs text-gray-500 mt-0.5">Solo registros nuevos o modificados desde el último respaldo completo. Requiere un Full previo.</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="form.errors.backup_mode" class="mt-1 text-sm text-red-600">{{ form.errors.backup_mode }}</p>
                    </div>

                    <!-- Notificaciones por Correo -->
                    <div class="flex items-center justify-between py-4 px-5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="form.email_notifications ? 'bg-[var(--institutional-primary)]' : 'bg-gray-200'">
                                <svg viewBox="0 0 24 24" class="w-5 h-5" :style="{ fill: form.email_notifications ? 'white' : '#6B7280' }">
                                    <path :d="form.email_notifications ? mdiBell : mdiBellOff"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Notificaciones por Correo</p>
                                <p class="text-xs text-gray-500">{{ form.email_notifications ? 'Se enviará un correo al completar cada respaldo' : 'Sin notificaciones por correo' }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="form.email_notifications = !form.email_notifications"
                            class="relative inline-flex w-14 h-7 items-center rounded-full transition-colors focus:outline-none cursor-pointer"
                            :class="form.email_notifications ? 'bg-[var(--institutional-primary)]' : 'bg-gray-300'"
                        >
                            <span
                                class="inline-block w-5 h-5 bg-white rounded-full shadow-md transform transition-transform"
                                :class="form.email_notifications ? 'translate-x-8' : 'translate-x-1'"
                            />
                        </button>
                    </div>

                    <!-- Activar / Desactivar programación -->
                    <div class="flex items-center justify-between py-4 px-5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="form.is_active ? 'bg-green-500' : 'bg-gray-200'">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" :fill="form.is_active ? 'white' : '#6B7280'">
                                    <path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm-40-120v-80q-50 0-85-35t-35-85v-40L100-680q-8 30-14 58.5T80-560q0 131 79 227t221 113Zm260-26q69-63 104.5-144T840-560v-20q0-26-2.5-48.5T826-673L560-409v89q0 50-35 85t-85 35v80q88-3 155-45t145-106Z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Programación Activa</p>
                                <p class="text-xs text-gray-500">{{ form.is_active ? 'Los respaldos automáticos están activados' : 'Los respaldos automáticos están desactivados' }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="form.is_active = !form.is_active"
                            class="relative inline-flex w-14 h-7 items-center rounded-full transition-colors focus:outline-none cursor-pointer"
                            :class="form.is_active ? 'bg-green-500' : 'bg-gray-300'"
                        >
                            <span
                                class="inline-block w-5 h-5 bg-white rounded-full shadow-md transform transition-transform"
                                :class="form.is_active ? 'translate-x-8' : 'translate-x-1'"
                            />
                        </button>
                    </div>

                    <!-- Next run preview -->
                    <div v-if="showNextRunInfo"
                         class="flex items-start gap-4 px-5 py-4 bg-white border border-gray-200 border-l-4 border-l-green-500 rounded-lg shadow-sm">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 flex-shrink-0 mt-0.5" style="fill:#16A34A">
                            <path :d="mdiCalendarClock"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-0.5">Configuración Actual</p>
                            <p class="text-sm text-gray-800 font-medium">
                                Con la configuración actual, el sistema ejecutará respaldos
                                <strong>{{ form.backup_mode === 'incremental' ? 'Incrementales' : 'Completos' }}</strong>
                                de manera <strong>{{ frequencyLabel }}</strong> a las <strong>{{ form.run_time }}</strong> hrs.
                                <span v-if="form.backup_mode === 'incremental'" class="text-amber-600 font-semibold">
                                    Asegúrate de tener un respaldo Completo previo como línea base.
                                </span>
                            </p>
                        </div>
                        <button type="button" @click="showNextRunInfo = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
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
                            v-if="canSchedule"
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 cursor-pointer bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium"
                        >
                            <span v-if="!form.processing">Guardar Configuración</span>
                            <span v-else>Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(.vue-select-form .vs__dropdown-toggle) {
    background: #F3F4F6;
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 0 8px;
    height: 44px;
    display: flex;
    align-items: center;
    transition: border-color .2s, background .2s;
}
:deep(.vue-select-form .vs__dropdown-toggle:focus-within) {
    border-bottom-color: #1B396A;
    background: white;
}
:deep(.vue-select-form .vs__selected) { color: #1F2937; font-weight: 500; font-size: 0.875rem; }
:deep(.vue-select-form .vs__search::placeholder) { color: #9CA3AF; }
:deep(.vue-select-form .vs__dropdown-menu) {
    border: 1px solid #D1D5DB;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
}
:deep(.vue-select-form .vs__dropdown-option) { padding: .75rem 1rem; color: #374151; }
:deep(.vue-select-form .vs__dropdown-option--highlight) { background: #1B396A; color: white; }
:deep(.vue-select-form .vs__open-indicator) { fill: #1B396A; transform: scale(.85); }
:deep(.vue-select-form .vs__clear) { fill: #9CA3AF; }
</style>

