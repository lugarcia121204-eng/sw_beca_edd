<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { ref, computed, watch } from 'vue';
import { Files, Download, Eye, Search, X } from 'lucide-vue-next';

const props = defineProps({
    activeAnnouncement: Object,
    managedDocumentIds: { type: Array, default: () => [] },
    workDocuments: { type: Array, default: () => [] }
});

const announcementYear = computed(() => {
    if (props.activeAnnouncement && props.activeAnnouncement.created_at) {
        return new Date(props.activeAnnouncement.created_at).getFullYear();
    }
    return 2026;
});

const activeDocument = ref(null);
const currentPdfUrl = ref('');
const currentPdfTitle = ref('');

const toggleDocument = (id, url, title) => {
    if (activeDocument.value === id) {
        closeDocument();
    } else {
        activeDocument.value = id;
        currentPdfUrl.value = url;
        currentPdfTitle.value = title;
    }
};

const closeDocument = () => {
    activeDocument.value = null;
    currentPdfUrl.value = '';
    currentPdfTitle.value = '';
};


const documentColor = (category) => ({ formatos: '#2B579A', guias: '#059669', convocatoria: 'var(--institutional-primary)', privacidad: '#E9C81F' }[category] || '#1b396a');

const selectedCategory = ref('todos');
const searchQuery = ref('');
const categories = [
    { key: 'todos', label: 'Todos' }, { key: 'formatos', label: 'Formatos' },
    { key: 'guias', label: 'Guías y normativa' }, { key: 'convocatoria', label: 'Convocatoria' },
    { key: 'privacidad', label: 'Privacidad' },
];
const searchIndex = [
    {
        "id": "doc1",
        "category": "formatos",
        "title": "Carta de Exclusividad Laboral",
        "description": "</svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Carta de Exclusividad Laboral</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Documento donde el docente manifiesta su compromiso exclusivo con el TecNM, declarando no exceder las 12 horas-semana en otras instituciones y autorizando la revisión con recursos humanos.",
        "variant": null
    },
    {
        "id": "doc2",
        "category": "formatos",
        "title": "Liberación de Actividades Académicas",
        "description": "</svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Liberación de Actividades Académicas</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Constancia de cumplimiento de actividades académicas como asistencia a reuniones, programas de formación docente, asesorías, propuestas de mejora, sinodales de titulación y participación en eventos académicos.",
        "variant": null
    },
    {
        "id": "doc3",
        "category": "formatos",
        "title": "Constancia Actividades Frente a Grupo",
        "description": "</svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Constancia Actividades Frente a Grupo</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Formato que certifica el cumplimiento de actividades docentes frente a grupo: elaboración de dosificación, evaluación de programas, entrega de informes, calificaciones y reportes finales del semestre.",
        "variant": null
    },
    {
        "id": "doc4",
        "category": "guias",
        "title": "Lineamientos del Programa",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Lineamientos del Programa</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Documento oficial con la estructura completa del programa: presentación, bases de operación, tipos de personal beneficiado, fuentes de financiamiento, criterios de evaluación y responsabilidades de docentes participantes.",
        "variant": null
    },
    {
        "id": "doc5",
        "category": "guias",
        "title": "Guía SPD - Integración de Expediente",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Guía SPD - Integración de Expediente</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Guía oficial para integrar el expediente del programa EDD con requisitos, instrucciones de captura, documentos comprobatorios, evidencias y responsabilidades del docente en el portal www.edd.tecnm.mx",
        "variant": null
    },
    {
        "id": "doc6",
        "category": "convocatoria",
        "title": "Convocatoria {{ announcementYear }}",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Convocatoria {{ announcementYear }}</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Documento oficial de convocatoria del TecNM para el programa de estímulos {{ announcementYear }}: bases de participación, registro, evaluación, estímulos diferenciados, suspensiones, responsabilidades y contacto oficial.",
        "variant": "dynamic"
    },
    {
        "id": "doc6",
        "category": "convocatoria",
        "title": "Convocatoria 2026 (Estática)",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Convocatoria 2026 (Estática)</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Documento oficial de convocatoria del TecNM para el programa de estímulos 2025: bases de participación, registro, evaluación, estímulos diferenciados, suspensiones, responsabilidades y contacto oficial.",
        "variant": "static"
    },
    {
        "id": "doc7",
        "category": "privacidad",
        "title": "Aviso de Privacidad Integral",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Aviso de Privacidad Integral</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Documento legal del TecNM sobre protección de datos personales: fundamento legal, finalidades para recabar información, tratamiento, resguardo y derechos de los participantes según la Ley General de Protección de Datos.",
        "variant": null
    },
    {
        "id": "doc8",
        "category": "privacidad",
        "title": "Aviso de Privacidad Simplificado",
        "description": "</path>\r\n                            </svg>\r\n                        </div>\r\n                        <div class=\"flex-1\">\r\n                            <h3 class=\"font-semibold text-gray-900 text-lg mb-2\">Aviso de Privacidad Simplificado</h3>\r\n                            <p class=\"text-sm text-gray-600 mb-3\">Versión simplificada del aviso de privacidad con las finalidades principales: gestión de becas, otorgamiento de estímulos, expedición de certificados, trámites de cédulas profesionales y suscripción de convenios del TecNM.",
        "variant": null
    }
];
const normalizeSearch = (value) => value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLocaleLowerCase('es-MX');
const searchableDocuments = computed(() => [...props.workDocuments, ...searchIndex.filter(document => !props.managedDocumentIds.includes(document.id) && (!document.variant || document.variant === (props.activeAnnouncement?.file_url ? 'dynamic' : 'static')))]);
const matchesDocument = (id) => {
    const document = searchableDocuments.value.find(document => document.id === id);
    if (!document || (selectedCategory.value !== 'todos' && selectedCategory.value !== document.category)) return false;
    const text = (document.title + ' ' + (document.description || '')).replaceAll('{{ announcementYear }}', String(announcementYear.value));
    const terms = normalizeSearch(searchQuery.value).trim().split(/\s+/).filter(Boolean);
    return terms.every(term => normalizeSearch(text).includes(term));
};
const resultCount = computed(() => searchableDocuments.value.filter(document => matchesDocument(document.id)).length);
watch([selectedCategory, searchQuery], closeDocument);

