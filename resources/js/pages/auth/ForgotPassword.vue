<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

defineProps({
    status: {
        type: String,
        default: null
    }
});

const form = useForm({
    email: '',
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
    if (!form.email) {
        form.errors.email = 'El correo electrónico es obligatorio';
        return;
    }
    if (!form.email.includes('@')) {
        form.errors.email = 'El correo electrónico debe contener un @';
        return;
    }
    
    alertaCargando('Enviando enlace', 'Por favor espera...');
    
    form.post(route('password.email'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Enlace enviado!', 'Revisa tu correo electrónico para restablecer tu contraseña');
            
            // Redirigir al login después de 2 segundos
            setTimeout(() => {
                router.visit(route('login'));
            }, 2000);
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'No se pudo enviar el enlace. Verifica tu correo electrónico');
        },
    });
};
</script>

<template>
    <LandingLayout>
        <Head title="Recuperar Contraseña" />

        <div class="flex items-center justify-center min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-md w-full">
                <!-- Card Principal -->
                <div class="bg-white rounded-lg shadow-xl p-5 sm:p-8 md:p-10 space-y-6 sm:space-y-7">
                    <!-- Title -->
                    <div class="text-center">
                        <h2 class="text-2xl sm:text-3xl text-[var(--institutional-primary)] font-semibold">¿Olvidaste tu contraseña?</h2>
                        <p class="mt-2 text-sm text-gray-600">
                            No te preocupes, te enviaremos instrucciones para restablecerla
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Correo Electrónico:
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autofocus
                                autocomplete="email"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': form.errors.email }"
                                placeholder="tu.correo@ejemplo.com"
                                @input="clearError('email')"
                            />
                            <div v-if="!form.errors.email" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Ingresa el correo electrónico asociado a tu cuenta</span>
                            </div>
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-[#002B5C] cursor-pointer text-white py-2.5 px-4 rounded font-medium hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-800 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!form.processing">Enviar enlace de recuperación</span>
                            <span v-else class="flex items-center justify-center">
                                Enviando...
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
