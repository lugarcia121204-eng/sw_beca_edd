<script setup>
import { Head, Link } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
defineProps({ authorizationStatus: String });
</script>
<template>
    <EvaluatorLayout>
        <Head title="Autorización de evaluador" />
        <section class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-[#1b396a]">{{ authorizationStatus === 'suspended' ? 'Acceso de evaluador suspendido' : 'Registro pendiente de autorización' }}</h1>
            <p class="mt-4 text-slate-600">Un administrador debe autorizar tu cuenta y asignar el nivel Local, Estatal o Nacional y su ámbito de evaluación antes de que puedas consultar expedientes.</p>
            <p class="mt-3 text-slate-600">Si necesitas una actualización, contacta a la administración del programa.</p>
            <Link :href="route('evaluator.dashboard')" class="mt-6 inline-block rounded-lg bg-[#1b396a] px-5 py-3 text-white">Comprobar autorización</Link>
        </section>
    </EvaluatorLayout>
</template>
