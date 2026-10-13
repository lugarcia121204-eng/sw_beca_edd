<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const form = useForm({
    password: '',
});

const clearError = (field: string) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    
    // Validación del lado del cliente
    if (!form.password) {
        form.errors.password = 'La contraseña es obligatoria';
        return;
    }
    
    alertaCargando('Confirmando contraseña', 'Por favor espera...');
    
    form.post(route('password.confirm'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Confirmado!', 'Contraseña confirmada correctamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'La contraseña es incorrecta');
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <LandingLayout>
        <Head title="Confirm password" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        name="password"
                        class="mt-1 block w-full"
                        :class="{ 'border-red-500': form.errors.password }"
                        autocomplete="current-password"
                        autofocus
                        @input="clearError('password')"
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="form.processing" />
                        Confirm Password
                    </Button>
                </div>
            </div>
        </form>
    </LandingLayout>
</template>

