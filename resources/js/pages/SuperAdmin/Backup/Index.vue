<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiDatabase, mdiDatabaseArrowDown, mdiDatabaseSync, mdiDatabaseRefresh, mdiCheck, mdiAlertCircle, mdiClock, mdiDatabaseOutline } from '@mdi/js';
import { alertaExito, alertaError, alertaPregunta, cerrarAlerta } from '@/utils/alerts.js';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCan } from '@/composables/usePermissions';

const canCreate   = useCan('backup.create');
const canSchedule = useCan('backup.schedule');
const canRestore  = useCan('backup.restore');
const canDownload = useCan('backup.download');
const canDelete   = useCan('backup.delete');

const props = defineProps({
    backups:  { type: Object, required: true },
    filters:  { type: Object, required: true },
    stats:    { type: Object, required: true },
    schedule: { type: Object, required: true },
});

const search = ref(props.filters.search || '');
const rows   = ref(props.filters.rows   || 10);

const rowOptions = [
    { label: '5 Registros',   value: 5  },
    { label: '10 Registros',  value: 10 },
    { label: '25 Registros',  value: 25 },
    { label: '50 Registros',  value: 50 },
];

const onSearch = debounce((value) => {
    router.get(route('superadmin.backup.index'), { search: value, rows: rows.value }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route('superadmin.backup.index'), { search: search.value, rows: rows.value }, { preserveState: true, replace: true });
};

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    router.get(route('superadmin.backup.index'), {}, { preserveState: true, replace: true });
};

watch(search, (v) => onSearch(v));

// Flash messages from Laravel
const page = usePage();
const flashSuccess        = computed(() => page.props.flash?.success            || null);
const flashError          = computed(() => page.props.flash?.error              || null);
const flashDownloadBackup = computed(() => page.props.flash?.download_backup_id || null);

watch(flashSuccess, (msg) => { if (msg) alertaExito('Éxito', msg); });
watch(flashError,   (msg) => { if (msg) alertaError('Error', msg); });
watch(flashDownloadBackup, (id) => {
    if (id) window.location.href = route('superadmin.backup.download', id);
}, { immediate: true });

const frequencyLabel = { daily: 'Diario', weekly: 'Semanal', monthly: 'Mensual' };

const downloadBackup = (id) => {
    window.location.href = route('superadmin.backup.download', id);
};

