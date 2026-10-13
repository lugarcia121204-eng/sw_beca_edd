<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import DynamicLayout from '@/layouts/DynamicLayout.vue';
import EyeIcon from '@/components/icons/EyeIcon.vue';
import EyeOffIcon from '@/components/icons/EyeOffIcon.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false
    },
    status: {
        type: String,
        default: null
    },
    user: {
        type: Object,
        required: true
    },
    priorityAreas: {
        type: Array,
        default: () => []
    },
    subAreas: {
        type: Array,
        default: () => []
    },
    institutions: {
        type: Array,
        default: () => []
    }
});

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    institution_id: props.user.institution_id,
    priority_area_id: props.user.priority_area_id,
    sub_area_id: props.user.sub_area_id,
});

const isSuperAdmin = computed(() => {
    return props.user.roles && props.user.roles.some(role => role.name === 'Super Admin');
});

const filteredSubAreas = computed(() => {
    if (!profileForm.priority_area_id) return [];
    return props.subAreas.filter(subArea => subArea.priority_area_id === profileForm.priority_area_id);
});

watch(() => profileForm.priority_area_id, (newValue) => {
    // Reset sub area when priority area changes
    if (profileForm.sub_area_id) {
        const subAreaExists = filteredSubAreas.value.some(sa => sa.id === profileForm.sub_area_id);
        if (!subAreaExists) {
            profileForm.sub_area_id = null;
        }
    }
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const clearProfileError = (field) => {
    if (profileForm.errors[field]) {
        delete profileForm.errors[field];
    }
};

const clearPasswordError = (field) => {
    if (passwordForm.errors[field]) {
        delete passwordForm.errors[field];
    }
};

const updateProfile = () => {
    profileForm.clearErrors();

    if (!profileForm.name) {
        profileForm.errors.name = 'El nombre completo es obligatorio';
        return;
    }
    if (!profileForm.email) {
        profileForm.errors.email = 'El correo electrónico es obligatorio';
        return;
    }
    if (!isSuperAdmin.value) {
        if (!profileForm.institution_id) {
            profileForm.errors.institution_id = 'Debes seleccionar una institución';
            return;
        }
        if (!profileForm.priority_area_id) {
            profileForm.errors.priority_area_id = 'Debes seleccionar un área prioritaria';
            return;
        }
        if (!profileForm.sub_area_id) {
            profileForm.errors.sub_area_id = 'Debes seleccionar una sub área';
            return;
        }
    }

    alertaCargando('Actualizando perfil', 'Por favor espera...');
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Perfil actualizado correctamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Por favor verifica los datos ingresados');
        }
    });
};

const updatePassword = () => {
    passwordForm.clearErrors();

    if (!passwordForm.current_password) {
        passwordForm.errors.current_password = 'La contraseña actual es obligatoria';
        return;
    }
    if (!passwordForm.password) {
        passwordForm.errors.password = 'La nueva contraseña es obligatoria';
        return;
    }
    if (!passwordForm.password_confirmation) {
        passwordForm.errors.password_confirmation = 'Debes confirmar la nueva contraseña';
        return;
    }
    if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordForm.errors.password_confirmation = 'Las contraseñas no coinciden';
        return;
    }

    alertaCargando('Actualizando contraseña', 'Por favor espera...');
    passwordForm.put(route('user-password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Contraseña actualizada correctamente');
            passwordForm.reset();
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Por favor verifica los datos ingresados');
        }
    });
};
</script>