const isPdfResource = (url) => /\.pdf($|\?)/i.test(url || '');
const isImageResource = (url) => /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(url || '');

const currentPreviewIsPdf = computed(() => isPdfResource(currentPdfUrl.value));
const currentPreviewIsImage = computed(() => isImageResource(currentPdfUrl.value));
</script>

<template>
    <LandingLayout>
        <Head title="Documentos" />

        <section class="documents-hero">
            <div class="documents-container">
                <nav class="documents-breadcrumb" aria-label="Ruta de navegación"><Link :href="route('inicio')">Inicio</Link><span aria-hidden="true">/</span><span>Documentos</span></nav>
                <div class="documents-hero-content">
                    <div><span class="documents-eyebrow">RECURSOS DEL PROGRAMA</span><h1>Documentos de Trabajo 2026</h1><p>Descarga los formatos oficiales y documentación necesaria para tu participación en el programa</p></div>
                    <span class="documents-hero-icon" aria-hidden="true"><Files :size="44" :stroke-width="1.4" /></span>
                </div>
                <div class="documents-formats"><span><Download :size="15" /> Formatos descargables</span><span><Eye :size="15" /> Documentos con vista previa</span></div>
            </div>
        </section>
        <!-- DOCUMENTOS Section -->
        <section class="documents-section">
            <div class="documents-container">
                <div class="documents-toolbar">
                    <div class="document-filters" role="group" aria-label="Clasificar documentos">
                        <button v-for="category in categories" :key="category.key" type="button" :aria-pressed="selectedCategory === category.key" :class="{ selected: selectedCategory === category.key }" @click="selectedCategory = category.key">{{ category.label }}</button>
                    </div>
                    <label class="document-search"><Search :size="20" aria-hidden="true" /><span class="sr-only">Buscar documento</span><input v-model="searchQuery" type="search" placeholder="Buscar documento..." /><button v-if="searchQuery" type="button" @click="searchQuery = ''" aria-label="Limpiar búsqueda"><X :size="16" /></button></label>
                </div>
                <div class="documents-grid">
                    <div class="library-heading"><div><h2>Formatos y documentación</h2><p>Consulta la descripción de cada archivo y selecciona el documento que necesitas.</p></div><span class="library-badge" role="status" aria-live="polite">{{ resultCount }} {{ resultCount === 1 ? 'documento' : 'documentos' }}</span></div>
                    <div v-if="resultCount === 0" class="documents-empty"><Search :size="28" /><h3>No se encontraron documentos</h3><p>Prueba con otra búsqueda o categoría.</p><button type="button" @click="selectedCategory = 'todos'; searchQuery = ''">Mostrar todos</button></div>
                    <article v-for="document in workDocuments" v-show="matchesDocument(document.id)" :key="document.id" class="document-card uploaded-document" :style="{ '--card-color': documentColor(document.category) }">
                        <div class="shrink-0"><Files :size="25" class="uploaded-icon" /></div><div class="uploaded-body"><h3>{{ document.title }}</h3><p>{{ document.description }}</p><div class="document-card-footer"><span>{{ document.format }} · {{ Math.ceil(document.file_size / 1024) }} KB</span><div class="uploaded-actions"><button v-if="document.preview_url" type="button" @click="toggleDocument(document.id, document.preview_url, document.title)"><Eye :size="16" /> {{ activeDocument === document.id ? 'Cerrar vista' : 'Ver documento' }}</button><a :href="document.download_url"><Download :size="16" /> Descargar</a></div></div><iframe v-if="activeDocument === document.id && document.preview_url" :src="document.preview_url" :title="document.title" class="uploaded-preview"></iframe></div>
                    </article>
                    <!-- 1. Carta de Exclusividad Laboral -->
                    <a v-show="matchesDocument('doc1')" :style="{ '--card-color': '#2B579A' }" href="https://edd.tecnm.mx/formatos/2025/CARTA_EXCLUSIVIDAD_EDD2025.docx" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#2B579A] hover:shadow-xl transition-all group cursor-pointer">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#2B579A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Carta de Exclusividad Laboral</h3>
                            <p class="text-sm text-gray-600 mb-3">Documento donde el docente manifiesta su compromiso exclusivo con el TecNM, declarando no exceder las 12 horas-semana en otras instituciones y autorizando la revisión con recursos humanos.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">DOCX • 85 KB</span>
                                <span class="document-action">Descargar</span>
                                <svg class="w-5 h-5 text-[#2B579A] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- 2. Formato de Liberación de Actividades Académicas -->
                    <a v-show="matchesDocument('doc2')" :style="{ '--card-color': '#2B579A' }" href="https://edd.tecnm.mx/formatos/2025/Formato_Liberacion_de_Actividades_Academicas_2025.docx" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#2B579A] hover:shadow-xl transition-all group cursor-pointer">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#2B579A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Liberación de Actividades Académicas</h3>
                            <p class="text-sm text-gray-600 mb-3">Constancia de cumplimiento de actividades académicas como asistencia a reuniones, programas de formación docente, asesorías, propuestas de mejora, sinodales de titulación y participación en eventos académicos.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">DOCX • 92 KB</span>
                                <span class="document-action">Descargar</span>
                                <svg class="w-5 h-5 text-[#2B579A] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- 3. Formato de Constancia de Liberación de Actividades Docentes -->
                    <a v-show="matchesDocument('doc3')" :style="{ '--card-color': '#2B579A' }" href="https://edd.tecnm.mx/formatos/2025/Formato_Constancia_de_Liberacion_de_Actividades_Docentes_2025.docx" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#2B579A] hover:shadow-xl transition-all group cursor-pointer">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#2B579A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Constancia Actividades Frente a Grupo</h3>
                            <p class="text-sm text-gray-600 mb-3">Formato que certifica el cumplimiento de actividades docentes frente a grupo: elaboración de dosificación, evaluación de programas, entrega de informes, calificaciones y reportes finales del semestre.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">DOCX • 88 KB</span>
                                <span class="document-action">Descargar</span>
                                <svg class="w-5 h-5 text-[#2B579A] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- 4. Lineamientos del Programa -->
                    <button v-show="matchesDocument('doc4')" :style="{ '--card-color': '#059669' }" @click="toggleDocument('doc4', 'https://edd.tecnm.mx/formatos/2025/LINEAMIENTOS_EDD_2025.pdf', 'Lineamientos del Programa')" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#059669] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[#059669]': activeDocument === 'doc4'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Lineamientos del Programa</h3>
                            <p class="text-sm text-gray-600 mb-3">Documento oficial con la estructura completa del programa: presentación, bases de operación, tipos de personal beneficiado, fuentes de financiamiento, criterios de evaluación y responsabilidades de docentes participantes.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • 4.8 MB</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[#059669] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- Viewer for Doc 4 -->
                    <div v-if="activeDocument === 'doc4'" class="document-preview col-span-1 md:col-span-2 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-4 transition-all">
                        <div class="flex justify-between items-center p-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800">{{ currentPdfTitle }}</h3>
                            <button @click="closeDocument" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-200 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] relative bg-gray-100">
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto animate-pulse mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Cargando vista previa...</span>
                                </div>
                            </div>
                            <iframe
                                v-if="currentPreviewIsPdf"
                                :src="currentPdfUrl"
                                class="w-full h-full relative z-10"
                                frameborder="0"
                            ></iframe>
                            <div
                                v-else-if="currentPreviewIsImage"
                                class="w-full h-full relative z-10 overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentPdfUrl"
                                    :alt="currentPdfTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>
                            <div v-else class="w-full h-full relative z-10 flex items-center justify-center p-8">
                                <a
                                    :href="currentPdfUrl"
                                    target="_blank"
                                    class="px-4 py-2 rounded-md bg-[var(--institutional-primary)] text-white font-medium hover:bg-[var(--institutional-primary-dark)]"
                                >
                                    Abrir documento
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 5. SPD - Solicitud de Personal Docente -->
                    <button v-show="matchesDocument('doc5')" :style="{ '--card-color': '#059669' }" @click="toggleDocument('doc5', 'https://edd.tecnm.mx/formatos/2025/FORMATO_SPD_2025.pdf', 'Guía SPD - Integración de Expediente')" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#059669] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[#059669]': activeDocument === 'doc5'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Guía SPD - Integración de Expediente</h3>
                            <p class="text-sm text-gray-600 mb-3">Guía oficial para integrar el expediente del programa EDD con requisitos, instrucciones de captura, documentos comprobatorios, evidencias y responsabilidades del docente en el portal www.edd.tecnm.mx</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • 1.2 MB</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[#059669] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- 6. Convocatoria (Dinámica) -->
                    <button v-show="matchesDocument('doc6')" :style="{ '--card-color': 'var(--institutional-primary)' }" v-if="activeAnnouncement && activeAnnouncement.file_url" @click="toggleDocument('doc6', activeAnnouncement.file_url, `Convocatoria ${announcementYear}`)" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[var(--institutional-primary)] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[var(--institutional-primary)]': activeDocument === 'doc6'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Convocatoria {{ announcementYear }}</h3>
                            <p class="text-sm text-gray-600 mb-3">Documento oficial de convocatoria del TecNM para el programa de estímulos {{ announcementYear }}: bases de participación, registro, evaluación, estímulos diferenciados, suspensiones, responsabilidades y contacto oficial.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • Dinámico</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[var(--institutional-primary)] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    <!-- Fallback 6. Convocatoria Estática -->
                    <button v-show="matchesDocument('doc6')" :style="{ '--card-color': 'var(--institutional-primary)' }" v-else @click="toggleDocument('doc6', 'https://edd.tecnm.mx/formatos/2025/CONVOCATORIA_EDD_2025.pdf', 'Convocatoria 2026')" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[var(--institutional-primary)] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[var(--institutional-primary)]': activeDocument === 'doc6'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Convocatoria 2026 (Estática)</h3>
                            <p class="text-sm text-gray-600 mb-3">Documento oficial de convocatoria del TecNM para el programa de estímulos 2025: bases de participación, registro, evaluación, estímulos diferenciados, suspensiones, responsabilidades y contacto oficial.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • 2.7 MB</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[var(--institutional-primary)] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- Viewer for Row 5/6 -->
                    <div v-if="activeDocument === 'doc5' || activeDocument === 'doc6'" class="document-preview col-span-1 md:col-span-2 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-4 transition-all">
                        <div class="flex justify-between items-center p-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800">{{ currentPdfTitle }}</h3>
                            <button @click="closeDocument" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-200 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] relative bg-gray-100">
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto animate-pulse mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Cargando vista previa...</span>
                                </div>
                            </div>
                            <iframe
                                v-if="currentPreviewIsPdf"
                                :src="currentPdfUrl"
                                class="w-full h-full relative z-10"
                                frameborder="0"
                            ></iframe>
                            <div
                                v-else-if="currentPreviewIsImage"
                                class="w-full h-full relative z-10 overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentPdfUrl"
                                    :alt="currentPdfTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>
                            <div v-else class="w-full h-full relative z-10 flex items-center justify-center p-8">
                                <a
                                    :href="currentPdfUrl"
                                    target="_blank"
                                    class="px-4 py-2 rounded-md bg-[var(--institutional-primary)] text-white font-medium hover:bg-[var(--institutional-primary-dark)]"
                                >
                                    Abrir documento
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Aviso de Privacidad Integral -->
                    <button v-show="matchesDocument('doc7')" :style="{ '--card-color': '#E9C81F' }" @click="toggleDocument('doc7', 'https://www.tecnm.mx/menu/proteccion_datos_personales/AVISO_DE_PRIVACIDAD_INTEGRAL_2023.pdf?doc=1', 'Aviso de Privacidad Integral')" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#E9C81F] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[#E9C81F]': activeDocument === 'doc7'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#E9C81F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Aviso de Privacidad Integral</h3>
                            <p class="text-sm text-gray-600 mb-3">Documento legal del TecNM sobre protección de datos personales: fundamento legal, finalidades para recabar información, tratamiento, resguardo y derechos de los participantes según la Ley General de Protección de Datos.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • 380 KB</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[#E9C81F] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- Doc 7 & 8 are handled after card 8 below -->

                    <!-- 8. Aviso de Privacidad Simplificado -->
                    <button v-show="matchesDocument('doc8')" :style="{ '--card-color': '#E9C81F' }" @click="toggleDocument('doc8', 'https://www.tecnm.mx/menu/proteccion_datos_personales/AVISO_DE_PRIVACIDAD_SIMPLIFICADO_2023.pdf?doc=1', 'Aviso de Privacidad Simplificado')" class="document-card flex items-start space-x-4 bg-white p-4 sm:p-6 rounded-xl shadow-md border-l-4 border-[#E9C81F] hover:shadow-xl transition-all group text-left w-full cursor-pointer" :class="{'ring-2 ring-offset-2 ring-[#E9C81F]': activeDocument === 'doc8'}">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#E9C81F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg mb-2">Aviso de Privacidad Simplificado</h3>
                            <p class="text-sm text-gray-600 mb-3">Versión simplificada del aviso de privacidad con las finalidades principales: gestión de becas, otorgamiento de estímulos, expedición de certificados, trámites de cédulas profesionales y suscripción de convenios del TecNM.</p>
                            <div class="document-card-footer flex items-center justify-between">
                                <span class="text-xs text-gray-500">PDF • 285 KB</span>
                                <span class="document-action">Ver documento</span>
                                <svg class="w-5 h-5 text-[#E9C81F] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- Viewer for Row 7/8 -->
                    <div v-if="activeDocument === 'doc7' || activeDocument === 'doc8'" class="document-preview col-span-1 md:col-span-2 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-4 transition-all">
                        <div class="flex justify-between items-center p-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800">{{ currentPdfTitle }}</h3>
                            <button @click="closeDocument" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-200 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] relative bg-gray-100">
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto animate-pulse mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Cargando vista previa...</span>
                                </div>
                            </div>
                            <iframe
                                v-if="currentPreviewIsPdf"
                                :src="currentPdfUrl"
                                class="w-full h-full relative z-10"
                                frameborder="0"
                            ></iframe>
                            <div
                                v-else-if="currentPreviewIsImage"
                                class="w-full h-full relative z-10 overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentPdfUrl"
                                    :alt="currentPdfTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>
                            <div v-else class="w-full h-full relative z-10 flex items-center justify-center p-8">
                                <a
                                    :href="currentPdfUrl"
                                    target="_blank"
                                    class="px-4 py-2 rounded-md bg-[var(--institutional-primary)] text-white font-medium hover:bg-[var(--institutional-primary-dark)]"
                                >
                                    Abrir documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

