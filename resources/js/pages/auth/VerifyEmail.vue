<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { alertaExito, alertaError, alertaAdvertencia, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    email: String,
    status: String,
});

const form = useForm({
    code: '',
});

const resendForm = useForm({
    email: props.email,
});

const codeInputs = ref([]);
const isResending = ref(false);

// Auto-focus en el primer input
const focusInput = (index) => {
    if (codeInputs.value[index]) {
        codeInputs.value[index].focus();
    }
};

// Manejar input en cada dígito
const handleInput = (index, event) => {
    const value = event.target.value;
    
    // Solo permitir números
    if (!/^\d$/.test(value) && value !== '') {
        event.target.value = '';
        return;
    }

    // Actualizar el código completo
    const digits = codeInputs.value.map(input => input.value).join('');
    form.code = digits;

    // Auto-focus al siguiente input
    if (value && index < 5) {
        focusInput(index + 1);
    }

    // Auto-submit cuando se completen los 6 dígitos
    if (digits.length === 6) {
        submit();
    }
};

// Manejar backspace
const handleKeydown = (index, event) => {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        focusInput(index - 1);
    }
};

// Manejar paste
const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    
    pastedData.split('').forEach((digit, index) => {
        if (codeInputs.value[index]) {
            codeInputs.value[index].value = digit;
        }
    });

    form.code = pastedData;
    
    if (pastedData.length === 6) {
        submit();
    }
};

const submit = () => {
    // Validación del lado del cliente
    if (!form.code || form.code.length !== 6) {
        alertaError('Código incompleto', 'Debes ingresar los 6 dígitos del código');
        return;
    }
    
    alertaCargando('Verificando código', 'Por favor espera...');
    
    form.post('/email/verify/code', {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Código verificado!', 'Tu cuenta ha sido activada exitosamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Código inválido', 'El código ingresado es incorrecto o ha expirado');
            // Limpiar inputs en caso de error
            codeInputs.value.forEach(input => input.value = '');
            form.code = '';
            focusInput(0);
        },
    });
};

const resendCode = () => {
    isResending.value = true;
    alertaCargando('Reenviando código', 'Por favor espera...');
    
    resendForm.post('/email/verify/resend', {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Código reenviado!', 'Revisa tu bandeja de entrada');
        },
        onError: (errors) => {
            cerrarAlerta();
            const msg = errors.email || 'No se pudo reenviar el código. Inténtalo de nuevo';
            alertaError('Error al reenviar', msg);
        },
        onFinish: () => {
            isResending.value = false;
        },
    });
};
</script>

<template>
    <LandingLayout>
        <Head title="Verificar Correo Electrónico" />

        <div class="flex items-center justify-center min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl w-full">
                <!-- Card Principal -->
                <div class="bg-white rounded-lg shadow-xl p-5 sm:p-8 md:p-10 space-y-6">
                    <!-- Título -->
                    <div class="text-center">
                        <h2 class="text-2xl sm:text-3xl font-semibold text-[var(--institutional-primary)] mb-3">Confirma tu Correo Electrónico</h2>
                        <p class="text-gray-600 text-sm text-left">
                            Hemos enviado un código de verificación de 6 dígitos a tu dirección de email.
                            Por favor revisa tu bandeja de entrada e ingrésalo a continuación para activar tu cuenta.<br>
                            <br>
                            Si no encuentras el correo, revisa la carpeta de spam o correo no deseado.
                        </p>
                    </div>

                    <!-- Email destacado -->
                    <!-- Email destacado (Banner Style) -->
                    <div v-if="email" 
                        class="relative flex items-center gap-3 px-4 py-2 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4 border-l-[var(--institutional-primary)]"
                    >
                        <div class="flex-shrink-0 text-[var(--institutional-primary)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center text-left">
                            <span class="text-[13px] font-medium text-gray-800">
                                Código enviado a: <span class="text-[var(--institutional-primary)] font-semibold">{{ email }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Inputs del código -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block mb-3 text-base text-[var(--institutional-primary)] font-medium text-center">
                                Ingresa el Código de 6 dígitos
                            </label>
                            
                            <div class="flex justify-center gap-2">
                                <input
                                    v-for="index in 6"
                                    :key="index"
                                    :ref="el => codeInputs[index - 1] = el"
                                    type="text"
                                    maxlength="1"
                                    inputmode="numeric"
                                    pattern="[0-9]"
                                    class="w-10 h-12 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold bg-[#F3F4F6] border-t-0 border-x-0 rounded-lg focus:ring-0 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)] transition-all"
                                    :class="{ 'border-b-red-500': form.errors.code }"
                                    @input="handleInput(index - 1, $event)"
                                    @keydown="handleKeydown(index - 1, $event)"
                                    @paste="handlePaste"
                                />
                            </div>

                            <div class="flex items-center gap-1 mt-2 text-xs text-gray-500 justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Ingresa los 6 dígitos que recibiste por correo</span>
                            </div>

                            <p v-if="form.errors.code" class="mt-2 text-sm text-red-600 text-center">
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || form.code.length !== 6"
                            class="w-full bg-[var(--institutional-primary)] text-white py-2.5 px-4 rounded font-medium hover:bg-[#0f2347] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!form.processing">Verificar Código</span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Verificando...
                            </span>
                        </button>
                    </form>

                    <!-- Reenviar código -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">
                            ¿No recibiste el código?
                        </p>
                        <button
                            @click="resendCode"
                            :disabled="resendForm.processing || isResending"
                            class="text-[var(--institutional-primary)] hover:text-[#0f2347] cursor-pointer font-medium text-sm disabled:text-gray-400 disabled:cursor-not-allowed hover:underline transition"
                        >
                            {{ isResending ? 'Reenviando...' : 'Reenviar código' }}
                        </button>
                    </div>

                    <!-- Mensajes de estado (Banner Style) -->
                    <div v-if="status" 
                        class="relative flex items-center gap-3 px-4 py-2 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4 border-l-[var(--institutional-primary)]"
                    >
                        <div class="flex-shrink-0 text-[var(--institutional-primary)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center text-left">
                            <span class="text-[13px] font-medium text-gray-800">{{ status }}</span>
                        </div>
                    </div>
                    
                    <div v-if="$page.props.flash?.success" 
                        class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4 border-l-green-500"
                    >
                        <div class="flex-shrink-0 text-green-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-green-400 mb-0.5">Éxito</span>
                            <span class="text-sm font-bold leading-tight text-gray-800">{{ $page.props.flash.success }}</span>
                        </div>
                    </div>
                    
                    <div v-if="$page.props.flash?.warning" 
                        class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4 border-l-yellow-500"
                    >
                        <div class="flex-shrink-0 text-yellow-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-yellow-400 mb-0.5">Atención</span>
                            <span class="text-sm font-bold leading-tight text-gray-800">{{ $page.props.flash.warning }}</span>
                        </div>
                    </div>

                    <!-- Volver al login (Cerrar Sesión Interno) -->
                    <div class="text-center">
                        <button
                            @click="$inertia.post(route('logout'))"
                            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 text-sm transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver e Iniciar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>