<template>
    <DynamicLayout>
        <Head title="Perfil" />

        <div class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-[var(--institutional-primary)]">Información del Perfil</h2>
                    <p class="text-sm text-gray-600 mt-1">Actualiza tu información personal</p>
                </div>

                <form @submit.prevent="updateProfile" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Nombre Completo: <span class="text-red-500">*</span></label>
                        <input
                            id="name"
                            v-model="profileForm.name"
                            type="text"
                            class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                            :class="{ 'border-b-red-500': profileForm.errors.name }"
                            placeholder="Tu nombre completo"
                            @input="clearProfileError('name')"
                        />
                        <div v-if="!profileForm.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Por favor, introduce tu nombre completo</span>
                        </div>
                        <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Correo Electrónico: <span class="text-red-500">*</span></label>
                        <input
                            id="email"
                            v-model="profileForm.email"
                            type="email"
                            class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                            :class="{ 'border-b-red-500': profileForm.errors.email }"
                            placeholder="tu@email.com"
                            @input="clearProfileError('email')"
                        />
                        <div v-if="!profileForm.errors.email" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Por favor, introduce tu correo electrónico</span>
                        </div>
                        <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">{{ profileForm.errors.email }}</p>
                    </div>

                    <!-- Institution (only for non-SuperAdmin) -->
                    <div v-if="!isSuperAdmin">
                        <label for="institucion" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Institución: <span class="text-red-500">*</span></label>
                        <VueSelect
                            v-model="profileForm.institution_id"
                            :options="institutions"
                            :reduce="institution => institution.id"
                            label="name"
                            placeholder="Selecciona una institución"
                            class="vue-select-custom"
                            :class="{ 'vue-select-error': profileForm.errors.institution_id }"
                            @option:selected="clearProfileError('institution_id')"
                            @option:deselected="clearProfileError('institution_id')"
                        />
                        <div v-if="!profileForm.errors.institution_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selecciona la institución a la que perteneces</span>
                        </div>
                        <p v-if="profileForm.errors.institution_id" class="mt-1 text-sm text-red-600">{{ profileForm.errors.institution_id }}</p>
                    </div>

                    <!-- Priority Area (only for non-SuperAdmin) -->
                    <div v-if="!isSuperAdmin">
                        <label for="priority_area" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Área Prioritaria: <span class="text-red-500">*</span></label>
                        <VueSelect
                            v-model="profileForm.priority_area_id"
                            :options="priorityAreas"
                            :reduce="area => area.id"
                            label="name"
                            placeholder="Selecciona un área prioritaria"
                            class="vue-select-custom"
                            :class="{ 'vue-select-error': profileForm.errors.priority_area_id }"
                            @option:selected="clearProfileError('priority_area_id')"
                            @option:deselected="clearProfileError('priority_area_id')"
                        />
                        <div v-if="!profileForm.errors.priority_area_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selecciona el área prioritaria de tu trabajo</span>
                        </div>
                        <p v-if="profileForm.errors.priority_area_id" class="mt-1 text-sm text-red-600">{{ profileForm.errors.priority_area_id }}</p>
                    </div>

                    <!-- Sub Area (only for non-SuperAdmin) -->
                    <div v-if="!isSuperAdmin">
                        <label for="sub_area" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Sub Área: <span class="text-red-500">*</span></label>
                        <VueSelect
                            v-model="profileForm.sub_area_id"
                            :options="filteredSubAreas"
                            :reduce="subArea => subArea.id"
                            label="name"
                            placeholder="Selecciona una sub área"
                            :searchable="true"
                            :clearable="true"
                            class="vue-select-custom"
                            :class="{ 'vue-select-error': profileForm.errors.sub_area_id }"
                            @option:selected="clearProfileError('sub_area_id')"
                            @option:deselected="clearProfileError('sub_area_id')"
                        >
                            <template #no-options="{ search, searching }">
                                <template v-if="searching">
                                    No se encontraron resultados para <em>{{ search }}</em>.
                                </template>
                                <em v-else>Selecciona un Área Prioritaria primero...</em>
                            </template>
                        </VueSelect>
                        <div v-if="!profileForm.errors.sub_area_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selecciona la sub área correspondiente</span>
                        </div>
                        <p v-if="profileForm.errors.sub_area_id" class="mt-1 text-sm text-red-600">{{ profileForm.errors.sub_area_id }}</p>
                    </div>

                    <!-- Email Verification -->
                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            Tu correo electrónico no está verificado.
                        </p>
                    </div>


                    <!-- Submit Button -->
                    <div class="flex justify-stretch sm:justify-end">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="w-full sm:w-auto px-6 py-2 cursor-pointer bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center justify-center gap-2 font-medium"
                        >
                            <span v-if="!profileForm.processing">Guardar Cambios</span>
                            <span v-else>Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-[var(--institutional-primary)]">Actualizar Contraseña</h2>
                    <p class="text-sm text-gray-600 mt-1">Asegúrate de usar una contraseña larga y segura</p>
                </div>

                <form @submit.prevent="updatePassword" class="space-y-5">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Contraseña Actual: <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 pr-10 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': passwordForm.errors.current_password }"
                                placeholder="••••••••"
                                @input="clearPasswordError('current_password')"
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition cursor-pointer"
                            >
                                <EyeIcon v-if="!showCurrentPassword" size="20" class="text-gray-600" />
                                <EyeOffIcon v-else size="20" class="text-gray-600" />
                            </button>
                        </div>
                        <div v-if="!passwordForm.errors.current_password" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Introduce tu contraseña actual para confirmar el cambio</span>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Nueva Contraseña: <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 pr-10 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': passwordForm.errors.password }"
                                placeholder="••••••••"
                                @input="clearPasswordError('password')"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition cursor-pointer"
                            >
                                <EyeIcon v-if="!showPassword" size="20" class="text-gray-600" />
                                <EyeOffIcon v-else size="20" class="text-gray-600" />
                            </button>
                        </div>
                        <div v-if="!passwordForm.errors.password" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Introduce la nueva contraseña</span>
                        </div>
                        <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium">Confirmar Nueva Contraseña: <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 pr-10 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]"
                                :class="{ 'border-b-red-500': passwordForm.errors.password_confirmation }"
                                placeholder="••••••••"
                                @input="clearPasswordError('password_confirmation')"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition cursor-pointer"
                            >
                                <EyeIcon v-if="!showPasswordConfirmation" size="20" class="text-gray-600" />
                                <EyeOffIcon v-else size="20" class="text-gray-600" />
                            </button>
                        </div>
                        <div v-if="!passwordForm.errors.password_confirmation" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Repite la nueva contraseña para confirmar</span>
                        </div>
                        <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password_confirmation }}</p>
                    </div>


                    <!-- Submit Button -->
                    <div class="flex justify-stretch sm:justify-end">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="w-full sm:w-auto px-6 py-2 cursor-pointer bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center justify-center gap-2 font-medium"
                        >
                            <span v-if="!passwordForm.processing">Actualizar Contraseña</span>
                            <span v-else>Actualizando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DynamicLayout>
