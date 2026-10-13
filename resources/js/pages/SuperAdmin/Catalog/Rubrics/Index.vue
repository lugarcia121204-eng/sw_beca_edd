<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { mdiBookOpenPageVariant, mdiClipboardTextOutline } from '@mdi/js';
import { useCan } from '@/composables/usePermissions';
import _ from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { alertaPregunta, alertaExito, alertaError } from '@/utils/alerts.js';

const props = defineProps({
    rubrics: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    evaluacionEnCurso: {
        type: Boolean,
        default: false,
    },
});

const search = ref(props.filters.search);
const rows = ref(props.filters.rows || 10);
const sortField = ref(props.filters.order || 'title');
const sortDirection = ref(props.filters.direction || 'asc');

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onSearch = _.debounce((value) => {
    router.get(route('catalog.rubrics.index'), {
        search: value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route('catalog.rubrics.index'), {
        search: search.value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

watch(search, (value) => {
    onSearch(value);
});

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    sortField.value = 'title';
    sortDirection.value = 'asc';
    router.get(route('catalog.rubrics.index'), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('catalog.rubrics.index'), {
        search: search.value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

const canToggle = (rubric) => {
    const anyActive = props.rubrics.data.some(r => r.is_active);
    if (props.evaluacionEnCurso && anyActive) return false;
    if (rubric.is_active) return true;
    return !anyActive;
};

const toggleReasonBlocked = (rubric) => {
    const anyActive = props.rubrics.data.some(r => r.is_active);
    if (props.evaluacionEnCurso && anyActive) return 'Hay una convocatoria en etapa de Evaluación. No se puede cambiar la rúbrica activa.';
    if (!rubric.is_active) return 'Ya existe una rúbrica activa. Desactívala primero.';
    return '';
};

const toggleActive = (rubric) => {
    if (!canToggle(rubric)) {
        alertaError('No permitido', toggleReasonBlocked(rubric));
        return;
    }
    router.post(route('catalog.rubrics.toggle-active', rubric.id), {}, {
        preserveScroll: true,
        onSuccess: () => {},
        onError: () => {
            alertaError('Error', 'No se pudo actualizar el estado de la rúbrica');
        }
    });
};

const deleteItem = async (id) => {
    const confirmed = await alertaPregunta(
        '¿Estás seguro?',
        'Esta acción no se puede deshacer'
    );

    if (confirmed) {
        router.delete(route('catalog.rubrics.destroy', id), {
            preserveScroll: true,
            onSuccess: () => alertaExito('¡Eliminado!', 'La rúbrica ha sido eliminada exitosamente'),
            onError: () => alertaError('Error', 'No se pudo eliminar la rúbrica')
        });
    }
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Rúbricas" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Rúbricas</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBookOpenPageVariant"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Catálogo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiClipboardTextOutline"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Rúbricas</span>
                    </div>
                </div>
                <Link :href="route('catalog.rubrics.create')" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                    </svg>
                    Agregar
                </Link>
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
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar rúbricas por título</div>
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
                        <VueSelect
                            v-model="rows"
                            :options="rowOptions"
                            :reduce="option => option.value"
                            :searchable="false"
                            :clearable="false"
                            placeholder="Registros"
                            class="vue-select-custom"
                            @option:selected="onRowsChange"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('title')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Título
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'title', 'opacity-50': sortField !== 'title' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Activa</th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(rubric, index) in rubrics.data" :key="rubric.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ rubrics.from + index }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ rubric.title }}</td>
                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        @click="toggleActive(rubric)"
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none',
                                            rubric.is_active ? 'bg-[var(--institutional-primary)]' : 'bg-gray-200',
                                            !canToggle(rubric) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                        ]"
                                        :title="!canToggle(rubric) ? toggleReasonBlocked(rubric) : (rubric.is_active ? 'Desactivar' : 'Activar')"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                rubric.is_active ? 'translate-x-6' : 'translate-x-1'
                                            ]"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link :href="route('catalog.rubrics.edit', rubric.id)" class="p-2 text-[var(--institutional-primary)] border border-[var(--institutional-primary)] rounded-full hover:bg-[var(--institutional-primary)] hover:text-white transition group cursor-pointer" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </Link>
                                        <button @click="deleteItem(rubric.id)" class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition group cursor-pointer" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="rubrics.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                 <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                     <Pagination :links="rubrics.links" :total="rubrics.total" :from="rubrics.from" :to="rubrics.to" />
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

:deep(.vue-select-custom .vs__selected) {
    color: #374151;
    font-weight: 500;
}

:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}

:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.75rem 1rem;
    color: #374151;
}

:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transform: scale(0.85);
}

:deep(.vue-select-custom .vs__actions) {
    padding-right: 4px;
}
</style>

