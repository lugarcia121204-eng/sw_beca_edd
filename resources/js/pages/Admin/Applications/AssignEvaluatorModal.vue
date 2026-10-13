<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">
                Seleccionar Evaluador
            </h2>
            <p class="text-sm text-gray-500 mb-6">
                Asigne un evaluador para revisar esta solicitud
            </p>

            <!-- Application Info -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg" v-if="application">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Profesor</p>
                        <p class="text-sm font-medium text-gray-900">{{ application.user?.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Convocatoria</p>
                        <p class="text-sm font-medium text-gray-900">{{ application.announcement?.name || 'General' }}</p>
                    </div>
                </div>
            </div>

            <!-- Selector -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Evaluador *</label>
                <div class="relative">
                    <v-select
                        v-model="selectedEvaluator"
                        :options="availableEvaluators"
                        label="name"
                        :reduce="eva => eva.id"
                        placeholder="Seleccione un evaluador"
                        class="style-chooser"
                        @update:modelValue="addEvaluator"
                    >
                        <template #no-options="{ search, searching }">
                            <template v-if="searching">
                                No se encontraron resultados para "<em>{{ search }}</em>".
                            </template>
                            <em v-else>Empiece a escribir para buscar un evaluador.</em>
                        </template>
                    </v-select>
                </div>
            </div>

            <!-- Selected Evaluators Table -->
            <div class="mb-6 border rounded-lg overflow-hidden" v-if="localEvaluators.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[var(--institutional-primary)] text-white">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-12">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(evaluator, index) in localEvaluators" :key="evaluator.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ evaluator.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    @click="removeEvaluator(index)"
                                    class="text-red-600 hover:text-red-900 transition-colors p-1 rounded-full hover:bg-red-50"
                                    title="Eliminar"
                                >
                                    <svg style="width:20px;height:20px" viewBox="0 0 24 24">
                                        <path fill="currentColor" :d="mdiDelete" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="mb-6 text-center py-4 bg-gray-50 rounded-lg border border-dashed text-gray-500 text-sm">
                No hay evaluadores seleccionados
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <button
                    @click="close"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                >
                    Cancelar
                </button>

                <button
                    @click="save"
                    class="px-4 py-2 bg-[var(--institutional-primary)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[var(--institutional-primary-dark)] focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="localEvaluators.length === 0 || form.processing"
                    :class="{'opacity-50 cursor-not-allowed': localEvaluators.length === 0 || form.processing}"
                >
                    <span v-if="form.processing">Asignando...</span>
                    <!-- User image shows 'Asignar' -->
                    <span v-else>Asignar</span>
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/components/Modal.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { mdiDelete } from '@mdi/js';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    show: Boolean,
    application: Object,
    evaluators: Array,
});

const emit = defineEmits(['close']);

const selectedEvaluator = ref(null);
const localEvaluators = ref([]);

// Form to submit
const form = useForm({
    application_id: null,
    evaluator_ids: [],
});

const availableEvaluators = computed(() => {
    const assignedIds = new Set(localEvaluators.value.map(e => e.id));
    
    if (props.application?.evaluations) {
        props.application.evaluations.forEach(ev => assignedIds.add(ev.evaluator_id));
    }

    return props.evaluators.filter(e => !assignedIds.has(e.id));
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        // Reset state when opening
        localEvaluators.value = [];
        selectedEvaluator.value = null;
        form.reset();
        form.clearErrors();
    }
});

const addEvaluator = (id) => {
    if (!id) return;
    const evaluator = props.evaluators.find(e => e.id === id);
    if (evaluator) {
        localEvaluators.value.push(evaluator);
    }
    selectedEvaluator.value = null; // Reset dropdown
};

const removeEvaluator = (index) => {
    localEvaluators.value.splice(index, 1);
};

const close = () => {
    emit('close');
};

const save = () => {
    form.application_id = props.application.id;
    form.evaluator_ids = localEvaluators.value.map(e => e.id);
    
    form.post(route('admin.applications.assign'), {
        preserveScroll: true,
        onSuccess: () => {
            close();
        }
    });
};
</script>

<style>
/* VueSelect Customization for Blueprint/TecNM style */
.style-chooser .vs__search::placeholder,
.style-chooser .vs__dropdown-toggle,
.style-chooser .vs__dropdown-menu {
    border-color: #d1d5db; /* gray-300 */
    border-radius: 0.375rem; /* rounded-md */
}
.style-chooser .vs__dropdown-toggle {
    padding-top: 2px;
    padding-bottom: 2px;
}
</style>

