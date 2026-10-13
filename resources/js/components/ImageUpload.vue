<template>
    <div class="space-y-3">
        <div v-if="title" class="text-center">
            <label class="block text-sm font-medium text-gray-700">{{ title }}</label>
        </div>
        
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50">
            <div v-if="!getImage" class="space-y-4">
                <div class="flex justify-center">
                    <div class="w-16 h-16 bg-gray-300 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500">
                    <p>{{ description || 'Seleccionar imagen (formato JPG, PNG)' }}</p>
                </div>
                <div class="flex justify-center">
                    <BaseButton 
                        @click="$refs.fileInput.click()"
                        :icon="mdiFile" 
                        color="lightDark" 
                        :label="selectButtonLabel"
                        outline
                        small
                    />
                </div>
            </div>

            <div v-else class="space-y-4">
                <img :src="getImage" class="mx-auto w-32 h-32 object-cover rounded-lg border" alt="Vista previa">
                <div class="flex justify-center space-x-2">
                    <BaseButton 
                        @click="$refs.fileInput.click()"
                        :icon="mdiFile" 
                        color="lightDark" 
                        :label="changeButtonLabel"
                        outline
                        small
                    />
                    <BaseButton 
                        :icon="mdiTrashCan" 
                        color="danger" 
                        @click="deleteImage" 
                        outline 
                        small
                    />
                </div>
            </div>

            <input 
                ref="fileInput"
                type="file" 
                :accept="acceptedFormats"
                class="hidden"
                @change="handleFileInput"
            >
        </div>

        <InputError :message="error" />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { mdiFile, mdiTrashCan } from "@mdi/js";
import BaseButton from "./BaseButton.vue";
import InputError from "./InputError.vue";

const fileInput = ref(null);
const imageDeleted = ref(false); 

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null,
    },
    title: {
        type: String,
        default: 'Imagen',
    },
    description: {
        type: String,
        default: '',
    },
    selectButtonLabel: {
        type: String,
        default: 'Seleccionar archivo',
    },
    changeButtonLabel: {
        type: String,
        default: 'Cambiar imagen',
    },
    acceptedFormats: {
        type: String,
        default: 'image/png,image/jpeg,image/jpg',
    },
    existingImage: {
        type: String,
        default: null,
    },
    editMode: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'delete']);

const getImage = computed(() => {
    if (props.modelValue instanceof File) {
        return URL.createObjectURL(props.modelValue);
    }
    if (props.editMode && props.existingImage && !imageDeleted.value) {
        return props.existingImage;
    }
    if (typeof props.modelValue === 'string' && props.modelValue) {
        return props.modelValue;
    }
    return null;
});

const handleFileInput = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            imageDeleted.value = false;
            emit('update:modelValue', file);
        }
    }
};

const deleteImage = () => {
    emit('update:modelValue', null);
    emit('delete');
    imageDeleted.value = true;
    
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>
