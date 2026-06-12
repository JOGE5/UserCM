<script setup>
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();

const finishSetup = () => {
    router.visit(route('dashboard'));
};
</script>

<template>
    <Head title="Paso Final: Configura tu Seguridad" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <AuthenticationCardLogo />
        </div>

        <div class="w-full sm:max-w-4xl mt-6 px-6 py-8 bg-white dark:bg-gray-800 shadow-xl overflow-hidden sm:rounded-2xl">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-black text-brand-600 dark:text-brand-400">Protege tu cuenta</h2>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                    Para finalizar el proceso y asegurar tu cuenta, debes configurar la Autenticación de Dos Factores (Google Authenticator).
                </p>
            </div>

            <div class="p-6 rounded-2xl">
                <TwoFactorAuthenticationForm :requires-confirmation="false" />
            </div>

            <!-- Botón para continuar al dashboard si ya está habilitado -->
            <div v-if="$page.props.auth.user?.two_factor_enabled" class="mt-8 flex justify-center border-t border-gray-200 dark:border-gray-700 pt-8">
                <PrimaryButton @click="finishSetup" class="w-full sm:w-auto justify-center bg-gradient-to-r from-green-500 to-green-600 hover:from-green-400 hover:to-green-500 py-3 px-10 text-lg shadow-[0_8px_20px_-6px_rgba(34,197,94,0.6)] hover:scale-105 transition-all duration-300">
                    ¡Todo listo! Entrar al sistema
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>
