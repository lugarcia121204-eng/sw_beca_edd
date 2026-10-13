<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { ref, computed } from 'vue';
import { Award, Search, UserRound, Hash, Info, LoaderCircle, Eye, Download } from 'lucide-vue-next';

const props = defineProps({
    recognitions: {
        type: Array,
        default: null,
    },
    searched: {
        type: Boolean,
        default: false,
    },
    filters: {
        type: Object,
        default: () => ({ curp: '', name: '', folio: '' }),
    },
});

const curp  = ref(props.filters.curp  || '');
const name  = ref(props.filters.name  || '');
const folio = ref(props.filters.folio || '');
const loading = ref(false);
const expandedRec = ref(null);

const toggleViewer = (rec) => {
    expandedRec.value = expandedRec.value?.id === rec.id ? null : rec;
};

const hasCriteria = computed(() => Boolean(curp.value.trim() || name.value.trim() || folio.value.trim()));

const search = () => {
    if (loading.value || !hasCriteria.value) return;
    expandedRec.value = null;
    loading.value = true;
    router.get(
        route('recognitions.search'),
        { curp: curp.value.trim(), name: name.value.trim(), folio: folio.value.trim() },
        {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; },
        }
    );
};

const clearFilters = () => {
    expandedRec.value = null;
    curp.value  = '';
    name.value  = '';
    folio.value = '';
    router.get(route('recognitions.search'), {}, { preserveState: true, replace: true });
};

const typeLabel = (type) => {
    if (!type) return 'Reconocimiento';
    if (type === 'evaluator') return 'Reconocimiento de Evaluador';
    if (type === 'teacher')   return 'Carta de Aceptación';
    return type;
};

const typeColor = (type) => {
    if (type === 'evaluator') return 'bg-[var(--institutional-primary)]';
    if (type === 'teacher')   return 'bg-[#10A558]';
    return 'bg-gray-500';
};

const typeIcon = (type) => {
    // Returns either 'star' for evaluator or 'file-check' for teacher
    return type === 'teacher' ? 'file' : 'star';
};
</script>

