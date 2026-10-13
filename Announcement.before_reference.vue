<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { ref, computed } from 'vue';
import { Users, ClipboardCheck, CalendarDays, FileText, ArrowRight, ArrowUpRight, Clock3, Megaphone, FileUp, ChartColumnIncreasing, Monitor } from 'lucide-vue-next';

const props = defineProps({
    announcement: Object, // Changed from convocatoria to match web.php
});

const showModal = ref(false);
const currentPdfUrl = ref('');
const currentPdfTitle = ref('');

const openPdfModal = (url, title) => {
    if (!url) return;
    currentPdfUrl.value = url;
    currentPdfTitle.value = title;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    currentPdfUrl.value = '';
    currentPdfTitle.value = '';
};

const isPdfResource = (url) => /\.pdf($|\?)/i.test(url || '');
const isImageResource = (url) => /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(url || '');

const currentPreviewIsPdf = computed(() => isPdfResource(currentPdfUrl.value));
const currentPreviewIsImage = computed(() => isImageResource(currentPdfUrl.value));

// Acceder a los datos correctamente (handling Resource wrapper)
const announcementData = computed(() => {
    return props.announcement?.data || props.announcement;
});

// Helper para parsear fecha "YYYY-MM-DD", "YYYY-MM-DD HH:MM:SS" o ISO "YYYY-MM-DDTHH:mm:ss"
const parseDateLocal = (dateString) => {
    if (!dateString) return null;
    // Tomamos los primeros 10 caracteres (YYYY-MM-DD)
    const datePart = dateString.substring(0, 10);
    const [year, month, day] = datePart.split('-').map(Number);
    
    if (!year || !month || !day) return null;

    return new Date(year, month - 1, day);
};

const etapas = computed(() => {
    const announcement = announcementData.value;
    const stages = [
        {
            id: 1,
            titulo: 'Publicación de Convocatoria',
            dateStartKey: 'publication_start',
            color: 'var(--institutional-primary)'
        },
        {
            id: 2,
            titulo: 'Registro y Carga de Documentos',
            dateStartKey: 'registration_start',
            dateEndKey: 'registration_end',
            color: 'var(--institutional-success)'
        },
        {
            id: 3,
            titulo: 'Evaluación y Dictaminación',
            dateStartKey: 'evaluation_start',
            dateEndKey: 'evaluation_end',
            color: 'var(--institutional-gold)'
        },
        {
            id: 4,
            titulo: 'Publicación de Resultados',
            dateStartKey: 'results_start',
            dateEndKey: 'results_end',
            color: 'var(--institutional-primary-light)'
        }
    ];

    if (!announcement || !announcement.calendar) {
        return stages.map(s => ({
            ...s,
            fechas: 'Por definir'
        }));
    }

    return stages.map(stage => {
        const start = parseDateLocal(announcement.calendar[stage.dateStartKey]);
        let fechasStr = start ? start.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' }) : 'Por definir';
        
        if (stage.dateEndKey && announcement.calendar[stage.dateEndKey]) {
            const end = parseDateLocal(announcement.calendar[stage.dateEndKey]);
            if (end) {
                fechasStr += ` - ${end.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })}`;
            }
        } else if (start) {
             fechasStr += ` ${start.getFullYear()}`;
        }

        return {
            ...stage,
            fechas: fechasStr
        };
    });
});
</script>

