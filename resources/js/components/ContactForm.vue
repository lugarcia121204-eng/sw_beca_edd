<template>
    <div class="grid grid-cols-1 md:grid-cols-2 md:gap-4">
        <FormField v-if="hasContactName" label="Nombre del contacto" :error="props.errors?.['contact.name']">
            <FormControl v-model="contact.name" type="text" placeholder="Nombre completo del contacto" maxlength="50" />
        </FormField>

        <FormField label="Correo electrónico" :error="props.errors?.['contact.email']">
            <FormControl v-model="contact.email" type="email" placeholder="contacto@institucion.edu.mx"
                :icon="mdiEmailOutline" />
        </FormField>

        <FormField label="Página web" :error="props.errors?.['contact.website']">
            <FormControl v-model="contact.website" type="url" placeholder="www.institucion.edu.mx" :icon="mdiWeb" />
        </FormField>
    </div>

    <BaseDivider />

    <div>
        <PhoneNumbersForm v-model="phones" :errors="props.errors">
            <div class="flex gap-2 items-center">
                <BaseIcon class="p-2 rounded-lg bg-forest-400 text-white" :path="mdiPhoneOutline" size="24" h="h-10"
                    w="w-10" />
                <div>
                    <h3 class="text-base text-forest-400 font-semibold">Teléfonos</h3>
                    <span class="text-sm text-gray-500">Administra múltiples números de teléfono con extensiones</span>
                </div>
            </div>
        </PhoneNumbersForm>
    </div>

    <BaseDivider />

    <div>
        <SocialLinks v-model="socialLinks" :errors="props.errors">
            <div class="flex gap-2 items-center">
                <BaseIcon class="p-2 rounded-lg bg-forest-400 text-white" :path="mdiLinkBoxOutline" size="24" h="h-10"
                    w="w-10" />
                <div>
                    <h3 class="text-base text-forest-400 font-semibold">Redes sociales</h3>
                    <span class="text-sm text-gray-500">Gestionar perfiles de redes sociales y redes
                        profesionales</span>
                </div>
            </div>
        </SocialLinks>
    </div>
</template>

<script setup>
import FormField from '@/components/FormField.vue';
import FormControl from '@/components/FormControl.vue';
import PhoneNumbersForm from './PhoneNumbersForm.vue';
import SocialLinks from './SocialLinks.vue';
import BaseDivider from './BaseDivider.vue';
import BaseIcon from './BaseIcon.vue';
import { mdiEmailOutline, mdiLinkBoxOutline, mdiPhoneOutline, mdiWeb } from '@mdi/js';

const contact = defineModel('contact', {
    default: {
        id: null,
        name: '',
        email: '',
        website: '',
    }
});

const phones = defineModel('phones', {
    default: [
        {
            id: null,
            number: '',
            dial_code: '',
            type: ''
        }
    ]
});

const socialLinks = defineModel('socialLinks', {
    default: [
        {
            id: null,
            url: '',
            type: ''
        }
    ]
});

const props = defineProps({
    hasContactName: {
        type: Boolean,
        default: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

</script>