<template>
    <LandingLayout>
        <Head title="Buscador de Reconocimientos" />

        <section class="recognition-hero"><div class="recognition-container">
            <nav class="recognition-breadcrumb" aria-label="Ruta de navegación"><Link :href="route('inicio')">Inicio</Link><span aria-hidden="true">/</span><span>Reconocimientos</span></nav>
            <div class="recognition-hero-content"><div><span class="recognition-eyebrow">CONSULTA DE DOCUMENTOS</span><h1>Buscador de Reconocimientos</h1><p>Encuentra fácilmente los reconocimientos que has obtenido a través de tu participación en convocatorias organizadas por el TecNM.</p></div><span class="recognition-hero-icon" aria-hidden="true"><Award :size="44" :stroke-width="1.4" /></span></div>
        </div></section>
        <!-- Search Section -->
        <section class="recognition-content">
            <div class="recognition-container">

                <form class="recognition-search-card" @submit.prevent="search" :aria-busy="loading">
                    <div class="search-card-heading"><span class="search-heading-icon"><Search :size="25" /></span><div><span class="search-card-eyebrow">LOCALIZA TU DOCUMENTO</span><h2>Criterios de Búsqueda</h2><p>Ingresa al menos un criterio para consultar tus documentos.</p></div></div>
                    <div class="recognition-fields">
                        <div class="recognition-field"><label for="recognition-curp">CURP</label><div class="recognition-input"><UserRound :size="19" /><input id="recognition-curp" v-model="curp" type="text" maxlength="18" placeholder="Ej. ABCD010101HXXXXX01" @input="curp = curp.toUpperCase()" spellcheck="false" aria-describedby="curp-help" /></div><p id="curp-help" class="field-help">Ingresa los 18 caracteres de tu CURP.</p></div>
                        <div class="recognition-field"><label for="recognition-name">Nombre Completo</label><div class="recognition-input"><UserRound :size="19" /><input id="recognition-name" v-model="name" type="text" placeholder="Ej. JUAN PÉREZ GARCÍA" @input="name = name.toUpperCase()" aria-describedby="name-help" /></div><p id="name-help" class="field-help">Busca con el nombre del participante.</p></div>
                        <div class="recognition-field"><label for="recognition-folio">Folio / Identificador</label><div class="recognition-input"><Hash :size="19" /><input id="recognition-folio" v-model="folio" type="text" placeholder="Ej. REC-2025-EV-12-ABCD" @input="folio = folio.toUpperCase()" spellcheck="false" aria-describedby="folio-help" /></div><p id="folio-help" class="field-help">Utiliza el folio de tu reconocimiento.</p></div>
                    </div>
                    <div class="recognition-search-actions"><span class="search-hint"><Info :size="15" /> Puedes combinar los criterios de búsqueda.</span><div><button v-if="searched || hasCriteria" type="button" @click="clearFilters" :disabled="loading" class="clear-search">Limpiar</button><button type="submit" :disabled="loading || !hasCriteria" class="submit-search"><LoaderCircle v-if="loading" :size="18" class="animate-spin" /><Search v-else :size="18" />{{ loading ? 'Buscando...' : 'Buscar Reconocimientos' }}</button></div></div>
                </form>
                <div v-if="!searched" class="recognition-initial"><span class="initial-icon"><Award :size="30" :stroke-width="1.5" /></span><h2>Consulta tus reconocimientos</h2><p>Busca por CURP, nombre completo o folio para encontrar los documentos de tu participación en el programa.</p><div class="initial-steps"><span><Search :size="15" /> Busca tu reconocimiento</span><span><Eye :size="15" /> Consulta el documento</span><span><Download :size="15" /> Descarga tu archivo</span></div></div>

                <!-- Results -->
                <div v-if="searched" class="mt-8">

                    <!-- No results -->
                    <div v-if="!recognitions || recognitions.length === 0" class="text-center py-16 bg-white rounded-2xl border border-gray-200 shadow-sm">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-2xl mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-8 h-8 fill-gray-400">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Sin resultados</h3>
                        <p class="text-gray-500 text-sm max-w-sm mx-auto">
                            No encontramos reconocimientos activos con los criterios ingresados.
                            Verifica los datos e intenta de nuevo.
                        </p>
                    </div>

                    <!-- Results grid -->
                    <div v-else>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">
                                Resultados encontrados
                                <span class="ml-2 text-sm font-normal text-gray-500">({{ recognitions.length }} reconocimiento{{ recognitions.length !== 1 ? 's' : '' }})</span>
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div
                                v-for="rec in recognitions"
                                :key="rec.id"
                                class="recognition-result bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow group"
                                :class="{'ring-2 ring-offset-2 ring-[var(--institutional-primary)]': expandedRec?.id === rec.id}"
                            >
                                <!-- Card Top Accent -->
                                <div :class="typeColor(rec.template_type)" class="h-1.5 w-full"></div>

                                <div class="p-5">
                                    <!-- Header row -->
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                                        <div>
                                            <h4 class="text-base font-bold text-gray-800 leading-snug">
                                                {{ rec.template_name }}
                                            </h4>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ rec.date || rec.year }}</p>
                                        </div>
                                        <!-- Folio badge -->
                                        <div class="recognition-folio flex-shrink-0 text-right">
                                            <span class="text-xs text-gray-400 block">Folio</span>
                                            <span class="text-xs font-mono font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-lg">
                                                {{ rec.folio }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Participant & Announcement -->
                                    <div class="space-y-1 mb-4">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-4 h-4 fill-gray-400 flex-shrink-0">
                                                <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/>
                                            </svg>
                                            <span class="font-medium recognition-detail">{{ rec.participant }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-4 h-4 fill-gray-400 flex-shrink-0">
                                                <path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280L160-640v400h640v-400L480-440Zm0-80 320-200H160l320 200ZM160-640v-80 480-400Z"/>
                                            </svg>
                                            <span class="recognition-detail">{{ rec.announcement }}</span>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-100 pt-4 flex gap-2">
                                        <button
                                            @click="toggleViewer(rec)"
                                            class="flex-1 flex items-center justify-center gap-2 h-10 rounded-xl text-sm font-semibold transition shadow-sm cursor-pointer"
                                            :class="expandedRec?.id === rec.id ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-[var(--institutional-primary)] text-white hover:bg-[var(--institutional-primary-dark)]'"
                                        >
                                            <svg v-if="expandedRec?.id !== rec.id" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-5 h-5 fill-white">
                                                <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-5 h-5 fill-gray-600">
                                                <path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t36.5-4q75 0 127.5 52.5T660-500q0 19-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>
                                            </svg>
                                            {{ expandedRec?.id === rec.id ? 'Ocultar' : 'Ver documento' }}
                                        </button>
                                        <a
                                            :href="route('recognitions.public-download', { identifier: rec.folio })"
                                            target="_blank"
                                            class="flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition flex-shrink-0"
                                            title="Descargar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-5 h-5 fill-gray-500">
                                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inline PDF Viewer -->
                        <div v-if="expandedRec" class="recognition-viewer mt-5 bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden animate-fadeIn">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 sm:px-5 py-3 bg-gray-50 border-b border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div :class="typeColor(expandedRec.template_type)" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg v-if="typeIcon(expandedRec.template_type) === 'star'" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-4 h-4 fill-white">
                                            <path d="m354-247 126-76 126 77-33-144 111-96-146-13-58-136-58 135-146 13 111 97-33 143ZM233-80l65-281L80-550l288-25 112-265 112 265 288 25-218 189 65 281-247-149L233-80Zm247-350Z"/>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-4 h-4 fill-white">
                                            <path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ expandedRec.template_name }}</p>
                                        <p class="text-xs text-gray-500">{{ expandedRec.participant }}</p>
                                    </div>
                                </div>
                                <button @click="expandedRec = null" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg transition" title="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-5 h-5 fill-current">
                                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] relative bg-gray-100">
                                <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                    Cargando vista previa...
                                </div>
                                <iframe
                                    :src="route('recognitions.public-download', { identifier: expandedRec.folio })"
                                    class="w-full h-full relative z-10"
                                    frameborder="0" :title="expandedRec.template_name"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </LandingLayout>
