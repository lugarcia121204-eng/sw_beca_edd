<script setup>
import { Head, router } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiDownload, mdiEye, mdiEyeOff, mdiStar } from '@mdi/js';

const props = defineProps({
    recognitions: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const rows = ref(props.filters?.rows || 10);
const expandedRows = ref({});

const togglePreview = (id) => {
    expandedRows.value[id] = !expandedRows.value[id];
};

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onSearch = debounce((value) => {
    router.get(route('evaluator.recognitions.index'), {
        search: value,
        rows: rows.value
    }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route('evaluator.recognitions.index'), {
        search: search.value,
        rows: rows.value,
    }, { preserveState: true, replace: true });
};

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    router.get(route('evaluator.recognitions.index'), {}, { preserveState: true, replace: true });
};

watch(search, (value) => {
    onSearch(value);
});
</script>

<template>
    <EvaluatorLayout>
        <Head title="Mis Reconocimientos" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mis Reconocimientos</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiStar"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Reconocimientos</span>
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
                <div class="text-sm text-gray-500 mb-4">Buscar por nombre de convocatoria o año</div>
                
                <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" 
                        />
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
                    <table class="w-full min-w-[860px] text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider text-center w-16">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Convocatoria</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Fecha de Emisión</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Año</th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="recognitions.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">
                                    <p>Aún no tienes reconocimientos disponibles.</p>
                                </td>
                            </tr>
                            <template v-for="(item, index) in recognitions.data" :key="item.id">
                                <tr class="hover:bg-gray-50 transition-colors" :class="{'bg-blue-50': expandedRows[item.id]}">
                                    <td class="px-6 py-4 text-center font-bold text-gray-700">
                                        {{ (recognitions.current_page - 1) * recognitions.per_page + index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ item.announcement }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ item.date }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            {{ item.year }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <button 
                                                @click="togglePreview(item.id)"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-md transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] cursor-pointer"
                                                :class="expandedRows[item.id] ? 'bg-gray-200 text-gray-800' : 'text-white bg-[var(--institutional-primary)] hover:bg-[#234a85]'"
                                            >
                                                <svg viewBox="0 0 24 24" class="w-4 h-4" style="fill: currentColor"><path :d="expandedRows[item.id] ? mdiEyeOff : mdiEye"/></svg>
                                                {{ expandedRows[item.id] ? 'Ocultar' : 'Ver' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Expanded Preview Row -->
                                <tr v-if="expandedRows[item.id]">
                                    <td colspan="5" class="px-6 py-6 bg-gray-50 border-b border-gray-200">
                                        <div class="flex flex-col gap-4 animate-fadeIn">
                                            <div class="flex justify-between items-center">
                                                <h3 class="font-bold text-gray-800 text-lg">Vista Previa del Reconocimiento</h3>
                                            </div>
                                            <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] border border-gray-300 rounded-lg overflow-hidden bg-white relative">
                                                <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                                    Cargando vista previa...
                                                </div>
                                                <iframe 
                                                    :src="route('evaluator.recognitions.download', item.id)" 
                                                    class="w-full h-full relative z-10" 
                                                    frameborder="0"
                                                ></iframe>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <Pagination :links="recognitions.links" :total="recognitions.total" :from="recognitions.from" :to="recognitions.to" />
                </div>
            </div>
        </div>
    </EvaluatorLayout>
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

.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
