<script setup>
import Pagination from '@/Shared/Pagination.vue';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Filter,
    ListChecks,
    Download,
    Files,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
const props = defineProps({ documents: Object, filters: Object });
const search = ref(props.filters.search || '');
const rows = ref(props.filters.rows || 10);
const clearFilters = () => { search.value = ''; rows.value = 10; filter(); };
const editing = ref(null);
const open = ref(false);
const fileInput = ref(null);
const categories = {
    formatos: 'Formatos',
    guias: 'Guías y normativa',
    convocatoria: 'Convocatoria',
    privacidad: 'Privacidad',
};
const form = useForm({
    title: '',
    description: '',
    category: 'formatos',
    published: true,
    file: null,
});
const begin = (document = null) => {
    editing.value = document;
    form.reset();
    form.clearErrors();
    if (document) {
        form.title = document.title;
        form.description = document.description || '';
        form.category = document.category;
        form.published = document.published;
    }
    if (fileInput.value) fileInput.value.value = '';
    open.value = true;
};
const save = () => {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    };
    if (editing.value)
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            route('superadmin.work-documents.update', editing.value.id),
            options,
        );
    else
        form.transform((data) => data).post(
            route('superadmin.work-documents.store'),
            options,
        );
};
const remove = (document) => {
    if (
        window.confirm(
            'Eliminar "' +
                document.title +
                '"? El archivo dejará de estar disponible en la página pública.',
        )
    )
        router.delete(route('superadmin.work-documents.destroy', document.id), {
            preserveScroll: true,
        });
};
const filter = () =>
    router.get(
        route('superadmin.work-documents.index'),
        { search: search.value, rows: rows.value },
        { preserveState: true, replace: true },
    );
const size = (bytes) =>
    bytes >= 1048576
        ? (bytes / 1048576).toFixed(1) + ' MB'
        : Math.ceil(bytes / 1024) + ' KB';
