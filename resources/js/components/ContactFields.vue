<template>
    <div class="space-y-6">
        <div class="md:grid grid-cols-2 gap-4">
            <LabelText v-if="hasName" label="Nombre del contacto" :text="contact?.name" />
            <LabelText label="Correo electrónico">
                <a v-if="contact?.email" :href="`mailto:${contact?.email}`" target="_blank"
                    class="text-primary hover:text-forest-50 transition-colors">
                    {{ contact?.email }}
                </a>
                <span v-else> Sin datos </span>
            </LabelText>
            <LabelText label="Página web">
                <a v-if="contact?.website" :href="contact?.website" target="_blank"
                    class="text-primary hover:text-forest-50 transition-colors">
                    {{ contact?.website }}
                </a>
                <span v-else> Sin datos </span>
            </LabelText>
        </div>

        <CardSection title="Teléfonos" description="Teléfonos del contacto">
            <PhoneNumbersList :phoneNumbers="phoneNumbers" />
        </CardSection>

        <CardSection title="Redes Sociales" description="Redes Sociales del contacto">
            <SocialLinksList :socialLinks="socialLinks" />
        </CardSection>
    </div>
</template>

<script setup>
import CardSection from '@/components/CardSection.vue';
import LabelText from '@/components/LabelText.vue';
import PhoneNumbersList from '@/components/PhoneNumbersList.vue';
import SocialLinksList from '@/components/SocialLinksList.vue';

defineProps({
    contact: {
        type: Object,
        default: null
    },
    hasName: {
        type: Boolean,
        default: true
    },
    phoneNumbers: {
        type: Array,
        default: () => []
    },
    socialLinks: {
        type: Array,
        default: () => []
    }
});
</script>
