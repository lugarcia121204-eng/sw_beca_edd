<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiSecurity } from '@mdi/js';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    id: props.user.data.id,
    name: props.user.data.name,
    email: props.user.data.email,
    password: '',
    roles: props.user.data.roles_ids || [], 
});

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    
    // Validación del lado del cliente
    if (!form.name) {
        form.errors.name = 'El nombre completo es obligatorio';
        return;
    }
    if (!form.email) {
        form.errors.email = 'El correo electrónico es obligatorio';
        return;
    }
    if (!form.roles || form.roles.length === 0) {
        form.errors.roles = 'Debes asignar al menos un rol';
        return;
    }
    
    // Si todo está correcto, mostrar alerta de cargando y enviar
    alertaCargando('Actualizando', 'Por favor espera...');
    
    form.put(route(`${props.routeName}update`, form.id), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Usuario actualizado correctamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Por favor verifica los datos ingresados');
        }
    });
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
                        <Link :href="route(`${routeName}index`)" class="flex items-center gap-2 hover:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#1B396A">
                                <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Usuarios</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Editar Usuario</span>
                    </div>
                </div>
                <Link :href="route(`${routeName}index`)" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Nombre Completo: <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.name }" placeholder="Nombre completo del usuario" @input="clearError('name')" />
                            <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce el nombre completo del usuario</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        
                         <!-- Correo -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Correo Electrónico: <span class="text-red-500">*</span></label>
                            <input v-model="form.email" type="email" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.email }" placeholder="correo@ejemplo.com" @input="clearError('email')" />
                            <div v-if="!form.errors.email" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce el correo electrónico</span>
                            </div>
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                         <!-- Contraseña -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Contraseña: <span class="text-gray-500">(Opcional)</span></label>
                            <input v-model="form.password" type="password" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.password }" placeholder="Dejar en blanco para mantener actual" @input="clearError('password')" />
                            <div v-if="!form.errors.password" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Solo llena este campo si deseas cambiar la contraseña</span>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <!-- Roles (selección múltiple card-style) -->
                        <div>
                            <label class="block mb-3 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Asignar Roles: <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label
                                    v-for="rol in roles"
                                    :key="rol.id"
                                    :for="`rol-${rol.id}`"
                                    class="flex items-center ps-4 bg-white rounded-lg shadow-sm cursor-pointer transition"
                                    :class="form.roles.includes(rol.id) ? 'border-[var(--institutional-primary)]' : 'border-gray-50 hover:border-gray-300'"
                                >
                                    <div class="relative flex items-center justify-center">
                                        <input
                                            :id="`rol-${rol.id}`"
                                            type="checkbox"
                                            :value="rol.id"
                                            v-model="form.roles"
                                            class="custom-checkbox"
                                            @change="clearError('roles')"
                                        />
                                        <div class="checkbox-custom">
                                            <svg v-if="form.roles.includes(rol.id)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="w-full py-4 ps-3 select-none">
                                        <div class="font-medium text-gray-900 text-sm">{{ rol.name }}</div>
                                    </div>
                                </label>
                            </div>
                            <div v-if="!form.errors.roles" class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Puedes asignar uno o varios roles al usuario</span>
                            </div>
                            <p v-if="form.errors.roles" class="mt-1 text-sm text-red-600">{{ form.errors.roles }}</p>
                        </div>

                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </Link>
                        <button :disabled="form.processing" type="submit" class="px-6 py-2 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer">
                            <span v-if="!form.processing">Actualizar</span>
                            <span v-else>Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Ocultar el checkbox nativo */
.custom-checkbox {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

/* Checkbox personalizado */
.checkbox-custom {
    position: relative;
    width: 20px;
    height: 20px;
    border: 2px solid #D1D5DB;
    border-radius: 4px;
    background-color: white;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Cuando está checked */
.custom-checkbox:checked + .checkbox-custom {
    background-color: #1B396A;
    border-color: #1B396A;
}

/* Hover */
label:hover .checkbox-custom {
    border-color: #1B396A;
}

/* Focus visible */
.custom-checkbox:focus-visible + .checkbox-custom {
    outline: 2px solid #1B396A;
    outline-offset: 2px;
}
</style>