</template>

<style scoped>
.recognition-container { max-width:1280px; margin:auto; padding:0 24px; }
.recognition-hero { color:white; background:linear-gradient(115deg,var(--institutional-primary),var(--institutional-primary-dark)); }
.recognition-breadcrumb { display:flex; gap:12px; align-items:center; padding-top:26px; font-size:12px; color:#c8d6e9; }
.recognition-breadcrumb a:hover { color:white; }
.recognition-hero-content { display:flex; align-items:center; justify-content:space-between; gap:32px; padding:32px 0 40px; }
.recognition-eyebrow { font-size:11px; font-weight:700; letter-spacing:.13em; color:var(--institutional-gold); }
.recognition-hero h1 { font-size:clamp(28px,3.4vw,40px); font-weight:750; line-height:1.2; letter-spacing:-.025em; margin:12px 0; }
.recognition-hero p { max-width:760px; font-size:15px; line-height:1.7; color:#d8e5f6; }
.recognition-hero-icon { padding:22px; border:1px solid #ffffff25; background:#ffffff08; border-radius:24px; color:#d5e3f6; }
.recognition-content { --recognition-surface:white; --recognition-text:#142c4d; --recognition-muted:#56677d; --recognition-border:#e2e8f0; --recognition-input:#fafbfd; background:#f5f7fb; padding:36px 0 64px; color:var(--recognition-text); }
.recognition-search-card { background:var(--recognition-surface); border:1px solid var(--recognition-border); border-radius:22px; padding:0; overflow:hidden; box-shadow:0 12px 36px #1023440a; }
.search-card-heading { display:flex; align-items:center; gap:18px; padding:26px 30px; background:linear-gradient(110deg,var(--institutional-primary),var(--institutional-primary-dark)); border-top:3px solid var(--institutional-gold); color:white; }
.search-heading-icon { display:flex; align-items:center; justify-content:center; width:56px; height:56px; border:1px solid #ffffff30; border-radius:16px; background:#ffffff12; color:white; flex-shrink:0; }
.search-card-eyebrow { display:block; font-size:10px; font-weight:700; letter-spacing:.14em; color:var(--institutional-gold); margin-bottom:5px; }
.search-card-heading h2 { font-size:23px; font-weight:700; letter-spacing:-.02em; margin-bottom:5px; }
.search-card-heading p { font-size:13px; color:#d8e5f6; line-height:1.6; }
.recognition-fields { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; padding:30px; }
.recognition-field { background:var(--recognition-input); border:1px solid var(--recognition-border); border-radius:15px; padding:20px; transition:border-color .2s,box-shadow .2s; min-width:0; }
.recognition-field:focus-within { border-color:var(--institutional-primary-light); box-shadow:0 5px 16px #1b396a0a; }
.recognition-field label { display:block; font-size:14px; font-weight:700; margin-bottom:13px; }
.recognition-input { display:flex; align-items:center; gap:10px; border:1px solid var(--recognition-border); border-radius:10px; padding:15px 12px; background:var(--recognition-surface); }
.recognition-input > svg { flex-shrink:0; color:#6884a9; }
.recognition-input input { width:100%; min-width:0; outline:none; border:0; font-size:13px; color:var(--recognition-text); background:transparent; }
.field-help { font-size:11px; line-height:1.7; color:var(--recognition-muted); margin-top:11px; }
.recognition-input input::placeholder { color:#8390a2; }
.recognition-input:focus-within { border-color:var(--institutional-primary-light); box-shadow:0 0 0 3px #1b396a12; }
.recognition-search-actions { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; margin-top:0; border-top:1px solid var(--recognition-border); padding:22px 30px; background:var(--recognition-input); }
.search-hint { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--recognition-muted); }
.search-hint svg { flex-shrink:0; }
.recognition-search-actions > div { display:flex; gap:12px; }
.submit-search,.clear-search { border-radius:10px; padding:14px 20px; display:inline-flex; align-items:center; justify-content:center; gap:9px; font-size:14px; font-weight:650; cursor:pointer; }
.submit-search { background:var(--institutional-primary); color:white; }
.submit-search:hover { background:var(--institutional-primary-dark); }
.clear-search { border:1px solid var(--recognition-border); color:var(--recognition-muted); }
.submit-search:disabled,.clear-search:disabled { opacity:.5; cursor:not-allowed; }
.recognition-initial { text-align:center; padding:48px 24px 10px; }
.initial-icon { display:inline-flex; align-items:center; justify-content:center; width:68px; height:68px; border:1px solid var(--recognition-border); border-radius:20px; background:var(--recognition-surface); color:#657f9f; margin-bottom:20px; }
.recognition-initial h2 { font-size:22px; font-weight:700; letter-spacing:-.02em; margin-bottom:10px; }
.recognition-initial p { max-width:540px; margin:auto; color:var(--recognition-muted); font-size:14px; line-height:1.8; }
.initial-steps { display:flex; justify-content:center; gap:26px; flex-wrap:wrap; margin-top:26px; }
.initial-steps span { display:flex; align-items:center; gap:8px; color:var(--recognition-muted); font-size:12px; }
.recognition-result { box-shadow:0 4px 18px #10234404; }
.recognition-detail { white-space:normal; overflow-wrap:anywhere; }
.recognition-folio { max-width:100%; }
.recognition-folio span { overflow-wrap:anywhere; }
.recognition-result .p-5 { padding:24px; }
.recognition-result .flex.items-center.gap-2 { align-items:flex-start; }
.recognition-viewer { box-shadow:0 6px 24px #10234408; }
.recognition-content button:focus-visible,.recognition-content a:focus-visible { outline:2px solid var(--institutional-gold); outline-offset:4px; }
:global(.dark .recognition-content) { --recognition-surface:var(--institutional-dark-surface); --recognition-text:var(--institutional-dark-text); --recognition-muted:#bbc8dd; --recognition-border:#ffffff18; --recognition-input:#1a1a2e; background:var(--institutional-dark-background); }
:global(.dark .recognition-content .search-heading-icon) { color:#b8d8ff; background:#ffffff08; }
@media(max-width:900px) { .recognition-fields { grid-template-columns:1fr; gap:18px; } }
@media(max-width:639px) { .recognition-container { padding:0 16px; } .recognition-hero-icon { display:none; } .recognition-hero-content { padding:26px 0 30px; } .recognition-content { padding:24px 0 40px; } .recognition-search-card { padding:22px; } .search-card-heading { align-items:flex-start; } .recognition-search-actions > div { width:100%; } .submit-search { flex:1; } .initial-steps { flex-direction:column; align-items:center; gap:14px; } .recognition-initial { padding:36px 8px 0; } .recognition-initial h2 { font-size:20px; } .recognition-folio { text-align:left; } }
@media(prefers-reduced-motion:reduce) { .animate-fadeIn { animation:none!important; } }
@media(max-width:639px) { .recognition-search-card { padding:0; } .search-card-heading { padding:22px; gap:14px; } .search-heading-icon { width:44px; height:44px; border-radius:12px; } .search-card-heading h2 { font-size:20px; } .recognition-fields { padding:22px; gap:16px; } .recognition-field { padding:17px; } .recognition-search-actions { padding:20px 22px; } .submit-search,.clear-search { font-size:12px; padding:13px 14px; } }
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
