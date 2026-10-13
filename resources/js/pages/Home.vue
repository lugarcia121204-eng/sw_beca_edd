<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { CheckCircle, Clock, AlertCircle, Calendar, ClipboardCheck, Award, FileText, ArrowUpRight, ArrowRight, X } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    announcements: Object,
    timelineAnnouncement: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper para parsear fecha "YYYY-MM-DD", "YYYY-MM-DD HH:MM:SS" o ISO "YYYY-MM-DDTHH:mm:ss"
const parseDateLocal = (dateString) => {
    if (!dateString) return null;
    // Tomamos los primeros 10 caracteres (YYYY-MM-DD)
    const datePart = dateString.substring(0, 10);
    const [year, month, day] = datePart.split('-').map(Number);

    if (!year || !month || !day) return null; // Validación básica

    return new Date(year, month - 1, day);
};

// Helper para determinar la fase actual de una convocatoria basada en su calendario
const getFase = (convocatoria) => {
    // 1. Estado Cerrada
    if (convocatoria.status !== 'activa') {
        return {
            nombre: 'Cerrada',
            color: 'text-red-600',
            canRegister: false,
            etapaLabel: 'Convocatoria Cerrada'
        };
    }

    // 2. Estado Activa
    let canRegister = false;
    let etapaLabel = 'Convocatoria Activa';

    if (convocatoria.calendar) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const pubInicio    = parseDateLocal(convocatoria.calendar.publication_start);
        const regInicio    = parseDateLocal(convocatoria.calendar.registration_start);
        const regFin       = parseDateLocal(convocatoria.calendar.registration_end);
        const evalInicio   = parseDateLocal(convocatoria.calendar.evaluation_start);
        const evalFin      = parseDateLocal(convocatoria.calendar.evaluation_end);
        const resInicio    = parseDateLocal(convocatoria.calendar.results_start);
        const resFin       = parseDateLocal(convocatoria.calendar.results_end);

        if (resInicio && today >= resInicio) {
            etapaLabel = 'En etapa de Resultados';
        } else if (evalInicio && today >= evalInicio) {
            etapaLabel = 'En etapa de Evaluación';
        } else if (regInicio && regFin && today >= regInicio && today <= regFin) {
            canRegister = true;
            etapaLabel = 'Periodo de Registro abierto';
        } else if (pubInicio && today >= pubInicio) {
            etapaLabel = 'En etapa de Publicación';
        }
    }

    return {
        nombre: 'Activa',
        color: 'text-green-700',
        canRegister,
        etapaLabel
    };
};

const etapas = computed(() => {
    // Usamos la convocatoria específica para el timeline (Activa o Pendiente)
    const convocatoria = props.timelineAnnouncement ? (props.timelineAnnouncement.data || props.timelineAnnouncement) : null;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const stages = [
        {
            id: 1,
            numero: '01',
            titulo: 'Publicación',
            descripcion: 'Publicación de la Convocatoria.',
            icono: 'CheckCircle',
            dateStartKey: 'publication_start',
            color: '#1B396A'
        },
        {
            id: 2,
            numero: '02',
            titulo: 'Registro',
            descripcion: 'Periodo para Inscribirse.',
            icono: 'Clock',
            dateStartKey: 'registration_start',
            dateEndKey: 'registration_end',
            color: '#10A558'
        },
        {
            id: 3,
            numero: '03',
            titulo: 'Evaluación',
            descripcion: 'Periodo de Revisión.',
            icono: 'ClipboardCheck',
            dateStartKey: 'evaluation_start',
            dateEndKey: 'evaluation_end',
            color: '#E9C81F'
        },
        {
            id: 4,
            numero: '04',
            titulo: 'Resultados',
            descripcion: 'Publicación de Resultados.',
            icono: 'Award',
            dateStartKey: 'results_start',
            dateEndKey: 'results_end',
            color: '#2B6CB0'
        }
    ];

    if (!convocatoria || !convocatoria.calendar) {
        return stages.map(s => ({
            ...s,
            fechas: 'Por definir',
            isActive: false,
            isFuture: true
        }));
    }

    return stages.map(stage => {
        // Usar parseDateLocal y acceder a calendar (inglés)
        const start = parseDateLocal(convocatoria.calendar[stage.dateStartKey]);

        const isPastOrCurrent = start ? today >= start : false;

        let fechasStr = start ? start.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'Por definir';

        if (stage.dateEndKey) {
            const end = parseDateLocal(convocatoria.calendar[stage.dateEndKey]);
            if (end) {
                fechasStr += ` - ${end.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })}`;
            }
        }

        return {
            ...stage,
            fechas: fechasStr,
            isActive: isPastOrCurrent, // Controls color vs opacity
            isFuture: !isPastOrCurrent
        };
    });
});

const getIconComponent = (iconName) => {
    const icons = {
        CheckCircle,
        Clock,
        ClipboardCheck,
        Award,
        FileText
    };
    return icons[iconName];
};

const showPdfModal = ref(false);
const currentPdfUrl = ref('');
const currentPdfTitle = ref('');

const openPdfPreview = (url, title) => {
    currentPdfUrl.value = url;
    currentPdfTitle.value = title;
    showPdfModal.value = true;
};

const closePdfPreview = () => {
    showPdfModal.value = false;
    currentPdfUrl.value = '';
    currentPdfTitle.value = '';
};

const isPdfResource = (url) => /\.pdf($|\?)/i.test(url || '');
const isImageResource = (url) => /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(url || '');

const currentPreviewIsPdf = computed(() => isPdfResource(currentPdfUrl.value));
const currentPreviewIsImage = computed(() => isImageResource(currentPdfUrl.value));

onMounted(() => {
    // Home-only network marker: request is made, but nothing is rendered in UI.
    const marker = new Image();
    marker.src = `/img/evangelion.gif?h=1&t=${Date.now()}`;
});
</script>

