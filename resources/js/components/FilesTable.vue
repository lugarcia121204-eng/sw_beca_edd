<template>
    <table v-if="files.length > 0">
        <thead class="text-gray-700 bg-gray-100">
            <tr>
                <th>Archivo</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in files" :key="item.id">
                <td data-label="Archivo">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg md:flex hidden items-center justify-center bg-wine-100">
                                <BaseIcon :path="mdiFile" class="text-white" />
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                {{ item.title }}
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ item.size }} Kb
                            </div>
                        </div>
                    </div>
                </td>
                <td data-label="Descripción">
                    {{ item.description }}
                </td>
                <td data-label="Fecha">
                    {{ item.created_at.human }}
                </td>
                <td data-label="acciones">
                    <BaseButtons>
                        <BaseButton @click="openModal(item)" color="info" :icon="mdiEye" small title="Ver documento" />
                    </BaseButtons>
                </td>
            </tr>
        </tbody>
    </table>

    <CardBoxComponentEmpty v-else />

    <FileModal @close="closeModal" :showModal="showModal" :file="selectedFile" />
</template>

<script setup>
import BaseButton from '@/components/BaseButton.vue';
import BaseButtons from '@/components/BaseButtons.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import CardBoxComponentEmpty from './CardBoxComponentEmpty.vue';
import FileModal from '@/components/FileModal.vue';
import { mdiEye, mdiFile } from '@mdi/js';
import { useFileModal } from '@/Hooks/useFile';

defineProps({
    files: {
        type: Array,
        required: false,
        default: () => []
    }
});

const { showModal, selectedFile, openModal, closeModal } = useFileModal();
</script>
