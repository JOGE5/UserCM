<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { 
    LayoutDashboard, 
    MessageSquare, 
    Layers, 
    Archive, 
    Heart, 
    Search, 
    Sun, 
    Moon, 
    LogOut, 
    User,
    Settings,
    Bell,
    ChevronLeft,
    ChevronRight,
    PanelLeftClose,
    PanelLeftOpen,
    Menu,
    X,
    Sparkles,
    Plus,
    ChevronDown
} from 'lucide-vue-next';

defineProps({
    title: String,
});

const page = usePage();
const { isDark, toggleDark } = useDarkMode();
const sidebarOpen = ref(false); // Móvil
const sidebarCollapsed = ref(false); // Desktop

const logout = () => {
    router.post(route('logout'));
};

const unreadCount = computed(() => page.props.unreadCount ?? 0);

const navigation = [
    { name: 'Publicaciones', href: route('dashboard'), icon: LayoutDashboard, active: route().current('dashboard') },
    { name: 'Mensajes', href: route('mensajes.index'), icon: MessageSquare, active: route().current('mensajes.index'), badge: unreadCount },
    { name: 'Foros', href: route('productos'), icon: Layers, active: route().current('productos') },
    { name: 'Borradores', href: route('borradores'), icon: Archive, active: route().current('borradores') },
    { name: 'Favoritos', href: route('favoritos.index'), icon: Heart, active: route().current('favoritos.index') },
];

const user = computed(() => page.props.auth.user);

