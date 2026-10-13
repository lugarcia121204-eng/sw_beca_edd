<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { ref, computed } from 'vue';
import { mdiDatabase, mdiDatabaseRefresh, mdiDatabaseOutline, mdiUpload } from '@mdi/js';
import { alertaExito, alertaError, alertaConfirmacionEscrita, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';
import { useCan } from '@/composables/usePermissions';

const hasRestorePermission = useCan('backup.restore');

const showWarning = ref(true);

const props = defineProps({
    backupList: { type: Array, required: true },
});

// ── Tab state ──────────────────────────────────────────────────────────
const activeTab = ref('server'); // 'server' | 'upload'

// ── Tab: restore from server backup ───────────────────────────────────
const selectedBackup = ref(null);

const form = useForm({
    backup_id: null,
});

const canRestore = computed(() => selectedBackup.value !== null);

// ── Tab: restore from uploaded file ───────────────────────────────────
const uploadForm = useForm({
    backup_file: null,
});

const uploadedFile = ref(null);
const isDragging = ref(false);

const canRestoreUpload = computed(() => uploadedFile.value !== null);

const ALLOWED_EXTS = ['.sql', '.sql.enc', '.enc'];

const validateFileExt = (file) => {
    const name = file.name.toLowerCase();
    return ALLOWED_EXTS.some(ext => name.endsWith(ext));
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (!validateFileExt(file)) {
        alertaError('Archivo inválido', 'Solo se aceptan archivos .sql o .sql.enc');
        e.target.value = '';
        return;
    }
    uploadedFile.value = file;
    uploadForm.backup_file = file;
};

const onDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (!file) return;
    if (!validateFileExt(file)) {
        alertaError('Archivo inválido', 'Solo se aceptan archivos .sql o .sql.enc');
        return;
    }
    uploadedFile.value = file;
    uploadForm.backup_file = file;
};

const removeUploadedFile = () => {
    uploadedFile.value = null;
    uploadForm.backup_file = null;
};

const formatBytes = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};

const submitUpload = async () => {
    if (!uploadedFile.value) {
        alertaError('Archivo requerido', 'Por favor selecciona un archivo de respaldo.');
        return;
    }

    const confirmed = await alertaConfirmacionEscrita(
        'Confirmar Restauración',
        `Esta acción <strong>sobrescribirá toda la base de datos</strong> con el contenido del archivo <strong>${uploadedFile.value.name}</strong>. <strong>Esta acción no se puede deshacer.</strong>`,
        'RESTAURAR'
    );

    if (!confirmed) return;

    alertaCargando('Restaurando...', 'Por favor espera mientras se restaura la base de datos.');
    uploadForm.backup_file = uploadedFile.value;
    uploadForm.post(route('superadmin.backup.restore-upload'), {
        forceFormData: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('Restauración completada', 'La base de datos fue restaurada exitosamente desde el archivo.');
        },
        onError: (errors) => {
            cerrarAlerta();
            const msg = Object.values(errors)[0] ?? 'Ocurrió un error durante la restauración.';
            alertaError('Error en la restauración', msg);
        },
    });
};

