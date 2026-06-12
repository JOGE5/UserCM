<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ActionSection from '@/Components/ActionSection.vue';
import { Link } from '@inertiajs/vue3';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                    Ajustes de <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-500 to-purple-500">Perfil</span>
                </h2>
                <p class="text-[10px] font-bold text-brand-400/80 uppercase tracking-widest mt-1">
                    Configuración y Seguridad
                </p>
            </div>
        </template>

        <div>
            <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8 space-y-10">
                <!-- Sección para Administradores -->
                <div v-if="$page.props.userPerfil && [1, 2].includes($page.props.userPerfil.Cod_Rol)">
                    <ActionSection>
                        <template #title>
                            Panel de Administración
                        </template>

                        <template #description>
                            Accede al panel de control exclusivo para administradores de la plataforma.
                        </template>

                        <template #content>
                            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                                Como administrador, tienes acceso a herramientas avanzadas para gestionar usuarios, revisar publicaciones, atender reportes y configurar los roles del sistema.
                            </div>

                            <div class="mt-5">
                                <Link :href="route('admin.dashboard')" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-brand-600 to-purple-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:from-brand-500 hover:to-purple-500 hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 shadow-[0_8px_20px_-6px_rgba(124,58,237,0.6)]">
                                    Entrar al Panel de Admin
                                </Link>
                            </div>
                        </template>
                    </ActionSection>

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />

                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
