<script setup>
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Mail, Filter, ListChecks, Search, Eye, X } from 'lucide-vue-next';
const props = defineProps({ messages: Object, filters: Object, unreadCount: Number });
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const rows = ref(props.filters.rows || 10);
const selected = ref(null);
const filter = () => router.get(route('superadmin.contact-messages.index'), { search: search.value, status: status.value, rows: rows.value }, { preserveState: true, preserveScroll: true, replace: true });
const clearFilters = () => { search.value = ''; status.value = ''; rows.value = 10; filter(); };
const open = (message) => {
    selected.value = message;
    if (!message.read_at) router.patch(route('superadmin.contact-messages.update', message.id), {}, { preserveScroll: true });
};
const date = (value) => new Date(value).toLocaleString('es-MX');
</script>
<template>
<Head title="Mensajes de contacto" />
<SuperAdminLayout>
<div class="contact-page">
    <header class="page-heading"><h1>Mensajes de contacto</h1><p><Mail :size="18" /> Mensajes de contacto <span class="unread-count">{{ unreadCount }} sin leer</span></p></header>
    <form @submit.prevent="filter" class="filter-card">
        <div class="filter-heading"><h2><Filter :size="21" /> Filtro de Búsqueda</h2><button type="button" class="clear-button" @click="clearFilters"><ListChecks :size="18" /> Limpiar Filtros</button></div>
        <p class="filter-description">Buscar por remitente, correo, institución o contenido del mensaje</p>
        <div class="filter-fields">
            <label class="search-field">BUSCAR<div class="search-input"><Search :size="20" /><input v-model="search" placeholder="Buscar mensajes..." /><button type="submit" aria-label="Buscar mensajes">Buscar</button></div></label>
            <label>FILTRAR POR ESTADO<select v-model="status" @change="filter"><option value="">Todos los mensajes</option><option value="unread">Sin leer</option><option value="read">Leídos</option></select></label>
            <label>REGISTROS<select v-model="rows" @change="filter"><option v-for="count in [5, 10, 25, 50]" :key="count" :value="count">{{ count }} Registros</option></select></label>
        </div>
    </form>
    <div class="table-card"><div class="table-scroll">
        <table><thead><tr><th>REMITENTE</th><th>INSTITUCIÓN</th><th>FECHA</th><th>ESTADO</th><th class="actions">ACCIONES</th></tr></thead>
        <tbody><tr v-for="message in messages.data" :key="message.id"><td><strong>{{ message.name }}</strong><div class="email">{{ message.email }}</div></td><td>{{ message.institution_name }}</td><td class="date">{{ date(message.created_at) }}</td><td><span class="status-badge" :class="{ unread: !message.read_at }"><i></i>{{ message.read_at ? 'Leído' : 'Sin leer' }}</span></td><td class="actions"><button type="button" @click="open(message)" class="view-button"><Eye :size="18" /> Ver mensaje</button></td></tr>
        <tr v-if="!messages.data.length"><td colspan="5" class="empty">No hay mensajes para mostrar.</td></tr></tbody></table>
    </div><div class="table-footer"><Pagination :links="messages.links" :total="messages.total" /></div></div>
    <section v-if="selected" class="message-card" aria-labelledby="message-title">
        <div class="message-heading"><h2 id="message-title">Mensaje de {{ selected.name }}</h2><button type="button" @click="selected = null" aria-label="Cerrar mensaje"><X :size="20" /> Cerrar</button></div>
        <p class="message-meta">{{ selected.email }} · {{ selected.institution_name }} · {{ date(selected.created_at) }}</p>
        <p class="message-content">{{ selected.message }}</p>
    </section>
</div>
</SuperAdminLayout>
</template>
<style scoped>
.contact-page{padding:24px;display:flex;flex-direction:column;gap:24px;color:#111827}
.page-heading h1{font-size:30px;line-height:1.25;font-weight:700;margin:0 0 12px}.page-heading>p{display:flex;align-items:center;gap:10px;font-weight:600}.page-heading svg{color:#1b396a}.unread-count{margin-left:8px;padding:4px 10px;background:#eaf1fb;color:#1b396a;border-radius:6px;font-size:13px}
.filter-card,.table-card,.message-card{background:white;border:1px solid #e0e4eb;border-radius:9px;box-shadow:0 3px 5px #00000018}.filter-card,.message-card{padding:20px}.filter-heading,.message-heading{display:flex;align-items:center;justify-content:space-between;gap:16px}.filter-heading h2{display:flex;align-items:center;gap:12px;font-size:21px;font-weight:700}.clear-button{display:flex;align-items:center;gap:8px;padding:11px 16px;border:1px solid #cbd5e1;border-radius:8px;color:#1b396a;background:white;cursor:pointer}.filter-description{color:#64748b;margin:10px 0 20px;font-size:14px}.filter-fields{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px}.filter-fields label{font-size:12px;font-weight:600;color:#64748b;display:flex;flex-direction:column;gap:6px}.filter-fields select,.search-input{height:46px;border:1px solid #cbd5e1;border-radius:8px;background:#fafbfc;color:#1b396a;font-size:15px;font-weight:400}.filter-fields select{padding:0 14px}.search-input{display:flex;align-items:center;padding:0 12px;gap:10px;background:white}.search-input input{min-width:0;flex:1;outline:none;background:transparent;color:#111827}.search-input button{font-size:13px;font-weight:600;cursor:pointer}.search-input:focus-within,.filter-fields select:focus{outline:2px solid #93b4e0;outline-offset:1px}
.table-card{overflow:hidden}.table-scroll{overflow-x:auto}table{width:100%;text-align:left;font-size:14px}thead{background:#1b396a;color:white}th{padding:18px 24px;font-size:12px;font-weight:700}td{padding:18px 24px;border-bottom:1px solid #e2e8f0}tbody tr:hover{background:#f1f6fd}.email{margin-top:4px;color:#64748b;overflow-wrap:anywhere}.date{white-space:nowrap}.actions{text-align:center}.status-badge{display:inline-flex;align-items:center;gap:7px;border:1px solid #e2e8f0;background:white;padding:6px 11px;border-radius:7px;font-size:12px;color:#64748b;white-space:nowrap}.status-badge i{width:8px;height:8px;border-radius:50%;background:#94a3b8}.status-badge.unread{color:#9a6700;font-weight:600}.status-badge.unread i{background:#eab308}.view-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #1b396a;border-radius:8px;padding:9px 13px;color:#1b396a;font-weight:600;white-space:nowrap;cursor:pointer}.view-button:hover{background:#1b396a;color:white}.empty{height:145px;text-align:center;color:#64748b}.table-footer{padding:18px 24px;background:#f8f9fb}.message-heading h2{font-size:20px;font-weight:700}.message-heading button{display:flex;align-items:center;gap:6px;cursor:pointer;color:#64748b}.message-meta{font-size:13px;color:#64748b;margin-top:10px;overflow-wrap:anywhere}.message-content{margin-top:22px;white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.7}
@media(max-width:900px){.filter-fields{grid-template-columns:1fr 1fr}.search-field{grid-column:1/-1}.contact-page{padding:16px}.page-heading h1{font-size:25px}}
@media(max-width:540px){.filter-heading{align-items:flex-start;flex-direction:column}.filter-fields{grid-template-columns:1fr}.search-field{grid-column:auto}.table-footer{padding:14px 8px;overflow-x:auto}th,td{padding:14px 16px}}
</style>
