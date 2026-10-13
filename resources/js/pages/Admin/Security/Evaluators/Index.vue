<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useCan } from '@/composables/usePermissions';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiSecurity } from '@mdi/js';
import { alertaExito, alertaPregunta } from '@/utils/alerts.js';

const props = defineProps({
    institutions: { type: Array, default: () => [] },
    states: { type: Array, default: () => [] },
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    registrationLink: {
        type: String,
        required: true,
    }
});

const editing = ref(null);
const levelNames = { local: 'Local', state: 'Estatal', national: 'Nacional' };
const statusNames = { pending: 'Pendiente', authorized: 'Autorizado', suspended: 'Suspendido' };
const configuration = useForm({ evaluator_level: '', evaluator_status: 'pending', evaluator_institution_id: null, evaluator_state_id: null });
const configure = (user) => {
    editing.value = user;
    configuration.clearErrors();
    configuration.evaluator_level = user.evaluator_level || '';
    configuration.evaluator_status = user.evaluator_status || 'pending';
    configuration.evaluator_institution_id = user.evaluator_institution_id;
    configuration.evaluator_state_id = user.evaluator_state_id;
};
const saveConfiguration = () => configuration.put(route('admin.evaluators.configuration', editing.value.id), {
    preserveScroll: true,
    onSuccess: () => { editing.value = null; alertaExito('Configuración guardada', 'El nivel y la autorización del evaluador fueron actualizados.'); },
});
watch(() => configuration.evaluator_level, () => {
    if (configuration.evaluator_level !== 'local') configuration.evaluator_institution_id = null;
    if (configuration.evaluator_level !== 'state') configuration.evaluator_state_id = null;
});

const search = ref(props.filters.search || '');
const rows = ref(props.filters.rows || 10);
const sortField = ref(props.filters.sort_field || '');
const sortDirection = ref(props.filters.sort_direction || 'asc');

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const copyRegistrationLink = () => {
    const fullUrl = window.location.origin + '/register/evaluator';
    
    const tempInput = document.createElement('input');
    tempInput.value = fullUrl;
    document.body.appendChild(tempInput);
    tempInput.select();
    
    try {
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alertaExito('¡Enlace copiado!', 'El enlace de registro ha sido copiado al portapapeles');
    } catch (err) {
        document.body.removeChild(tempInput);
        console.error('Error al copiar:', err);
    }
};

const onSearch = debounce((value) => {
    router.get(route('admin.evaluators.index'), {
        search: value, 
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route('admin.evaluators.index'), { 
        search: search.value, 
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
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
    router.get(route('admin.evaluators.index'), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('admin.evaluators.index'), {
        search: search.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

const deleteUser = async (id, name) => {
    const confirmed = await alertaPregunta(
        '¿Eliminar evaluador?',
        `Se eliminará a ${name}`
    );
    
    if (confirmed) {
        router.delete(route('admin.evaluators.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                alertaExito('¡Eliminado!', 'Evaluador eliminado correctamente');
            }
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Evaluadores" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestión de Evaluadores</h1>
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
                        <span class="text-gray-900 font-semibold">Evaluadores</span>
                    </div>
                </div>
                <div class="flex w-full md:w-auto">
                    <button @click="copyRegistrationLink" class="w-full md:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z"/>
                        </svg>
                        Copiar Enlace de Registro
                    </button>
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
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar evaluadores</div>
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Buscar por nombre o email..." class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" />
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

            <section v-if="editing" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="configuration-title">
                <h2 id="configuration-title" class="text-xl font-semibold text-[#1b396a]">Configurar evaluador — {{ editing.name }}</h2>
                <p class="mt-2 text-sm text-slate-600">La institución de procedencia del registro es independiente del ámbito autorizado.</p>
                <p v-if="!editing.email_verified" class="mt-2 text-sm text-amber-700">El correo todavía no está verificado. Puedes configurar el nivel, pero no autorizar el acceso.</p>
                <form @submit.prevent="saveConfiguration" class="mt-5 space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="space-y-2">Nivel
                            <select v-model="configuration.evaluator_level" class="block w-full rounded-lg border border-slate-300 p-3">
                                <option value="">Sin asignar</option><option value="local">Local</option><option value="state">Estatal</option><option value="national">Nacional</option>
                            </select>
                        </label>
                        <label class="space-y-2">Autorización
                            <select v-model="configuration.evaluator_status" class="block w-full rounded-lg border border-slate-300 p-3">
                                <option value="pending">Pendiente</option><option value="authorized" :disabled="!editing.email_verified">Autorizado</option><option value="suspended">Suspendido</option>
                            </select>
                        </label>
                        <label v-if="configuration.evaluator_level === 'local'" class="space-y-2">Institución autorizada
                            <select v-model="configuration.evaluator_institution_id" required class="block w-full rounded-lg border border-slate-300 p-3">
                                <option :value="null">Selecciona una institución</option><option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                            </select>
                        </label>
                        <label v-if="configuration.evaluator_level === 'state'" class="space-y-2">Estado autorizado
                            <select v-model="configuration.evaluator_state_id" required class="block w-full rounded-lg border border-slate-300 p-3">
                                <option :value="null">Selecciona un estado</option><option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                            </select>
                        </label>
                    </div>
                    <p v-for="(error, field) in configuration.errors" :key="field" role="alert" class="text-sm text-red-600">{{ error }}</p>
                    <div class="flex gap-3"><button :disabled="configuration.processing" type="submit" class="rounded-lg bg-[#1b396a] px-5 py-3 text-white disabled:opacity-50">Guardar configuración</button><button type="button" @click="editing = null" class="rounded-lg border border-slate-300 px-5 py-3">Cancelar</button></div>
                </form>
            </section>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm text-left">
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
                                        Email
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'email', 'opacity-50': sortField !== 'email' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-6 py-4">Nivel</th><th class="px-6 py-4">Ámbito</th><th class="px-6 py-4">Autorización</th><th scope="col" class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(user, index) in users.data" :key="user.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ (users.meta.current_page - 1) * users.meta.per_page + index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ user.name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ user.email }}<span v-if="!user.email_verified" class="block text-xs text-amber-700">Correo sin verificar</span></td>
                                <td class="px-6 py-4">{{ levelNames[user.evaluator_level] || 'Sin asignar' }}</td><td class="px-6 py-4">{{ user.evaluator_scope || (user.evaluator_level === 'national' ? 'Nacional' : 'Sin asignar') }}</td><td class="px-6 py-4">{{ statusNames[user.evaluator_status] || 'Pendiente' }}</td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="configure(user)" class="rounded-lg border border-[#1b396a] px-3 py-2 text-[#1b396a] hover:bg-blue-50">Configurar</button>
                                        <button v-if="useCan('users.delete')" @click="deleteUser(user.id, user.name)" class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition group cursor-pointer" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron evaluadores
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
    </AdminLayout>
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

:deep(.vue-select-custom .vs__search) {
    color: #111827;
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
