<template>
    <div v-if="socialLinks.length > 0">
        <table>
            <thead class="text-gray-700 bg-gray-100">
                <tr>
                    <th>Red social</th>
                    <th>Enlace</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(socialLink, index) in socialLinks" :key="index">
                    <td>{{ socialLink.type }}</td>
                    <td>{{ socialLink.url }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <CardBoxComponentEmpty v-else message="No hay enlaces sociales disponibles" />
</template>

<script setup>
import CardBoxComponentEmpty from '@/components/CardBoxComponentEmpty.vue';

defineProps({
    socialLinks: {
        type: Array,
        default: () => []
    }
})
</script>
