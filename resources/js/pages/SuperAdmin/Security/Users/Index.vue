<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useCan } from '@/composables/usePermissions';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiSecurity } from '@mdi/js';
import { alertaPregunta, alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    rolesForImport: {
        type: Array,
        default: () => [],
    },
    roleFilter: {
        type: [String, Number],
        default: null,
    }
});

const search = ref(props.filters.search);
const rows = ref(props.filters.rows || 10);
const sortField = ref(props.filters.sort_field || '');
const sortDirection = ref(props.filters.sort_direction || 'asc');
const selectedRole = ref(props.roleFilter || '');
const showImportSection = ref(false);
const importForm = useForm({
    file: null,
});

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onSearch = debounce((value) => {
    router.get(route(`${props.routeName}index`), { 
        search: value, 
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
        role_id: selectedRole.value
    }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route(`${props.routeName}index`), { 
        search: search.value, 
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
        role_id: selectedRole.value
    }, { preserveState: true, replace: true });
};

const onRoleChange = () => {
    router.get(route(`${props.routeName}index`), {
        search: search.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
        role_id: selectedRole.value
    }, { preserveState: true, replace: true });
};

watch(search, (value) => {
    onSearch(value);
});

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    sortField.value = '';
    sortDirection.value = 'asc';
    selectedRole.value = '';
    router.get(route(`${props.routeName}index`), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route(`${props.routeName}index`), {
        search: search.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
        role_id: selectedRole.value
    }, { preserveState: true, replace: true });
};

const deleteUser = async (id) => {
    const confirmed = await alertaPregunta(
        '¿Eliminar usuario?',
        'Esta acción no se puede deshacer'
    );
    
    if (confirmed) {
        router.delete(route(`${props.routeName}destroy`, id), {
            onSuccess: () => {
                alertaExito('¡Eliminado!', 'Usuario eliminado correctamente');
            }
        });
    }
};

const handleExport = () => {
    window.location.href = route('security.users.export');
};

const toggleImportSection = () => {
    showImportSection.value = !showImportSection.value;
    if (!showImportSection.value) {
        importForm.reset();
    }
};

const submitImport = () => {
    if (!importForm.file) {
        alertaError('Error', 'Por favor selecciona un archivo');
        return;
    }
    
    alertaCargando('Importando', 'Por favor espera...');
    
    importForm.post(route('security.users.import'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Usuarios importados correctamente');
            showImportSection.value = false;
            importForm.reset();
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Hubo un problema al importar los usuarios');
        },
    });
};

