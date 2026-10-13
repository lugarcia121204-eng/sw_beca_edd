<template>
    <div class="w-full mx-auto p-4 space-y-6">
        <div class="relative">
            <input ref="fileInput" type="file" multiple accept="image/*" @change="handleFileInput" class="hidden" />

            <div @click="$refs.fileInput.click()" @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false" @drop.prevent="handleFileInput" :class="[
                    'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all duration-200',
                    isDragging
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                ]">
                <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                        <BaseIcon :path="mdiPlus" size="24" h="h-auto" w="w-auto" class="text-gray-500" />
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-700">
                            {{ totalFiles === 0 ? 'Selecciona o arrastra imágenes aquí' : 'Agregar más imágenes' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            PNG, JPG, GIF hasta 5MB cada una
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="totalFiles > 0" class="flex justify-between items-center">
            <Badge size="md">
                {{ fileCountText }}
            </Badge>
            <button @click="removeAllFiles" class="text-sm text-red-600 hover:opacity-80 font-medium cursor-pointer">
                Eliminar todas
            </button>
        </div>

        <ImageSwiper :photos="form.photos" :errors="form.errors">
            <template #actions="{ index, photo }">
                <BaseButton :icon="mdiTrashCan" color="danger" @click="removeFile(index)" title="Eliminar imagen" />
                <BaseButton :icon="mdiCommentText" color="whiteDark" @click="openModal(photo)" title="Descripción" />
            </template>
        </ImageSwiper>
    </div>
    <DialogModal :show="showFile" :closeable="true" @close="closeModal">
        <template #title>
            <SectionTitleLineWithButton title="Configurar descripción" :hisBreadCrumb="false"
                :icon="mdiTextBoxOutline" />
        </template>
        <template #content>
            <FormField label="Título" required :error="form.errors[`photos.${selectedFile.index}.title`]">
                <FormControl v-model="selectedFile.title" placeholder="Ingresa un título" />
            </FormField>
            <FormField label="Descripción" :error="form.errors[`photos.${selectedFile.index}.description`]">
                <FormControl v-model="selectedFile.description" placeholder="Ingresa una descripción" type="textarea"
                    height="h-24" max-length="150" />
            </FormField>
        </template>
        <template #footer>
            <div class="flex justify-end gap-2">
                <BaseButton label="Aceptar" color="lightDark" @click="closeModal" :icon="mdiCheck" />
            </div>
        </template>
    </DialogModal>
</template>

<script setup>
import { mdiCheck, mdiCommentText, mdiPlus, mdiTextBoxOutline, mdiTrashCan } from '@mdi/js'
import BaseIcon from './BaseIcon.vue'
import DialogModal from './DialogModal.vue';
import BaseButton from './BaseButton.vue';
import FormField from './FormField.vue';
import FormControl from './FormControl.vue';
import SectionTitleLineWithButton from './SectionTitleLineWithButton.vue';
import Badge from './Badge.vue';
import { useComments, useImage } from '@/Hooks/useImage';
import ImageSwiper from './ImageSwiper.vue';

const { form } = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

const { showFile, selectedFile, openModal, closeModal, } = useComments();
const { isDragging, fileCountText, totalFiles, handleFileInput, removeFile, removeAllFiles } = useImage(form, 'photos', 'imagene');
</script>