const submit = async () => {
    if (!selectedBackup.value) {
        alertaError('Selección requerida', 'Por favor selecciona un respaldo para restaurar.');
        return;
    }

    const confirmed = await alertaConfirmacionEscrita(
        'Confirmar Restauración',
        'Esta acción <strong>sobrescribirá toda la base de datos</strong> con el contenido del respaldo seleccionado. <strong>Esta acción no se puede deshacer.</strong>',
        'RESTAURAR'
    );

    if (!confirmed) return;

    alertaCargando('Restaurando...', 'Por favor espera mientras se restaura la base de datos.');
    form.backup_id = selectedBackup.value.id;
    form.post(route('superadmin.backup.do-restore'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('Restauración completada', 'La base de datos fue restaurada exitosamente.');
        },
        onError: (errors) => {
            cerrarAlerta();
            const msg = Object.values(errors)[0] ?? 'Ocurrió un error durante la restauración.';
            alertaError('Error en la restauración', msg);
        },
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Restauración de Base de Datos" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Restauración de Base de Datos</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill:#1B396A">
                            <path :d="mdiDatabaseOutline"/>
                        </svg>
                        <Link :href="route('superadmin.backup.index')" class="text-gray-700 font-medium hover:underline">Respaldos</Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Restauración de Base de Datos</span>
                    </div>
                </div>
                <Link :href="route('superadmin.backup.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Warning card -->
            <div v-if="showWarning" class="flex items-start gap-4 px-5 py-4 bg-white border border-gray-200 border-l-4 border-l-[#C9A800] rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#C9A800" class="flex-shrink-0 mt-0.5">
                    <path d="m40-120 440-760 440 760H40Zm138-80h604L480-720 178-200Zm302-40q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240Zm-40-120h80v-200h-80v200Zm40-100Z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#C9A800] mb-0.5">Advertencia</p>
                    <p class="text-sm text-gray-800 font-medium">
                        <strong>Operación irreversible</strong> — La restauración <strong>reemplaza completamente</strong> la base de datos actual con el contenido del respaldo seleccionado.
                        Todos los datos ingresados después de la fecha del respaldo se perderán permanentemente.
                        Se recomienda <strong>crear un respaldo manual</strong> antes de proceder.
                    </p>
                </div>
                <button type="button" @click="showWarning = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                    </svg>
                </button>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="flex border-b border-gray-200">
                    <button
                        type="button"
                        @click="activeTab = 'server'"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold transition cursor-pointer"
                        :class="activeTab === 'server' ? 'text-[var(--institutional-primary)] border-b-2 border-[var(--institutional-primary)] bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path :d="mdiDatabaseRefresh"/></svg>
                        Desde respaldo del servidor
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'upload'"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold transition cursor-pointer"
                        :class="activeTab === 'upload' ? 'text-[var(--institutional-primary)] border-b-2 border-[var(--institutional-primary)] bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path :d="mdiUpload"/></svg>
                        Desde archivo local
                    </button>
                </div>

                <!-- TAB: Desde servidor -->
                <div v-if="activeTab === 'server'" class="p-8">
                    <!-- No backups notice -->
                    <div v-if="!backupList || backupList.length === 0" class="py-10 text-center">
                        <svg viewBox="0 0 24 24" class="w-14 h-14 mx-auto mb-4 opacity-30" style="fill:#6B7280">
                            <path :d="mdiDatabase"/>
                        </svg>
                        <p class="text-gray-600 font-semibold text-lg">No hay respaldos disponibles</p>
                        <p class="text-gray-400 text-sm mt-1">Crea al menos un respaldo antes de intentar restaurar.</p>
                        <Link :href="route('superadmin.backup.create')" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-[var(--institutional-primary)] text-white text-sm font-semibold rounded-lg hover:bg-[#0f2347] transition">
                            Crear Respaldo Manual
                        </Link>
                    </div>

                    <!-- Form -->
                    <form v-else @submit.prevent="submit" class="space-y-6">
                    <!-- Selector -->
                    <div>
                        <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">
                            Respaldo a Restaurar: <span class="text-red-500">*</span>
                        </label>
                        <VueSelect
                            v-model="selectedBackup"
                            :options="backupList"
                            label="label"
                            placeholder="Selecciona un respaldo..."
                            class="vue-select-custom"
                        >
                            <template #option="option">
                                <div class="flex items-center justify-between gap-2 py-0.5">
                                    <span class="font-medium text-gray-800">{{ option.name }}</span>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs text-gray-400 whitespace-nowrap">{{ option.date }} · {{ option.size }}</span>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-md bg-white text-[12px] font-bold shadow-sm"
                                            :class="option.mode === 'Incremental' ? 'text-purple-700' : 'text-blue-700'"
                                        >{{ option.mode }}</span>
                                    </div>
                                </div>
                            </template>
                            <template #selected-option="option">
                                <span class="font-medium text-gray-800">{{ option.name }}</span>
                                <span class="ml-2 text-xs text-gray-400">{{ option.date }}</span>
                                <span
                                    class="ml-2 inline-flex items-center px-3 py-1 rounded-md bg-white text-[12px] font-bold shadow-sm"
                                    :class="option.mode === 'Incremental' ? 'text-purple-700' : 'text-blue-700'"
                                >{{ option.mode }}</span>
                            </template>
                        </VueSelect>
                    </div>

                    <!-- Selected backup info -->
                    <div v-if="selectedBackup" class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-2 text-sm">
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <div>
                                <span class="text-gray-500">Nombre:</span>
                                <span class="ml-2 font-semibold text-gray-800">{{ selectedBackup.name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Fecha:</span>
                                <span class="ml-2 font-semibold text-gray-800">{{ selectedBackup.date }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tamaño:</span>
                                <span class="ml-2 font-semibold text-gray-800">{{ selectedBackup.size }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tipo:</span>
                                <span class="ml-2 font-semibold text-gray-800">{{ selectedBackup.type }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-gray-500">Modo:</span>
                                <span
                                    class="ml-2 inline-flex items-center px-3 py-1 rounded-md bg-white text-[12px] font-bold shadow-sm"
                                    :class="selectedBackup.mode === 'Incremental' ? 'text-purple-700' : 'text-blue-700'"
                                >{{ selectedBackup.mode ?? 'Completo' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route('superadmin.backup.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </Link>
                        <button
                            v-if="hasRestorePermission"
                            type="button"
                            @click="submit"
                            :disabled="!canRestore || form.processing"
                            class="px-6 py-2 cursor-pointer font-medium rounded-lg transition shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white"
                        >
                            {{ form.processing ? 'Restaurando...' : 'Restaurar Base de Datos' }}
                        </button>
                    </div>
                </form>
                </div>

                <!-- TAB: Desde archivo local -->
                <div v-if="activeTab === 'upload'" class="p-8">
                    <form @submit.prevent="submitUpload" class="space-y-6">

                        <!-- Archivo de Respaldo -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">
                                Archivo de Respaldo: <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Acepta archivos <span class="font-mono font-semibold">.sql</span> (texto plano) y <span class="font-mono font-semibold">.sql.enc</span> (cifrado). Tamaño máximo: 200 MB.</p>

                            <!-- Visor de archivo si existe uno seleccionado -->
                            <div v-if="uploadedFile" class="border-2 border-gray-300 rounded-lg overflow-hidden">
                                <!-- Header del archivo -->
                                <div class="bg-gray-100 border-b border-gray-300 p-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Nombre del Archivo: {{ uploadedFile.name }}</p>
                                        <p class="text-xs text-gray-600">Tamaño: {{ formatBytes(uploadedFile.size) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label for="archivo-restauracion-change" class="px-4 py-2 bg-[var(--institutional-primary)] text-white text-sm rounded hover:bg-[#0f2347] transition cursor-pointer">
                                            Cambiar Archivo
                                        </label>
                                        <button type="button" @click="removeUploadedFile"
                                            class="px-4 py-2 bg-red-100 text-red-700 text-sm rounded hover:bg-red-200 transition cursor-pointer">
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                                <!-- Preview (SQL no tiene preview visual) -->
                                <div class="bg-gray-50 flex items-center justify-center p-10">
                                    <div class="text-center">
                                        <svg viewBox="0 0 24 24" class="w-14 h-14 mx-auto mb-3 opacity-50" style="fill:#1B396A">
                                            <path :d="mdiDatabase"/>
                                        </svg>
                                        <p class="text-gray-700 font-semibold text-sm">Archivo de respaldo listo para restaurar</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ uploadedFile.name }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Drop zone cuando NO hay archivo -->
                            <div v-else class="flex items-center justify-center w-full">
                                <label
                                    for="archivo-restauracion"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="onDrop"
                                    class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer transition-all"
                                    :class="isDragging ? 'border-[var(--institutional-primary)] bg-gradient-to-br from-[#EFF6FF] to-[#DBEAFE]' : 'bg-gradient-to-br from-[#F3F4F6] to-[#E5E7EB] hover:bg-gradient-to-br hover:from-[#EFF6FF] hover:to-[#DBEAFE] hover:border-[var(--institutional-primary)]'"
                                >
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-700">
                                            <span class="font-semibold">Haz clic para seleccionar</span> o arrastra y suelta
                                        </p>
                                        <p class="text-xs text-gray-500">Tamaño máximo: 200 MB &nbsp;|&nbsp; .sql, .sql.enc, .enc</p>
                                    </div>
                                    <input id="archivo-restauracion" type="file" class="hidden" @change="onFileChange" accept=".sql,.enc" />
                                </label>
                            </div>

                            <!-- Input oculto para cambiar archivo -->
                            <input id="archivo-restauracion-change" type="file" class="hidden" @change="onFileChange" accept=".sql,.enc" />
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                            <Link :href="route('superadmin.backup.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                                Cancelar
                            </Link>
                            <button
                                v-if="hasRestorePermission"
                                type="button"
                                @click="submitUpload"
                                :disabled="!canRestoreUpload || uploadForm.processing"
                                class="px-6 py-2 cursor-pointer font-medium rounded-lg transition shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white"
                            >
                                <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path :d="mdiDatabaseRefresh"/></svg>
                                {{ uploadForm.processing ? 'Restaurando...' : 'Restaurar desde Archivo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
.vue-select-custom :deep(.vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}

.vue-select-custom :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}

.vue-select-custom :deep(.vs--open .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
    border-bottom-color: #1B396A;
}

.vue-select-custom :deep(.vs__search) {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 0.875rem;
    color: #111827;
}

.vue-select-custom :deep(.vs__search::placeholder) {
    color: #9CA3AF;
}

.vue-select-custom :deep(.vs__selected) {
    margin: 0;
    padding: 0;
    border: none;
    color: #111827;
    font-size: 0.875rem;
}

.vue-select-custom :deep(.vs__actions) {
    padding: 0 4px 0 6px;
}

.vue-select-custom :deep(.vs__clear),
.vue-select-custom :deep(.vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

.vue-select-custom :deep(.vs__open-indicator) {
    transform: scale(0.70);
}

.vue-select-custom :deep(.vs--open .vs__open-indicator) {
    transform: rotate(180deg) scale(0.70);
}

.vue-select-custom :deep(.vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 4px;
}

.vue-select-custom :deep(.vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
    transition: all 0.15s;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight span) {
    color: white;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight .rounded-full) {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Fix: el badge de modo (Completo/Incremental) tiene bg-white, al hacer hover
   el texto se vuelve blanco sobre blanco. Cambiamos el fondo a transparente blanco. */
.vue-select-custom :deep(.vs__dropdown-option--highlight .rounded-md) {
    background: rgba(255, 255, 255, 0.25) !important;
    color: white !important;
}

.vue-select-custom :deep(.vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}
</style>

