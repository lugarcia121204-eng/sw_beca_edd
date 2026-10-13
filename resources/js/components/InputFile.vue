<script setup>
import { ref, computed, watch } from 'vue';
import BaseButtons from './BaseButtons.vue';
import BaseButton from './BaseButton.vue';
import { mdiImage, mdiTrashCan } from '@mdi/js';

const { form, attribute, existingFileUrl, disabled } = defineProps({
    attribute: {
        type: String,
        default: 'file'
    },
    existingFileUrl: {
        type: String,
        default: null,
    },
    form: {
        type: Object,
        required: true
    },
    disabled: Boolean
});

const fileInput = ref(null);

const getImageUrl = computed(() => {
    if (form[attribute]) {
        return URL.createObjectURL(form[attribute]);
    }
    return existingFileUrl || null;
});

const openFileDialog = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const onFileSelected = (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        form[attribute] = files[0];
    } else {
        form.reset(attribute);
    }
};

const clearFile = () => {
    form.reset(attribute);
    if (fileInput.value) {
        fileInput.value.value = null;
    };
};

const classContainer = computed(() => {
    const base = [
        "border-4 border-dashed flex flex-col items-center justify-center rounded-lg shadow-lg p-6 md:p-10 mb-4  mt-2",

        form[attribute]
            ? "bg-emerald-50 border-emerald-400 dark:bg-gray-700 dark:border-gray-400"
            : "bg-slate-100 border-gray-400 dark:bg-gray-800 dark:border-gray-600"
    ];

    if(disabled){
        base.push("opacity-50 dark:opacity-80 cursor-not-allowed");
    }

    return base;
});

watch(
    () => form[attribute],
    (newFile, existingFileUrl) => {
        if (existingFileUrl && existingFileUrl instanceof File) {
            URL.revokeObjectURL(URL.createObjectURL(existingFileUrl));
        }
    }
);
</script>

<template>
    <div :class="classContainer">
        <input id="file" ref="fileInput" type="file" @change="onFileSelected" class="hidden" />

        <div v-if="getImageUrl" class="relative w-full max-w-2xl border rounded shadow-inner overflow-hidden">
            <div class="aspect-[3/2] bg-black/5">
                <img :src="getImageUrl" class="w-full h-full object-cover" alt="Vista previa" />
            </div>
        </div>

        <BaseButtons class="justify-center mt-2">
            <BaseButton 
                :disabled="disabled" 
                small 
                :icon="mdiImage" 
                @click="openFileDialog" 
                color="lightDark"
                :label="existingFileUrl ? 'Selecciona una nueva imagen' : 'Selecciona una imagen'" 
            />
            <BaseButton 
                :disabled="disabled" 
                v-if="form[attribute]" 
                small 
                :icon="mdiTrashCan" 
                label="Eliminar" 
                color="lightDark"
                @click="clearFile()" 
            />
        </BaseButtons>
    </div>
</template>