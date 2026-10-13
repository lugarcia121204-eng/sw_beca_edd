<template>
    <div v-if="links && links.length > 0" class="flex items-center justify-between">
        <div class="w-[180px]"></div>
        
        <div class="flex items-center gap-1.5">
            <ul class="flex items-center gap-1.5">
                <!-- Primera página (doble flecha izquierda) -->
                <li>
                    <Link 
                        :class="[
                            'inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors',
                            firstPageUrl && links.length > 1 && !links[1]?.active
                                ? 'text-gray-600 hover:bg-gray-100 cursor-pointer' 
                                : 'text-gray-300 cursor-not-allowed'
                        ]" 
                        :href="firstPageUrl || '#'" 
                        @click="firstPageUrl && (isLoading = true)"
                        :disabled="!firstPageUrl || (links.length > 1 && links[1]?.active)"
                        :preserve-scroll="true"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M440-240 200-480l240-240 56 56-183 184 183 184-56 56Zm264 0L464-480l240-240 56 56-183 184 183 184-56 56Z"/>
                        </svg>
                    </Link>
                </li>
                
                <!-- Página anterior (flecha izquierda simple) -->
                <li>
                    <Link 
                        :class="[
                            'inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors',
                            links[0]?.url 
                                ? 'text-gray-600 hover:bg-gray-100 cursor-pointer' 
                                : 'text-gray-300 cursor-not-allowed'
                        ]" 
                        :href="links[0]?.url || '#'" 
                        @click="links[0]?.url && (isLoading = true)"
                        :disabled="!links[0]?.url"
                        :preserve-scroll="true"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M560-240 320-480l240-240 56 56-184 184 184 184-56 56Z"/>
                        </svg>
                    </Link>
                </li>

                <!-- Números de página -->
                <li v-for="(link, key) in links.slice(1, -1)" :key="key">
                    <Link 
                        :class="[
                            'inline-flex items-center justify-center w-9 h-9 rounded-full font-medium text-sm transition-colors',
                            link.active 
                                ? 'bg-[var(--institutional-primary)] text-white shadow-md' 
                                : 'text-gray-700 hover:bg-gray-100 bg-gray-50'
                        ]" 
                        :href="link.url || '#'" 
                        @click="link.url && (isLoading = true)"
                        :preserve-scroll="true"
                    >
                        {{ link.label }}
                    </Link>
                </li>

                <!-- Página siguiente (flecha derecha simple) -->
                <li>
                    <Link 
                        :class="[
                            'inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors',
                            links[links.length - 1]?.url 
                                ? 'text-gray-600 hover:bg-gray-100 cursor-pointer' 
                                : 'text-gray-300 cursor-not-allowed'
                        ]" 
                        :href="links[links.length - 1]?.url || '#'" 
                        @click="links[links.length - 1]?.url && (isLoading = true)"
                        :disabled="!links[links.length - 1]?.url"
                        :preserve-scroll="true"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/>
                        </svg>
                    </Link>
                </li>

                <!-- Última página (doble flecha derecha) -->
                <li>
                    <Link 
                        :class="[
                            'inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors',
                            lastPageUrl && links.length > 2 && !links[links.length - 2]?.active
                                ? 'text-gray-600 hover:bg-gray-100 cursor-pointer' 
                                : 'text-gray-300 cursor-not-allowed'
                        ]" 
                        :href="lastPageUrl || '#'" 
                        @click="lastPageUrl && (isLoading = true)"
                        :disabled="!lastPageUrl || (links.length > 2 && links[links.length - 2]?.active)"
                        :preserve-scroll="true"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                            <path d="M383-480 200-664l56-56 240 240-240 240-56-56 183-184Zm264 0L464-664l56-56 240 240-240 240-56-56 183-184Z"/>
                        </svg>
                    </Link>
                </li>
            </ul>
        </div>

        <div v-if="showTotal" class="w-[180px] text-sm text-gray-600 text-right">
            <span class="font-medium">Total de registros: {{ total }}</span>
        </div>
        <div v-else class="w-[180px]"></div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Loading from 'vue-loading-overlay';
import { ref, computed } from "vue";

export default {
    inheritAttrs: false,
    props: { 
        links: {
            type: Array,
            required: true,
            default: () => []
        },
        total: {
            type: Number,
            default: 0
        },
        from: {
            type: Number,
            default: 0
        },
        to: {
            type: Number,
            default: 0
        },
        showTotal: {
            type: Boolean,
            default: true
        }
    },
    components: {
        Link,
        Loading,
    },
    setup(props) {
        const isLoading = ref(false)
        
        // Obtener URL de primera página
        const firstPageUrl = computed(() => {
            if (!props.links || props.links.length === 0) return null;
            const firstPageLink = props.links[1]; // El primer número de página
            if (!firstPageLink || !firstPageLink.url) return null;
            
            // Extraer la URL base y cambiar el parámetro page a 1
            try {
                const url = new URL(firstPageLink.url, window.location.origin);
                url.searchParams.set('page', '1');
                return url.toString();
            } catch (e) {
                return null;
            }
        });
        
        // Obtener URL de última página
        const lastPageUrl = computed(() => {
            if (!props.links || props.links.length === 0) return null;
            const lastPageLink = props.links[props.links.length - 2]; // El último número de página
            if (!lastPageLink || !lastPageLink.url) return null;
            
            // Extraer el número de la última página desde el label
            const lastPageNumber = lastPageLink.label;
            try {
                const url = new URL(lastPageLink.url, window.location.origin);
                url.searchParams.set('page', lastPageNumber);
                return url.toString();
            } catch (e) {
                return null;
            }
        });
        
        return { isLoading, firstPageUrl, lastPageUrl };
    },
};
</script>
