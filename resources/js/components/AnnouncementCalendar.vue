<script setup>
import { ref, computed, watch } from 'vue';
import { CheckCircle, Clock, ClipboardCheck, Award } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

// ─── Phase configuration ────────────────────────────────────────────────────
const phases = [
    {
        key:        'publicacion',
        label:      'Publicación',
        number:     '01',
        desc:       'Publicación de la Convocatoria.',
        color:      '#1B396A',
        colorLight: '#dce6f5',
        startKey:   'publication_start',
        endKey:     null,           // single day
        iconName:   'CheckCircle'
    },
    {
        key:        'registro',
        label:      'Registro',
        number:     '02',
        desc:       'Periodo para Inscribirse.',
        color:      '#10A558',
        colorLight: '#d0f2e3',
        startKey:   'registration_start',
        endKey:     'registration_end',
        iconName:   'Clock'
    },
    {
        key:        'evaluacion',
        label:      'Evaluación',
        number:     '03',
        desc:       'Periodo de Revisión.',
        color:      '#C9A800',
        colorLight: '#fdf6d0',
        startKey:   'evaluation_start',
        endKey:     'evaluation_end',
        iconName:   'ClipboardCheck'
    },
    {
        key:        'resultados',
        label:      'Resultados',
        number:     '04',
        desc:       'Publicación de Resultados.',
        color:      '#2B6CB0',
        colorLight: '#dbeafe',
        startKey:   'results_start',
        endKey:     'results_end',
        iconName:   'Award'
    }
];

const getIconComponent = (name) => {
    const icons = { CheckCircle, Clock, ClipboardCheck, Award };
    return icons[name];
};

// ─── State ───────────────────────────────────────────────────────────────────
const activePhase    = ref(null);   // key of the phase being configured
const dragStart      = ref(null);   // Date object
const dragEnd        = ref(null);   // Date object
const isDragging     = ref(false);
const hoverDay       = ref(null);
const errMessage     = ref('');

// Local dates - sync from props
const dates = ref({ ...props.modelValue });

watch(() => props.modelValue, (v) => { dates.value = { ...v }; }, { deep: true });

// ─── Progressive & Context Logic ─────────────────────────────────────────────
const unlockedPhases = computed(() => {
    const list = [];
    list.push('publicacion'); // First is always enabled
    
    if (dates.value.publication_start) list.push('registro');
    if (dates.value.registration_start && dates.value.registration_end) list.push('evaluacion');
    if (dates.value.evaluation_start && dates.value.evaluation_end) list.push('resultados');
    
    return list;
});

const editablePhases = computed(() => {
    // If it's a new announcement, all unlocked phases are editable
    if (!props.isEdit) return unlockedPhases.value;
    
    // In edit mode, we restrict phases that have already finished
    const now = new Date();
    now.setHours(0,0,0,0);
    
    return phases.filter(ph => {
        const endStr = ph.endKey ? dates.value[ph.endKey] : dates.value[ph.startKey];
        if (!endStr) return unlockedPhases.value.includes(ph.key);
        
        const end = parseDate(endStr);
        return end >= now;
    }).map(ph => ph.key);
});

const isDayDisabled = (day) => {
    if (!day) return true;
    const now = new Date();
    now.setHours(0,0,0,0);
    
    // 1. Disable past days only if not editing
    if (!props.isEdit && day < now) return true;
    
    if (!activePhase.value) return false;
    
    const phIdx = phases.findIndex(p => p.key === activePhase.value);
    
    // 2. Can't select dates before or on the previous phase's end date
    if (phIdx > 0) {
        const prevPh = phases[phIdx - 1];
        const prevEndStr = prevPh.endKey ? dates.value[prevPh.endKey] : dates.value[prevPh.startKey];
        if (prevEndStr) {
            const prevEnd = parseDate(prevEndStr);
            if (day <= prevEnd) return true;
        }
    }
    
    // 3. Can't select dates after next phase's start date
    if (phIdx < phases.length - 1) {
        const nextPh = phases[phIdx + 1];
        const nextStartStr = dates.value[nextPh.startKey];
        if (nextStartStr) {
            const nextStart = parseDate(nextStartStr);
            if (day >= nextStart) return true;
        }
    }

    return false;
};

// ─── Calendar navigation ─────────────────────────────────────────────────────
const today = new Date();
const viewYear  = ref(today.getFullYear());
const viewMonth = ref(today.getMonth()); // Base month for the multi-view

const MONTHS_ES = [
    'Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
];
const DAYS_ES = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