</template>

<style scoped>
.vue-select-custom :deep(.vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
    border: none !important;
    border-bottom: 2px solid #D1D5DB !important;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    box-shadow: none;
    transition: all 0.2s;
    min-height: 45px;
}

.vue-select-custom :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}

.vue-select-custom :deep(.vs--open .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
    border-bottom-color: #1B396A;
}

.vue-select-custom :deep(.vs__search) {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 0.875rem;
    color: #111827;
}

.vue-select-custom :deep(.vs__search::placeholder) {
    color: #9CA3AF;
}

.vue-select-custom :deep(.vs__selected) {
    margin: 0;
    padding: 0;
    border: none;
    color: #111827;
    font-size: 0.875rem;
    font-weight: 500;
}

.vue-select-custom :deep(.vs__actions) {
    padding: 0 4px 0 6px;
}

.vue-select-custom :deep(.vs__clear),
.vue-select-custom :deep(.vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

.vue-select-custom :deep(.vs__open-indicator) {
    transform: scale(0.70);
}

.vue-select-custom :deep(.vs--open .vs__open-indicator) {
    transform: rotate(180deg) scale(0.70);
}

.vue-select-custom :deep(.vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 4px;
}

.vue-select-custom :deep(.vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
    transition: all 0.15s;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

.vue-select-custom :deep(.vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}

.vue-select-custom :deep(.vs--disabled .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #E5E7EB, #D1D5DB);
    cursor: not-allowed;
    opacity: 0.6;
}

/* Error state */
.vue-select-error :deep(.vs__dropdown-toggle),
.vue-select-error :deep(.vs--open .vs__dropdown-toggle),
.vue-select-error :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: #EF4444 !important;
}
</style>

