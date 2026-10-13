<template>
    <Modal :show="show" max-width="xl" :closeable="true" @close="close">
        <CardBox :hasBorder="false">
            <div class="mb-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <CalendarCog class="w-5 h-5 text-forest-400" />
                        <h2 class="text-forest-400 text-xl font-bold">Extender vigencia</h2>
                    </div>

                    <BaseButton color="light" @click="close" :icon="mdiClose" />
                </div>
                <span class="text-sm font-light text-slate-800 dark:text-slate-300">
                    Extiende el tiempo de vigencia del registro
                </span>
            </div>

            <div class="space-y-4 mb-4">
                <FormField label="Título">
                    <span v-html="record.title"></span>
                </FormField>

                <FormField label="Descripción técnica">
                    <span v-html="record.technical_description"></span>
                </FormField>

                <slot name="content" />

                <div class="grid grid-cols-2 gap-4">
                    <FormField label="Fecha de inicio" :error="form.errors.start_date">
                        <FormControl v-model="form.start_date" type="date" />
                    </FormField>

                    <FormField label="Fecha de cierre" :error="form.errors.end_date">
                        <FormControl v-model="form.end_date" type="date" />
                    </FormField>
                </div>
            </div>

            <BaseButton @click="updateValidity" class="w-full" color="success" label="Actualizar vigencia"
                :icon="mdiCheck" />
        </CardBox>
    </Modal>

    <loading v-model:active="form.processing" :can-cancel="false" :is-full-page="true" loader="dots" color="#283C2A" />
</template>

<script setup>
import BaseButton from '@/components/BaseButton.vue';
import CardBox from '@/components/CardBox.vue';
import FormControl from '@/components/FormControl.vue';
import FormField from '@/components/FormField.vue';
import Modal from '@/components/Modal.vue';
import { mdiCheck, mdiClose } from '@mdi/js';
import { CalendarCog } from 'lucide-vue-next';
import { useValidity } from '@/Hooks/useValidity';

const props = defineProps({
    show: Boolean,
    record: Object,
    routeName: String,
});

const emit = defineEmits(['close']);

const { form, close, updateValidity } = useValidity(props, emit);
</script>