</script>
<template>
    <SuperAdminLayout
        ><Head title="Documentos de Trabajo" />
        <div class="work-page">
            <header class="page-header">
                <div>
                    <h1>Documentos de Trabajo</h1>
                    <p>
                        Administra los archivos que aparecen en Documentos de la
                        página pública.
                    </p>
                </div>
                <button class="primary" @click="begin()">
                    <Plus :size="18" /> Subir documento
                </button>
            </header>
            <p v-if="$page.props.flash?.success" role="status" class="notice">
                {{ $page.props.flash.success }}
            </p>
            <section
                v-if="open"
                class="panel editor"
                aria-labelledby="editor-title"
            >
                <header>
                    <h2 id="editor-title">
                        {{ editing ? 'Editar documento' : 'Nuevo documento' }}
                    </h2>
                    <button
                        :disabled="form.processing"
                        aria-label="Cerrar formulario"
                        @click="open = false"
                    >
                        <X :size="20" />
                    </button>
                </header>
                <form @submit.prevent="save">
                    <div class="form-grid">
                        <div>
                            <label for="title">Nombre del documento</label
                            ><input
                                id="title"
                                v-model="form.title"
                                required
                                maxlength="180"
                            />
                            <p class="error">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label for="category">Categoría</label
                            ><select id="category" v-model="form.category">
                                <option
                                    v-for="(label, key) in categories"
                                    :key="key"
                                    :value="key"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <p class="error">{{ form.errors.category }}</p>
                        </div>
                        <div class="full">
                            <label for="description">Descripción</label
                            ><textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                maxlength="2000"
                            />
                            <p class="error">{{ form.errors.description }}</p>
                        </div>
                        <div class="full">
                            <label for="file">{{
                                editing
                                    ? 'Reemplazar archivo (opcional)'
                                    : 'Archivo'
                            }}</label
                            ><input
                                id="file"
                                ref="fileInput"
                                type="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                :required="!editing"
                                @change="
                                    form.file = $event.target.files[0] || null
                                "
                            />
                            <p class="hint">
                                PDF, Word, Excel o PowerPoint. Máximo 20 MB.
                                {{
                                    editing
                                        ? 'Si no seleccionas otro archivo, se conserva el actual.'
                                        : ''
                                }}
                            </p>
                            <p class="error">{{ form.errors.file }}</p>
                        </div>
                    </div>
                    <label class="checkbox"
                        ><input v-model="form.published" type="checkbox" />
                        Publicar en la página de Documentos</label
                    >
                    <p class="hint">
                        Si desmarcas esta opción, el documento solo estará
                        disponible en este catálogo.
                    </p>
                    <p class="error">{{ form.errors.published }}</p>
                    <p v-if="form.progress" role="status" class="hint">
                        Subiendo archivo: {{ form.progress.percentage }} %
                    </p>
                    <footer>
                        <button
                            type="button"
                            class="secondary"
                            :disabled="form.processing"
                            @click="open = false"
                        >
                            Cancelar</button
                        ><button class="primary" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar documento'
                            }}
                        </button>
                    </footer>
                </form>
            </section>
            <form class="panel filter-panel" @submit.prevent="filter">
                <div class="filter-heading"><h2><Filter :size="21" /> Filtro de Búsqueda</h2><button type="button" class="secondary" @click="clearFilters"><ListChecks :size="18" /> Limpiar Filtros</button></div>
                <p class="filter-description">Buscar por nombre o descripción del documento</p>
                <div class="filter-fields"><label>BUSCAR<div class="filter-search"><Search :size="20" /><input v-model="search" placeholder="Buscar documento..." aria-label="Buscar documento" /><button type="submit">Buscar</button></div></label><label>REGISTROS<select v-model="rows" @change="filter"><option v-for="count in [5, 10, 25, 50]" :key="count" :value="count">{{ count }} Registros</option></select></label></div>
            </form>
            <section class="panel table-panel">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>DOCUMENTO</th>
                                <th>CATEGORÍA</th>
                                <th>PUBLICACIÓN</th>
                                <th class="actions-heading">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="document in documents.data"
                                :key="document.id"
                            >
                                <td>
                                    <strong>{{ document.title }}</strong>
                                    <p class="description">
                                        {{ document.description }}
                                    </p>
                                    <span class="hint"
                                        >{{ document.file_name }} ·
                                        {{ size(document.file_size) }}</span
                                    >
                                </td>
                                <td>{{ categories[document.category] }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="{
                                            published: document.published,
                                        }"
                                        >{{
                                            document.published
                                                ? 'Publicado'
                                                : 'Oculto'
                                        }}</span
                                    >
                                </td>
                                <td>
                                    <div class="actions">
                                        <a
                                            :href="
                                                route(
                                                    'superadmin.work-documents.download',
                                                    document.id,
                                                )
                                            "
                                            class="action"
                                            :aria-label="
                                                'Descargar ' + document.title
                                            "
                                            ><Download :size="17" /></a
                                        ><button
                                            class="action"
                                            :aria-label="
                                                'Editar ' + document.title
                                            "
                                            @click="begin(document)"
                                        >
                                            <Pencil :size="17" /></button
                                        ><button
                                            class="action danger"
                                            :aria-label="
                                                'Eliminar ' + document.title
                                            "
                                            @click="remove(document)"
                                        >
                                            <Trash2 :size="17" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!documents.data.length">
                                <td colspan="4" class="empty">
                                    <Files :size="32" /><strong
                                        >No hay documentos</strong
                                    >
                                    <p>Sube un archivo o cambia la búsqueda.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer"><Pagination :links="documents.links" :total="documents.total" /></div>
            </section>
            <a
                :href="route('documents.index')"
                target="_blank"
                rel="noopener"
                class="public-link"
                >Ver Documentos en la página pública →</a
            >
        </div></SuperAdminLayout
    >