<style scoped>
.uploaded-document{display:flex;align-items:flex-start;gap:18px}.uploaded-icon{color:var(--card-color);flex-shrink:0}.uploaded-body{flex:1;min-width:0}.uploaded-body h3{font-size:18px;font-weight:600;color:#102344}.uploaded-body>p{margin:8px 0 14px;font-size:13px;color:#64748b;white-space:pre-wrap;overflow-wrap:anywhere}.uploaded-actions{display:flex;flex-wrap:wrap;gap:14px}.uploaded-actions button,.uploaded-actions a{display:inline-flex;align-items:center;gap:6px;color:var(--card-color);font-size:12px;cursor:pointer}.uploaded-preview{width:100%;height:500px;border:1px solid #e2e8f0;border-radius:8px;margin-top:16px}

.documents-hero { background:linear-gradient(115deg,var(--institutional-primary),var(--institutional-primary-dark)); color:white; }
.documents-container { max-width:1280px; padding:0 24px; margin:auto; }
.documents-breadcrumb { display:flex; align-items:center; gap:12px; padding-top:26px; font-size:12px; color:#c8d6e9; }
.documents-breadcrumb a:hover { color:white; }
.documents-hero-content { display:flex; justify-content:space-between; align-items:center; gap:32px; padding:32px 0; }
.documents-eyebrow { font-size:11px; font-weight:700; letter-spacing:.13em; color:var(--institutional-gold); }
.documents-hero h1 { font-size:clamp(28px,3.4vw,40px); font-weight:750; line-height:1.2; letter-spacing:-.025em; margin:12px 0; }
.documents-hero p { max-width:710px; font-size:15px; line-height:1.7; color:#d8e5f6; }
.documents-hero-icon { padding:22px; border:1px solid #ffffff25; background:#ffffff08; border-radius:24px; color:#d5e3f6; }
.documents-formats { display:flex; gap:30px; padding:19px 0; border-top:1px solid #ffffff20; }
.documents-formats span { display:flex; align-items:center; gap:9px; font-size:13px; color:#edf4ff; }
.documents-section { background:#f5f7fb; padding:36px 0 64px; }
.documents-toolbar { display:flex; align-items:center; justify-content:space-between; gap:24px; margin-bottom:32px; }
.document-filters { display:flex; gap:10px; flex-wrap:wrap; }
.document-filters button { border:1px solid #dce4ef; background:white; color:#405570; border-radius:999px; padding:11px 18px; font-size:14px; line-height:1.4; cursor:pointer; transition:background .2s; }
.document-filters button.selected { background:var(--institutional-primary); border-color:var(--institutional-primary); color:white; font-weight:650; }
.document-filters button:not(.selected):hover { background:#eaf0f8; }
.document-search { display:flex; align-items:center; gap:10px; border:1px solid #dce4ef; background:white; border-radius:14px; padding:12px 15px; width:320px; flex-shrink:0; color:#8b9eb8; }
.document-search input { width:100%; min-width:0; outline:none; border:0; background:transparent; color:#142c4d; font-size:14px; }
.document-search input::placeholder { color:#8390a2; }
.document-search input::-webkit-search-cancel-button { display:none; }
.document-search > svg { flex-shrink:0; }
.document-search button { cursor:pointer; padding:3px; }
.document-search:focus-within { border-color:var(--institutional-primary); box-shadow:0 0 0 3px #1b396a15; }
.document-filters button:focus-visible,.document-search button:focus-visible { outline:2px solid var(--institutional-gold); outline-offset:3px; }
.documents-empty { grid-column:1/-1; padding:48px 24px; text-align:center; border:1px dashed #ccd7e6; border-radius:18px; color:#56677d; }
.documents-empty svg { margin:0 auto 16px; }
.documents-empty h3 { font-size:18px; font-weight:650; margin-bottom:8px; }
.documents-empty p { font-size:14px; margin-bottom:20px; }
.documents-empty button { color:var(--institutional-primary); font-size:14px; font-weight:700; cursor:pointer; }
.document-card { border-left:4px solid var(--card-color)!important; }
:global(.dark .documents-toolbar .document-filters button),:global(.dark .documents-toolbar .document-search) { background:var(--institutional-dark-surface); color:#bbc8dd; border-color:#ffffff20; }
:global(.dark .documents-toolbar .document-filters button.selected) { background:var(--institutional-primary); color:white; }
:global(.dark .documents-toolbar .document-search input) { color:var(--institutional-dark-text); }
:global(.dark .documents-section .documents-empty) { color:var(--institutional-dark-text); border-color:#ffffff20; }
@media(max-width:1100px) { .documents-toolbar { flex-wrap:wrap; } .document-search { width:100%; } }
@media(max-width:639px) { .document-filters { gap:8px; } .document-filters button { padding:9px 13px; font-size:12px; } .documents-toolbar { gap:16px; margin-bottom:24px; } }
.documents-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; align-items:stretch; }
.library-heading { grid-column:1/-1; display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:6px; }
.library-heading h2 { font-size:22px; font-weight:700; letter-spacing:-.02em; color:#142c4d; margin-bottom:7px; }
.library-heading p { font-size:14px; line-height:1.7; color:#56677d; }
.library-badge { flex-shrink:0; font-size:11px; font-weight:650; letter-spacing:.06em; border:1px solid #dce4ef; background:white; color:#56677d; padding:8px 12px; border-radius:8px; }
.document-card { border:1px solid #e2e8f0; border-radius:14px; padding:18px; background:#fff; box-shadow:0 4px 18px #10234404; gap:18px; align-items:stretch; transition:border-color .2s,box-shadow .2s,transform .2s; }
.document-card:hover { border-color:#9badc7; box-shadow:0 8px 24px #1023440b; transform:translateY(-2px); }
.document-card > div { margin-left:0; }
.document-card > div:first-child { width:36px; height:40px; border:1px solid #e3eaf3; background:#f5f7fb; border-radius:12px; display:flex; align-items:center; justify-content:center; }
.document-card > div:first-child svg { width:25px; height:25px; color:var(--card-color); }
.document-card > div:last-child { display:flex; flex-direction:column; min-width:0; }
.document-card h3 { font-size:15px; line-height:1.4; font-weight:700; letter-spacing:-.015em; color:#142c4d; }
.document-card p { font-size:12px; line-height:1.6; color:#56677d; margin-bottom:12px; }
.document-card-footer { margin-top:auto; border-top:1px solid #e2e8f0; padding-top:10px; gap:10px; }
.document-card-footer > span:first-child { font-size:11px; font-weight:650; color:#62748b; }
.document-action { margin-left:auto; color:var(--card-color); font-size:12px; font-weight:700; }
.document-card-footer svg { width:16px; height:16px; color:var(--card-color); flex-shrink:0; }
.document-preview { grid-column:1/-1; border-radius:18px; box-shadow:0 6px 24px #10234408; }
.document-card:focus-visible,.documents-breadcrumb a:focus-visible { outline:3px solid var(--institutional-gold); outline-offset:4px; }
:global(.dark .documents-section) { background:var(--institutional-dark-background); }
:global(.dark .documents-section .document-card) { background:var(--institutional-dark-surface); border-color:#ffffff18; }
:global(.dark .documents-section .document-card h3),:global(.dark .documents-section .library-heading h2) { color:var(--institutional-dark-text); }
:global(.dark .documents-section .document-card p),:global(.dark .documents-section .library-heading p) { color:#bbc8dd; }
:global(.dark .documents-section .document-card > div:first-child),:global(.dark .documents-section .library-badge) { background:#ffffff08; border-color:#ffffff18; color:#bbc8dd; }
:global(.dark .documents-section .document-card-footer) { border-color:#ffffff18; }
:global(.dark .documents-section .document-card-footer > span:first-child) { color:#b8d8ff; }
@media(max-width:767px) { .documents-grid { grid-template-columns:1fr; } .documents-container { padding:0 16px; } .documents-hero-icon { display:none; } .documents-hero-content { padding:26px 0; } .documents-formats { gap:18px; flex-wrap:wrap; } .documents-formats span { font-size:12px; } .documents-section { padding:26px 0 40px; } .document-card { padding:22px; gap:14px; } .library-heading { align-items:flex-start; } .library-badge { display:none; } .document-card h3 { font-size:16px; } }
@media(prefers-reduced-motion:reduce) { .document-card { transition:none; } .document-card:hover { transform:none; } }
</style>

<style scoped>
.uploaded-document .uploaded-body { width:auto; height:auto; border:0; background:transparent; border-radius:0; align-items:stretch; justify-content:initial; flex:1; }
.uploaded-document .uploaded-body h3 { font-size:15px; line-height:1.4; }
.uploaded-document .uploaded-body > p { margin:6px 0 12px; font-size:12px; line-height:1.6; }
.uploaded-document .document-card-footer { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; }
</style>