const prevMonth = () => {
    if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value--; }
    else viewMonth.value--;
};
const nextMonth = () => {
    if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++; }
    else viewMonth.value++;
};

// ─── Drag Selection Life Cycle ───────────────────────────────────────────────
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    window.addEventListener('mouseup', onMouseUp);
});

onUnmounted(() => {
    window.removeEventListener('mouseup', onMouseUp);
});

// Returns year/month for the Nth offset month from viewYear/viewMonth
const getOffsetMonth = (offset) => {
    let m = viewMonth.value + offset;
    let y = viewYear.value;
    while (m > 11) { m -= 12; y++; }
    while (m < 0) { m += 12; y--; }
    return { year: y, month: m };
};

// Build grid of days for a specific year/month
const getDaysInMonth = (y, m) => {
    const firstDay = new Date(y, m, 1).getDay();
    const daysInMonthCount = new Date(y, m + 1, 0).getDate();
    const days = [];
    for (let i = 0; i < firstDay; i++) days.push(null);
    for (let d = 1; d <= daysInMonthCount; d++) {
        days.push(new Date(y, m, d));
    }
    return days;
};

const visibleMonths = computed(() => {
    return [0, 1, 2, 3].map(offset => {
        const { year, month } = getOffsetMonth(offset);
        return {
            year,
            month,
            label: `${MONTHS_ES[month]} ${year}`,
            days: getDaysInMonth(year, month)
        };
    });
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
const toDateStr = (d) => {
    if (!d) return '';
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const parseDate = (s) => {
    if (!s) return null;
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
};

const sameDay = (a, b) => a && b && toDateStr(a) === toDateStr(b);

// Which phase (if any) covers this date?
const phaseForDay = (day) => {
    if (!day) return null;
    const ds = toDateStr(day);
    for (const ph of phases) {
        const s = dates.value[ph.startKey];
        const e = ph.endKey ? dates.value[ph.endKey] : s;
        if (s && ds >= s && ds <= (e || s)) return ph;
    }
    return null;
};

// During active drag, highlight preview range
const isInDragPreview = (day) => {
    if (!day || !activePhase.value || !isDragging.value || !dragStart.value) return false;
    const end = dragEnd.value || dragStart.value;
    const lo = dragStart.value <= end ? dragStart.value : end;
    const hi = dragStart.value <= end ? end : dragStart.value;
    return day >= lo && day <= hi;
};

const formatRange = (phase) => {
    const s = dates.value[phase.startKey];
    if (!s) return 'Sin configurar';
    const start = parseDate(s);
    const startStr = start.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
    if (!phase.endKey) return startStr;
    const e = dates.value[phase.endKey];
    if (!e) return startStr;
    const end = parseDate(e);
    return `${startStr} – ${end.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })}`;
};

const phaseConfigured = (phase) => !!dates.value[phase.startKey];

// ─── Validation Logic ────────────────────────────────────────────────────────
const validateDates = (newDates) => {
    errMessage.value = '';
    
    // Order of sequences to check
    const order = [
        { name: 'Publicación', start: 'publication_start', end: 'publication_start' },
        { name: 'Registro', start: 'registration_start', end: 'registration_end' },
        { name: 'Evaluación', start: 'evaluation_start', end: 'evaluation_end' },
        { name: 'Resultados', start: 'results_start', end: 'results_end' }
    ];

    for (let i = 0; i < order.length; i++) {
        const current = order[i];
        const curStart = newDates[current.start];
        const curEnd = newDates[current.end];

        if (curStart && curEnd && curStart > curEnd) {
            errMessage.value = `Error en ${current.name}: La fecha de inicio no puede ser posterior a la de fin.`;
            return false;
        }

        if (i > 0) {
            const prev = order[i - 1];
            const prevEnd = newDates[prev.end];

            // In edit mode, skip sequence check if both phases are already concluded (non-editable)
            const currentPhase = phases.find(p => p.startKey === current.start);
            const prevPhase = phases.find(p => p.startKey === prev.start);
            const bothConcluded = props.isEdit
                && currentPhase && !editablePhases.value.includes(currentPhase.key)
                && prevPhase && !editablePhases.value.includes(prevPhase.key);

            if (!bothConcluded && prevEnd && curStart && curStart <= prevEnd) {
                errMessage.value = `Advertencia: La etapa de ${current.name} debe iniciar después de que termine la etapa de ${prev.name}.`;
                return false;
            }
        }
    }
    return true;
};

// ─── Interaction ─────────────────────────────────────────────────────────────
const selectPhase = (phaseKey) => {
    if (!unlockedPhases.value.includes(phaseKey)) {
        errMessage.value = 'Debes configurar las etapas anteriores primero.';
        return;
    }
    if (!editablePhases.value.includes(phaseKey)) {
        errMessage.value = 'Esta etapa ya ha concluido y no se puede editar.';
        return;
    }
    activePhase.value = activePhase.value === phaseKey ? null : phaseKey;
    dragStart.value = null;
    dragEnd.value = null;
    isDragging.value = false;
    errMessage.value = '';
};

const onMouseDown = (day) => {
    if (!day || !activePhase.value || isDayDisabled(day)) return;

    const phase = phases.find(p => p.key === activePhase.value);
    if (!phase) return;

    isDragging.value = true;
    dragStart.value = day;
    dragEnd.value = day;
};

const onMouseEnter = (day) => {
    hoverDay.value = day;
    if (isDragging.value && day && !isDayDisabled(day)) {
        dragEnd.value = day;
    }
};

const onMouseUp = () => {
    if (!isDragging.value || !dragStart.value || !dragEnd.value) {
        isDragging.value = false;
        // Don't reset dragStart here if we want to allow single clicks (optional)
        return;
    }

    const phase = phases.find(p => p.key === activePhase.value);
    if (!phase) {
        isDragging.value = false;
        return;
    }

    const lo = dragStart.value <= dragEnd.value ? dragStart.value : dragEnd.value;
    const hi = dragStart.value <= dragEnd.value ? dragEnd.value : dragStart.value;

    if (!phase.endKey) {
        // Single day phase (Publication)
        applyPhaseDates(phase, lo, lo);
    } else {
        // Range phase
        applyPhaseDates(phase, lo, hi);
    }

    isDragging.value = false;
    dragStart.value = null;
    dragEnd.value = null;
};

// We keep onDayClick as a fallback or for single-click configuration
const onDayClick = (day) => {
    // With drag-and-drop, onDayClick might be redundant, 
    // but useful for accessibility or quick single-day range selection
    if (!day || !activePhase.value || isDayDisabled(day)) return;
    
    const phase = phases.find(p => p.key === activePhase.value);
    if (!phase) return;

    if (!phase.endKey) {
        applyPhaseDates(phase, day, day);
    }
};

const applyPhaseDates = (phase, start, end) => {
    const updated = { ...dates.value };
    updated[phase.startKey] = toDateStr(start);
    if (phase.endKey) updated[phase.endKey] = toDateStr(end);
    
    if (validateDates(updated)) {
        dates.value = updated;
        emit('update:modelValue', updated);
        errMessage.value = '';
        return true;
    }
    return false;
};

const clearPhase = (phase) => {
    if (!editablePhases.value.includes(phase.key)) return;
    
    const updated = { ...dates.value };
    const idx = phases.findIndex(p => p.key === phase.key);
    
    // Clear current and all subsequent phases (progressive requirement)
    for (let i = idx; i < phases.length; i++) {
        const p = phases[i];
        updated[p.startKey] = '';
        if (p.endKey) updated[p.endKey] = '';
    }
    
    dates.value = updated;
    emit('update:modelValue', updated);
    
    // Bug fix: Reset active selection to null to avoid staying on a now-locked phase
    activePhase.value = null;
    errMessage.value = '';
    dragStart.value = null;
    dragEnd.value = null;
};

// ─── Day style ───────────────────────────────────────────────────────────────
const dayStyle = (day) => {
    if (!day) return {};

    const ph = phaseForDay(day);
    const disabled = isDayDisabled(day);

    // 1. Drag/Multi-click preview takes priority
    if (activePhase.value && (isDragging.value || dragStart.value) && isInDragPreview(day)) {
        const phActive = phases.find(p => p.key === activePhase.value);
        return { background: phActive.colorLight, color: phActive.color, borderRadius: '6px', fontWeight: '700' };
    }

    // 2. If it belongs to a configured phase, show its color
    if (ph) {
        return { 
            background: ph.color, 
            color: '#fff', 
            borderRadius: '6px', 
            fontWeight: '700',
            cursor: disabled ? 'not-allowed' : (activePhase.value ? 'crosshair' : 'pointer'),
            opacity: disabled && activePhase.value ? '0.6' : '1'
        };
    }

    // 3. Otherwise, if it's disabled (past or locked), show gray
    if (disabled) {
        return { background: '#F9FAFB', color: '#D1D5DB', cursor: 'not-allowed', opacity: '0.6' };
    }

    return {};
};

const dayLabel = (day) => {
    if (!day) return '';
    const ph = phaseForDay(day);
    if (!ph) return '';
    if (ph.endKey) {
        const s = dates.value[ph.startKey];
        const e = dates.value[ph.endKey];
        if (s === toDateStr(day)) return ph.label.slice(0, 3).toUpperCase();
        if (e === toDateStr(day)) return 'FIN';
    } else {
        return ph.label.slice(0, 3).toUpperCase();
    }
    return '';
};
</script>

<template>
    <div class="space-y-6 select-none">

        <!-- Phase selector cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button
                v-for="phase in phases"
                :key="phase.key"
                type="button"
                @click="selectPhase(phase.key)"
                class="relative flex flex-col items-start gap-2 p-4 rounded-xl border-2 text-left transition-all duration-200 focus:outline-none"
                :class="[
                    activePhase === phase.key
                        ? 'shadow-lg scale-105'
                        : 'hover:shadow-md hover:scale-[1.02] bg-white border-gray-200',
                    !unlockedPhases.includes(phase.key) || !editablePhases.includes(phase.key) ? 'opacity-50 grayscale-[0.5]' : ''
                ]"
                :style="activePhase === phase.key
                    ? { borderColor: phase.color, background: phase.colorLight }
                    : {}"
            >
                <!-- Number badge -->
                <span
                    class="absolute top-3 right-3 text-xs font-bold px-2 py-0.5 rounded-full"
                    :style="{ background: phase.color, color: '#fff' }"
                >{{ phase.number }}</span>

                <!-- Icon -->
                <div class="p-2 rounded-lg" :style="{ background: activePhase === phase.key ? phase.color : '#F3F4F6' }">
                    <component 
                        :is="getIconComponent(phase.iconName)"
                        class="w-6 h-6 block"
                        :style="{ color: activePhase === phase.key ? '#fff' : phase.color }"
                    />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-gray-900 flex items-center gap-1">
                        {{ phase.label }}
                        <svg v-if="!unlockedPhases.includes(phase.key)" class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </p>
                    <p class="text-xs text-gray-500 leading-tight">{{ phase.desc }}</p>
                </div>

                <!-- Date display -->
                <div class="w-full mt-1">
                    <div
                        v-if="phaseConfigured(phase)"
                        class="flex items-center justify-between gap-1"
                    >
                        <p class="text-xs font-semibold truncate" :style="{ color: phase.color }">
                            {{ formatRange(phase) }}
                        </p>
                        <button
                            type="button"
                            @click.stop="clearPhase(phase)"
                            class="p-1.5 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition flex items-center justify-center cursor-pointer shadow-sm ml-2"
                            title="Limpiar"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Sin configurar</p>
                </div>

                <!-- Active indicator ring -->
                <div
                    v-if="activePhase === phase.key"
                    class="absolute inset-0 rounded-xl pointer-events-none"
                    :style="{ boxShadow: `0 0 0 3px ${phase.color}` }"
                ></div>
            </button>
        </div>

        <!-- Global Navigation -->
        <div class="flex items-center justify-center gap-8 bg-white py-3 px-6 rounded-xl border border-gray-200 shadow-sm">
            <button type="button" @click="prevMonth"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-[var(--institutional-primary)] transition font-bold border border-gray-100 hover:border-[var(--institutional-primary)] cursor-pointer">
                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/>
                </svg>
                Anterior
            </button>
            <div class="h-6 w-px bg-gray-200"></div>
            <button type="button" @click="nextMonth"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-[var(--institutional-primary)] transition font-bold border border-gray-100 hover:border-[var(--institutional-primary)] cursor-pointer">
                Siguiente
                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Error / Warning message (Simplified Minimalist) -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="errMessage" 
                class="relative px-5 py-4 rounded-lg border-l-4 border-red-500 bg-white shadow-sm flex items-center gap-4"
            >
                <div class="flex-shrink-0 text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-red-400 mb-0.5">Atención</span>
                    <span class="text-sm font-bold text-gray-800 leading-tight">{{ errMessage }}</span>
                </div>

                <button @click="errMessage = ''" class="ml-auto p-2 text-gray-300 hover:text-red-500 transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </transition>

        <!-- Instruction banner (Simplified Minimalist) -->
        <transition enter-active-class="transition duration-500 ease-out" enter-from-class="transform -translate-y-4 opacity-0 scale-95" enter-to-class="transform translate-y-0 opacity-100 scale-100" leave-active-class="transition duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div
                v-if="activePhase"
                class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                :style="{ 
                    borderLeft: `5px solid ${phases.find(p => p.key === activePhase)?.color}`
                }"
            >
                <div class="flex-shrink-0" :style="{ color: phases.find(p => p.key === activePhase)?.color || '#1B396A' }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold tracking-widest opacity-60 mb-0.5" :style="{ color: phases.find(p => p.key === activePhase)?.color || '#1B396A' }">
                        Instrucciones
                    </span>
                    <span class="text-sm font-bold leading-tight text-gray-800">
                        Configurando: <strong :style="{ color: phases.find(p => p.key === activePhase)?.color || '#1B396A' }">{{ phases.find(p => p.key === activePhase)?.label }}</strong> —
                        {{ phases.find(p => p.key === activePhase)?.endKey
                            ? 'Selecciona la fecha inicial y luego la final haciendo clic y arrastrando.'
                            : 'Selecciona el día de publicación con un solo clic.' }}
                    </span>
                </div>

                <button type="button" @click="activePhase = null" class="ml-auto p-2 text-gray-300 hover:opacity-60 transition-all cursor-pointer" :style="{ hoverColor: phases.find(p => p.key === activePhase)?.color }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </transition>

        <!-- Multi-Month Calendar Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div v-for="mInfo in visibleMonths" :key="`${mInfo.year}-${mInfo.month}`" 
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                
                <!-- Month header -->
                <div class="flex items-center justify-center px-6 py-4 border-b border-gray-100 bg-gray-50/30">
                    <h3 class="text-base font-bold text-gray-800">{{ mInfo.label }}</h3>
                </div>

                <!-- Day headers -->
                <div class="grid grid-cols-7 border-b border-gray-100 bg-white">
                    <div v-for="d in DAYS_ES" :key="d"
                        class="py-2 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                    >{{ d }}</div>
                </div>

                <!-- Day grid -->
                <div class="grid grid-cols-7 gap-1 p-3 bg-white flex-1" :class="activePhase ? 'cursor-crosshair' : 'cursor-default'">
                    <div
                        v-for="(day, idx) in mInfo.days"
                        :key="idx"
                        class="relative flex flex-col items-center justify-start pt-1 pb-1 min-h-[56px] rounded-lg transition-all duration-100 group"
                        :class="[
                            !day ? 'invisible' : (activePhase ? 'hover:ring-2 hover:ring-offset-1' : ''),
                            day && sameDay(day, new Date()) && !phaseForDay(day) ? 'ring-1 ring-gray-400' : ''
                        ]"
                        :style="[
                            dayStyle(day),
                            activePhase && day ? { '--tw-ring-color': phases.find(p => p.key === activePhase)?.color } : {}
                        ]"
                        @mouseenter="onMouseEnter(day)"
                        @mousedown="onMouseDown(day)"
                        @click="onDayClick(day)"
                    >
                        <!-- Past day indicator (if isEdit) -->
                        <div v-if="props.isEdit && day && day < new Date().setHours(0,0,0,0) && !phaseForDay(day)" 
                             class="absolute inset-0 bg-gray-50/50 rounded-lg pointer-events-none"></div>
                        <!-- Day number -->
                        <span v-if="day" class="text-sm font-bold leading-none"
                            :class="phaseForDay(day) || isInDragPreview(day) ? '' : 'text-gray-700'">
                            {{ day.getDate() }}
                        </span>

                        <!-- Phase label badge -->
                        <span v-if="day && dayLabel(day)" class="mt-1 text-[8px] font-extrabold leading-none tracking-tighter opacity-90">
                            {{ dayLabel(day) }}
                        </span>



                        <!-- Active Start Date indicator -->
                        <div v-if="dragStart && sameDay(day, dragStart)" 
                             class="absolute -bottom-1 left-1/2 -translate-x-1/2">
                            <div class="w-1.5 h-1.5 rounded-full bg-white ring-3 ring-black/40"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center justify-center gap-6 px-6 py-4 border border-gray-200 bg-gray-50 rounded-xl shadow-inner">
            <div v-for="phase in phases" :key="phase.key" class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-md shadow-sm" :style="{ background: phase.color }"></div>
                <span class="text-xs font-semibold text-gray-700">{{ phase.label }}</span>
            </div>
        </div>

        <!-- Hidden inputs for form compatibility -->
        <div class="hidden">
            <input v-for="phase in phases" :key="phase.startKey"
                :name="phase.startKey" :value="dates[phase.startKey]" type="text" readonly>
            <input v-for="phase in phases.filter(p => p.endKey)" :key="phase.endKey"
                :name="phase.endKey" :value="dates[phase.endKey]" type="text" readonly>
        </div>
    </div>
</template>

<style scoped>
.select-none {
    user-select: none;
    -webkit-user-select: none;
}
</style>