const deleteBackup = async (id, name) => {
    const confirmed = await alertaPregunta(
        '¿Eliminar respaldo?',
        `Se eliminará permanentemente el respaldo "${name}"`
    );
    if (confirmed) {
        router.delete(route('superadmin.backup.destroy', id), {
            preserveScroll: true,
            onSuccess: () => alertaExito('¡Eliminado!', 'El respaldo ha sido eliminado'),
            onError:   () => alertaError('Error', 'No se pudo eliminar el respaldo'),
        });
    }
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Gestión de Respaldos" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestión de Respaldos</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill:#1B396A">
                            <path :d="mdiDatabaseOutline"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Respaldos</span>
                    </div>
                </div>
            </div>

            <!-- Action cards -->
            <div class="grid grid-cols-1 gap-4" :class="[canCreate && canSchedule && canRestore ? 'md:grid-cols-3' : (canCreate && canSchedule || canCreate && canRestore || canSchedule && canRestore ? 'md:grid-cols-2' : 'md:grid-cols-1')]">
                <!-- Respaldo Manual -->
                <Link v-if="canCreate" :href="route('superadmin.backup.create')"
                      class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col items-center gap-2 hover:shadow-md hover:border-[#10A558] transition-all cursor-pointer group">
                    <div class="w-12 h-12 rounded-lg bg-[#f3f4f6] flex items-center justify-center group-hover:bg-[#10A558] transition-colors">
                        <svg viewBox="0 0 24 24" class="w-6 h-6 text-[#10A558] group-hover:text-white transition-colors" style="fill:currentColor">
                            <path :d="mdiDatabaseArrowDown"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 group-hover:text-[#10A558] transition-colors">Respaldo Manual</span>
                    <span class="text-xs text-gray-500 text-center">Crear respaldo inmediato</span>
                </Link>

                <!-- Programación -->
                <Link v-if="canSchedule" :href="route('superadmin.backup.edit')"
                      class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col items-center gap-2 hover:shadow-md hover:border-[var(--institutional-primary)] transition-all cursor-pointer group">
                    <div class="w-12 h-12 rounded-lg bg-[#f3f4f6] flex items-center justify-center group-hover:bg-[var(--institutional-primary)] transition-colors">
                        <svg viewBox="0 0 24 24" class="w-6 h-6 text-[var(--institutional-primary)] group-hover:text-white transition-colors" style="fill:currentColor">
                            <path :d="mdiDatabaseSync"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 group-hover:text-[var(--institutional-primary)] transition-colors">Programación</span>
                    <span class="text-xs text-gray-500 text-center">Configurar respaldos automáticos</span>
                </Link>

                <!-- Restaurar -->
                <Link v-if="canRestore" :href="route('superadmin.backup.restore-view')"
                      class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col items-center gap-2 hover:shadow-md hover:border-[#C9A800] transition-all cursor-pointer group">
                    <div class="w-12 h-12 rounded-lg bg-[#f3f4f6] flex items-center justify-center group-hover:bg-[#C9A800] transition-colors">
                        <svg viewBox="0 0 24 24" class="w-6 h-6 text-[#C9A800] group-hover:text-white transition-colors" style="fill:currentColor">
                            <path :d="mdiDatabaseRefresh"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 group-hover:text-[#C9A800] transition-colors">Restaurar</span>
                    <span class="text-xs text-gray-500 text-center">Recuperar desde respaldo</span>
                </Link>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Último Respaldo -->
                <div class="bg-white border-l-4 border-[var(--institutional-primary)] rounded-lg shadow-sm p-5 flex items-center justify-between hover:shadow-lg transition-shadow">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Último Respaldo</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ stats.last_backup }}</p>
                    </div>
                    <div class="p-2">
                        <svg viewBox="0 0 24 24" class="w-8 h-8" style="fill:#1B396A">
                            <path :d="mdiDatabaseArrowDown"/>
                        </svg>
                    </div>
                </div>

                <!-- Próximo Programado -->
                <div class="bg-white border-l-4 border-[#10A558] rounded-lg shadow-sm p-5 flex items-center justify-between hover:shadow-lg transition-shadow">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Próximo Programado</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ stats.next_backup }}</p>
                    </div>
                    <div class="p-2">
                        <svg viewBox="0 0 24 24" class="w-8 h-8" style="fill:#10A558">
                            <path :d="mdiClock"/>
                        </svg>
                    </div>
                </div>

                <!-- Respaldos Totales -->
                <div class="bg-white border-l-4 border-[#2B6CB0] rounded-lg shadow-sm p-5 flex items-center justify-between hover:shadow-lg transition-shadow">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Respaldos Totales</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ stats.total }}</p>
                    </div>
                    <div class="p-2">
                        <svg viewBox="0 0 24 24" class="w-8 h-8" style="fill:#2B6CB0">
                            <path :d="mdiDatabase"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <button @click="cleanFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                            <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar registros de respaldo</div>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Buscar..." class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" />
                    </div>
                    <div class="w-full md:w-52 flex-shrink-0">
                        <VueSelect v-model="rows" :options="rowOptions" :reduce="o => o.value" :searchable="false" :clearable="false" placeholder="Registros" class="vue-select-custom" @option:selected="onRowsChange" />
                    </div>
                </div>
            </div>

            <!-- Auditoría y estado -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <!-- Section header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" style="fill:#1B396A">
                            <path :d="mdiDatabaseOutline"/>
                        </svg>
                        <h2 class="text-lg font-bold text-gray-800">Auditoría y estado</h2>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">Historial completo de operaciones de respaldo y restauración</p>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4 tracking-wider">Acción</th>
                                <th class="px-6 py-4 tracking-wider">Usuario</th>
                                <th class="px-6 py-4 tracking-wider">Nombre</th>
                                <th class="px-6 py-4 tracking-wider">Fecha</th>
                                <th class="px-6 py-4 tracking-wider">Tamaño</th>
                                <th class="px-6 py-4 text-center tracking-wider">Modo</th>
                                <th class="px-6 py-4 text-center tracking-wider">Resultado</th>
                                <th class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!backups.data || backups.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <svg viewBox="0 0 24 24" class="w-10 h-10 mx-auto mb-3 opacity-30" style="fill:#6B7280">
                                        <path :d="mdiDatabase"/>
                                    </svg>
                                    <p class="font-medium">No hay respaldos registrados</p>
                                    <p class="text-xs mt-1">Crea un respaldo manual o configura una programación automática</p>
                                </td>
                            </tr>
                            <tr v-for="item in backups.data" :key="item.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-700">{{ item.action }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ item.user }}</td>
                                <td class="px-6 py-4 text-gray-800 font-medium">{{ item.name }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ item.date }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ item.size }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <span v-if="item.backup_mode === 'incremental'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-purple-700 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor">
                                                <path d="M480-120 200-272v-200L80-536l400-224 400 224v320h-80v-276l-120 67v200L480-120Zm0-332 274-153-274-154-274 154 274 153Zm0 241 160-89v-151L480-321 320-411v151l160 89Zm0-241Zm0 90Zm0 0Z"/>
                                            </svg>
                                            Incremental
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-blue-700 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor">
                                                <path d="M160-160v-80h80v80h-80Zm160 0v-80h80v80h-80Zm-160-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm160 320v-80h80v80h-80Zm160 0v-80h80v80h-80Zm160 160v-80h80v80h-80Zm0-160v-80h80v80h-80Zm0-160v-80h80v80h-80ZM320-640v-80h80v80h-80Zm160 480v-80h80v80h-80Zm-160 0v-80h80v80h-80Zm160-320v-80h80v80h-80Zm160 0v-80h80v80h-80Z"/>
                                            </svg>
                                            Completa
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <span v-if="item.status === 'completed'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-green-700 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor">
                                                <path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/>
                                            </svg>
                                            Realizado
                                        </span>
                                        <span v-else-if="item.status === 'failed'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-red-700 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor">
                                                <path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                                            </svg>
                                            Error
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-yellow-700 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor">
                                                <path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm-40-246v-334h80v334h-80Zm40 126q17 0 28.5-11.5T520-240q0-17-11.5-28.5T480-280q-17 0-28.5 11.5T440-240q0 17 11.5 28.5T480-200Z"/>
                                            </svg>
                                            En progreso
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            v-if="canDownload && item.status === 'completed'"
                                            @click="downloadBackup(item.id)"
                                            class="p-2 text-[var(--institutional-primary)] border border-[var(--institutional-primary)] rounded-full hover:bg-[var(--institutional-primary)] hover:text-white transition cursor-pointer"
                                            title="Descargar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                                            </svg>
                                        </button>
                                        <button
                                            v-if="canDelete"
                                            @click="deleteBackup(item.id, item.name)"
                                            class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition cursor-pointer"
                                            title="Eliminar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <Pagination :links="backups.links" :total="backups.total" :from="backups.from" :to="backups.to" />
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem;
    min-height: 42px;
}
:deep(.vue-select-custom .vs__selected) { color: #374151; font-weight: 500; }
:deep(.vue-select-custom .vs__search::placeholder) { color: #9ca3af; }
:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
}
:deep(.vue-select-custom .vs__dropdown-option) { padding: .75rem 1rem; color: #374151; }
:deep(.vue-select-custom .vs__dropdown-option--highlight) { background: #1B396A; color: white; }
:deep(.vue-select-custom .vs__open-indicator) { fill: #1B396A; transform: scale(.85); }
:deep(.vue-select-custom .vs__actions) { padding-right: 4px; }
</style>

