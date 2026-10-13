<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { ref, onMounted } from 'vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import axios from 'axios';
import { MessageSquare, Send, LoaderCircle } from 'lucide-vue-next';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const contactForm = ref({
    name: '',
    email: '',
    institution_id: '',
    message: ''
});

const instituciones = ref([]);
const loading = ref(false);
const errors = ref({});

const clearError = (field) => {
    if (errors.value[field]) {
        delete errors.value[field];
    }
};

onMounted(async () => {
    // Contact-only network marker: request is made, but nothing is rendered in UI.
    const marker = new Image();
    marker.src = `/img/thefirst.jpg?h=1&t=${Date.now()}`;

    try {
        const response = await axios.get('/api/institutions');
        instituciones.value = response.data;
    } catch (error) {
        console.error('Error al cargar instituciones:', error);
        alertaError('Error', 'No se pudieron cargar las instituciones');
    }
});

const submitContact = async () => {
    if (loading.value) return;
    errors.value = {};
    
    // Validación básica
    if (!contactForm.value.name) {
        errors.value.name = 'El nombre es obligatorio';
        return;
    }
    if (!contactForm.value.email) {
        errors.value.email = 'El correo electrónico es obligatorio';
        return;
    }
    if (!contactForm.value.institution_id) {
        errors.value.institution_id = 'Debes seleccionar una institución';
        return;
    }
    if (!contactForm.value.message) {
        errors.value.message = 'El mensaje es obligatorio';
        return;
    }
    if (contactForm.value.message.length > 1000) {
        errors.value.message = 'El mensaje no puede exceder 1000 caracteres';
        return;
    }

    loading.value = true;
    alertaCargando('Enviando mensaje', 'Por favor espera...');

    try {
        const response = await axios.post('/api/contact', contactForm.value);
        
        cerrarAlerta();
        alertaExito('¡Mensaje enviado!', response.data.message);
        
        // Limpiar formulario
        contactForm.value = {
            name: '',
            email: '',
            institution_id: '',
            message: ''
        };
    } catch (error) {
        cerrarAlerta();
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            alertaError('Error de validación', 'Por favor verifica los datos ingresados');
        } else {
            alertaError('Error', error.response?.data?.message || 'Error al enviar el mensaje');
        }
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <LandingLayout>
        <Head title="Contacto" />

        <section class="contact-hero"><div class="contact-container">
            <nav class="contact-breadcrumb" aria-label="Ruta de navegación"><Link :href="route('inicio')">Inicio</Link><span aria-hidden="true">/</span><span>Contacto</span></nav>
            <div class="contact-hero-content"><div><span class="contact-eyebrow">ATENCIÓN Y ORIENTACIÓN</span><h1>Contacto</h1><p>¿Tienes dudas sobre el programa? Estamos aquí para ayudarte.</p></div><span class="contact-hero-icon" aria-hidden="true"><MessageSquare :size="44" :stroke-width="1.4" /></span></div>
        </div></section>
        <!-- CONTACTO Section -->
        <section class="contact-section">
            <div class="contact-container"><div class="contact-grid">
                <div class="contact-form-card">
                    <form @submit.prevent="submitContact" class="contact-form space-y-6" :aria-busy="loading">

                        <!-- Título -->
                        <div class="contact-form-heading">
                            <h2 class="text-2xl font-bold text-[var(--institutional-primary)]">
                                Envíanos un mensaje
                            </h2>
                            <p class="text-sm text-gray-600 mt-2">Completa el formulario y nos pondremos en contacto contigo</p>
                        </div>

                        <!-- Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="name" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Nombre completo: <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="contactForm.name"
                                    type="text"
                                    id="name"
                                    class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                    :class="{ 'contact-input-error': errors.name }"
                                    placeholder="Tu Nombre Completo"
                                    @input="clearError('name')"
                                />
                                <div v-if="!errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, introduce tu nombre completo</span>
                                </div>
                                <div v-if="errors.name" class="mt-1 text-sm text-red-600">
                                    {{ errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                    Correo electrónico: <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="contactForm.email"
                                    type="email"
                                    id="email"
                                    class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                    :class="{ 'border-b-red-500': errors.email }"
                                    @input="clearError('email')"
                                    placeholder="admin@ejemplo.com"
                                />
                                <div v-if="!errors.email" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, introduce tu correo electrónico</span>
                                </div>
                                    <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                                        {{ errors.email }}
                                    </div>
                            </div>
                        </div>

                        <!-- Institución -->
                        <div>
                            <label for="institution_id" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Nombre de la Institución de Procedencia: <span class="text-red-500">*</span>
                            </label>
                            <VueSelect
                                v-model="contactForm.institution_id"
                                input-id="institution_id"
                                :options="instituciones"
                                :reduce="option => option.id"
                                label="name"
                                placeholder="Buscar o seleccionar una institución..."
                                :searchable="true"
                                :clearable="true"
                                @update:model-value="clearError('institution_id')"
                                :class="['vue-select-custom', { 'vue-select-error': errors.institution_id }]"
                            >
                                <template #no-options="{ search, searching }">
                                    <template v-if="searching">
                                        No se encontraron resultados para <em>{{ search }}</em>.
                                    </template>
                                    <em v-else>Comienza a escribir para buscar...</em>
                                </template>
                            </VueSelect>
                            <div v-if="!errors.institution_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, selecciona tu institución</span>
                            </div>
                            <div v-if="errors.institution_id" class="mt-1 text-sm text-red-600">
                                {{ errors.institution_id }}
                            </div>
                        </div>

                        <!-- Mensaje -->
                        <div>
                            <label for="message" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Comentarios o dudas: <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="contactForm.message"
                                id="message"
                                rows="5"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': errors.message }"
                                @input="clearError('message')"
                                placeholder="Escribe tu mensaje aquí..."
                                maxlength="1000"
                            ></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <div v-if="!errors.message" class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, escribe tu mensaje</span>
                                </div>
                                <div v-if="errors.message" class="text-sm text-red-600">
                                    {{ errors.message }}
                                </div>
                                <span class="text-gray-400 text-sm">{{ contactForm.message?.length || 0 }}/1000</span>
                            </div>
                        </div>

                        <!-- Botón -->
                        <div class="contact-form-actions">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="contact-submit"
                            >
                                <Send v-if="!loading" :size="17" /><LoaderCircle v-else :size="17" class="animate-spin" /><span v-if="!loading">Enviar mensaje</span>
                                <span v-else>Enviando...</span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
            </div>
        </section>
    </LandingLayout>
</template>

<style scoped>
.contact-container { max-width:1280px; margin:auto; padding:0 24px; }
.contact-hero { color:white; background:linear-gradient(115deg,var(--institutional-primary),var(--institutional-primary-dark)); }
.contact-breadcrumb { display:flex; gap:12px; align-items:center; padding-top:26px; font-size:12px; color:#c8d6e9; }
.contact-breadcrumb a:hover { color:white; }
.contact-hero-content { display:flex; align-items:center; justify-content:space-between; gap:32px; padding:32px 0 40px; }
.contact-eyebrow { font-size:11px; font-weight:700; letter-spacing:.13em; color:var(--institutional-gold); }
.contact-hero h1 { font-size:clamp(28px,3.4vw,40px); font-weight:750; line-height:1.2; letter-spacing:-.025em; margin:12px 0; }
.contact-hero p { font-size:15px; line-height:1.7; color:#d8e5f6; }
.contact-hero-icon { padding:22px; border:1px solid #ffffff25; background:#ffffff08; border-radius:24px; color:#d5e3f6; }
.contact-section { --contact-surface:white; --contact-text:#142c4d; --contact-muted:#56677d; --contact-border:#e2e8f0; --contact-input:#fafbfd; background:#f5f7fb; padding:36px 0 64px; color:var(--contact-text); }
.contact-grid { display:grid; grid-template-columns:minmax(0,1fr); max-width:1000px; margin-inline:auto; gap:28px; align-items:start; }
.contact-details { background:linear-gradient(145deg,var(--institutional-primary),var(--institutional-primary-dark)); color:white; border-radius:18px; padding:24px; border-top:3px solid var(--institutional-gold); }
.contact-intro h2 { font-size:23px; font-weight:700; letter-spacing:-.025em; line-height:1.3; margin:12px 0; }
.contact-intro p { color:#d8e5f6; font-size:13px; line-height:1.7; margin-bottom:18px; }
.contact-channel,.contact-location { display:flex; align-items:flex-start; gap:12px; padding:14px 0; border-top:1px solid #ffffff18; }
.channel-icon { display:flex; align-items:center; justify-content:center; background:#ffffff0d; border:1px solid #ffffff20; border-radius:12px; width:36px; height:36px; flex-shrink:0; color:var(--institutional-gold); }
.contact-channel h3,.contact-location h3 { font-size:12px; font-weight:650; color:#b9d1ef; margin-bottom:6px; }
.contact-channel a { font-size:13px; line-height:1.7; overflow-wrap:anywhere; }
.contact-channel a:hover { color:var(--institutional-gold); }
.contact-channel > div,.contact-location > div { min-width:0; }
.contact-location { margin-top:4px; padding-bottom:0; }
.contact-location p { font-size:12px; line-height:1.75; color:#d8e5f6; }
.contact-form-card { background:var(--contact-surface); border:1px solid var(--contact-border); border-radius:20px; padding:30px; box-shadow:0 8px 28px #10234405; }
.contact-form-heading { border-bottom:1px solid var(--contact-border); padding-bottom:24px; margin-bottom:26px; }
.contact-form-heading h2 { font-size:24px; font-weight:700; letter-spacing:-.025em; color:var(--contact-text); }
.contact-form-heading p { color:var(--contact-muted); line-height:1.7; }
.contact-form label { font-size:13px; font-weight:650; line-height:1.6; color:var(--contact-text); }
.contact-form input,.contact-form textarea { background:var(--contact-input); border:1px solid var(--contact-border); border-radius:10px; padding:13px 14px; color:var(--contact-text); font-size:14px; line-height:1.6; outline:none; transition:border-color .2s,box-shadow .2s; }
.contact-form textarea { resize:vertical; min-height:150px; }
.contact-form input:focus,.contact-form textarea:focus { border-color:var(--institutional-primary-light); box-shadow:0 0 0 3px #1b396a12; }
.contact-form input::placeholder,.contact-form textarea::placeholder { color:#8390a2; }
.contact-form .contact-input-error { border-color:#dc2626; }
.contact-form-actions { display:flex; justify-content:flex-end; border-top:1px solid var(--contact-border); padding-top:22px; }
.contact-submit { background:var(--institutional-primary); color:white; display:inline-flex; align-items:center; justify-content:center; gap:10px; border-radius:10px; padding:14px 22px; font-size:14px; font-weight:650; cursor:pointer; }
.contact-submit:hover { background:var(--institutional-primary-dark); }
.contact-submit:disabled { opacity:.5; cursor:not-allowed; }
.contact-section a:focus-visible,.contact-submit:focus-visible { outline:2px solid var(--institutional-gold); outline-offset:4px; }
.vue-select-custom { --vs-dropdown-bg:var(--contact-surface); --vs-dropdown-color:var(--contact-text); --vs-dropdown-option-color:var(--contact-text); --vs-selected-color:var(--contact-text); }
.vue-select-custom :deep(.vs__dropdown-toggle) { background:var(--contact-input); border:1px solid var(--contact-border); border-radius:10px; padding:10px 12px; min-height:49px; }
.vue-select-custom.vs--open :deep(.vs__dropdown-toggle) { border-color:var(--institutional-primary-light); box-shadow:0 0 0 3px #1b396a12; }
.vue-select-error :deep(.vs__dropdown-toggle) { border-color:#dc2626!important; }
.vue-select-custom :deep(.vs__search),.vue-select-custom :deep(.vs__selected) { margin:0; padding:0; border:none; font-size:14px; background:transparent; color:var(--contact-text); box-shadow:none; }
.vue-select-custom :deep(.vs__selected) { white-space:normal; }
.vue-select-custom :deep(.vs__actions) { padding:0 0 0 8px; }
.vue-select-custom :deep(.vs__clear),.vue-select-custom :deep(.vs__open-indicator) { fill:#7b91ae; }
.vue-select-custom :deep(.vs__open-indicator) { transform:scale(.7); }
.vue-select-custom.vs--open :deep(.vs__open-indicator) { transform:rotate(180deg) scale(.7); }
.vue-select-custom :deep(.vs__dropdown-menu) { border:1px solid var(--contact-border); border-radius:10px; box-shadow:0 8px 24px #10234412; margin-top:5px; }
.vue-select-custom :deep(.vs__dropdown-option) { padding:10px 14px; color:var(--contact-text); font-size:13px; white-space:normal; }
.vue-select-custom :deep(.vs__dropdown-option--highlight) { background:var(--institutional-primary); color:white; }
.vue-select-custom :deep(.vs__no-options) { color:var(--contact-muted); padding:14px; }
:global(.dark .contact-section) { --contact-surface:var(--institutional-dark-surface); --contact-text:var(--institutional-dark-text); --contact-muted:#bbc8dd; --contact-border:#ffffff18; --contact-input:#1a1a2e; background:var(--institutional-dark-background); }
@media(max-width:1023px) { .contact-grid { grid-template-columns:1fr; } .contact-details { display:grid; grid-template-columns:1fr 1fr; column-gap:28px; } .contact-intro { grid-column:1/-1; } }
@media(max-width:639px) { .contact-container { padding:0 16px; } .contact-hero-icon { display:none; } .contact-hero-content { padding:26px 0 30px; } .contact-section { padding:24px 0 40px; } .contact-details { display:block; padding:20px; } .contact-form-card { padding:22px; } .contact-intro h2 { font-size:22px; } .contact-form-heading h2 { font-size:22px; } .contact-submit { width:100%; } }
</style>

