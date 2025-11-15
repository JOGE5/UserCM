<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="flex h-screen bg-gray-100">

            <!-- SIDEBAR OSCURO -->
            <aside class="w-64 bg-gray-900 text-gray-200 flex flex-col border-r border-gray-800">

                <!-- LOGO -->
                <div class="p-4 border-b border-gray-800">
                    <Link :href="route('dashboard')" class="flex items-center space-x-2">
                        <ApplicationMark class="h-8 w-auto" />
                        <span class="font-semibold text-lg">Mi Panel</span>
                    </Link>
                </div>

                <!-- ITEMS DEL SIDEBAR -->
                <nav class="flex-1 p-4 space-y-2">

                    <Link :href="route('dashboard')" class="block p-2 rounded hover:bg-gray-800"
                          :class="{ 'bg-gray-800': route().current('dashboard') }">
                         Publicaciones
                    </Link>

                    <Link href="/usuarios" class="block p-2 rounded hover:bg-gray-800">
                         Usuarios
                    </Link>

                    <Link href="/productos" class="block p-2 rounded hover:bg-gray-800">
                         Productos
                    </Link>

                    <Link href="/roles" class="block p-2 rounded hover:bg-gray-800">
                         Roles
                    </Link>

                    <Link href="/ajustes" class="block p-2 rounded hover:bg-gray-800">
                         Ajustes
                    </Link>
                </nav>

                <!-- USUARIO ABAJO -->
                <div class="p-4 border-t border-gray-800 flex items-center space-x-2">
                    <img
                        v-if="$page.props.jetstream.managesProfilePhotos"
                        :src="$page.props.auth.user.profile_photo_url"
                        class="w-10 h-10 rounded-full object-cover"
                    />
                    <div>
                        <p class="font-semibold text-gray-100">{{ $page.props.auth.user.name }}</p>
                        <p class="text-gray-400 text-sm">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
            </aside>

            <!-- CONTENIDO PRINCIPAL (Navbar + Página) -->
            <div class="flex-1 flex flex-col overflow-hidden">

                <!-- NAVBAR QUE YA TENÍAS -->
                <nav class="bg-white border-b border-gray-100">
                    <!-- TODO el código de la navbar se mantiene igual -->
                    <!-- PEGAMOS AQUÍ la navbar original completa -->

                    <!-- Primary Navigation Menu -->
                    <div class="max-w-7xl mx-auto px-10 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            
                          

                            <!-- DERECHA (Perfil, Teams, Logout...) -->
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="ms-3 relative">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button class="flex text-sm border-2 border-transparent rounded-full">
                                                <img class="size-8 rounded-full object-cover"
                                                     :src="$page.props.auth.user.profile_photo_url">
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink :href="route('profile.show')">
                                                Perfil
                                            </DropdownLink>

                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button">
                                                    Cerrar sesión
                                                </DropdownLink>
                                            </form>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>

                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
                    <slot />
                </main>

            </div>
        </div>
    </div>
</template>

