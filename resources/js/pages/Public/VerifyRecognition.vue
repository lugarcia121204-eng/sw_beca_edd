<template>
    <div class="min-h-screen bg-gray-50 flex py-6 sm:py-10 px-3 sm:px-4 justify-center items-start font-sans">
        <!-- Top Green Border Line Effect (like the screenshot) -->
        <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-2 sm:mt-4 relative">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-green-600 rounded-t-xl"></div>
            
            <!-- Header Section -->
            <div class="px-4 sm:px-8 pt-8 sm:pt-10 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-xl bg-green-500 text-white mb-5 shadow-sm shadow-green-200">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <!-- Title matching screenshot lines -->
                <h2 class="text-lg sm:text-xl font-bold text-green-600 mb-4 leading-snug px-2 sm:px-4">
                    Reconocimiento de<br>Participación Válido
                </h2>
                <!-- Validation Text matching screenshot -->
                <p class="text-sm sm:text-[15px] text-gray-600 leading-relaxed px-1 sm:px-2">
                    El documento con el siguiente folio<br>
                    es auténtico y ha sido verificado en<br>
                    nuestra base de datos:
                </p>
            </div>

            <!-- Content Section -->
            <div class="px-4 sm:px-8 pb-8">
                
                <div class="border-t border-gray-100 mb-6"></div>

                <!-- Institution & Director Details -->
                <div class="space-y-5 text-left pl-1">
                    <div>
                        <h3 class="text-[15px] font-bold text-black mb-1">Institución que avala y expide:</h3>
                        <p class="text-[15px] text-gray-700">TecNM</p>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black mb-1">{{ directorTitle ? (directorTitle.charAt(0).toUpperCase() + directorTitle.slice(1).toLowerCase()) : 'Director general' }}:</h3>
                        <p class="text-[15px] text-gray-700 leading-tight">
                            {{ directorName || 'VÍCTOR VÁZQUEZ LÓPEZ' }}
                        </p>
                    </div>
                </div>

                <!-- Separator and Participant Subtitle -->
                <div class="text-center mt-8 mb-6">
                    <h3 class="text-[16px] font-bold text-black inline-block px-4 relative">
                        Datos del Participante
                    </h3>
                </div>
                <div class="border-t border-gray-100 mb-6"></div>
                
                <!-- Participant Name & Announcement -->
                <div class="text-center mb-8 space-y-4">
                    <div>
                        <h4 class="text-[13px] font-bold text-gray-400 uppercase tracking-widest mb-1">Participante</h4>
                        <p class="text-sm sm:text-[16px] text-gray-800 uppercase font-medium">{{ participant || 'NO ESPECIFICADO' }}</p>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-gray-400 uppercase tracking-widest mb-1">Convocatoria</h4>
                        <p class="text-[14px] text-gray-600 uppercase">{{ announcementName || 'NO ESPECIFICADA' }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 mb-6"></div>

                <!-- Digital Seal Section -->
                <div class="text-center mb-8">
                    <h3 class="text-[16px] font-bold text-black mb-4">Sello Digital de Verificación</h3>
                    <div class="bg-white p-3 border border-gray-200 rounded-sm text-left shadow-sm max-h-32 sm:max-h-24 overflow-y-auto custom-scrollbar">
                        <p class="text-[13px] text-gray-600 font-mono break-all whitespace-pre-wrap leading-relaxed">
                            {{ digitalSeal || 'No se proporcionó sello digital' }}
                        </p>
                    </div>
                </div>

                <!-- Folio Section -->
                <div class="text-center">
                    <h3 class="text-[16px] font-bold text-black mb-4">Folio:</h3>
                    <div class="bg-gray-50/30 p-4 border border-gray-200 rounded-sm text-left shadow-sm min-h-[5rem] flex items-center">
                        <p class="text-[14px] text-gray-700 font-mono break-all w-full">
                            {{ identifier || 'NO ESPECIFICADO' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Scrollbar for the Digital Seal Box */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8; 
}
</style>

<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    identifier: String,
    participant: String,
    announcementName: String,
    digitalSeal: String,
    directorName: String,
    directorTitle: String,
});
</script>
