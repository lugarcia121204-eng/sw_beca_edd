<template>
    <DialogModal :show="show" @close="close" maxWidth="lg">
        <template #title>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-bold text-lg text-gray-800 uppercase tracking-tight">Rechazar Solicitud</span>
                </div>
                <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors duration-200 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        <template #content>
            <div class="space-y-4 pt-4">
                <!-- Banner de Instrucciones Estilo Premium -->
                <div class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100 border-l-[5px] border-l-red-500">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-red-500 opacity-80 mb-0.5">
                            Instrucciones
                        </span>
                        <span class="text-sm font-bold leading-tight text-gray-800">
                            Por favor, proporciona una justificación detallada para el rechazo. Esta información será enviada al docente.
                        </span>
                    </div>
                </div>

                <div class="relative group">
                    <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900 uppercase tracking-tight">
                        Motivo del Rechazo: <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        v-model="form.comentario" 
                        class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)] transition-all duration-300 min-h-[160px] leading-relaxed" 
                        :class="{ 'border-b-red-500': form.errors.comentario }"
                        placeholder="Escribe aquí los motivos detallados..."
                        maxlength="855"
                        required
                    ></textarea>
                    
                    <div class="flex justify-between items-center mt-1">
                        <div class="flex items-center gap-1 text-xs text-gray-500">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Escribe una descripción</span>
                        </div>
                        <span class="text-gray-400 text-sm">{{ form.comentario.length }}/855</span>
                    </div>
                    <InputError :message="form.errors.comentario" class="mt-1" />
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <SecondaryButton 
                    @click="close"
                    class="flex-1 sm:flex-none justify-center px-6 py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-gray-200 transition-all duration-200 cursor-pointer"
                >
                    Cancelar
                </SecondaryButton>
                
                <button 
                    class="flex-1 sm:flex-none justify-center px-8 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-red-700 focus:ring-4 focus:ring-red-100 transition-all duration-200 shadow-lg shadow-red-200/50 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    :disabled="form.processing"
                    @click="confirmSubmit"
                >
                    <span v-if="!form.processing">Confirmar Rechazo</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
        </template>
    </DialogModal>
</template>

<script setup>
import DialogModal from '@/components/DialogModal.vue';
import InputError from '@/components/InputError.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { alertaCargando, cerrarAlerta, alertaExito, alertaError, alertaConfirmacionEscrita } from '@/utils/alerts.js';

const props = defineProps({
    show: Boolean,
    applicationId: Number,
});

const emit = defineEmits(['close']);

const form = useForm({
    status: 'rejected',
    comentario: '',
});

watch(() => props.show, (val) => {
    if (val) {
        form.reset();
        form.comentario = 'Estimado docente: Le informamos que tras revisar su expediente, su solicitud no cumple con los criterios y lineamientos establecidos en las bases vigentes de la presente convocatoria, por lo cual no ha sido aprobada. Agradecemos su participación.';
        form.clearErrors();
    }
});

const close = () => {
    emit('close');
};

const confirmSubmit = async () => {
    if (!props.applicationId) return;

    // Validación client-side
    form.clearErrors();
    if (!form.comentario || form.comentario.trim() === '') {
        form.errors.comentario = 'El motivo del rechazo es obligatorio';
        return;
    }

    const confirmed = await alertaConfirmacionEscrita(
        '¿Confirmar rechazo?',
        'Esta acción notificará al docente sobre el rechazo de su solicitud. Esta operación no se puede deshacer.',
        'RECHAZAR'
    );

    if (confirmed) {
        submit();
    }
};

const submit = () => {
    alertaCargando('Procesando', 'Rechazando solicitud...');
    
    form.post(route('admin.applications.verdict', props.applicationId), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Realizado!', 'La solicitud ha sido rechazada.');
            close();
        },
        onError: (errors) => {
            cerrarAlerta();
            const firstError = Object.values(errors)[0];
            alertaError('No se pudo rechazar', firstError || 'Hubo un problema al procesar el rechazo.');
        },
    });
};
</script>

