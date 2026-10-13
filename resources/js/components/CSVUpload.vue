<template>
    <CardSection :hasDivider="hasDivider">
        
        <template #heading>
            
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 text-white flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full">
                    <BaseIcon class="bg-forest-400 text-mono-100 rounded-lg" :path="mdiFileDocumentOutline" size="24" h="h-10"
                    w="w-10" />
                </div>
                <div>
                    <h3 class="text-lg font-medium leading-6 text-primary">
                        Cargar archivo CSV
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Selecciona un archivo CSV para importar datos
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Se permiten hasta 100 filas por carga.
                    </p>
                </div>
            </div>
        </template>

        <div class="mt-4">
            <div class="grid grid-cols-1 items-center space-x-4">
                <button
                    v-if="!hasFile"
                    type="button"
                    class="relative flex w-full inline-flex items-center rounded-md border border-gray-300 px-4 py-3 bg-secondary text-sm font-medium text-primary shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <BaseIcon :path="mdiPlus" class="mr-2" />
                    <span>Seleccionar archivo</span>
                    <input 
                        ref="fileInput" 
                        type="file" 
                        accept=".csv, text/csv" 
                        @change="handleFileInput"
                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0" 
                    />
                    
                </button>

                <div v-else class="flex h-10 flex-grow items-center justify-between rounded-md border bg-gray-100 px-3 text-sm py-6 shadow-sm">
                    <div class="flex items-center gap-2 overflow-hidden py-2">
                        <BaseIcon :path="mdiFileDocumentOutline" class="text-gray-500" />
                        <span class="truncate font-medium text-gray-800" :title="fileName">
                            {{ fileName }}
                        </span>
                    </div>
                    <div>
                        <button
                            @click="removeFile"
                            type="button"
                            class="ml-4 inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"

                        >
                            <BaseIcon :path="mdiTrashCan" class="h-4 w-4"  />
                            <span class="text-sm">Eliminar archivo</span>
                        </button>
                        <button
                            @click="saveForm"
                            type="button"
                            class="ml-4 inline-flex items-center rounded-md border border-transparent bg-forest-400 px-3 py-1 text-sm font-medium text-white shadow-sm hover:bg-forest-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            :disabled="processing"
                        >
                            <BaseIcon :path="mdiCheck" class="h-4 w-4" />
                            {{ processing ? 'Procesando...' : 'Confirmar' }}
                        </button>
                    </div>
                </div>
                <div class="mt-2 space-x-2 text-sm text-gray-500 flex items-center">
                    <BaseIcon :path="mdiFileDocumentOutline" class="h-4 w-4 text-gray-400" />
                    <span>El archivo debe estar en formato CSV y seguir la plantilla adecuada.</span>
                        <a :href="route('csv.downloadTemplate', { model_class: props.model_class })"
                        class="ml-4 inline-flex items-center rounded-md border border-transparent bg-forest-400 px-3 py-1 text-sm font-medium text-white shadow-sm hover:bg-forest-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition-colors duration-200">
                            Descargar Plantilla
                        </a>
                </div>
            </div>
            <div v-if="form.errors.csv" class="mt-2">
                <InputError :message="form.errors.csv" />
            </div>
        </div>
    </CardSection>
</template>

<script setup>
import CardSection from './CardSection.vue';
import BaseIcon from './BaseIcon.vue';
import { mdiFileDocumentOutline, mdiPlus, mdiTrashCan, mdiCheck } from '@mdi/js';
import { ref, computed } from 'vue';
import InputError from './InputError.vue';
import { useForm } from '@inertiajs/vue3';

const fileInput = ref(null);

const props = defineProps({
    routeName: {
        type: String,
        default: 'csv.'
    },
    model_class: {
        type: String,
        required: true,
    },
    hasDivider: {
        type: Boolean,
        default: false
    },
});

const form = useForm({
    csv: null,
    model_class: props.model_class,
});

const hasFile = computed(() => !!form.csv);
const fileName = computed(() => form.csv?.name || '');
const processing = computed(() => form.processing);

function handleFileInput(e) {
    const file = e.target.files[0];
    form.csv = file;
}

function removeFile() {
    form.csv = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

const emit = defineEmits(['file-confirmed']);


const saveForm = () => {
    form.post(route(`${props.routeName}store`));
    removeFile();
};
</script>