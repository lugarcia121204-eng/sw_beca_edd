<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { ref, computed, watch } from 'vue';
import { mdiBookOpenPageVariant, mdiFileDocumentMultiple, mdiStar, mdiSchool } from '@mdi/js';
import { alertaPregunta, alertaExito, alertaError } from '@/utils/alerts.js';
import { useCan } from '@/composables/usePermissions';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.js';
import pdfWorkerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.js?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const previewDate = computed(() => {
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = today.toLocaleDateString('es-MX', options);
    return `CIUDAD DE MÉXICO, A ${dateStr.toUpperCase()}`;
});

const props = defineProps({
    templates: Object,
    filters: Object,
});

const formatMarkdown = (text) => {
    if (!text) return '';
    return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
};

const showModal = ref(false);

const activeTab = ref(props.filters?.type || 'recognition');
const expandedRows = ref({});
const search = ref(props.filters?.search || '');
const rows = ref(props.filters?.rows || 10);

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

import Pagination from '@/Shared/Pagination.vue';
import { debounce } from 'lodash';

const resetFilters = () => {
    router.get(route('catalog.templates.index'), {}, { preserveState: false });
};

const onSearch = debounce((value) => {
    router.get(route('catalog.templates.index'), {
        search: value,
        rows: rows.value,
        type: activeTab.value
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 500);

const onRowsChange = () => {
    router.get(route('catalog.templates.index'), {
        search: search.value,
        rows: rows.value,
        type: activeTab.value
    }, { preserveState: true, replace: true, preserveScroll: true });
};

watch(search, (value) => {
    onSearch(value);
});

watch(activeTab, (value) => {
    router.get(route('catalog.templates.index'), {
        search: search.value,
        rows: rows.value,
        type: value
    }, { preserveState: true, replace: true, preserveScroll: true });
});

const toggleActive = (template) => {
  router.post(route('catalog.templates.toggle-active', template.id), {}, {
      preserveScroll: true,
      preserveState: true,
            onSuccess: () => {}
  });
};

const deleteTemplate = async (template) => {
    const confirmed = await alertaPregunta(
        '¿Estás seguro?',
        'Esta acción eliminará la plantilla permanentemente.'
    );

    if (confirmed) {
        router.delete(route('catalog.templates.destroy', template.id), {
            onSuccess: () => {
                alertaExito('¡Eliminado!', 'La plantilla ha sido eliminada.');
            }
        });
    }
};

const editingTemplateId = ref(null);
const editForm = useForm({
    director_name: '',
    director_title: '',
    body_text: ''
});

const toggleEdit = (template) => {
    if (editingTemplateId.value === template.id) {
        editingTemplateId.value = null;
        expandedRows.value[template.id] = false;
    } else {
        editingTemplateId.value = template.id;
        expandedRows.value[template.id] = true;
        // Cargar data existente
        const content = template.content_data || {};
        editForm.director_name = content.director_name || 'RAMÓN JIMÉNEZ LÓPEZ';
        editForm.director_title = content.director_title || 'DIRECTOR GENERAL';
        
        if (template.type === 'recognition') {
            editForm.body_text = content.body_text || 'Por su destacada participación como miembro de la Comisión de Evaluación\nLocal y Nacional al Programa de Estímulo al Desempeño del Personal\nDocente para los Institutos Federales y Centros';
        } else {
            editForm.body_text = content.body_text || 'Con fundamento en las atribuciones que me confiere el numeral 6...\nSe les informa que su solicitud fue dictaminada con un resultado...';
        }

        // Cargar el PDF real a nuestro canvas virtual
        renderPdf(template);
    }
};

const pdfScale = ref(1.1); // Escala lógica UI
const canvasRefs = ref({});
const renderPdf = async (template) => {
    try {
        const url = route('catalog.templates.stream', template.id);
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        
        // Obtener la primer página
        const page = await pdf.getPage(1);
        
        const scale = pdfScale.value;
        const viewport = page.getViewport({ scale: scale });

        // Encontrar el canvas de esta fila (Vue re-render delay trick)
        setTimeout(() => {
            const canvas = canvasRefs.value[template.id];
            if (!canvas) return;

            const context = canvas.getContext('2d');
            
            // Resolución HD para evitar canvas borroso:
            const outputScale = window.devicePixelRatio || 2;
            
            canvas.width = Math.floor(viewport.width * outputScale);
            canvas.height = Math.floor(viewport.height * outputScale);
            canvas.style.width = Math.floor(viewport.width) + "px";
            canvas.style.height = Math.floor(viewport.height) + "px";

            const transform = outputScale !== 1 
              ? [outputScale, 0, 0, outputScale, 0, 0] 
              : null;

            const renderContext = {
                canvasContext: context,
                transform: transform,
                viewport: viewport
            };
            page.render(renderContext);
        }, 100);
    } catch (error) {
        console.error('Error rendering PDF:', error);
    }
};

const saveTemplateContent = (templateId) => {
    editForm.put(route('catalog.templates.update-content', templateId), {
        preserveScroll: true,
        onSuccess: () => {
            alertaExito('¡Guardado!', 'Textos oficiales actualizados correctamente.');
            togglePreview(templateId); // Cerrar visualización automáticamente
        }
    });
};

const togglePreview = (id) => {
    if (expandedRows.value[id]) {
        expandedRows.value[id] = false;
        editingTemplateId.value = null;
    } else {
        expandedRows.value[id] = true;
        editingTemplateId.value = null;
    }
};

const switchTab = (tab) => {
    activeTab.value = tab;
    expandedRows.value = {};
    editingTemplateId.value = null;
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Plantillas de Documentos" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Plantillas de Documentos</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBookOpenPageVariant"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Catálogo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Plantillas</span>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-2 w-full sm:w-auto">
                    <Link 
                        v-if="useCan('templates.create')"
                        :href="route('catalog.templates.create', { type: activeTab })"
                        class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                        </svg>
                        Agregar
                    </Link>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="flex border-b border-gray-200">
                    <button
                        type="button"
                        @click="switchTab('recognition')"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold transition cursor-pointer"
                        :class="activeTab === 'recognition' ? 'text-[var(--institutional-primary)] border-b-2 border-[var(--institutional-primary)] bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor">
                            <path :d="mdiStar"/>
                        </svg>
                        Reconocimientos
                    </button>
                    <button
                        type="button"
                        @click="switchTab('acceptance')"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold transition cursor-pointer border-l border-gray-200"
                        :class="activeTab === 'acceptance' ? 'text-[var(--institutional-primary)] border-b-2 border-[var(--institutional-primary)] bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor">
                            <path :d="mdiSchool"/>
                        </svg>
                        Carta de Aceptación
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <button @click="resetFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                            <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
                <div class="text-sm text-gray-500 mb-4">Buscar plantillas por nombre o archivo</div>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Buscar..." class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" />
                    </div>
                    <div class="w-full md:w-52 flex-shrink-0">
                        <VueSelect
                            v-model="rows"
                            :options="rowOptions"
                            :reduce="option => option.value"
                            :searchable="false"
                            :clearable="false"
                            placeholder="Registros"
                            class="vue-select-custom"
                            @option:selected="onRowsChange"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider w-16 text-center">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">Archivo</th>
                                <th scope="col" class="px-6 py-4 tracking-wider text-center">Activa</th>
                                <th scope="col" class="px-6 py-4 tracking-wider text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                             <tr v-if="templates.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">
                                    No se encontraron registros
                                </td>
                            </tr>
                            <template v-for="(template, index) in templates.data" :key="template.id">
                                <tr 
                                    class="hover:bg-gray-50 transition"
                                    :class="{'bg-blue-50': expandedRows[template.id]}"
                                >
                                    <td class="px-6 py-4 text-center text-gray-500 font-bold">
                                        {{ (templates.current_page - 1) * templates.per_page + index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        {{ template.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                            </svg>
                                            <span class="text-gray-600 truncate max-w-[200px]" :title="template.file_name">
                                                {{ template.file_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                :checked="template.is_active" 
                                                @change="toggleActive(template)" 
                                                class="sr-only peer" 
                                                :disabled="!useCan('templates.edit')"
                                            />
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--institutional-primary)]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--institutional-primary)]"></div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <!-- Botón Visualizar y Editar Unificado -->
                                            <button 
                                                @click="useCan('templates.edit') ? toggleEdit(template) : togglePreview(template.id)"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border rounded-lg font-bold transition cursor-pointer text-sm whitespace-nowrap"
                                                :class="expandedRows[template.id] ? 'bg-[var(--institutional-primary)] text-white border-[var(--institutional-primary)]' : 'text-[var(--institutional-primary)] border-[var(--institutional-primary)] hover:bg-[var(--institutional-primary)] hover:text-white'"
                                                title="Configurar y Ver Plantilla"
                                            >
                                                <svg v-if="!(expandedRows[template.id])" xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                                </svg>
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>
                                                </svg>
                                                {{ expandedRows[template.id] ? 'Ocultar' : 'Visualizar y Editar' }}
                                            </button>

                                            <button 
                                                v-if="useCan('templates.destroy')"
                                                @click="deleteTemplate(template)"
                                                class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition group cursor-pointer"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Inline Preview Row -->
                                <tr v-if="expandedRows[template.id]">
                                    <td colspan="5" class="px-6 py-6 border-b border-gray-200" :class="editingTemplateId === template.id ? 'bg-white' : 'bg-gray-50'">
                                        <div class="flex flex-col gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                                            <div class="flex justify-between items-center">
                                                <h3 class="font-bold text-[var(--institutional-primary)] text-lg">
                                                    {{ editingTemplateId === template.id ? 'Configurar Textos Oficiales de: ' : 'Vista Previa de Planta: ' }} "{{ template.name }}"
                                                </h3>
                                                <button @click="togglePreview(template.id)" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            
                                            <div class="flex flex-col gap-6">
                                                
                                                <!-- Action Bar -->
                                                <div v-if="editingTemplateId === template.id" class="flex justify-between items-center bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm">
                                                    <div class="flex items-center gap-3">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1B396A">
                                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                                        </svg>
                                                            <div class="flex items-center gap-2 mb-4 mt-6">
                                                                <h4 class="font-bold text-[var(--institutional-primary)]">Modo Edición Visual</h4>
                                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded border border-blue-200">Haz clic sobre los recuadros para editar.</span>
                                                            </div>
                                                    </div>
                                                    <button 
                                                        @click="saveTemplateContent(template.id)" 
                                                        :disabled="editForm.processing"
                                                        class="flex justify-center py-2.5 px-6 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[var(--institutional-primary)] hover:bg-[#0f2347] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] disabled:opacity-50 transition items-center gap-2 cursor-pointer h-fit"
                                                    >
                                                        <svg v-if="editForm.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <svg v-else xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                            <path d="M840-680v480q0 33-23.5 56.5T760-120H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h480l160 160Zm-80 34L646-760H200v560h560v-446ZM480-240q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35ZM240-560h360v-160H240v160Zm-40-86v446-560 114Z"/>
                                                        </svg>
                                                        Guardar Cambios
                                                    </button>
                                                </div>

                                                <!-- PDF Viewer Envelope -->
                                                <div class="w-full flex justify-center bg-gray-100 rounded-xl p-8 border border-gray-200">
                                                    
                                                    <!-- Canvas Container with Absolute Edit Overlays -->
                                                    <div class="relative shadow-2xl transition-all inline-block bg-white" style="line-height: 0;">
                                                        <!-- The background PDF rendered via pdf.js -->
                                                        <canvas :ref="el => { if (el) canvasRefs[template.id] = el }" class="block mx-auto"></canvas>
                                                        
                                                        <!-- Absolute Floating Inputs (WYSIWYG) -->
                                                        <div v-if="editingTemplateId === template.id" class="absolute inset-0 z-10 w-full h-full pointer-events-none">
                                                            
                                                            <!-- ============================================== -->
                                                            <!-- LOGIC FOR RECOGNITIONS (Horizontal Layout)     -->
                                                            <!-- ============================================== -->
                                                            <template v-if="template.type === 'recognition'">
                                                                <!-- Evaluator / Teacher Name Mockup -->
                                                                <div 
                                                                    class="absolute text-[#505050] font-bold text-center font-sans tracking-wide pointer-events-none"
                                                                    style="top: 45%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.5 * 14px);"
                                                                >
                                                                    NOMBRE DEL EVALUADOR O DOCENTE
                                                                </div>

                                                                <!-- Body Text (centered block, approx middle) -->
                                                                <textarea 
                                                                    v-model="editForm.body_text"
                                                                    class="absolute bg-transparent text-[#505050] text-center font-sans tracking-wide leading-tight editable-overlay pointer-events-auto resize-none outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all overflow-hidden"
                                                                    style="top: 51%; left: 10%; width: 80%; height: 18%; font-size: calc(1.1 * 12px); padding: 5px;"
                                                                ></textarea>

                                                                <!-- Director Name (centered bottom) -->
                                                                <input 
                                                                    v-model="editForm.director_name"
                                                                    type="text"
                                                                    class="absolute bg-transparent text-[#505050] font-bold text-center font-sans tracking-wide editable-overlay pointer-events-auto outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all"
                                                                    style="top: 74.7%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 12px); padding: 0;"
                                                                />

                                                                <!-- Director Title (centered tiny below name) -->
                                                                <input 
                                                                    v-model="editForm.director_title"
                                                                    type="text"
                                                                    class="absolute bg-transparent text-[#646464] text-center font-sans tracking-wide editable-overlay pointer-events-auto outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all"
                                                                    style="top: 77%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 12px); padding: 0;"
                                                                />

                                                                <!-- Date Mockup (Bottom Center) -->
                                                                <div 
                                                                    class="absolute text-[#C29B22] font-bold text-center font-sans tracking-wide pointer-events-none"
                                                                    style="top: 82%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    {{ previewDate }}
                                                                </div>
                                                            </template>

                                                            <!-- ============================================== -->
                                                            <!-- LOGIC FOR ACCEPTANCE LETTERS (Vertical Layout) -->
                                                            <!-- ============================================== -->
                                                            <template v-if="template.type === 'acceptance'">
                                                                <!-- Date (Top Right) -->
                                                                <div 
                                                                    class="absolute text-[#000000] text-right font-sans tracking-wide pointer-events-none"
                                                                    style="top: 20.5%; right: 7%; width: 50%; height: 3.5%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    {{ previewDate }}
                                                                </div>


                                                                <!-- Participant Name -->
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide pointer-events-none"
                                                                    style="top: 32%; left: 11.5%; width: 80%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    NOMBRE DEL DOCENTE
                                                                </div>

                                                                <!-- Participant Institution -->
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide pointer-events-none"
                                                                    style="top: 33.5%; left: 22%; width: 50%; font-size: calc(1.1 * 9px);"
                                                                >
                                                                    XXXXXXXXXXXXXXX
                                                                </div>

                                                                <!-- Body Text as Textarea -->
                                                                <textarea 
                                                                    v-model="editForm.body_text"
                                                                    class="absolute bg-transparent text-[#000000] text-justify font-sans editable-overlay pointer-events-auto outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all resize-none leading-relaxed"
                                                                    style="top: 36%; left: 10.5%; width: 83%; height: 27.5%; font-size: calc(1.1 * 9px); padding: 5px; overflow-y: auto;"
                                                                ></textarea>

                                                                <!-- Director Name (Bottom) -->
                                                                <input 
                                                                    v-model="editForm.director_name"
                                                                    type="text"
                                                                    class="absolute bg-transparent text-[#000000] font-bold text-left font-sans tracking-wide editable-overlay pointer-events-auto outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all"
                                                                    style="top: 73.1%; left: 11.5%; width: 50%; font-size: calc(1.1 * 9px); padding: 0;"
                                                                />

                                                                <!-- Director Title (Bottom) -->
                                                                <input 
                                                                    v-model="editForm.director_title"
                                                                    type="text"
                                                                    class="absolute bg-transparent text-[#000000] font-bold text-left font-sans tracking-wide editable-overlay pointer-events-auto outline-none border-2 border-dashed border-transparent hover:border-blue-400 focus:border-blue-500 focus:bg-blue-50/50 transition-all"
                                                                    style="top: 74.6%; left: 11.5%; width: 50%; font-size: calc(1.1 * 9px); padding: 0;"
                                                                />
                                                            </template>
                                                        </div>
                                                        <!-- Leyenda Modo Solo Visualizar -->
                                                        <div v-else class="absolute inset-0 z-10 w-full h-full pointer-events-none">
                                                            <template v-if="template.type === 'recognition'">
                                                                <div 
                                                                    class="absolute text-[#505050] font-bold text-center font-sans tracking-wide pointer-events-none opacity-70"
                                                                    style="top: 45%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.5 * 14px);"
                                                                >
                                                                    NOMBRE DEL EVALUADOR O DOCENTE
                                                                </div>
                                                                <div class="absolute text-[#505050] text-center font-sans tracking-wide leading-tight whitespace-pre-wrap flex items-center justify-center opacity-70"
                                                                    style="top: 51%; left: 10%; width: 80%; height: 18%; font-size: calc(1.1 * 12px);">{{ template.content_data?.body_text || 'Motivo de participación (Previsualización)' }}</div>
                                                                <div class="absolute text-[#505050] font-bold text-center font-sans tracking-wide opacity-70"
                                                                    style="top: 75.3%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 12px); line-height: 1;">{{ template.content_data?.director_name || 'NOMBRE DIRECTOR' }}</div>
                                                                <div class="absolute text-[#646464] text-center font-sans tracking-wide opacity-70"
                                                                    style="top: 77.8%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 10px); line-height: 1;">{{ template.content_data?.director_title || 'CARGO DIRECTOR' }}</div>
                                                                <div 
                                                                    class="absolute text-[#C29B22] font-bold text-center font-sans tracking-wide pointer-events-none opacity-70"
                                                                    style="top: 82%; left: 10%; width: 80%; height: 3.5%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    {{ previewDate }}
                                                                </div>
                                                            </template>
                                                            <template v-else-if="template.type === 'acceptance'">
                                                                <div 
                                                                    class="absolute text-[#000000] text-right font-sans tracking-wide pointer-events-none"
                                                                    style="top: 22.5%; right: 9%; width: 50%; height: 3.5%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    {{ previewDate }}
                                                                </div>
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide pointer-events-none"
                                                                    style="top: 31%; left: 9%; width: 80%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    NOMBRE DEL EVALUADOR O DOCENTE
                                                                </div>
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide pointer-events-none"
                                                                    style="top: 33.2%; left: 24%; width: 50%; font-size: calc(1.1 * 10px);"
                                                                >
                                                                    CENTRO NACIONAL DE INVESTIGACIÓN Y DESARROLLO...
                                                                </div>
                                                                <div 
                                                                    class="absolute text-[#000000] text-justify font-sans tracking-wide leading-relaxed whitespace-pre-wrap flex"
                                                                    style="top: 40%; left: 9%; width: 82%; height: 38%; font-size: calc(1.1 * 10px);"
                                                                    v-html="formatMarkdown(template.content_data?.body_text || 'Cuerpo de carta de aceptación...')"
                                                                ></div>
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide"
                                                                    style="top: 83.5%; left: 9%; width: 50%; font-size: calc(1.1 * 10px); line-height: 1;"
                                                                >{{ template.content_data?.director_name || 'XXXXXXXXXXXXXXXXX' }}</div>
                                                                <div 
                                                                    class="absolute text-[#000000] font-bold text-left font-sans tracking-wide"
                                                                    style="top: 85.5%; left: 9%; width: 50%; font-size: calc(1.1 * 10px); line-height: 1;"
                                                                >{{ template.content_data?.director_title || 'XXXXXXXXXXXXXXXXX' }}</div>
                                                            </template>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                            
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" v-if="templates.links">
                    <Pagination 
                        :links="templates.links" 
                        :total="templates.total" 
                        :from="templates.from" 
                        :to="templates.to" 
                    />
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0 4px;
    height: 45px;
    display: flex;
    align-items: center;
}

:deep(.vue-select-custom .vs__selected-options) {
    flex-wrap: nowrap !important;
    min-width: 0;
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 4px;
}

:deep(.vue-select-custom .vs__selected) {
    color: #374151;
    font-weight: 500;
}

:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}

:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.75rem 1rem;
    color: #374151;
}

:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

:deep(.vue-select-custom .vs__actions) {
    padding-right: 4px;
    display: flex;
    align-items: center;
}

:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transform: scale(0.8);
}
</style>

