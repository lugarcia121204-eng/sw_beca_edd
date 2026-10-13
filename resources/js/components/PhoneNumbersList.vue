<template>
    <div v-if="phoneNumbers.length > 0">
        <table>
            <thead class="text-gray-700 bg-gray-100">
                <tr>
                    <th>Teléfono</th>
                    <th>Extensión</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(phone, index) in phoneNumbers" :key="index">
                    <td>{{ phone.number }}</td>
                    <td>{{ phone.dial_code || 'N/A' }}</td>
                    <td>
                        <Badge color="gray" size="md" variant="soft">
                            {{ phone.type || 'General' }}
                        </Badge>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <CardBoxComponentEmpty v-else message="No hay números de contacto disponibles" />
</template>

<script setup>
import Badge from '@/components/Badge.vue';
import CardBoxComponentEmpty from '@/components/CardBoxComponentEmpty.vue';


defineProps({
    phoneNumbers: {
        type: Array,
        default: () => []
    }
});
</script>