</template>
<style scoped>
.work-page {
    max-width: 1320px;
    margin: auto;
    color: #102344;
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.page-header,
.toolbar,
.editor > header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
}
.page-header h1 {
    font-size: 28px;
    font-weight: 700;
}
.page-header p,
.hint {
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
}
.page-header p {
    font-size: 14px;
    margin-top: 6px;
}
.primary,
.secondary,
.action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
}
.primary {
    background: var(--institutional-primary, #1b396a);
    color: white;
}
.primary:hover {
    background: #102344;
}
.secondary,
.action {
    border: 1px solid #cbd5e1;
    background: white;
}
.action {
    padding: 8px;
    color: #1b396a;
}
.danger,
.error {
    color: #b91c1c;
}
.error {
    font-size: 12px;
    margin-top: 4px;
}
.panel {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
h2 {
    font-size: 16px;
    font-weight: 600;
}
.editor {
    padding: 24px;
}
.editor form {
    margin-top: 20px;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
.full {
    grid-column: 1/-1;
}
label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}
input:not([type='checkbox']),
textarea,
select {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: white;
    padding: 10px 12px;
    font-size: 14px;
    color: #102344;
}
input:focus,
textarea:focus,
select:focus {
    outline: 2px solid #93c5fd;
    outline-offset: 1px;
}
.checkbox {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 20px;
}
input[type='checkbox'] {
    accent-color: #1b396a;
}
.editor footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}
.toolbar {
    padding: 20px 24px;
}
.search {
    display: flex;
    gap: 8px;
    max-width: 360px;
}
.table-scroll {
    overflow-x: auto;
}
table {
    width: 100%;
    text-align: left;
    font-size: 13px;
}
th {
    background: #1b396a;
    color: white;
    font-size: 12px;
    padding: 16px 24px;
}
td {
    padding: 18px 24px;
    border-bottom: 1px solid #edf1f6;
}
td:first-child {
    min-width: 260px;
    max-width: 500px;
}
.description {
    color: #64748b;
    margin: 5px 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
.actions {
    display: flex;
    gap: 8px;
}
.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 11px;
}
.published {
    background: #ecfdf5;
    color: #166534;
}
.pagination {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 18px 24px;
    font-size: 12px;
    color: #64748b;
}
.pagination > div {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.pagination a,
.pagination .disabled {
    padding: 6px 9px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
.pagination .current {
    background: #1b396a;
    color: white;
}
.disabled {
    opacity: 0.5;
}
.empty {
    text-align: center;
    padding: 48px;
}
.empty svg {
    margin: 0 auto 12px;
}
.empty strong {
    display: block;
}
.public-link {
    font-size: 13px;
    color: #1b396a;
}
.notice {
    background: #ecfdf5;
    padding: 12px;
    color: #166534;
    border-radius: 8px;
    font-size: 13px;
}
button:disabled {
    opacity: 0.6;
    cursor: wait;
}
button:focus-visible,
a:focus-visible {
    outline: 3px solid #93c5fd;
    outline-offset: 3px;
}
@media (max-width: 700px) {
    .page-header,
    .toolbar {
        align-items: stretch;
        flex-direction: column;
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
    .search {
        max-width: none;
    }
    .editor {
        padding: 18px;
    }
}

.work-page{max-width:none;margin:0;padding:24px;color:#111827}.page-header h1{font-size:30px}.panel{border-radius:9px;box-shadow:0 3px 5px #00000018}.filter-panel{padding:20px}.filter-heading{display:flex;align-items:center;justify-content:space-between;gap:16px}.filter-heading h2{display:flex;align-items:center;gap:12px;font-size:21px;font-weight:700}.filter-description{color:#64748b;margin:10px 0 20px;font-size:14px}.filter-fields{display:grid;grid-template-columns:3fr 1fr;gap:16px}.filter-fields label{font-size:12px;color:#64748b;margin:0}.filter-fields select{margin-top:6px;height:46px;background:#fafbfc}.filter-search{margin-top:6px;display:flex;align-items:center;gap:10px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;height:46px;color:#1b396a}.filter-search input{border:0;padding:0;min-width:0;flex:1;outline:none}.filter-search:focus-within{outline:2px solid #93b4e0}.filter-search button{font-size:13px;font-weight:600;cursor:pointer}th{padding:18px 24px}table{font-size:14px}tbody tr:hover{background:#f1f6fd}.actions,.actions-heading{justify-content:center;text-align:center}.action{border-radius:50%;border-color:#1b396a}.action:hover{background:#1b396a;color:white}.action.danger{border-color:#b91c1c}.action.danger:hover{background:#b91c1c;color:white}.badge{border-radius:7px;border:1px solid #e2e8f0;background:white;padding:6px 11px;font-size:12px}.published{color:#166534}.table-footer{padding:18px 24px;background:#f8f9fb}.empty{height:145px}
@media(max-width:700px){.work-page{padding:16px}.page-header h1{font-size:25px}.filter-fields{grid-template-columns:1fr}.filter-heading{align-items:flex-start;flex-direction:column}.table-footer{padding:14px 8px;overflow-x:auto}}
</style>
