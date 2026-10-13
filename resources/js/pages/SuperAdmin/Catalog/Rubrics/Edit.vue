<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiBookOpenPageVariant, mdiClipboardTextOutline } from '@mdi/js';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    title: String,
    routeName: String,
    rubric: Object,
});

const form = useForm({
    title: props.rubric.data.title,
    is_active: props.rubric.data.is_active,
    questions: props.rubric.data.questions.map(q => ({
        id: q.id,
        text: q.text,
        options: q.options.map(o => ({
            id: o.id,
            text: o.text,
            score: o.score
        }))
    }))
});

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const addQuestion = () => {
    form.questions.push({
        text: '',
        options: []
    });
};

const removeQuestion = (index) => {
    form.questions.splice(index, 1);
};

const addOption = (qIndex) => {
    const question = form.questions[qIndex];
    if (question.options.length >= 5) {
        alertaError('Límite alcanzado', 'Máximo 5 respuestas por pregunta.');
        return;
    }
    question.options.push({ text: '', score: 0 });
};

const removeOption = (qIndex, oIndex) => {
    form.questions[qIndex].options.splice(oIndex, 1);
};

// Validar puntajes únicos y rango
const validateScore = (qIndex, oIndex) => {
    const question = form.questions[qIndex];
    const currentScore = question.options[oIndex].score;
    
    // Limpiar error previo
    delete form.errors[`questions.${qIndex}.options.${oIndex}.score`];
    
    if (currentScore < 1 || currentScore > 5) {
        form.errors[`questions.${qIndex}.options.${oIndex}.score`] = 'Puntaje: 1-5';
        return;
    }

    // Verificar duplicados
    const duplicate = question.options.some((opt, idx) => idx !== oIndex && opt.score === currentScore);
    if (duplicate) {
        form.errors[`questions.${qIndex}.options.${oIndex}.score`] = 'Puntaje duplicado';
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    
    // Validación del lado del cliente
    if (!form.title || form.title.trim() === '') {
        form.errors.title = 'El título de la rúbrica es obligatorio';
        return;
    }
    
    if (form.questions.length === 0) {
        alertaError('Faltan preguntas', 'Debes agregar al menos una pregunta a la rúbrica');
        return;
    }
    
    // Validar cada pregunta
    for (let i = 0; i < form.questions.length; i++) {
        const q = form.questions[i];
        
        if (!q.text || q.text.trim() === '') {
            form.errors[`questions.${i}.text`] = 'El texto de la pregunta es obligatorio';
            return;
        }
        
        if (q.options.length < 2) {
            form.errors[`questions.${i}.text`] = 'Debe tener al menos 2 opciones de respuesta';
            return;
        }
        
        // Validar cada opción
        for (let j = 0; j < q.options.length; j++) {
            const opt = q.options[j];
            if (!opt.text || opt.text.trim() === '') {
                form.errors[`questions.${i}.options.${j}.text`] = 'El texto de la respuesta es obligatorio';
                return;
            }
            if (!opt.score || opt.score < 1 || opt.score > 5) {
                form.errors[`questions.${i}.options.${j}.score`] = 'Puntaje: 1-5';
                return;
            }
        }
        
        // Validar puntajes únicos
        const scores = q.options.map(o => o.score);
        const uniqueScores = new Set(scores);
        if (scores.length !== uniqueScores.size) {
            // Marcar las opciones con puntajes duplicados
            const scoreCount = {};
            q.options.forEach((opt, idx) => {
                if (scoreCount[opt.score]) {
                    form.errors[`questions.${i}.options.${idx}.score`] = 'Puntaje duplicado';
                } else {
                    scoreCount[opt.score] = true;
                }
            });
            return;
        }
    }
    
    alertaCargando('Actualizando', 'Por favor espera mientras se actualiza la rúbrica');
    
    form.put(route('catalog.rubrics.update', props.rubric.data.id), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Rúbrica actualizada correctamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Hubo un problema al actualizar la rúbrica');
        }
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBookOpenPageVariant"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Catálogo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <Link :href="route('catalog.rubrics.index')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)] transition flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #6B7280;">
                                <path :d="mdiClipboardTextOutline"/>
                            </svg>
                            Rúbricas
                        </Link>
                         <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Editar Rúbrica</span>
                    </div>
                </div>
                <Link :href="route('catalog.rubrics.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                     <!-- Título -->
                    <div>
                        <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Título de la Rúbrica: <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.title }" placeholder="Escribe el titulo..." @input="clearError('title')">
                        <div v-if="!form.errors.title" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Por favor, introduce el título de la rúbrica</span>
                        </div>
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <!-- Preguntas -->
                    <div v-for="(question, qIndex) in form.questions" :key="qIndex" class="border border-gray-200 p-4 rounded-lg bg-gray-50 relative">
                        <div class="mb-4">
                             <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Pregunta {{ qIndex + 1 }}: <span class="text-red-500">*</span></label>
                             <div class="flex gap-2">
                                 <input v-model="question.text" type="text" class="bg-white border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors[`questions.${qIndex}.text`] }" placeholder="Escribe la pregunta..." @input="clearError(`questions.${qIndex}.text`)">
                                 <button type="button" @click="removeQuestion(qIndex)" class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition cursor-pointer" title="Eliminar pregunta">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                                 </button>
                             </div>
                             <p v-if="form.errors[`questions.${qIndex}.text`]" class="mt-1 text-sm text-red-600">{{ form.errors[`questions.${qIndex}.text`] }}</p>
                        </div>

                        <div class="flex justify-start mb-4">
                            <button type="button" @click="addOption(qIndex)" class="px-3 py-1.5 bg-[var(--institutional-primary)] text-white rounded text-xs hover:bg-[#0f2347] transition flex items-center gap-1 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor"><path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/></svg>
                                Agregar Respuesta
                            </button>
                        </div>

                        <div class="space-y-3 pl-4 border-l-2 border-gray-200">
                            <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex flex-col md:flex-row gap-4 items-start border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <div class="w-full md:flex-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Respuesta {{ oIndex + 1 }}:</label>
                                    <input v-model="option.text" type="text" class="bg-white border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors[`questions.${qIndex}.options.${oIndex}.text`] }" placeholder="Escribe la respuesta..." @input="clearError(`questions.${qIndex}.options.${oIndex}.text`)">
                                    <p v-if="form.errors[`questions.${qIndex}.options.${oIndex}.text`]" class="text-red-600 text-xs mt-1">{{ form.errors[`questions.${qIndex}.options.${oIndex}.text`] }}</p>
                                </div>
                                <div class="w-full md:w-32 flex gap-2 items-end">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Puntaje (1-5):</label>
                                         <input v-model="option.score" type="number" class="bg-white border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors[`questions.${qIndex}.options.${oIndex}.score`] }" @input="validateScore(qIndex, oIndex)">
                                    </div>
                                    <button type="button" @click="removeOption(qIndex, oIndex)" class="p-2 mb-[1px] text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition cursor-pointer" title="Eliminar respuesta">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                                    </button>
                                </div>
                                <div class="w-full sm:w-auto md:hidden">
                                     <p v-if="form.errors[`questions.${qIndex}.options.${oIndex}.score`]" class="text-red-600 text-xs mt-1">{{ form.errors[`questions.${qIndex}.options.${oIndex}.score`] }}</p>
                                </div>
                                <div class="hidden md:block w-32 -ml-32 mt-16">
                                     <p v-if="form.errors[`questions.${qIndex}.options.${oIndex}.score`]" class="text-red-600 text-xs mt-1">{{ form.errors[`questions.${qIndex}.options.${oIndex}.score`] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mt-4 border-t pt-4 border-dashed border-gray-300">
                         <button type="button" @click="addQuestion" class="px-6 py-2 bg-white text-[var(--institutional-primary)] border-2 border-[var(--institutional-primary)] rounded-lg hover:bg-[var(--institutional-primary)] hover:text-white transition flex items-center gap-2 text-sm font-bold w-full justify-center cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/></svg>
                            Agregar Nueva Pregunta
                        </button>
                    </div>

                    <!-- Footer Buttons -->
                    <div v-if="form.errors.is_active" class="col-span-12 text-red-600 text-sm mb-4 text-right">
                        {{ form.errors.is_active }}
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route('catalog.rubrics.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer">
                            <span v-if="!form.processing">Actualizar</span>
                            <span v-else>Actualizando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

