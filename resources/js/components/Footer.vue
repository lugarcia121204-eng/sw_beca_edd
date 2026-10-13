<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const currentYear = ref(new Date().getFullYear());

const updateYear = () => {
  currentYear.value = new Date().getFullYear();
};

onMounted(() => {
  updateYear();
});

</script>

<template>
    <div
        class="w-full text-white p-4 text-center bg-blue-950 dark:bg-gray-900"
        >
        © {{ currentYear }} Copyright:
        <a class="text-white" href="https://cenidet.tecnm.mx/">CENIDET</a>
    </div>
</template>