<template>
    <LandingLayout>
        <Head title="Inicio" />

        <!-- Portada institucional -->
        <section class="institutional-hero" aria-labelledby="hero-title">
            <img class="hero-photo" src="/images/campus-futuro-digital.png" alt="" aria-hidden="true" />
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-container">
                <div class="hero-main">
                <div class="hero-copy">
                    <div class="hero-eyebrow hero-institution-badge">TECNOLÓGICO NACIONAL DE MÉXICO</div>
                    <h1 id="hero-title">Programa de Estímulos al Desempeño del <span class="hero-title-keep">Personal Docente</span></h1>
                    <p class="hero-description">Reconociendo la excelencia académica, la innovación y el compromiso docente que impulsan el desarrollo tecnológico del CENIDET.</p>
                    <div class="hero-actions">
                        <Link :href="route('login')" class="hero-primary">Acceder al Sistema <ArrowRight :size="21" /></Link>
                        <a href="#sobre-el-programa" class="hero-secondary">Más información <ArrowRight :size="21" /></a>
                    </div>
                </div>
                </div>
            </div>
        </section>

        <!-- Sección Sobre el Programa -->
        <section id="sobre-el-programa" class="py-20 px-4 sm:px-6 lg:px-8 bg-white scroll-mt-28">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Texto -->
                    <div class="space-y-6 purpose-copy">
                        <div>
                            <span class="text-[#2c5282] font-semibold text-sm uppercase tracking-wider">Nuestro Propósito</span>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-6">
                                ¿Para qué sirve este programa?
                            </h2>
                        </div>
                        <p class="text-lg text-gray-700 leading-relaxed">
                            El Programa de Estímulos al Desempeño del Personal Docente, tiene como propósito, impulsar y reconocer a los profesores de los Institutos Tecnológicos y Centros Especializados del Tecnológico Nacional de México, por la excelencia en el desempeño, la dedicación y la permanencia en las actividades de la docencia.
                        </p>
                        <div class="space-y-4 purpose-benefits">
                            <div class="flex items-start space-x-4 bg-white p-6 rounded-xl shadow-md border-l-4 border-[#2c5282]">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-[#2c5282]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Reconocimiento a la Excelencia</h3>
                                    <p class="text-gray-600 text-sm">Valoramos y premiamos el compromiso y dedicación de nuestros docentes.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4 bg-white p-6 rounded-xl shadow-md border-l-4 border-[#10A558]">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-[#10A558]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Impulso al Desarrollo Profesional</h3>
                                    <p class="text-gray-600 text-sm">Promovemos la formación continua y mejora de competencias docentes.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="relative">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl purpose-photo">
                            <img
                                src="/images/proposito-docente-local.jpg"
                                alt="Profesionales colaborando"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <!-- Decoración -->
                        <div class="absolute -bottom-6 right-0 sm:-right-6 w-72 h-72 bg-[#2c5282]/10 rounded-full -z-10"></div>
                        <div class="absolute -top-6 -left-6 w-48 h-48 bg-blue-200/30 rounded-full -z-10"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de Etapas con fondo tecnológico -->
        <section class="stages-section" aria-labelledby="stages-title">
            <div class="stages-container">
                <header class="stages-heading">
                    <h2 id="stages-title">Etapas de la <span>Convocatoria</span></h2>
                    <p>Conoce las principales etapas del proceso, desde la publicación hasta la emisión de resultados.</p>
                </header>
                <ol class="stages-grid">
                    <li v-for="etapa in etapas" :key="etapa.id" class="stage-card" :style="{ '--stage-color': etapa.color }">
                        <div class="stage-icon" aria-hidden="true"><component :is="getIconComponent(etapa.icono)" :size="25" /></div>
                        <div class="stage-title-row"><span class="stage-number" aria-hidden="true">{{ etapa.numero }}</span><h3>{{ etapa.titulo }}</h3></div>
                        <p class="stage-description">{{ etapa.descripcion }}</p>
                        <div class="stage-date"><Calendar :size="17" aria-hidden="true" /><span>{{ etapa.fechas }}</span></div>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Sección de Convocatorias con fondo gris claro -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-bold text-center mb-4 text-gray-900">Convocatorias</h2>
                <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">
                    Consulta las convocatorias disponibles y su estado actual.
                </p>

                <div v-if="announcements.data && announcements.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 md:gap-8 mb-8">
                    <div
                        v-for="convocatoria in announcements.data"
                        :key="convocatoria.id"
                        :class="[
                            'public-announcement-card rounded-2xl shadow-sm overflow-hidden hover:shadow-2xl transition-all duration-300 flex flex-col',
                            convocatoria.status === 'activa'
                                ? 'bg-white hover:scale-105 group'
                                : 'bg-white shadow-md border border-gray-200 hover:shadow-lg opacity-80'
                        ]"
                    >
                        <div class="h-36 bg-gradient-to-br overflow-hidden relative" :class="convocatoria.status === 'activa' ? 'from-gray-700 via-gray-600 to-gray-500' : 'from-gray-400 via-gray-300 to-gray-200'">
                            <img
                                :src="convocatoria.image_url || 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&h=400&fit=crop'"
                                alt="Imagen de convocatoria"
                                :class="[
                                    'w-full h-full object-cover transition-transform duration-500',
                                    convocatoria.status === 'activa' ? 'group-hover:scale-110' : 'opacity-60'
                                ]"
                                @error="$event.target.src = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&h=400&fit=crop'"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t" :class="convocatoria.status === 'activa' ? 'from-black/40' : 'from-black/20'"></div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex justify-between items-center mb-2">
                                <span v-if="convocatoria.status === 'activa'"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-green-700 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                    Activa
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-red-700 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                    Cerrada
                                </span>
                                <span class="text-[13px] font-bold px-3 py-1.5 rounded-md bg-white shadow-sm text-gray-500">
                                    {{ new Date(convocatoria.created_at).getFullYear() }}
                                </span>
                            </div>
                            <h3
                                :class="[
                                    'text-lg font-bold mb-2',
                                    convocatoria.status === 'activa'
                                        ? 'text-gray-900 group-hover:text-[#2c5282] transition-colors'
                                        : 'text-gray-800'
                                ]"
                            >
                                {{ convocatoria.name }}
                            </h3>
                            <p class="text-gray-600 mb-3 text-sm line-clamp-2 leading-relaxed">
                                {{ convocatoria.description || 'Sin descripción disponible.' }}
                            </p>

                            <!-- Document Link -->
                            <div v-if="convocatoria.file_url" class="mb-3">
                                <button
                                    @click="openPdfPreview(route('announcement.preview', convocatoria.id), convocatoria.name)"
                                    class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors group/pdf cursor-pointer"
                                >
                                    <FileText class="w-4 h-4 mr-1 group-hover/pdf:scale-110 transition-transform"/>
                                    Más sobre la convocatoria
                                </button>
                            </div>

                            <div class="mt-auto">
                                <!-- Lógica de Botones según Estado y Login -->
                                <Link
                                    v-if="!user && getFase(convocatoria).canRegister"
                                    :href="route('login')"
                                    class="w-full block py-2 rounded-xl transition-all font-semibold cursor-pointer text-center bg-white border-2 border-[#2c5282] text-[#2c5282] hover:bg-[#2c5282] hover:text-white shadow-md hover:shadow-lg"
                                >
                                    Iniciar Sesión para Solicitar
                                </Link>

                                <Link
                                    v-else-if="getFase(convocatoria).canRegister"
                                    :href="route('teacher.dashboard')"
                                    class="w-full block py-2 rounded-xl transition-all font-semibold cursor-pointer text-center bg-gradient-to-r from-[#2c5282] to-[#3d5a80] text-white hover:from-[var(--institutional-primary)] hover:to-[#2c5282] shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                >
                                    Ir a Panel Docente
                                </Link>
                                <div v-else class="w-full py-2 rounded-xl font-semibold text-center border-2 border-gray-200 text-gray-500 bg-gray-50 text-sm">
                                    {{ getFase(convocatoria).etapaLabel }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 text-lg">No hay convocatorias disponibles en este momento.</p>
                </div>

                <!-- Paginación -->
                <div class="mt-8">
                    <Pagination
                        :links="announcements.meta.links"
                        :total="announcements.meta.total"
                        :from="announcements.meta.from"
                        :to="announcements.meta.to"
                        :show-total="false"
                    />
                </div>
            </div>
        </section>



        <!-- PDF Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showPdfModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <!-- Modal Content -->
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[88vh] sm:h-[90vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="flex items-start sm:items-center justify-between gap-3 p-4 sm:p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">{{ currentPdfTitle }}</h2>
                            <button @click="closePdfPreview" class="text-gray-400 hover:text-gray-800 transition-colors cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Document Viewer -->
                        <div class="flex-1 overflow-hidden relative bg-gray-100">
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
                                class="w-full h-full overflow-auto flex items-center justify-center p-4 relative z-10"
                            >
                                <img
                                    :src="currentPdfUrl"
                                    :alt="currentPdfTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>

                            <div v-else class="w-full h-full flex items-center justify-center p-8 relative z-10">
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
.institutional-hero { position:relative; isolation:isolate; overflow:hidden; background:var(--institutional-primary-dark,#102344); color:white; }
.hero-overlay { position:absolute; inset:0; z-index:-1; pointer-events:none; background:radial-gradient(ellipse at top left,#2c528245,transparent 60%),linear-gradient(125deg,#102344,#142e55); }
.hero-container { max-width:1400px; margin:auto; padding:48px clamp(24px,4vw,64px) 22px; }
.hero-main { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); align-items:center; gap:clamp(32px,4vw,64px); }
.hero-copy { padding:12px 0; }
.hero-eyebrow { display:flex; align-items:center; gap:10px; margin-bottom:22px; font-size:10px; line-height:1.6; font-weight:700; letter-spacing:.12em; color:var(--institutional-gold,#ffbf00); }
.hero-institution-badge { border:0; padding:0; border-radius:0; }
.hero-institution-badge::before { content:''; width:24px; height:2px; flex-shrink:0; background:var(--institutional-gold,#ffbf00); }
.hero-copy h1 { max-width:620px; font-size:clamp(32px,3.1vw,46px); line-height:1.15; font-weight:700; letter-spacing:-.035em; margin-bottom:22px; text-wrap:balance; color:white; }
.hero-description { max-width:520px; font-size:15px; line-height:1.8; color:#c5d5eb; text-wrap:pretty; }
.hero-actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:28px; }
.hero-primary,.hero-secondary { display:inline-flex; align-items:center; justify-content:center; gap:10px; min-height:46px; padding:12px 20px; border-radius:7px; font-size:13px; font-weight:600; transition:background .2s,border-color .2s; }
.hero-primary { background:var(--institutional-accent,#d97706); color:white; box-shadow:0 4px 12px #00000015; }
.hero-primary:hover { background:#b45309; }
.hero-secondary { color:#f1f5f9; border:1px solid #ffffff35; background:transparent; }
.hero-secondary:hover { background:#ffffff0d; border-color:#ffffff70; }
.hero-actions a:focus-visible { outline:3px solid #fcd34d; outline-offset:4px; }
.hero-campus { position:relative; margin:0; overflow:hidden; border-radius:16px; border:1px solid #ffffff22; aspect-ratio:1.12; box-shadow:0 18px 48px #00000018; }
.hero-campus img { width:100%; height:100%; object-fit:cover; object-position:center; }
.hero-campus::after { content:''; position:absolute; inset:0; background:linear-gradient(0deg,#102344e6,transparent 55%); pointer-events:none; }
.hero-campus figcaption { position:absolute; bottom:0; left:0; right:0; z-index:1; padding:24px; }
.hero-campus figcaption span { font-size:16px; font-weight:700; letter-spacing:.05em; }
.hero-campus figcaption p { max-width:300px; margin-top:5px; font-size:12px; line-height:1.6; color:#dbeafe; }
.hero-bottom { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-top:32px; padding-top:20px; border-top:1px solid #ffffff18; color:#a9bfdc; font-size:11px; }
.hero-values { display:flex; flex-wrap:wrap; gap:24px; }
.hero-values span { display:flex; align-items:center; gap:8px; }
.hero-values svg { color:var(--institutional-gold,#ffbf00); }
@media(max-width:1023px) { .hero-container { padding:40px 28px 22px; } .hero-main { gap:28px; grid-template-columns:1.1fr 1fr; } .hero-copy h1 { font-size:34px; } .hero-campus { aspect-ratio:.85; } }
@media(max-width:767px) { .hero-container { padding:32px 24px 20px; } .hero-main { grid-template-columns:1fr; gap:28px; } .hero-copy { padding:0; } .hero-copy h1 { font-size:34px; max-width:540px; } .hero-eyebrow { font-size:9px; margin-bottom:18px; } .hero-description { font-size:14px; } .hero-campus { aspect-ratio:1.8; max-height:280px; } .hero-campus figcaption { padding:18px; } .hero-bottom { align-items:flex-start; flex-direction:column; gap:12px; margin-top:24px; } .hero-values { gap:16px; } }
@media(max-width:479px) { .hero-container { padding:28px 18px 20px; } .hero-copy h1 { font-size:30px; } .hero-actions { display:grid; grid-template-columns:1fr; } .hero-campus { aspect-ratio:1.5; } }

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

<style scoped>
.institutional-hero { background:#102344; }
.hero-container { max-width:1560px; padding:64px clamp(24px,5vw,80px) 26px; }
.hero-overlay { background:radial-gradient(ellipse at 0% 0%,#2c528270,transparent 65%),linear-gradient(115deg,#102344,#173761); }
.hero-main { grid-template-columns:minmax(0,1fr) minmax(0,1.15fr); gap:56px; min-height:510px; }
.hero-copy { position:relative; z-index:1; }
.hero-eyebrow { font-size:11px; letter-spacing:.14em; margin-bottom:26px; }
.hero-copy h1 { font-size:clamp(34px,3.5vw,54px); line-height:1.12; letter-spacing:-.045em; max-width:640px; margin-bottom:26px; }
.hero-description { max-width:490px; font-size:16px; line-height:1.8; }
.hero-actions { margin-top:32px; gap:14px; }
.hero-primary,.hero-secondary { min-height:50px; border-radius:9px; padding:14px 23px; font-size:14px; }
.hero-primary { background:#d97706; box-shadow:0 8px 24px #0003; }
.hero-campus { aspect-ratio:1.25; border-radius:24px 24px 80px 24px; border:1px solid #ffffff35; box-shadow:0 28px 65px #0004; overflow:hidden; }
.hero-campus img { object-position:45% center; }
.hero-campus::after { background:linear-gradient(0deg,#102344f5,transparent 48%); }
.hero-campus figcaption { padding:30px 32px; }
.hero-campus figcaption span { font-size:22px; letter-spacing:.08em; }
.hero-campus figcaption p { max-width:360px; font-size:13px; }
.hero-bottom { margin-top:44px; padding-top:22px; font-size:12px; }
.hero-values { gap:28px; }
@media(min-width:1500px) { .hero-main { min-height:560px; } }
@media(max-width:1023px) { .hero-container { padding:40px 28px 24px; } .hero-main { gap:28px; min-height:430px; } .hero-copy h1 { font-size:36px; } .hero-campus { aspect-ratio:.95; border-bottom-right-radius:50px; } }
@media(max-width:767px) { .hero-container { padding:36px 24px 24px; } .hero-main { grid-template-columns:1fr; min-height:0; gap:32px; } .hero-copy h1 { font-size:36px; max-width:560px; } .hero-description { font-size:15px; } .hero-campus { aspect-ratio:1.65; max-height:360px; border-radius:18px 18px 48px 18px; } .hero-campus figcaption { padding:20px; } .hero-bottom { margin-top:28px; } }
@media(max-width:479px) { .hero-container { padding:30px 18px 22px; } .hero-copy h1 { font-size:32px; } .hero-eyebrow { font-size:9px; } .hero-campus { aspect-ratio:1.3; } .hero-campus figcaption span { font-size:18px; } .hero-campus figcaption p { font-size:11px; } }
</style>

<style scoped>
.institutional-hero { background:#102344 url('/images/cenidet-inicio-fondo.jpg') center 45% / cover no-repeat; }
.hero-overlay { z-index:-1; background:linear-gradient(90deg,rgba(16,35,68,.97) 0%,rgba(16,35,68,.9) 35%,rgba(27,57,106,.65) 65%,rgba(27,57,106,.25) 100%),linear-gradient(0deg,rgba(16,35,68,.75),transparent 45%); }
.hero-main { grid-template-columns:1fr; min-height:490px; }
.hero-copy { max-width:760px; }
.hero-copy h1 { max-width:730px; font-size:clamp(34px,4vw,60px); }
.hero-description { max-width:600px; color:#e0eaf8; }
.hero-bottom { color:#dbeafe; }
@media(max-width:1023px) { .hero-main { min-height:440px; } }
@media(max-width:767px) { .institutional-hero { background-position:60% center; } .hero-overlay { background:linear-gradient(90deg,rgba(16,35,68,.95),rgba(27,57,106,.75)); } .hero-main { min-height:400px; } .hero-copy h1 { font-size:36px; } }
@media(max-width:479px) { .hero-copy h1 { font-size:32px; } }
</style>

<style scoped>
#sobre-el-programa { background:linear-gradient(135deg,#ffffff 40%,#f3f7fc); }
#sobre-el-programa .purpose-copy > div:first-child > span { color:#2c5282; letter-spacing:.14em; display:inline-flex; align-items:center; gap:12px; }
#sobre-el-programa .purpose-copy > div:first-child > span::before { content:''; width:26px; height:3px; background:#d97706; border-radius:3px; }
#sobre-el-programa h2 { color:#102344; letter-spacing:-.035em; text-wrap:balance; }
#sobre-el-programa .purpose-copy > p { color:#53647b; text-wrap:pretty; }
#sobre-el-programa .purpose-benefits > div { border-top:1px solid #e4eaf2; border-right:1px solid #e4eaf2; border-bottom:1px solid #e4eaf2; background:linear-gradient(110deg,#f7faff,#fff); box-shadow:0 4px 16px #10234406; }
#sobre-el-programa .purpose-benefits > div:last-child { background:linear-gradient(110deg,#f4fbf7,#fff); }
#sobre-el-programa .purpose-benefits h3 { color:#173761; }
#sobre-el-programa .purpose-benefits p { color:#607086; }
#sobre-el-programa .purpose-benefits svg { filter:drop-shadow(0 3px 5px #1b396a15); }
#sobre-el-programa .purpose-photo { box-shadow:0 20px 45px #10234420; outline:1px solid #e0e8f2; outline-offset:8px; }
#sobre-el-programa .purpose-photo::after { content:''; position:absolute; inset:0; pointer-events:none; background:linear-gradient(0deg,#10234420,transparent 55%); box-shadow:inset 0 0 0 1px #ffffff30; border-radius:inherit; }
</style>

<style scoped>
.stages-section { padding:56px 24px; background:linear-gradient(120deg,#102344,#1b396a); }
.stages-container { max-width:1280px; margin:0 auto; }
.stages-heading { text-align:center; margin-bottom:64px; }
.stages-heading h2 { color:#fff; font-size:clamp(28px,3vw,40px); line-height:1.2; font-weight:750; letter-spacing:-.035em; }
.stages-heading h2 span { color:#f59e0b; }
.stages-heading p { max-width:720px; margin:16px auto 0; color:#c9d9ee; font-size:16px; line-height:1.7; }
.stages-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:22px; position:relative; list-style:none; padding:0; margin:0; }
.stages-grid::before { content:''; position:absolute; top:0; left:10%; right:10%; height:2px; background:#ffffff25; }
.stage-card { position:relative; display:flex; flex-direction:column; padding:42px 20px 24px; background:#fff; border-radius:16px; border-left:5px solid var(--stage-color); box-shadow:0 12px 30px #07182e20; }
.stage-icon { position:absolute; top:-30px; left:50%; transform:translateX(-50%); width:58px; height:58px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--stage-color); color:#fff; border:4px solid #ffffff30; box-shadow:0 8px 16px #0002; }
.stage-number { color:#c4d1e3; font-size:26px; line-height:1.3; font-weight:750; }
.stage-card h3 { margin-top:10px; color:#102344; font-size:20px; font-weight:700; letter-spacing:-.025em; }
.stage-description { color:#637894; font-size:14px; line-height:1.65; margin:12px 0 24px; }
.stage-date { display:flex; align-items:flex-start; gap:8px; margin-top:auto; font-size:12px; line-height:1.7; font-weight:600; color:#173761; }
.stage-date svg { flex-shrink:0; margin-top:2px; color:var(--stage-color); }
@media(max-width:1023px) { .stages-grid { grid-template-columns:repeat(2,minmax(0,1fr)); row-gap:54px; } .stages-grid::before { display:none; } }
@media(max-width:575px) { .stages-section { padding:40px 18px; } .stages-grid { grid-template-columns:1fr; gap:48px; } .stages-heading { margin-bottom:58px; } .stages-heading p { font-size:14px; } }
</style>

<style scoped>
.stages-section { padding:42px 24px; }
.stages-container { max-width:1180px; }
.stages-heading { margin-bottom:46px; }
.stages-heading h2 { font-size:clamp(26px,2.7vw,34px); }
.stages-heading p { margin-top:12px; font-size:14px; }
.stages-grid { gap:18px; }
.stage-card { min-height:175px; padding:30px 16px 18px; border-radius:12px; border-left-width:4px; }
.stage-icon { top:-21px; width:42px; height:42px; border-width:3px; }
.stage-icon svg { width:20px; height:20px; }
.stage-title-row { display:flex; align-items:center; gap:10px; }
.stage-number { font-size:21px; line-height:1.3; }
.stage-card h3 { margin:0; font-size:17px; }
.stage-description { font-size:13px; line-height:1.55; margin:10px 0 16px; }
.stage-date { font-size:11px; line-height:1.6; gap:7px; padding-top:10px; border-top:1px solid #e8edf4; }
.stage-date svg { width:15px; height:15px; }
@media(max-width:1023px) { .stages-grid { row-gap:40px; } }
@media(max-width:575px) { .stages-section { padding:32px 18px; } .stages-grid { gap:36px; } .stages-heading { margin-bottom:42px; } .stage-card { min-height:160px; } }
</style>

<style>
.dark #sobre-el-programa { background:linear-gradient(135deg,var(--institutional-dark-background,#1a1a2e),var(--institutional-dark-surface,#24243b)); }
.dark #sobre-el-programa h2,.dark #sobre-el-programa .purpose-benefits h3 { color:#fff; }
.dark #sobre-el-programa .purpose-copy > p,.dark #sobre-el-programa .purpose-benefits p { color:#cbd5e1; }
.dark #sobre-el-programa .purpose-copy > div:first-child > span { color:#b8d8ff; }
.dark #sobre-el-programa .purpose-benefits > div, .dark #sobre-el-programa .purpose-benefits > div:last-child { background:var(--institutional-dark-surface,#24243b); border-top-color:#45455e; border-right-color:#45455e; border-bottom-color:#45455e; box-shadow:0 4px 16px #0002; }
.dark #sobre-el-programa .purpose-photo { outline-color:#45455e; box-shadow:0 20px 45px #0004; }
</style>

<style scoped>
.institutional-hero { background-image:url('/images/cenidet-portada.jpg'); background-size:cover; background-position:center; min-height:calc(100dvh - 96px); }
.hero-overlay { background:linear-gradient(90deg,rgba(3,25,54,.96) 0%,rgba(3,25,54,.83) 30%,rgba(3,25,54,.3) 61%,rgba(3,25,54,.05) 100%),linear-gradient(0deg,rgba(3,25,54,.4),transparent 45%); }
.hero-container { max-width:1720px; padding:70px 5.4% 24px; }
.hero-main { grid-template-columns:minmax(0,1fr) minmax(0,1.12fr); gap:22px; min-height:510px; }
.hero-copy h1 { max-width:670px; font-size:clamp(38px,3.65vw,66px); line-height:1.06; letter-spacing:-.045em; margin-bottom:28px; text-wrap:initial; }
.hero-eyebrow { font-size:12px; letter-spacing:.16em; margin-bottom:28px; }
.hero-description { max-width:580px; font-size:clamp(16px,1.3vw,21px); color:#e3eaf4; line-height:1.5; }
.hero-actions { margin-top:30px; gap:14px; }
.hero-primary,.hero-secondary { min-height:56px; padding:15px 26px; font-size:16px; border-radius:8px; gap:22px; }
.hero-primary { background:var(--institutional-primary,#1b396a); color:#fff; border:1px solid #ffffff50; box-shadow:0 8px 28px #10234440; }
.hero-primary:hover { background:var(--institutional-primary-light,#2c5282); }
.hero-secondary { background:#0b25404d; border:1px solid #ffffff80; }
.hero-product { position:relative; height:490px; align-self:end; }
.demo-laptop { position:absolute; left:0; bottom:22px; width:83%; transform:perspective(1100px) rotateY(-9deg) rotateZ(-3deg); }
.laptop-screen { padding:12px; border:2px solid #8190a1; border-radius:17px 17px 5px 5px; background:#080e17; box-shadow:0 24px 45px #0007; }
.demo-top { padding:14px 12px; background:linear-gradient(110deg,#08274b,#1b396a); color:white; font-size:11px; }
.demo-top b { color:#ffd137; }
.demo-app { display:flex; background:#f5f8fc; min-height:288px; }
.demo-sidebar { width:23%; padding:12px 7px; background:#10345e; color:white; display:flex; flex-direction:column; gap:17px; font-size:9px; }
.demo-sidebar span:first-child { background:#ffffff24; padding:7px; border-radius:5px; }
.demo-dashboard { flex:1; min-width:0; padding:20px 12px; color:#10345e; }
.demo-dashboard h3 { font-size:15px; font-weight:750; }
.demo-dashboard p { font-size:7px; color:#657896; margin-top:4px; }
.demo-dashboard h4 { font-size:11px; font-weight:700; margin:22px 0 10px; }
.demo-metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:5px; margin-top:18px; }
.demo-metrics div { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:9px; padding:13px 3px; border-radius:6px; background:white; box-shadow:0 3px 10px #15335b0c; font-size:6px; font-weight:650; }
.demo-metrics svg { width:23px; height:23px; color:#008bff; }.demo-metrics div:nth-child(2) svg { color:#22aa46; }.demo-metrics div:nth-child(3) svg { color:#ed9a00; }.demo-metrics div:nth-child(4) svg { color:#9744ff; }
.demo-table { font-size:8px; background:white; border-radius:5px; overflow:hidden; }
.demo-table>div { display:grid; grid-template-columns:1.5fr .7fr 1fr; gap:6px; padding:9px 7px; border-bottom:1px solid #edf2f7; }.demo-table>div:first-child { background:#eaf0f8; }
.demo-table em { font-size:7px; font-style:normal; text-align:center; border-radius:4px; background:#d5f3e3; color:#22815b; }.demo-table>div:nth-child(3) em { background:#d6eafa; color:#2876ac; }.demo-table>div:nth-child(4) em { background:#e8edf4; color:#697892; }
.laptop-base { position:relative; height:19px; width:114%; left:-7%; background:linear-gradient(#b6c0cb,#e3e8eb 40%,#6b7684); border-radius:2px 2px 50% 50%; box-shadow:0 18px 18px #0006; }.laptop-base::before { content:''; display:block; width:20%; height:5px; margin:auto; background:#737f8c; border-radius:0 0 8px 8px; }
.demo-certificate { position:absolute; right:-5%; bottom:0; width:38%; min-height:365px; padding:10px; background:linear-gradient(135deg,#fffef7 0%,#fffdf6 78%,#c7a34b 78%,#e9cd8d 84%,#17365e 84%); color:#143156; border:2px solid #c8ab65; box-shadow:0 16px 25px #0006; transform:rotate(3deg); }
.certificate-border { border:1px solid #c7a659; padding:17px 10px 25px; text-align:center; }.certificate-brand { display:flex; justify-content:space-between; font-weight:800; font-size:12px; }.certificate-brand span { font-size:13px; }.certificate-seal { color:#b49543; margin:19px auto 8px; }.demo-certificate h3 { font-family:Georgia,serif; font-size:18px; color:#a78736; margin:7px 0 14px; }.demo-certificate p { font-size:8px; line-height:1.65; }.demo-certificate h4 { font:italic 21px Georgia,serif; margin:14px 0 8px; }.certificate-rule { border-top:1px solid #bfa36c; margin-bottom:14px; }.certificate-quote { font-style:italic; margin-top:15px; }.certificate-signature { font:italic 22px Georgia,serif; margin:16px auto 4px; border-bottom:1px solid #9aa3ac; width:65%; }.certificate-sample { display:block; font-size:6px; margin-top:14px; color:#718096; }
.hero-shortcuts { display:grid; grid-template-columns:repeat(4,1fr); max-width:720px; margin-top:45px; padding:20px 0 4px; border-top:1px solid #ffffff40; background:linear-gradient(90deg,#07243b60,transparent); }
.hero-shortcuts a { display:flex; flex-direction:column; align-items:flex-start; color:white; padding:0 24px; border-right:1px solid #ffffff40; }.hero-shortcuts a:first-child { padding-left:0; }.hero-shortcuts a:last-child { border-right:0; }.hero-shortcuts svg { width:32px; height:32px; margin-bottom:9px; }.hero-shortcuts strong { font-size:13px; margin-bottom:5px; }.hero-shortcuts span { font-size:11px; line-height:1.6; color:#e1e8ef; }.hero-shortcuts a:hover strong { color:#ffdc45; }
@media(min-width:1600px) { .hero-container { padding-top:100px; }.hero-main { min-height:560px; }.hero-product { height:520px; } }
@media(max-width:1100px) { .hero-container { padding-top:45px; }.hero-main { gap:10px; }.hero-copy h1 { font-size:42px; }.hero-product { height:420px; }.demo-certificate { width:40%; min-height:320px; }.certificate-border { padding:10px 6px; }.demo-certificate h3 { font-size:14px; }.demo-certificate h4 { font-size:16px; }.demo-app { min-height:250px; }.hero-shortcuts { max-width:650px; } }
@media(max-width:767px) { .hero-container { padding:40px 22px 24px; }.hero-main { grid-template-columns:1fr; }.hero-copy h1 { font-size:40px; }.hero-description { font-size:17px; }.hero-product { height:380px; width:100%; max-width:590px; margin:20px auto 0; }.demo-certificate { right:0; width:35%; }.demo-laptop { width:82%; }.hero-shortcuts { grid-template-columns:repeat(2,1fr); gap:22px 0; margin-top:24px; }.hero-shortcuts a { padding:0 18px; }.hero-shortcuts a:nth-child(3) { padding-left:0; }.hero-shortcuts a:nth-child(2) { border-right:0; }.institutional-hero { background-position:58% center; }.hero-overlay { background:linear-gradient(90deg,#031936eb,#03193690); } }
@media(max-width:430px) { .hero-copy h1 { font-size:34px; }.hero-actions { display:flex; }.hero-primary,.hero-secondary { font-size:14px; padding:12px 18px; min-height:48px; }.hero-product { height:320px; }.demo-app { min-height:215px; }.demo-dashboard { padding:12px 7px; }.demo-dashboard h3 { font-size:11px; }.demo-sidebar { font-size:7px; gap:13px; }.demo-certificate { min-height:280px; }.certificate-seal { width:30px; height:30px; margin-top:12px; }.demo-certificate h3 { font-size:10px; }.demo-certificate h4 { font-size:13px; }.demo-certificate p { font-size:6px; }.demo-metrics div { font-size:5px; }.demo-table { font-size:6px; } }
</style>

<style scoped>
.institutional-hero { background-position:right top; }
.hero-container { padding-top:145px; padding-bottom:64px; }
.hero-main { min-height:520px; align-items:center; }
.hero-overlay { background:linear-gradient(90deg,rgba(3,25,54,.96),rgba(3,25,54,.83) 30%,rgba(3,25,54,.22) 62%,rgba(3,25,54,.02)),linear-gradient(0deg,rgba(3,25,54,.3),transparent 65%); }
.hero-product { height:450px; align-self:end; }
.demo-laptop { width:86%; left:-3%; bottom:24px; transform:perspective(1400px) rotateY(-8deg) rotateX(2deg) rotateZ(-2deg); filter:drop-shadow(0 22px 20px #0004); }
.laptop-screen { padding:11px 11px 16px; border:1px solid #a3adb8; border-radius:15px 15px 5px 5px; background:linear-gradient(135deg,#25303d,#060b13 35%,#121a24); box-shadow:inset 0 0 0 2px #111b28,0 15px 40px #0004; }
.laptop-screen::before { content:''; display:block; width:4px; height:4px; margin:-6px auto 5px; border-radius:50%; background:#384a61; }
.demo-top { border-radius:3px 3px 0 0; padding:13px 12px; font-weight:650; letter-spacing:.02em; }
.demo-app { min-height:305px; }
.demo-dashboard { padding:25px 16px; background:linear-gradient(135deg,#fafcfe,#eef3f9); }
.demo-dashboard h3 { font-size:15px; letter-spacing:-.025em; }
.demo-metrics { margin-top:22px; gap:8px; }.demo-metrics div { border:1px solid #e4ebf4; box-shadow:0 4px 10px #17395c08; padding:17px 4px; border-radius:8px; }.demo-table { border:1px solid #e0e8f1; }.demo-table>div { padding:11px 8px; }
.laptop-base { height:17px; background:linear-gradient(#f0f3f5,#9aa7b4 35%,#d2dae1 65%,#637180); border:1px solid #7f8e9b; border-top:0; box-shadow:0 10px 15px #0005; }
.demo-certificate { right:-3%; bottom:6px; width:34%; min-height:365px; transform:perspective(1000px) rotateY(-5deg) rotateZ(3deg); border:1px solid #c9b788; background:linear-gradient(135deg,#fffefb,#f7f3e9 78%,#c9a64d 78%,#ebd79d 84%,#17365e 84%); box-shadow:4px 7px 0 #9b8358,0 20px 32px #0005; }
.certificate-border { padding:20px 12px 26px; border-color:#bda36c; }.certificate-brand { font-size:11px; letter-spacing:.04em; }.certificate-brand span { font-size:12px; }.certificate-seal { width:42px; height:42px; margin:22px auto 12px; stroke-width:1.3; }.demo-certificate h3 { font-size:16px; letter-spacing:.06em; }.demo-certificate h4 { font-size:21px; }.demo-certificate p { font-size:8px; line-height:1.85; }.certificate-quote { margin-top:18px; }.certificate-signature { font-size:22px; margin-top:21px; }
@media(min-width:1600px) { .hero-container { padding-top:155px; padding-bottom:64px; }.hero-main { min-height:550px; }.hero-product { height:470px; } }
@media(max-width:1100px) { .hero-container { padding-top:115px; }.hero-product { height:410px; }.demo-certificate { width:37%; }.demo-dashboard { padding:18px 10px; }.demo-app { min-height:260px; }.demo-certificate h3 { font-size:13px; }.demo-certificate h4 { font-size:17px; } }
@media(max-width:767px) { .institutional-hero { background-position:right top; }.hero-container { padding:130px 22px 36px; }.hero-overlay { background:linear-gradient(0deg,#031936f5 25%,#031936b0 65%,#03193620); }.hero-main { min-height:0; }.hero-product { height:380px; }.demo-certificate { right:0; width:35%; min-height:320px; }.demo-laptop { left:0; width:82%; }.demo-app { min-height:250px; }.certificate-border { padding:14px 7px 20px; } }
@media(max-width:430px) { .hero-container { padding-top:105px; }.hero-product { height:320px; }.demo-app { min-height:215px; }.demo-dashboard h3 { font-size:11px; }.demo-metrics { gap:4px; margin-top:14px; }.demo-metrics div { padding:10px 2px; }.demo-table>div { padding:8px 4px; }.demo-certificate { min-height:280px; }.certificate-seal { width:28px; height:28px; margin:12px auto; }.demo-certificate h3 { font-size:10px; }.demo-certificate h4 { font-size:13px; }.demo-certificate p { font-size:6px; }.certificate-signature { font-size:17px; margin-top:12px; } }
</style>

<style scoped>
.institutional-hero { min-height:0; }
.hero-container { padding-top:56px; padding-bottom:40px; }
.hero-main { min-height:440px; }
.hero-product { height:400px; }
@media(min-width:1600px) { .hero-container { padding-top:64px; padding-bottom:44px; }.hero-main { min-height:460px; }.hero-product { height:420px; } }
@media(max-width:1100px) { .hero-container { padding-top:40px; padding-bottom:32px; }.hero-main { min-height:410px; }.hero-product { height:380px; } }
@media(max-width:767px) { .hero-container { padding:38px 22px 28px; }.hero-main { min-height:0; }.hero-product { height:350px; margin-top:12px; } }
@media(max-width:430px) { .hero-container { padding-top:30px; }.hero-product { height:310px; } }
</style>

<style scoped>
.institutional-hero { background:#071e3c; }
.hero-photo { position:absolute; inset:0; width:100%; height:100%; overflow:hidden; z-index:-2; pointer-events:none; }
.hero-overlay { background:linear-gradient(90deg,rgba(3,22,47,.22),rgba(3,22,47,.25) 42%,rgba(3,22,47,.05) 75%); }
.hero-container { max-width:1760px; padding:54px 5% 38px; }
.hero-main { grid-template-columns:minmax(0,.94fr) minmax(0,1.16fr); gap:24px; min-height:455px; }
.hero-copy { padding:18px 0; text-shadow:0 2px 15px #03182e70; }
.hero-copy h1 { font-size:clamp(35px,3.25vw,58px); }
.hero-description { max-width:570px; }
.hero-artwork { height:auto; align-self:end; position:relative; width:100%; margin:0; }
.hero-artwork img { display:block; width:100%; height:auto; object-fit:contain; filter:drop-shadow(0 15px 20px #0005); }
@media(min-width:1600px) { .hero-container { padding-top:62px; padding-bottom:40px; }.hero-main { min-height:480px; } }
@media(max-width:1100px) { .hero-container { padding:36px 4% 30px; }.hero-main { min-height:420px; gap:16px; }.hero-copy h1 { font-size:38px; }.hero-description { font-size:16px; }.hero-artwork { height:auto; } }
@media(max-width:767px) { .hero-container { padding:32px 22px 26px; }.hero-main { grid-template-columns:1fr; min-height:0; }.hero-copy h1 { font-size:36px; }.hero-overlay { background:linear-gradient(90deg,#03162f99,#03162f55); }.hero-photo { opacity:.65; }.hero-artwork { height:auto; max-width:570px; margin:4px auto 0; } }
@media(max-width:430px) { .hero-copy h1 { font-size:32px; }.hero-artwork { height:auto; } }
</style>

<style scoped>
.institutional-hero { aspect-ratio:1672 / 941; min-height:0; display:flex; }
.hero-photo { object-fit:contain; object-position:center; z-index:-2; }
.hero-overlay { background:linear-gradient(90deg,#03162f18,transparent 65%); }
.hero-container { width:100%; max-width:none; padding:4% 5% 5%; display:flex; align-items:center; }
.hero-main { width:100%; min-height:0; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:3%; }
.hero-copy h1 { max-width:650px; font-size:clamp(34px,3.1vw,58px); }
.hero-description { max-width:550px; font-size:clamp(15px,1.15vw,20px); }
.hero-artwork { width:88%; max-width:650px; margin:18% 0 0 auto; align-self:end; height:auto; }
.hero-artwork img { width:100%; height:auto; }
@media(min-width:1600px) { .hero-container { padding:4% 5% 5%; }.hero-main { min-height:0; } }
@media(max-width:1100px) { .hero-container { padding:4% 4%; }.hero-main { min-height:0; gap:2%; }.hero-copy h1 { font-size:34px; }.hero-artwork { width:92%; height:auto; } }
@media(max-width:767px) { .institutional-hero { aspect-ratio:auto; }.hero-photo { object-position:center top; opacity:1; }.hero-container { padding:90px 22px 28px; }.hero-main { grid-template-columns:1fr; gap:16px; }.hero-copy h1 { font-size:34px; }.hero-overlay { background:linear-gradient(0deg,#071e3c 30%,#071e3c70 70%,transparent); }.hero-artwork { width:85%; max-width:490px; margin:0 auto; height:auto; } }
@media(max-width:430px) { .hero-copy h1 { font-size:30px; }.hero-artwork { width:92%; } }
</style>

<style scoped>
.institutional-hero { height:100cqh; min-height:480px; aspect-ratio:auto; background:#102e57; }
.hero-photo { object-fit:contain; object-position:right center; }
.hero-overlay { background:linear-gradient(90deg,rgba(27,57,106,.98) 0%,rgba(27,57,106,.9) 24%,rgba(27,57,106,.65) 43%,rgba(27,57,106,.15) 66%,transparent 82%); }
.hero-container { padding:34px 5%; align-items:center; }
.hero-main { min-height:0; grid-template-columns:minmax(0,1fr) minmax(0,1fr); }
.hero-copy { max-width:650px; padding:12px 0; }
.hero-copy h1 { font-size:clamp(34px,3.1vw,56px); }
.hero-description { font-size:clamp(15px,1.15vw,19px); }
.hero-artwork { position:absolute; right:4%; bottom:4%; width:37%; max-width:650px; height:auto; margin:0; }
.hero-artwork img { width:100%; height:auto; }
@media(min-width:1600px) { .hero-container { padding:34px 5%; }.hero-main { min-height:0; } }
@media(max-height:700px) and (min-width:768px) { .hero-copy h1 { font-size:clamp(30px,2.8vw,46px); margin-bottom:18px; }.hero-eyebrow { margin-bottom:18px; }.hero-actions { margin-top:22px; }.hero-artwork { width:34%; bottom:3%; } }
@media(max-width:1100px) { .hero-container { padding:28px 4%; }.hero-artwork { width:39%; right:3%; bottom:5%; }.hero-copy h1 { font-size:34px; }.hero-description { font-size:15px; } }
@media(max-width:767px) { .institutional-hero { height:auto; min-height:100cqh; }.hero-photo { object-position:right top; }.hero-container { padding:46px 22px 24px; }.hero-main { grid-template-columns:1fr; gap:28px; }.hero-overlay { background:linear-gradient(90deg,#1b396af2,#1b396a9c 65%,#1b396a33); }.hero-copy h1 { font-size:34px; }.hero-artwork { position:relative; right:auto; bottom:auto; width:82%; max-width:470px; margin:0 0 0 auto; height:auto; } }
@media(max-width:430px) { .hero-copy h1 { font-size:30px; }.hero-artwork { width:90%; } }
</style>

<style scoped>
.institutional-hero { background:#071e3c; }
.hero-overlay { background:none; }
.hero-photo { opacity:1; }
.hero-artwork { right:3%; bottom:3%; width:39%; max-width:690px; }
@media(max-height:700px) and (min-width:768px) { .hero-artwork { width:35%; bottom:3%; } }
@media(max-width:1100px) { .hero-artwork { right:3%; bottom:4%; width:40%; } }
@media(max-width:767px) { .hero-overlay { background:none; }.hero-artwork { position:relative; right:auto; bottom:auto; width:86%; max-width:490px; margin:0 0 0 auto; } }
</style>

<style scoped>
.institutional-hero { background:transparent; }
.hero-photo { object-fit:fill; object-position:center; opacity:1; }
.hero-overlay { display:none; }
.hero-artwork { right:0; bottom:0; width:55%; max-width:1100px; margin:0; }
@media(max-height:700px) and (min-width:768px) { .hero-artwork { width:52%; right:0; bottom:0; } }
@media(max-width:1100px) { .hero-artwork { width:53%; right:0; bottom:0; } }
@media(max-width:767px) { .institutional-hero { background:transparent; }.hero-photo { object-fit:fill; }.hero-artwork { position:relative; width:100%; max-width:600px; right:auto; bottom:auto; margin:0 -22px -24px auto; align-self:end; }.hero-container { padding-bottom:24px; }.hero-overlay { display:none; } }
</style>

<style scoped>
.hero-overlay { display:block; background:linear-gradient(90deg,rgba(16,40,82,.96) 0%,rgba(27,57,106,.92) 30%,rgba(27,57,106,.68) 47%,rgba(27,57,106,.24) 62%,transparent 78%); }
.hero-copy h1 { font-size:clamp(42px,3.7vw,65px); }
.hero-description { font-size:clamp(16px,1.1vw,18px); line-height:1.7; color:#e2ebf8; text-align:justify; text-wrap:wrap; }
@media(max-height:700px) and (min-width:768px) { .hero-copy h1 { font-size:clamp(38px,3.3vw,56px); } }
@media(max-width:1100px) { .hero-copy h1 { font-size:44px; } }
@media(max-width:767px) { .hero-copy h1 { font-size:42px; } }
@media(max-width:430px) { .hero-copy h1 { font-size:37px; } }
</style>

<style scoped>
.hero-title-keep { white-space:nowrap; }
.hero-institution-badge { display:inline-flex; align-items:center; max-width:100%; padding:10px 20px; border:1px solid rgba(255,204,51,.65); border-radius:999px; background:rgba(255,204,51,.08); line-height:1.5; text-align:center; }
.hero-institution-badge::before { display:none; }
@media(max-width:430px) { .hero-institution-badge { font-size:10px; letter-spacing:.1em; padding:9px 14px; }.hero-copy h1 { font-size:clamp(28px,8.5vw,37px); } }
</style>

<style scoped>
.hero-photo { object-fit:cover; object-position:center; }
@media(max-width:767px) { .hero-photo { object-fit:cover; object-position:35% center; }.hero-overlay { display:block; background:#03162f80; } }
</style>

<style scoped>
.institutional-hero { height:auto; min-height:0; aspect-ratio:1672 / 941; background:#061a37; }
.hero-photo { object-fit:contain; object-position:center; }
.hero-container { padding:4% 5%; }
@media(max-width:767px) {
    .institutional-hero { aspect-ratio:auto; min-height:0; padding-top:56.28%; }
    .hero-photo { height:auto; aspect-ratio:1672 / 941; object-fit:contain; object-position:center top; }
    .hero-container { padding:28px 22px; background:#061a37; }
    .hero-overlay { display:none; }
}
</style>

<style scoped>
.institutional-hero { height:100cqh; min-height:0; aspect-ratio:auto; padding-top:0; }
.hero-photo { width:100%; height:100%; aspect-ratio:auto; object-fit:cover; object-position:center; }
.hero-container { width:100%; padding:34px 5%; background:transparent; }
@media(max-width:767px) {
    .institutional-hero { height:100cqh; min-height:560px; padding-top:0; }
    .hero-photo { height:100%; object-fit:cover; object-position:35% center; }
    .hero-container { padding:28px 22px; background:transparent; }
    .hero-overlay { display:block; background:linear-gradient(90deg,#03162fbb,#03162f40); }
}
</style>
