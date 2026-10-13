<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, nextTick } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import EyeIcon from '@/components/icons/EyeIcon.vue';
import EyeOffIcon from '@/components/icons/EyeOffIcon.vue';
import LupaIcon from '@/components/icons/LupaIcon.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import axios from 'axios';
import { alertaExito, alertaError, alertaAdvertencia, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    institutions: {
        type: Array,
        default: () => [],
    },
    priorityAreas: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    curp: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    institution_id: '',
    priority_area_id: '',
    sub_area_id: '',
    role_type: 'docente'
});

// Map props to dropdown options
const institucionesOptions = props.institutions;
const areasPrioritariasOptions = props.priorityAreas;
const subareasPrioritariasOptions = ref([]);

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const buscandoCurp = ref(false);
const curpEncontrado = ref(false);
const usuarioExistente = ref(false);
const errorCurp = ref('');
const protectedEmails = ['admin@gmail.com', 'superadmin@gmail.com'];

const isProtectedEmail = (email) => protectedEmails.includes((email || '').trim().toLowerCase());

// Watch for Priority Area change to fetch Sub Areas
watch(() => form.priority_area_id, async (newValue) => {
    form.sub_area_id = ''; // Reset sub area
    subareasPrioritariasOptions.value = [];
    
    if (newValue) {
        try {
            const response = await axios.get(`/api/sub-areas/${newValue}`);
            subareasPrioritariasOptions.value = response.data;
        } catch (error) {
            console.error('Error fetching subareas:', error);
        }
    }
});

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    errorCurp.value = '';
    
    // Validación del lado del cliente
    if (!form.curp) {
        form.errors.curp = 'El CURP es obligatorio';
        return;
    }
    if (form.curp.length !== 18) {
        form.errors.curp = 'El CURP debe tener exactamente 18 caracteres';
        return;
    }
    if (!curpEncontrado) {
        errorCurp.value = 'Debes buscar y validar tu CURP primero';
        alertaAdvertencia('CURP no validado', 'Debes buscar y validar tu CURP antes de continuar');
        return;
    }
    if (!form.name) {
        form.errors.name = 'El nombre completo es obligatorio';
        return;
    }
    if (!form.email) {
        form.errors.email = 'El correo electrónico es obligatorio';
        return;
    }
    if (isProtectedEmail(form.email)) {
        form.errors.email = 'Este correo está reservado para la administración del sistema';
        alertaError('Correo no permitido', 'Este correo está reservado para la administración del sistema');
        return;
    }
    if (!form.email.includes('@')) {
        form.errors.email = 'El correo electrónico debe contener un @';
        return;
    }
    if (!form.password) {
        form.errors.password = 'La contraseña es obligatoria';
        return;
    }
    if (form.password.length < 8) {
        form.errors.password = 'La contraseña debe tener al menos 8 caracteres';
        return;
    }
    if (!form.password_confirmation) {
        form.errors.password_confirmation = 'Debes confirmar tu contraseña';
        return;
    }
    if (form.password !== form.password_confirmation) {
        form.errors.password_confirmation = 'Las contraseñas no coinciden';
        return;
    }
    if (!form.institution_id) {
        form.errors.institution_id = 'Debes seleccionar una institución';
        return;
    }
    if (!form.priority_area_id) {
        form.errors.priority_area_id = 'Debes seleccionar un área prioritaria';
        return;
    }
    if (!form.sub_area_id) {
        form.errors.sub_area_id = 'Debes seleccionar una subárea prioritaria';
        return;
    }
    
    // Si todo está correcto, mostrar alerta de cargando y enviar
    alertaCargando('Registrando cuenta', 'Por favor espera...');
    
    form.post(route('register'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Registro exitoso!', 'Revisa tu correo para verificar tu cuenta');
        },
        onError: (errors) => {
            cerrarAlerta();
            const backendMsg = errors?.email || errors?.curp || 'Por favor verifica los datos ingresados';
            alertaError('Error en el registro', backendMsg);
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const buscarCurp = async () => {
    if (!form.curp || form.curp.length !== 18) {
        alertaAdvertencia('CURP incompleto', 'El CURP debe tener 18 caracteres');
        errorCurp.value = 'El CURP debe tener 18 caracteres';
        return;
    }

    buscandoCurp.value = true;
    errorCurp.value = '';
    curpEncontrado.value = false;
    alertaCargando('Buscando CURP', 'Consultando en el sistema RENAPO...');

    try {
        const response = await axios.post('/api/buscar-curp', {
            curp: form.curp.toUpperCase(),
            role_type: form.role_type
        });

        if (response.data.success) {
            cerrarAlerta();
            const { nombres, apellidoPaterno, apellidoMaterno } = response.data.data;
            form.name = `${nombres} ${apellidoPaterno} ${apellidoMaterno}`.trim();
            curpEncontrado.value = true;

            // Si el usuario ya existe en el sistema, pre-llenar sus datos
            if (response.data.existing_user && response.data.existing_data) {
                usuarioExistente.value = true;
                const d = response.data.existing_data;

                if (isProtectedEmail(d.email)) {
                    errorCurp.value = 'Esta cuenta pertenece a la administración del sistema y no puede modificarse desde registro';
                    alertaError('Cuenta protegida', errorCurp.value);
                    form.name = '';
                    curpEncontrado.value = false;
                    usuarioExistente.value = false;
                    return;
                }

                form.email = d.email ?? form.email;
                form.institution_id = d.institution_id ?? form.institution_id;

                // Cargar sub-áreas antes de asignar priority_area_id,
                // luego esperar nextTick para que el watcher se ejecute y resetee
                // sub_area_id, y DESPUÉS sobreescribilo con el valor correcto
                if (d.priority_area_id) {
                    try {
                        const subRes = await axios.get(`/api/sub-areas/${d.priority_area_id}`);
                        subareasPrioritariasOptions.value = subRes.data;
                        form.priority_area_id = d.priority_area_id;
                        // Esperar a que el watcher dispare y resetee sub_area_id
                        await nextTick();
                        // Ahora asignar DESPUÉS del reset del watcher
                        form.sub_area_id = d.sub_area_id ?? '';
                    } catch {}
                } else {
                    form.priority_area_id = d.priority_area_id ?? form.priority_area_id;
                    form.sub_area_id      = d.sub_area_id      ?? form.sub_area_id;
                }

                alertaExito('¡Cuenta existente detectada!', 'Se han cargado tus datos previos. Confirma tu contraseña para continuar.');
            } else {
                usuarioExistente.value = false;
                alertaExito('¡CURP encontrado!', 'Datos cargados correctamente');
            }
        }
    } catch (error) {
        cerrarAlerta();
        if (error.response?.status === 404) {
            errorCurp.value = 'CURP no encontrado en el sistema RENAPO';
            alertaError('CURP no encontrado', 'No se encontró el CURP en el sistema RENAPO');
        } else if (error.response?.status === 422) {
            const backendMsg = error.response.data.message
                || error.response.data.errors?.curp?.[0]
                || error.response.data.errors?.email?.[0]
                || 'Este CURP ya está registrado';
            errorCurp.value = backendMsg;
            alertaError('CURP ya registrado', backendMsg);
        } else if (error.response?.status === 503) {
            errorCurp.value = error.response.data.message;
            alertaError('Servicio de CURP no disponible', errorCurp.value);
        } else {
            errorCurp.value = 'Error al buscar el CURP. Por favor intenta de nuevo.';
            alertaError('Error', 'Error al buscar el CURP. Por favor intenta de nuevo');
        }
        
        form.name = '';
        curpEncontrado.value = false;            usuarioExistente.value = false;    } finally {
        buscandoCurp.value = false;
    }
};
</script>

<template>
    <LandingLayout>
        <Head title="Crear Cuenta" />

        <div class="flex items-center justify-center min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl w-full">
                <!-- Register Card -->
                <div class="bg-white rounded-lg shadow-xl p-5 sm:p-8 md:p-10 space-y-6 sm:space-y-7">
                    <!-- Title -->
                    <div class="text-center">
                        <h2 class="text-2xl sm:text-3xl font-semibold text-[var(--institutional-primary)]">Crear Cuenta</h2>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- CURP -->
                        <div>
                            <label for="curp" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                CURP
                            </label>
                            <div class="flex gap-2">
                                <input
                                    id="curp"
                                    v-model="form.curp"
                                    type="text"
                                    autofocus
                                    maxlength="18"
                                    :disabled="curpEncontrado"
                                    class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)] disabled:bg-gray-100"
                                    :class="{ 'border-b-red-500': form.errors.curp || errorCurp, 'border-b-green-500': curpEncontrado }"
                                    placeholder="Tu curp"
                                    @input="form.curp = form.curp.toUpperCase(); clearError('curp'); errorCurp = ''"
                                />
                                <button
                                    type="button"
                                    @click="buscarCurp"
                                    :disabled="buscandoCurp || curpEncontrado"
                                    class="bg-[var(--institutional-primary)] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#0f2347] transition whitespace-nowrap flex items-center gap-2 disabled:bg-gray-400 cursor-pointer"
                                >
                                    <svg v-if="buscandoCurp" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <LupaIcon v-else size="16" />
                                    {{ buscandoCurp ? 'Buscando...' : 'Buscar CURP' }}
                                </button>
                            </div>
                            <div v-if="form.errors.curp" class="mt-1 text-sm text-red-600">
                                {{ form.errors.curp }}
                            </div>
                            <div v-else-if="errorCurp" class="mt-1 text-sm text-red-600">
                                {{ errorCurp }}
                            </div>
                            <div v-else-if="curpEncontrado" class="mt-1 text-sm" :class="usuarioExistente ? 'font-medium' : 'text-green-600'" :style="usuarioExistente ? 'color: #1B396A' : ''">
                                <span v-if="usuarioExistente" class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Cuenta existente detectada &mdash; datos precargados. Ingresa tu contrase&ntilde;a para continuar.
                                </span>
                                <span v-else>&#10003; CURP v&aacute;lido encontrado</span>
                            </div>
                            <div v-else class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce tu CURP y haz clic en buscar</span>
                            </div>
                        </div>

                        <!-- Grid de 2 columnas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Nombre Completo -->
                            <div>
                                <label for="name" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Nombre Completo:
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    :readonly="curpEncontrado"
                                    class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                    :class="{ 'bg-gray-100': curpEncontrado, 'border-b-red-500': form.errors.name }"
                                    placeholder="Tu nombre"
                                    @input="clearError('name')"
                                />
                                <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ curpEncontrado ? 'Nombre obtenido del CURP' : 'Por favor, introduce tu nombre completo' }}</span>
                                </div>
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Correo Electrónico -->
                            <div>
                                <label for="email" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Correo Electrónico:
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    :readonly="usuarioExistente"
                                    :disabled="usuarioExistente"
                                    class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                    :class="{ 'border-b-red-500': form.errors.email, 'bg-gray-100 cursor-not-allowed': usuarioExistente }"
                                    placeholder="admin@example.com"
                                    @input="clearError('email')"
                                />
                                <div v-if="!form.errors.email" class="flex items-center gap-1 mt-1 text-xs" :class="usuarioExistente ? '' : 'text-gray-500'" :style="usuarioExistente ? 'color: #1B396A' : ''">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span v-if="usuarioExistente">Correo cargado desde tu cuenta existente</span>
                                    <span v-else>Por favor, introduce tu correo electrónico</span>
                                </div>
                                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </div>
                            </div>
                        </div>

                        <!-- Grid de 2 columnas - Contraseñas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Contraseña -->
                            <div>
                                <label for="password" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Contraseña:
                                </label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 pr-10 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                        :class="{ 'border-b-red-500': form.errors.password }"
                                        placeholder="••••••••"
                                        @input="clearError('password')"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition cursor-pointer"
                                    >
                                        <EyeIcon v-if="!showPassword" size="20" class="text-gray-600" />
                                        <EyeOffIcon v-else size="20" class="text-gray-600" />
                                    </button>
                                </div>
                                <div v-if="!form.errors.password" class="flex items-center gap-1 mt-1 text-xs" :class="usuarioExistente ? '' : 'text-gray-500'" :style="usuarioExistente ? 'color: #1B396A' : ''">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span v-if="usuarioExistente">Ya tienes cuenta. Puedes usar tu contrase&ntilde;a actual o establecer una nueva.</span>
                                    <span v-else>Por favor, introduce tu contrase&ntilde;a</span>
                            </div>
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password }}
                            </div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div>
                                <label for="password_confirmation" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Confirmar Contraseña:
                                </label>
                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 pr-10 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                        :class="{ 'border-b-red-500': form.errors.password_confirmation }"
                                        placeholder="••••••••"
                                        @input="clearError('password_confirmation')"
                                    />
                                    <button
                                        type="button"
                                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition cursor-pointer"
                                    >
                                        <EyeIcon v-if="!showPasswordConfirmation" size="20" class="text-gray-600" />
                                        <EyeOffIcon v-else size="20" class="text-gray-600" />
                                    </button>
                                </div>
                                <div v-if="!form.errors.password_confirmation" class="flex items-center gap-1 mt-1 text-xs" :class="usuarioExistente ? '' : 'text-gray-500'" :style="usuarioExistente ? 'color: #1B396A' : ''">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span v-if="usuarioExistente">Repite la misma contrase&ntilde;a que ingresaste arriba</span>
                                    <span v-else>Por favor, confirma tu contrase&ntilde;a</span>
                            </div>
                            <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password_confirmation }}
                            </div>
                            </div>
                        </div>

                        <!-- Nombre de la Institución de Procedencia -->
                        <div>
                            <label for="institution_id" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Nombre de la Institución de Procedencia:
                            </label>
                            <VueSelect
                                v-model="form.institution_id"
                                :options="institucionesOptions"
                                :reduce="option => option.id"
                                label="name"
                                placeholder="Buscar o seleccionar una institución..."
                                :searchable="true"
                                :clearable="true"
                                :class="['vue-select-custom', { 'has-error': form.errors.institution_id }]"
                                @update:modelValue="clearError('institution_id')"
                            >
                                <template #no-options="{ search, searching }">
                                    <template v-if="searching">
                                        No se encontraron resultados para <em>{{ search }}</em>.
                                    </template>
                                    <em v-else>Comienza a escribir para buscar...</em>
                                </template>
                            </VueSelect>
                            <div v-if="!form.errors.institution_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, selecciona tu institución de procedencia</span>
                            </div>
                            <div v-if="form.errors.institution_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.institution_id }}
                            </div>
                        </div>

                        <!-- Grid de 2 columnas - Áreas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Área Prioritaria -->
                            <div>
                                <label for="priority_area_id" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Área Prioritaria:
                                </label>
                                <VueSelect
                                    v-model="form.priority_area_id"
                                    :options="areasPrioritariasOptions"
                                    :reduce="option => option.id"
                                    label="name"
                                    placeholder="Buscar o seleccionar un área..."
                                    :searchable="true"
                                    :clearable="true"
                                    :class="['vue-select-custom', { 'has-error': form.errors.priority_area_id }]"
                                    @update:modelValue="clearError('priority_area_id')"
                                >
                                    <template #no-options="{ search, searching }">
                                        <template v-if="searching">
                                            No se encontraron resultados para <em>{{ search }}</em>.
                                        </template>
                                        <em v-else>Comienza a escribir para buscar...</em>
                                    </template>
                                </VueSelect>
                                <div v-if="!form.errors.priority_area_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, selecciona el área prioritaria</span>
                                </div>
                                <div v-if="form.errors.priority_area_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.priority_area_id }}
                                </div>
                            </div>

                            <!-- SubÁrea Prioritaria -->
                            <div>
                                <label for="sub_area_id" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    SubÁrea Prioritaria:
                                </label>
                                <VueSelect
                                    v-model="form.sub_area_id"
                                    :options="subareasPrioritariasOptions"
                                    :reduce="option => option.id"
                                    label="name"
                                    placeholder="Buscar o seleccionar una subárea..."
                                    :searchable="true"
                                    :clearable="true"
                                    :class="['vue-select-custom', { 'has-error': form.errors.sub_area_id }]"
                                    @update:modelValue="clearError('sub_area_id')"
                                >
                                    <template #no-options="{ search, searching }">
                                        <template v-if="searching">
                                            No se encontraron resultados para <em>{{ search }}</em>.
                                        </template>
                                        <em v-else>Selecciona un Área Prioritaria primero...</em>
                                    </template>
                                </VueSelect>
                                <div v-if="!form.errors.sub_area_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, selecciona la subárea prioritaria</span>
                                </div>
                                <div v-if="form.errors.sub_area_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.sub_area_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-[var(--institutional-primary)] text-white py-2.5 px-4 rounded font-medium hover:bg-[#0f2347] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed mt-6 cursor-pointer"
                        >
                            <span v-if="!form.processing">Crear una cuenta</span>
                            <span v-else class="flex items-center justify-center">
                                Creando cuenta...
                            </span>
                        </button>

                        <!-- Login Link -->
                        <div class="text-center pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                ¿Ya una cuenta?
                                <Link
                                    :href="route('login')"
                                    class="font-medium text-[var(--institutional-primary)] hover:text-[#0f2347] hover:underline transition"
                                >
                                    Inicia Sesión
                                </Link>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </LandingLayout>
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
.vue-select-custom.has-error :deep(.vs__dropdown-toggle) {
    border-bottom-color: #EF4444;
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

.vue-select-custom :deep(.vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}
</style>

