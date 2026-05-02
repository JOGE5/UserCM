<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, FileText, MessageSquare,
    Flag, LogOut, Menu, X, ChevronDown, Shield
} from 'lucide-vue-next';

defineProps({ title: String });

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth.user);

const navigation = [
    { name: 'Dashboard',     href: route('admin.dashboard'), icon: LayoutDashboard, active: route().current('admin.dashboard') },
    { name: 'Usuarios',      href: '#', icon: Users,         active: false },
    { name: 'Publicaciones', href: '#', icon: FileText,      active: false },
    { name: 'Reportes',      href: '#', icon: Flag,          active: false },
    { name: 'Mensajes',      href: '#', icon: MessageSquare, active: false },
];

const logout = () => router.post(route('logout'));
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-gray-100 font-sans antialiased">
        <Head :title="`Admin · ${title}`" />

        <!-- Mobile overlay -->
        <Transition name="fade">
            <div v-if="sidebarOpen" @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" />
        </Transition>

        <!-- SIDEBAR -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-gray-900 border-r border-gray-800 transition-transform duration-300',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]">
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
                <div class="p-2 rounded-xl bg-indigo-600">
                    <Shield class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Campus Market</p>
                    <p class="text-xs text-indigo-400 font-semibold">Panel Admin</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link v-for="item in navigation" :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                    :class="item.active
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User footer -->
            <div class="px-3 py-4 border-t border-gray-800">
                <div class="flex items-center gap-3 px-3 py-2 mb-2">
                    <img :src="user?.profile_photo_url" class="w-8 h-8 rounded-full object-cover" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                    </div>
                </div>
                <button @click="logout"
                    class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                    <LogOut class="w-4 h-4" />
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- MAIN -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Topbar móvil -->
            <header class="flex items-center gap-4 px-4 py-3 bg-gray-900 border-b border-gray-800 lg:hidden">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800">
                    <Menu v-if="!sidebarOpen" class="w-5 h-5" />
                    <X v-else class="w-5 h-5" />
                </button>
                <span class="text-sm font-bold text-white">Panel Admin</span>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