const profilePhotoUrl = computed(() => {
    if (!user.value) return '';
    return user.value.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name)}&color=7F9CF5&background=EBF4FF`;
});
</script>

<template>
    <div class="min-h-screen font-sans antialiased text-gray-900 bg-light-bg dark:bg-dark-bg transition-colors duration-500">
        <Head :title="title" />

        <Banner />

        <!-- Sidebar Móvil Overlay -->
        <Transition name="fade">
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm lg:hidden"></div>
        </Transition>

        <!-- SIDEBAR -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-[70] flex flex-col transition-all duration-300 ease-in-out border-r shadow-2xl backdrop-blur-xl',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
                sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0',
                'bg-white/90 dark:bg-dark-surface/95 border-light-border dark:border-dark-border'
            ]"
        >
            <!-- Logo Area -->
            <div class="relative flex items-center h-24 px-6 shrink-0 group">
                <Link :href="route('dashboard')" class="flex items-center gap-3 overflow-hidden">
                    <div class="flex items-center justify-center shrink-0">
                        <img 
                            src="/images/posters/logo-team.png" 
                            alt="Logo" 
                            :class="['transition-all duration-500 object-contain', sidebarCollapsed ? 'w-10 h-10' : 'w-12 h-12']"
                        />
                    </div>
                    <div 
                        v-show="!sidebarCollapsed" 
                        class="flex flex-col transition-all duration-300 whitespace-nowrap"
                        :class="sidebarCollapsed ? 'opacity-0' : 'opacity-100'"
                    >
                        <span class="text-sm font-black tracking-tighter text-brand-600 dark:text-brand-400 uppercase">Campus Market</span>
                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 tracking-widest uppercase">Ecosistema Uni</span>
                    </div>
                </Link>

                <!-- Toggle Button (Desktop) -->
                <button 
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden lg:flex absolute -right-3 top-1/2 -translate-y-1/2 w-7 h-7 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-full items-center justify-center shadow-lg hover:text-brand-500 transition-all z-50 text-gray-400 hover:scale-110 active:scale-90"
                >
                    <PanelLeftClose v-if="!sidebarCollapsed" class="w-4 h-4" />
                    <PanelLeftOpen v-else class="w-4 h-4" />
                </button>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto custom-scrollbar overflow-x-hidden">
                <div v-for="item in navigation" :key="item.name">
                    <Link 
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all duration-300 group relative',
                            item.active 
                                ? 'bg-brand-600 text-white shadow-[0_8px_20px_-6px_rgba(124,58,237,0.5)]' 
                                : 'text-gray-500 dark:text-gray-400 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400'
                        ]"
                    >
                        <div class="relative shrink-0">
                            <component :is="item.icon" :class="['w-6 h-6 transition-transform duration-300', item.active ? '' : 'group-hover:scale-110']" />
                            <span v-if="item.badge?.value > 0"
                                class="absolute -top-1.5 -right-1.5 min-w-[1.1rem] h-[1.1rem] flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full px-0.5 shadow-lg shadow-rose-500/40 animate-pulse"
                            >
                                {{ item.badge.value > 99 ? '99+' : item.badge.value }}
                            </span>
                        </div>
                        <span
                            class="text-sm transition-all duration-300 flex-1"
                            :class="[sidebarCollapsed ? 'lg:opacity-0 lg:w-0' : 'opacity-100 w-auto']"
                        >
                            {{ item.name }}
                        </span>
                        <span v-if="!sidebarCollapsed && item.badge?.value > 0"
                            class="shrink-0 min-w-[1.4rem] h-[1.4rem] flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full px-1 shadow-lg shadow-rose-500/40"
                        >
                            {{ item.badge.value > 99 ? '99+' : item.badge.value }}
                        </span>
                    </Link>
                </div>
            </nav>

            <!-- User Footer Sidebar -->
            <div class="p-3 border-t border-light-border dark:border-dark-border/50">
                <div 
                    :class="[
                        'flex items-center gap-3 p-2 rounded-[1.5rem] bg-gray-50 dark:bg-black/20 border border-light-border dark:border-dark-border transition-all duration-300 overflow-hidden',
                        sidebarCollapsed ? 'justify-center' : 'px-3'
                    ]"
                >
                    <div class="relative shrink-0">
                        <img 
                            :src="profilePhotoUrl" 
                            class="h-10 w-10 min-w-[40px] rounded-xl object-cover ring-2 ring-brand-500/20"
                            :alt="user?.name"
                        />
                    </div>
                    
                    <div 
                        v-show="!sidebarCollapsed" 
                        class="flex flex-col min-w-0 flex-1 transition-all duration-300"
                    >
                        <span class="text-xs font-black truncate text-gray-800 dark:text-white leading-tight">{{ user?.name }}</span>
                        <span class="text-[9px] font-bold text-gray-400 truncate dark:text-gray-500 uppercase tracking-tighter">{{ user?.email }}</span>
                    </div>

                    <button @click="logout" v-show="!sidebarCollapsed" class="p-2 text-gray-400 hover:text-rose-500 transition-colors">
                        <LogOut class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main 
            :class="[
                'flex flex-col min-h-screen transition-all duration-300 ease-in-out',
                sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'
            ]"
        >
            <!-- Top Header -->
            <header class="sticky top-0 z-[40] h-20 flex items-center justify-between px-6 lg:px-10 bg-light-bg/80 dark:bg-dark-bg/80 backdrop-blur-xl border-b border-light-border dark:border-dark-border">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2.5 text-gray-500 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-xl lg:hidden shadow-sm">
                        <Menu class="w-5 h-5" />
                    </button>

                    <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-gray-400 lg:text-[10px] uppercase tracking-widest">
                        <span>Panel</span>
                        <ChevronRight class="w-3 h-3" />
                        <span class="text-brand-600 dark:text-brand-400">{{ title || 'Dashboard' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Search -->
                    <div class="hidden md:flex relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 transition-colors group-focus-within:text-brand-600" />
                        <input 
                            type="text" 
                            placeholder="Buscar..." 
                            class="pl-12 pr-4 py-2 text-sm bg-gray-50 dark:bg-white/5 border border-light-border dark:border-dark-border focus:bg-white dark:focus:bg-black/30 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 rounded-2xl w-64 lg:w-80 transition-all duration-300"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button @click="toggleDark" class="p-2.5 text-gray-500 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 rounded-2xl transition-all">
                            <Sun v-if="isDark" class="w-5 h-5" />
                            <Moon v-else class="w-5 h-5" />
                        </button>

                        <div class="h-8 w-px bg-light-border dark:bg-dark-border mx-2 hidden sm:block"></div>

                        <!-- User Dropdown (Igual que Jetstream original pero rediseñado) -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center gap-2 p-1.5 pr-3 rounded-2xl bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border hover:border-brand-500/50 transition-all shadow-sm">
                                        <img :src="profilePhotoUrl" class="h-8 w-8 rounded-xl object-cover" :alt="user?.name" />
                                        <span class="hidden md:inline text-xs font-bold text-gray-700 dark:text-gray-300">{{ user?.name?.split(' ')[0] }}</span>
                                        <ChevronDown class="w-4 h-4 text-gray-400" />
                                    </button>
                                </template>

                                <template #content>
                                    <div class="block px-4 py-2 text-xs text-gray-400 font-black uppercase tracking-widest">Administrar Cuenta</div>
                                    <DropdownLink :href="route('profile.show')">Mi Perfil</DropdownLink>
                                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">API Tokens</DropdownLink>
                                    <div class="border-t border-light-border dark:border-dark-border" />
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button">Cerrar Sesión</DropdownLink>
                                    </form>
                                </template>
                            </Dropdown>
                        </div>

                        <Link 
                            :href="route('dashboard.create')" 
                            class="hidden sm:flex items-center gap-2 ml-2 px-6 py-2.5 text-sm font-black text-white bg-brand-600 hover:bg-brand-500 rounded-2xl shadow-lg shadow-brand-500/30 active:scale-95 transition-all group"
                        >
                            <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                            <span>Publicar</span>
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 p-6 lg:p-10 w-full max-w-[1700px] mx-auto">
                <div v-if="$slots.header" class="mb-10 animate-fade-in">
                    <slot name="header" />
                </div>
                <div class="animate-fade-in">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <footer class="p-8 text-center bg-white/30 dark:bg-dark-surface/10 border-t border-light-border dark:border-dark-border mt-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 max-w-[1700px] mx-auto px-6">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">&copy; {{ new Date().getFullYear() }} Campus Market • Unifranz</p>
                    <div class="flex items-center gap-6">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">Privacidad</span>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">Términos</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-gray-200 dark:bg-white/10 rounded-full; }

.animate-fade-in { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
body { overflow-x: hidden; }
</style>