<template>
    <LandingLayout>

        <Head title="Convocatoria" />

        <div class="announcement-page">
            <section class="announcement-hero">
                <div class="announcement-container">
                    <nav class="announcement-breadcrumb" aria-label="Ruta de navegación">
                        <Link :href="route('inicio')">Inicio</Link><span aria-hidden="true">/</span><span>Convocatoria</span>
                    </nav>
                    <div class="hero-heading">
                        <div>
                            <span class="hero-eyebrow">PROGRAMA DE ESTÍMULOS DOCENTES</span>
                            <h1><span>Información de</span> Convocatoria</h1>
                            <p>Detalles y requisitos para participar en el proceso actual para los Institutos Tecnológicos Federales y Centros</p>
                        </div>
                        <div class="hero-mark" aria-hidden="true"><FileText :size="44" :stroke-width="1.4" /></div>
                    </div>
                    <nav class="section-navigation" aria-label="Secciones de la convocatoria">
                        <a href="#participantes"><Users :size="16" /> ¿A quién va dirigido?</a>
                        <a href="#bases"><ClipboardCheck :size="16" /> Bases de Participación</a>
                        <a href="#calendario"><CalendarDays :size="16" /> Calendario de Actividades</a>
                    </nav>
                </div>
            </section>

            <section class="announcement-content announcement-container">
                <div class="details-column">
                    <article id="participantes" class="information-card">
                        <div class="card-heading"><span class="heading-icon"><Users :size="23" /></span><h2>¿A quién va dirigido?</h2></div>
                        <p class="audience-copy">Personal docente de tiempo completo (40 hrs) adscrito a los Institutos y Centros Federales del TecNM que imparten el 80% del año lectivo 2024 y cumplan con el 80% durante el primer semestre de 2025.</p>
                    </article>

                    <article id="bases" class="information-card requirements-card">
                        <div class="card-heading"><span class="heading-icon"><ClipboardCheck :size="23" /></span><h2>Bases de Participación</h2></div>
                        <ol class="requirements-list">
                            <li><span class="requirement-number">1</span><p>Haber cubierto la carga horaria reglamentaria frente a grupo durante los dos semestres de 2024 y en el primer semestre de 2026.</p></li>
                            <li><span class="requirement-number">2</span><p>Personal docente titular en subdirección, jefatura de departamento o área administrativa (50% docencia, 50% funciones directivas).</p></li>
                            <li><span class="requirement-number">3</span><p>Personal docente titular en Docencia e Investigación con mínimo 20 horas-semana en los últimos dos semestres de 2024 y primer semestre de 2026.</p></li>
                            <li><span class="requirement-number">4</span><p>Personal docente que haya disfrutado licencia sin goce de sueldo según Artículo 12 de los Lineamientos del Programa.</p></li>
                            <li><span class="requirement-number">5</span><p>Personal con nombramiento de tiempo parcial no podrá participar.</p></li>
                            <li><span class="requirement-number">6</span><p>Integrar expediente virtual completo según período establecido.</p></li>
                        </ol>
                    </article>

                    <article class="participation-card">
                        <span class="participation-icon" aria-hidden="true"><Monitor :size="30" /></span>
                        <div><h2>¿Listo para participar?</h2><p>Si cumples con los requisitos, inicia tu trámite hoy mismo.</p></div>
                        <Link :href="route('login')" class="participation-button">Iniciar Sesión <ArrowRight :size="18" /></Link>
                    </article>
                </div>

                <aside class="calendar-column" aria-label="Documento y calendario">
                    <article v-if="announcementData" class="featured-announcement">
                        <div class="pdf-art" aria-hidden="true"><div class="pdf-sheet"><FileText :size="94" :stroke-width="1.1" /></div><span class="pdf-badge">PDF</span></div>
                        <div class="featured-top"><span class="document-icon"><FileText :size="24" /></span><span class="featured-label">CONVOCATORIA</span></div>
                        <h2>{{ announcementData.name }}</h2>
                        <p class="announcement-description">{{ announcementData.description || 'Sin descripción disponible.' }}</p>
                        <button v-if="announcementData.file_url && announcementData.status !== 'pendiente'" @click="openPdfModal(route('announcement.preview', announcementData.id), announcementData.name)" class="document-button"><FileText :size="18" /> Ver Convocatoria PDF <ArrowUpRight :size="18" /></button>
                        <div v-else-if="announcementData.status === 'pendiente'" class="document-notice"><Clock3 :size="18" /> Convocatoria Próximamente</div>
                        <div v-else class="document-notice"><FileText :size="18" /> Sin PDF disponible</div>
                    </article>
                    <article v-else class="information-card empty-announcement"><FileText :size="32" /><h2>No hay convocatorias activas</h2><p>Por favor, vuelve más tarde.</p></article>

                    <article id="calendario" class="information-card calendar-card">
                        <div class="card-heading"><span class="heading-icon"><CalendarDays :size="23" /></span><h2>Calendario de Actividades</h2></div>
                        <ol class="stages-list">
                            <li v-for="etapa in etapas" :key="etapa.id" class="stage-item" :style="{ '--stage-color': etapa.color }">
                                <span class="stage-number">{{ etapa.id }}</span>
                                <div><span class="stage-label">Fase {{ etapa.id }}</span><h3>{{ etapa.titulo }}</h3><p class="stage-date"><CalendarDays :size="14" /><span>{{ etapa.fechas }}</span></p></div>
                                <span class="stage-icon" aria-hidden="true"><component :is="[Megaphone, FileUp, ClipboardCheck, ChartColumnIncreasing][etapa.id - 1]" :size="25" /></span>
                            </li>
                        </ol>
                    </article>
                </aside>
            </section>
        </div>

        <!-- PDF Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[88vh] sm:h-[90vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="flex items-start sm:items-center justify-between gap-3 p-4 sm:p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">{{ currentPdfTitle }}</h2>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-800 transition-colors cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Document Viewer -->
                        <div class="flex-1 overflow-hidden bg-gray-100">
                            <iframe
                                v-if="currentPreviewIsPdf"
                                :src="currentPdfUrl"
                                class="w-full h-full"
                                frameborder="0"
                            ></iframe>

                            <div
                                v-else-if="currentPreviewIsImage"
                                class="w-full h-full overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentPdfUrl"
                                    :alt="currentPdfTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>

                            <div v-else class="w-full h-full flex items-center justify-center p-8">
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
            </Transition>
        </Teleport>
    </LandingLayout>
</template>

<style scoped>
.announcement-page { --page-surface:#fff; --page-background:#f1f5fa; --page-text:var(--institutional-primary-dark); --page-muted:#4c6281; --page-border:#e2eaf4; color:var(--page-text); background:linear-gradient(135deg,transparent 65%,#dce7f340 65%,transparent 85%),linear-gradient(45deg,#e3edf680,transparent 40%),var(--page-background); min-height:100%; }
.announcement-container { max-width:1500px; margin:0 auto; padding-left:32px; padding-right:32px; }
.announcement-hero { position:relative; isolation:isolate; overflow:hidden; color:white; background:var(--institutional-primary-dark); }
.announcement-hero::before { content:''; position:absolute; z-index:-2; inset:0 0 0 38%; background:url('/images/cenidet-official-panorama.jpg') center 48%/cover no-repeat; }
.announcement-hero::after { content:''; position:absolute; z-index:-1; inset:0; background:linear-gradient(53deg,transparent 9%,#ffffff05 9% 13%,transparent 13% 32%,#2c528255 32% 39%,transparent 39%),linear-gradient(90deg,var(--institutional-primary-dark) 0%,var(--institutional-primary-dark) 32%,#1b396aee 43%,#1b396a70 56%,transparent 76%); }
.announcement-breadcrumb { display:flex; gap:10px; font-size:11px; color:#dce7f5; padding-top:15px; }
.announcement-breadcrumb a:hover { color:var(--institutional-gold); }
.hero-heading { padding:17px 0 26px; }
.hero-eyebrow,.hero-mark { display:none; }
.hero-heading h1 { font-weight:750; line-height:1.05; font-size:clamp(38px,4vw,58px); letter-spacing:-.025em; max-width:650px; margin:0 0 15px; }
.hero-heading h1 span { display:block; font-size:.67em; font-weight:400; letter-spacing:0; margin-bottom:3px; }
.hero-heading h1::after { content:''; display:block; width:56px; height:3px; margin-top:17px; background:var(--institutional-gold); }
.hero-heading p { max-width:530px; font-size:16px; line-height:1.55; color:#f1f5fc; }
.section-navigation { display:flex; gap:24px; flex-wrap:wrap; padding:0 0 14px; }
.section-navigation a { display:inline-flex; align-items:center; gap:7px; font-size:11px; color:#dce7f5; }
.section-navigation a:hover { color:var(--institutional-gold); }
.announcement-content { display:grid; grid-template-columns:minmax(0,1.05fr) minmax(0,1fr); align-items:start; gap:20px; padding-top:20px; padding-bottom:32px; }
.details-column,.calendar-column { display:grid; gap:16px; min-width:0; }
.information-card { border-radius:18px; padding:22px 26px; background:var(--page-surface); border:1px solid #ffffff70; box-shadow:0 6px 22px #1b396a0a; scroll-margin-top:24px; }
.card-heading { display:flex; align-items:center; gap:20px; margin-bottom:12px; }
.heading-icon { display:flex; align-items:center; justify-content:center; flex-shrink:0; width:56px; height:56px; border-radius:12px; color:white; background:linear-gradient(135deg,var(--institutional-primary-light),var(--institutional-primary)); box-shadow:inset 0 1px 2px #ffffff45,0 3px 8px #1b396a15; }
.card-heading h2 { font-size:clamp(19px,1.7vw,24px); line-height:1.25; font-weight:750; letter-spacing:-.02em; }
.card-heading h2::after { content:''; display:block; width:44px; height:3px; background:var(--institutional-primary-light); margin-top:8px; }
#participantes { display:grid; grid-template-columns:56px minmax(0,1fr); column-gap:20px; }
#participantes .card-heading { display:contents; }
#participantes .heading-icon { grid-column:1; grid-row:1 / span 2; }
#participantes h2 { grid-column:2; }
.audience-copy { grid-column:2; font-size:14px; line-height:1.55; margin-top:8px; color:var(--page-muted); }
.requirements-card { position:relative; overflow:hidden; }
.requirements-card::after { content:''; position:absolute; width:58px; height:72px; border-radius:7px; right:30px; top:24px; transform:rotate(18deg); background:repeating-linear-gradient(0deg,transparent 0 8px,#ffffffb0 8px 12px),#e8f0fa80; pointer-events:none; }
.requirements-card .card-heading { position:relative; z-index:1; margin-bottom:10px; }
.requirements-list { padding:0; margin:0; list-style:none; }
.requirements-list li { display:grid; grid-template-columns:56px minmax(0,1fr); column-gap:20px; align-items:start; }
.requirement-number { display:flex; align-items:center; justify-content:center; width:36px; height:36px; justify-self:center; border-radius:50%; font-size:18px; font-weight:750; background:#e3ecf7; color:var(--institutional-primary); margin:7px 0; }
.requirements-list p { font-size:14px; line-height:1.5; padding:8px 0; color:var(--page-muted); border-bottom:1px solid var(--page-border); }
.requirements-list li:last-child p { border-bottom:0; }
.participation-card { display:flex; gap:20px; align-items:center; padding:20px 26px; border-radius:18px; color:white; background:linear-gradient(110deg,var(--institutional-primary),var(--institutional-primary-dark) 55%,var(--institutional-primary-light)); box-shadow:0 4px 16px #1b396a25; border-top:2px solid #ffffff30; }
.participation-icon { display:flex; flex-shrink:0; justify-content:center; align-items:center; width:58px; height:58px; border:1px solid #b4d5f7; border-radius:50%; background:linear-gradient(140deg,#ffffff25,#ffffff05); box-shadow:inset 0 0 12px #ffffff20; }
.participation-card h2 { font-size:21px; font-weight:750; line-height:1.25; margin-bottom:5px; }
.participation-card p { font-size:13px; color:#e4eefb; line-height:1.5; }
.participation-button { display:inline-flex; flex-shrink:0; align-items:center; justify-content:center; gap:16px; background:white; color:var(--institutional-primary); padding:14px 18px; border-radius:12px; font-weight:700; font-size:13px; margin-left:auto; box-shadow:0 3px 8px #00000015; }
.participation-button:hover,.document-button:hover { background:#e8eff9; }
.featured-announcement { position:relative; overflow:hidden; isolation:isolate; padding:25px 26px; min-height:220px; border-radius:18px; background:linear-gradient(115deg,var(--institutional-primary),var(--institutional-primary-dark) 60%,var(--institutional-primary-light)); color:white; box-shadow:0 5px 16px #1b396a15; border:1px solid #ffffff30; }
.featured-announcement::before { content:''; position:absolute; z-index:-1; inset:0; background:linear-gradient(115deg,transparent 65%,#ffffff08 65% 70%,transparent 70% 79%,#ffffff08 79% 84%,transparent 84%); }
.featured-top { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
.document-icon { display:flex; color:var(--institutional-gold); }
.featured-label { font-size:10px; letter-spacing:.12em; color:#d1e1f6; }
.featured-announcement h2 { font-size:clamp(27px,2.8vw,40px); font-weight:750; line-height:1.15; margin:0 0 12px; max-width:67%; overflow-wrap:anywhere; }
.announcement-description { white-space:pre-line; overflow-wrap:anywhere; color:#e7effa; font-size:14px; line-height:1.5; max-width:65%; margin-bottom:16px; }
.document-button { display:inline-flex; align-items:center; gap:10px; background:white; color:var(--institutional-primary-dark); border-radius:12px; padding:14px 18px; font-size:13px; font-weight:750; cursor:pointer; position:relative; z-index:2; }
.document-notice { display:flex; align-items:center; gap:8px; font-size:13px; color:#e7effa; position:relative; z-index:2; }
.pdf-art { position:absolute; right:24px; bottom:10px; width:145px; height:165px; transform:rotate(15deg); z-index:-1; }
.pdf-art::before { content:''; position:absolute; width:110px; height:150px; border:1px solid #ffffff30; background:linear-gradient(135deg,#8cb7e66b,#1b396a99); border-radius:15px; top:-17px; right:-6px; }
.pdf-sheet { display:flex; justify-content:center; align-items:center; position:absolute; inset:10px 20px 0 0; border-radius:12px; color:#7e9cbe; background:linear-gradient(120deg,#ffffff,#c6d9ed); box-shadow:0 8px 15px #00000020; }
.pdf-badge { position:absolute; right:0; bottom:22px; padding:18px 14px; font-size:18px; font-weight:800; border-radius:12px; background:var(--institutional-primary-dark); color:white; box-shadow:0 5px 15px #00000030; }
.empty-announcement { text-align:center; }
.empty-announcement svg { margin:0 auto 12px; }
.empty-announcement h2 { font-size:20px; font-weight:700; }
.empty-announcement p { font-size:14px; color:var(--page-muted); margin-top:8px; }
.calendar-card { padding-top:20px; padding-bottom:18px; }
.calendar-card .card-heading { gap:18px; margin-bottom:8px; }
.calendar-card .heading-icon { width:48px; height:48px; }
.stages-list { list-style:none; padding:0; margin:0; }
.stage-item { position:relative; display:grid; grid-template-columns:56px minmax(0,1fr) 50px; gap:18px; align-items:center; min-height:83px; }
.stage-item:not(:last-child)::before { content:''; position:absolute; left:27px; top:50%; bottom:-50%; width:2px; background:#a9bdd8; }
.stage-number { position:relative; z-index:1; width:42px; height:42px; display:flex; align-items:center; justify-content:center; justify-self:center; border-radius:50%; color:white; font-weight:750; font-size:18px; background:var(--stage-color); box-shadow:0 2px 5px #10234415; }
.stage-item:nth-child(3) .stage-number { color:var(--institutional-primary-dark); }
.stage-item > div { border-top:1px solid var(--page-border); padding:9px 0; }
.stage-label { font-size:11px; color:var(--page-muted); }
.stage-item h3 { font-size:15px; font-weight:750; line-height:1.35; margin:2px 0 5px; }
.stage-date { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--page-muted); line-height:1.5; }
.stage-date svg { flex-shrink:0; }
.stage-icon { display:flex; justify-content:center; align-items:center; width:50px; height:50px; border-radius:50%; background:#e5edf7; color:var(--institutional-primary); }
.announcement-page a:focus-visible,.announcement-page button:focus-visible { outline:3px solid var(--institutional-gold); outline-offset:4px; }
:global(.dark .announcement-page) { --page-background:var(--institutional-dark-background); --page-surface:var(--institutional-dark-surface); --page-text:var(--institutional-dark-text); --page-muted:#bbc8dd; --page-border:#ffffff18; }
:global(.dark .announcement-page .requirement-number),:global(.dark .announcement-page .stage-icon) { background:#ffffff10; color:#c6ddff; }
@media(max-width:1200px) { .participation-card { flex-wrap:wrap; gap:14px; } .participation-button { margin-left:78px; } .participation-card > div { flex:1; } }
@media(max-width:900px) { .announcement-content { grid-template-columns:1fr; } .announcement-hero::before { inset-inline-start:25%; } .hero-heading p { max-width:65%; } .hero-heading h1 { font-size:44px; } .participation-card { flex-wrap:nowrap; } .participation-button { margin-left:auto; } }
@media(max-width:600px) { .announcement-container { padding-left:16px; padding-right:16px; } .hero-heading h1 { font-size:36px; } .hero-heading p { max-width:100%; font-size:14px; } .announcement-hero::after { background:linear-gradient(90deg,#102344f5,#102344cc); } .section-navigation { gap:12px; } .announcement-content { gap:16px; } .information-card { padding:20px 16px; } #participantes { grid-template-columns:42px minmax(0,1fr); column-gap:12px; } .heading-icon { width:42px; height:46px; } .card-heading { gap:12px; } .card-heading h2 { font-size:19px; } .requirements-list li { grid-template-columns:34px minmax(0,1fr); gap:12px; } .requirement-number { width:30px; height:30px; font-size:16px; } .requirements-list p,.audience-copy { font-size:13px; } .participation-card { flex-wrap:wrap; padding:20px; } .participation-icon { width:46px; height:46px; } .participation-card h2 { font-size:19px; } .participation-button { width:100%; margin:0; } .featured-announcement { padding:22px; } .featured-announcement h2 { max-width:70%; font-size:28px; } .announcement-description { max-width:70%; font-size:13px; } .pdf-art { right:5px; width:100px; height:145px; opacity:.6; } .pdf-sheet { right:5px; } .pdf-sheet svg { width:70px; } .pdf-art::before { width:90px; } .pdf-badge { font-size:14px; padding:14px 10px; } .document-button { font-size:12px; padding:13px; gap:7px; } .stage-item { grid-template-columns:38px minmax(0,1fr) 34px; gap:10px; } .stage-number { width:34px; height:34px; font-size:16px; } .stage-item:not(:last-child)::before { left:18px; } .stage-icon { width:34px; height:34px; } .stage-icon svg { width:20px; } .stage-item h3 { font-size:14px; } .stage-date { font-size:12px; } }
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}
</style>
