<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import GraduationCapIcon from '@/Components/Icons/GraduationCapIcon.vue';
import ArchiveIcon from '@/Components/Icons/ArchiveIcon.vue';
import MessagesSquareIcon from '@/Components/Icons/MessagesSquareIcon.vue';
import FilePenIcon from '@/Components/Icons/FilePenIcon.vue';
import TagIcon from '@/Components/Icons/TagIcon.vue';

defineProps({
    title: String,
});

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
        <aside class="flex flex-col w-64 text-gray-200 bg-gray-900 border-r border-gray-800">

                        <!-- LOGO y burbuja de usuario -->
                        <div class="p-4 border-b border-gray-800 flex items-center justify-between">
                                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                                        <ApplicationMark class="w-auto h-8" />
                                        <span class="text-lg font-semibold">Campus Market</span>
                                </Link>
                                <!-- Burbuja de usuario con dropdown -->
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-700 hover:bg-gray-600 focus:outline-none">
                                            <img
                                                v-if="$page.props.jetstream.managesProfilePhotos"
                                                :src="$page.props.auth.user.profile_photo_url"
                                                class="object-cover w-9 h-9 rounded-full"
                                                alt="Usuario"
                                            />
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.show')">Perfil</DropdownLink>
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Cerrar sesión</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                        </div>

            <!-- ITEMS DEL SIDEBAR -->
            <nav class="flex-1 p-4 space-y-2">

                <Link :href="route('algo')"
                      class="block p-2 rounded hover:bg-gray-800 flex items-center"
                      :class="{ 'bg-gray-800': route().current('algo') }">
                    <MessagesSquareIcon class="w-6 h-6 mr-2" />
                    <span>Mensajes</span>
                </Link>


                                <Link :href="route('dashboard')"
                                            class="block p-2 rounded hover:bg-gray-800 flex items-center"
                                            :class="{ 'bg-gray-800': route().current('dashboard') }">
                                            <GraduationCapIcon class="w-6 h-6 mr-2" />
                                            <span>Publicaciones</span>
                                </Link>


                <Link :href="route('productos')"
                      class="block p-2 rounded hover:bg-gray-800 flex items-center"
                      :class="{ 'bg-gray-800': route().current('productos') }">
                    <FilePenIcon class="w-6 h-6 mr-2" />
                    <span>Foros</span>
                </Link>

                <Link :href="route('borradores')"
                      class="block p-2 rounded hover:bg-gray-800 flex items-center"
                      :class="{ 'bg-gray-800': route().current('borradores') }">
                  <ArchiveIcon class="w-6 h-6 mr-2" />
                  <span>Borradores</span>
                </Link>

                <Link :href="route('favoritos.index')"
                      class="block p-2 rounded hover:bg-gray-800 flex items-center"
                      :class="{ 'bg-gray-800': route().current('favoritos.index') }">
                    <TagIcon class="w-6 h-6 mr-2" />
                    <span>Favoritos</span>
                </Link>

            </nav>

            <!-- USUARIO ABAJO: correo y username -->
            <div class="mt-auto p-4 border-t border-gray-800">
                <div class="flex flex-col">
                    <img
                        v-if="$page.props.jetstream.managesProfilePhotos"
                        :src="$page.props.auth.user.profile_photo_url"
                        class="object-cover w-10 h-10 rounded-full mb-2"
                        alt="Usuario"
                    />
                    <span class="font-semibold text-gray-100 text-left">{{ $page.props.auth.user.name }}</span>
                    <span class="text-sm text-gray-400 text-left">{{ $page.props.auth.user.email }}</span>
                </div>
            </div>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex flex-col flex-1 overflow-hidden">

          
            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
                <slot />
            </main>

        </div>
    </div>
</div>
</template>
