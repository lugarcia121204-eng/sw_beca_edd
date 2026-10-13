<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import EyeOffIcon from '@/components/icons/EyeIcon.vue';
import EyeIcon from '@/components/icons/EyeOffIcon.vue';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    token: {
        type: String,
        required: true
    },
    email: {
        type: String,
        required: true
    }
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: ''
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    
    // Validación del lado del cliente
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
    
    alertaCargando('Restableciendo contraseña', 'Por favor espera...');
    
    form.post(route('password.update'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Contraseña actualizada!', 'Tu contraseña ha sido restablecida exitosamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'No se pudo restablecer la contraseña. El enlace puede haber expirado');
        },
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};
</script>

<template>
    <LandingLayout>
        <Head title="Restablecer Contraseña" />

        <div class="flex items-center justify-center min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-md w-full">
                <!-- Card Principal -->
                <div class="bg-white rounded-lg shadow-xl p-5 sm:p-8 md:p-10 space-y-6 sm:space-y-7">
                    <!-- Title -->
                    <div class="text-center">
                        <h2 class="text-2xl sm:text-3xl text-[var(--institutional-primary)] font-semibold">Restablecer Contraseña</h2>
                        <p class="mt-2 text-sm text-gray-600">
                            Ingresa tu nueva contraseña para continuar
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email (readonly) -->
                        <div>
                            <label for="email" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Correo Electrónico:
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                readonly
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 cursor-not-allowed opacity-75"
                            />
                        </div>

                        <!-- Nueva Contraseña -->
                        <div>
                            <label for="password" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Nueva Contraseña:
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
                            <div v-if="!form.errors.password" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Debe tener al menos 8 caracteres</span>
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
                            <div v-if="!form.errors.password_confirmation" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Vuelve a escribir tu contraseña</span>
                            </div>
                            <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-[#002B5C] cursor-pointer text-white py-2.5 px-4 rounded font-medium hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-800 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!form.processing">Restablecer Contraseña</span>
                            <span v-else class="flex items-center justify-center">
                                Restableciendo...
                            </span>
                        </button>

                        <!-- Login Link -->
                        <div class="text-center pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                ¿Recordaste tu contraseña?
                                <Link 
                                    :href="route('login')" 
                                    class="text-[var(--institutional-primary)] hover:text-[#0f2347] font-medium hover:underline transition"
                                >
                                    Iniciar sesión
                                </Link>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

