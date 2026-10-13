<script setup>

import SectionMain from '@/components/SectionMain.vue';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';

</script>

<template>
    <LayoutAuthenticated>
        <SectionMain>
            <slot />
        </SectionMain>
    </LayoutAuthenticated>
</template>