const downloadTemplate = () => {
    window.location.href = route('security.users.template');
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiSecurity"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Seguridad</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#1B396A">
                            <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Usuarios</span>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row w-full sm:w-auto items-center gap-2">
                     <button @click="handleExport" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[#0D7239] text-white rounded-lg hover:bg-green-800 transition flex items-center gap-2 font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                        </svg>
                        Exportar
                    </button>

                    <button @click="toggleImportSection" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                        </svg>
                        {{ showImportSection ? 'Ocultar Importar' : 'Importar' }}
                    </button>

                    <Link v-if="useCan('users.create')" :href="route(`${routeName}create`)" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                        </svg>
                        Agregar
                    </Link>
                </div>
            </div>

            <!-- Sección de Importación -->
            <Transition name="slide-up">
                <div v-if="showImportSection" class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                                <path d="M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                            </svg>
                            <h2 class="text-xl font-semibold text-gray-800">Importar Usuarios</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="downloadTemplate" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                                    <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                                </svg>
                                Descargar Plantilla
                            </button>
                            <button @click="toggleImportSection" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                                    <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                                </svg>
                                Cerrar
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-4">Carga masiva de usuarios con rol de Evaluador mediante archivo Excel</div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Archivo Excel <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <input 
                                    type="file" 
                                    @change="e => importForm.file = e.target.files[0]" 
                                    accept=".xlsx,.xls,.csv"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-[var(--institutional-primary)] file:text-white hover:file:bg-[#0f2347] transition"
                                />
                            </div>
                            <p v-if="importForm.errors.file" class="mt-1 text-sm text-red-600">{{ importForm.errors.file }}</p>
                            <p v-if="importForm.file" class="mt-1 text-sm text-green-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Archivo seleccionado: {{ importForm.file.name }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="toggleImportSection" type="button" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                @click="submitImport" 
                                :disabled="importForm.processing"
                                type="button" 
                                class="px-6 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer"
                            >
                                <span>{{ importForm.processing ? 'Importando...' : 'Importar' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>

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
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar usuarios</div>
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
                            v-model="selectedRole"
                            :options="[{ id: '', name: 'Todos los roles' }, ...roles]"
                            :reduce="rol => rol.id"
                            label="name"
                            placeholder="Filtrar por rol"
                            :searchable="false"
                            :clearable="false"
                            class="vue-select-custom"
                            @option:selected="onRoleChange"
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
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('name')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Nombre
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'name', 'opacity-50': sortField !== 'name' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('email')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Correo Electrónico
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'email', 'opacity-50': sortField !== 'email' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Rol</th>
                                <th v-if="useCan('users.edit') || useCan('users.delete')" scope="col" class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(user, index) in users.data" :key="user.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ (users.meta.current_page - 1) * users.meta.per_page + index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ user.name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            v-for="rol in user.roles"
                                            :key="rol.id"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-white text-[13px] font-bold shadow-sm"
                                            :class="{
                                                'text-purple-700': rol.name === 'Super Admin',
                                                'text-blue-700':   rol.name === 'Admin',
                                                'text-green-700':  rol.name === 'Docente',
                                                'text-orange-600': rol.name === 'Evaluador',
                                                'text-gray-600':   !['Super Admin','Admin','Docente','Evaluador'].includes(rol.name),
                                            }"
                                        >
                                            {{ rol.name }}
                                        </span>
                                        <span v-if="!user.roles || user.roles.length === 0" class="text-gray-400 text-xs">Sin rol</span>
                                    </div>
                                </td>
                                <td v-if="useCan('users.edit') || useCan('users.delete')" class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link v-if="useCan('users.edit')" :href="route(`${routeName}edit`, user.id)" class="p-2 text-[var(--institutional-primary)] border border-[var(--institutional-primary)] rounded-full hover:bg-[var(--institutional-primary)] hover:text-white transition group cursor-pointer" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </Link>
                                        <button v-if="useCan('users.delete')" @click="deleteUser(user.id)" class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition group cursor-pointer" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                 <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                     <Pagination :links="users.meta.links" :total="users.meta.total" :from="users.meta.from" :to="users.meta.to" />
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Estilo para filtros y número de registros */
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

/* Estilo para selector de rol en importación (igual a Register.vue) */
:deep(.vue-select-import-role .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}

:deep(.vue-select-import-role .vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}

:deep(.vue-select-import-role .vs--open .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
    border-bottom-color: #1B396A;
}

:deep(.vue-select-import-role .vs__search) {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 0.875rem;
    color: #111827;
}

:deep(.vue-select-import-role .vs__search::placeholder) {
    color: #9CA3AF;
}

:deep(.vue-select-import-role .vs__selected) {
    margin: 0;
    padding: 0;
    border: none;
    color: #111827;
    font-size: 0.875rem;
}

:deep(.vue-select-import-role .vs__actions) {
    padding: 0 4px 0 6px;
}

:deep(.vue-select-import-role .vs__clear),
:deep(.vue-select-import-role .vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

:deep(.vue-select-import-role .vs__open-indicator) {
    transform: scale(0.70);
}

:deep(.vue-select-import-role .vs--open .vs__open-indicator) {
    transform: rotate(180deg) scale(0.70);
}

:deep(.vue-select-import-role .vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 4px;
}

:deep(.vue-select-import-role .vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
    transition: all 0.15s;
}

:deep(.vue-select-import-role .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

:deep(.vue-select-import-role .vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
