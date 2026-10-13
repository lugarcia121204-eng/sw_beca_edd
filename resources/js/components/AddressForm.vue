<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <FormField label="Código Postal" :error="props.errors?.['location.postal_code']">
            <FormControl v-model="form.postal_code" type="text" placeholder="12345" maxlength="5"
                @change="() => onPostalCodeChange(form.postal_code)" />
        </FormField>

        <FormField label="Estado" :error="props.errors?.['location.state_id']">
            <FormControl v-model="form.state_id" :options="states" value-select="id" value-option="name"
                @change="() => onStateChange(form.state_id)" />
        </FormField>

        <FormField label="Municipio" :error="props.errors?.['location.municipality_id']">
            <FormControl v-model="form.municipality_id" :options="municipalities" value-select="id" value-option="name"
                :disabled="!form.state_id || municipalities.length === 0"
                @change="() => onMunicipalityChange(form.municipality_id)" />
        </FormField>

        <FormField label="Colonia" :error="props.errors?.['location.neighborhood_id']">
            <FormControl v-model="form.neighborhood_id" :options="filteredNeighborhoods" value-select="id"
                value-option="name" :disabled="!form.municipality_id || neighborhoods.length === 0" />
        </FormField>

        <FormField label="Calle" :error="props.errors?.['location.street']">
            <FormControl v-model="form.street" type="text" placeholder="Calle principal" />
        </FormField>

        <FormField label="Número Exterior" :error="props.errors?.['location.exterior_number']">
            <FormControl v-model="form.exterior_number" type="text" placeholder="Número exterior" />
        </FormField>

        <FormField label="Número Interior" :error="props.errors?.['location.interior_number']">
            <FormControl v-model="form.interior_number" type="text" placeholder="Número interior (opcional)" />
        </FormField>

        <div class="md:col-span-2">
            <FormField label="Ubicación en el mapa"
                :error="props.errors?.['location.latitude'] || props.errors?.['location.longitude']">
                <div class="space-y-4">
                    <div class="flex gap-2">
                        <BaseButton @click="geocodeAddress('manual')" :icon="mdiMagnify"
                            color="bg-forest-400 text-mono-100" label="Buscar dirección en mapa" small />
                    </div>
                    <div ref="mapContainer" class="w-full h-64 rounded-lg border border-gray-300"
                        style="min-height: 256px;" />
                </div>
            </FormField>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, nextTick } from 'vue';
import { mdiMagnify } from '@mdi/js';
import FormField from './FormField.vue';
import FormControl from './FormControl.vue';
import BaseButton from './BaseButton.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useAddressManagement } from '@/Hooks/useAddressManagement.js';
import { useMapManagement } from '@/Hooks/useMapManagement.js';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png'
});

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            state_id: null,
            municipality_id: null,
            neighborhood_id: null,
            postal_code: null,
            street: null,
            interior_number: null,
            exterior_number: null,
            latitude: null,
            longitude: null
        })
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue']);

const form = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const {
    states,
    municipalities,
    neighborhoods,
    onStateChange,
    onMunicipalityChange,
    onPostalCodeChange
} = useAddressManagement(form);

const {
    mapContainer,
    initMap,
    geocodeAddress,
    setupAutoGeocode
} = useMapManagement(form, states, municipalities, neighborhoods);

const filteredNeighborhoods = computed(() => {
    if (!form.value.postal_code || form.value.postal_code.length !== 5) {
        return neighborhoods.value;
    }
    return neighborhoods.value.filter(neighborhood =>
        neighborhood.postal_code === form.value.postal_code
    );
});

onMounted(() => {
    nextTick(() => {
        initMap();
        setupAutoGeocode();
    });
});
</script>
