<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import CreateModal from '@/Components/CreateModal.vue';
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
    ChevronDown,
    ChevronRight,
    Menu,
    X,
    Sparkles,
    Plus,
    ShoppingCart
} from 'lucide-vue-next';

defineProps({
    title: String,
});

const page = usePage();
const { isDark, toggleDark } = useDarkMode();
const mobileMenuOpen = ref(false);
const showCreateModal = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const unreadCount = computed(() => page.props.unreadCount ?? 0);
const categoriasGlobales = computed(() => page.props.categoriasGlobales ?? []);

// Evento global para abrir el modal desde cualquier componente
import { onMounted, onUnmounted } from 'vue';

const openModalHandler = () => {
    showCreateModal.value = true;
};

onMounted(() => {
    window.addEventListener('open-create-modal', openModalHandler);
});

onUnmounted(() => {
    window.removeEventListener('open-create-modal', openModalHandler);
});

const navigation = [
    { name: 'Tienda', href: route('dashboard'), icon: Sparkles, active: route().current('dashboard') },
    { name: 'Foros', href: route('productos'), icon: Layers, active: route().current('productos') },
    { name: 'Mensajes', href: route('mensajes.index'), icon: MessageSquare, active: route().current('mensajes.index'), badge: unreadCount },
    { name: 'Favoritos', href: route('favoritos.index'), icon: Heart, active: route().current('favoritos.index') },
    { name: 'Mis Publicaciones', href: route('borradores'), icon: Archive, active: route().current('borradores') },
];

const user = computed(() => page.props.auth.user);

const profilePhotoUrl = computed(() => {
    if (!user.value) return '';
    return user.value.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name)}&color=7F9CF5&background=EBF4FF`;
});
</script>

<template>
    <div class="min-h-screen bg-dynamic font-sans antialiased text-light-text dark:text-dark-text transition-colors duration-500 relative overflow-x-hidden flex flex-col">
        <Head :title="title" />
        <Banner />

        <!-- Decoración de fondo extra (Luces) -->
        <div class="absolute top-[5%] left-[-10%] w-[40%] h-[40%] rounded-full bg-brand-500/20 blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[20%] right-[-10%] w-[30%] h-[30%] rounded-full bg-purple-500/20 blur-[100px] pointer-events-none"></div>

        <!-- NAVBAR PRINCIPAL (E-COMMERCE) -->
        <header class="sticky top-0 z-[100] w-full glass-nav px-4 sm:px-6 lg:px-12 py-3 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <Link :href="route('dashboard')" class="flex items-center gap-3 float-3d group">
                    <div class="px-3 py-1.5 bg-white/10 dark:bg-black/10 backdrop-blur-md rounded-xl shadow-sm hover:shadow-lg group-hover:bg-brand-500/10 transition-all flex items-center justify-center">
                        <img src="/images/posters/logo-team.png" alt="Logo" class="h-6 sm:h-8 w-auto object-contain drop-shadow-md" />
                    </div>
                </Link>
            </div>

            <!-- Centro: Navegación Principal (Desktop) -->
            <nav class="hidden lg:flex items-center gap-1 bg-white/30 dark:bg-black/30 backdrop-blur-md border border-white/40 dark:border-white/10 p-1.5 rounded-[2rem] shadow-sm">
                <Link 
                    v-for="item in navigation" 
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'relative px-5 py-2.5 rounded-full text-xs font-bold transition-all duration-300 flex items-center gap-2 group overflow-hidden',
                        item.active 
                            ? 'text-white shadow-lg shadow-brand-500/40' 
                            : 'text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-white/50 dark:hover:bg-white/10'
                    ]"
                >
                    <!-- Fondo animado activo -->
                    <div v-if="item.active" class="absolute inset-0 bg-gradient-to-r from-brand-600 to-purple-600 z-0"></div>
                    
                    <component :is="item.icon" class="w-4 h-4 z-10 relative group-hover:scale-110 transition-transform" />
                    <span class="z-10 relative tracking-wide uppercase">{{ item.name }}</span>
                    
                    <span v-if="item.badge?.value > 0"
                        class="z-10 relative ml-1 min-w-[1.2rem] h-[1.2rem] flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full shadow-lg animate-pulse"
                    >
                        {{ item.badge.value > 99 ? '99+' : item.badge.value }}
                    </span>
                </Link>

                <!-- Menú Dropdown de Categorías -->
                <div class="relative z-50 float-3d ml-1 group/cat cursor-pointer">
                    <Dropdown align="left" width="64">
                        <template #trigger>
                            <button class="relative px-5 py-2.5 rounded-full text-xs font-bold transition-all duration-300 flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-white/50 dark:hover:bg-white/10 overflow-hidden">
                                <Layers class="w-4 h-4 z-10 relative transition-transform group-hover/cat:scale-110" />
                                <span class="z-10 relative tracking-wide uppercase">Categorías</span>
                                <ChevronDown class="w-3 h-3 z-10 relative ml-1 opacity-60" />
                            </button>
                        </template>

                        <template #content>
                            <div class="glass-panel rounded-2xl overflow-hidden shadow-2xl border border-white/20 dark:border-white/10 !bg-white/90 dark:!bg-[#16161a]/90 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                <div class="px-4 py-3 bg-brand-50/50 dark:bg-brand-500/10 border-b border-light-border dark:border-dark-border sticky top-0 backdrop-blur-md z-10">
                                    <p class="text-[10px] font-black tracking-widest text-brand-600 dark:text-brand-400 uppercase">Explorar Catálogo</p>
                                </div>
                                <div class="py-2 flex flex-col">
                                    <Link 
                                        :href="route('dashboard')" 
                                        class="px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-2"
                                    >
                                        <Sparkles class="w-3.5 h-3.5" />
                                        <span>Todas las Categorías</span>
                                    </Link>
                                    <Link 
                                        v-for="cat in categoriasGlobales" 
                                        :key="cat.Cod_Categoria"
                                        :href="route('dashboard', { categoria: cat.Cod_Categoria })" 
                                        class="px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-2 border-t border-light-border/50 dark:border-dark-border/50"
                                    >
                                        <ChevronRight class="w-3.5 h-3.5 opacity-50" />
                                        <span>{{ cat.Nombre_Categoria }}</span>
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <!-- Derecha: Acciones, Buscador y Perfil -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Buscar (Icono expandible o barra pequeña) -->
                <div class="hidden md:flex relative group float-3d">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 transition-colors group-focus-within:text-brand-600" />
                    <input 
                        type="text" 
                        placeholder="Buscar productos..." 
                        class="pl-11 pr-4 py-2.5 text-xs font-medium bg-white/40 dark:bg-dark-surface/40 border border-white/50 dark:border-white/10 focus:bg-white/80 dark:focus:bg-dark-surface/80 focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 rounded-[2rem] w-48 lg:w-64 backdrop-blur-md transition-all duration-300 shadow-inner dark:text-white outline-none"
                    />
                </div>

                <!-- Dark Mode -->
                <button @click="toggleDark" class="p-2.5 text-gray-600 dark:text-gray-300 bg-white/40 dark:bg-black/40 border border-white/50 dark:border-white/10 hover:bg-white/80 dark:hover:bg-black/80 rounded-full transition-all backdrop-blur-md shadow-sm float-3d">
                    <Sun v-if="isDark" class="w-4 h-4" />
                    <Moon v-else class="w-4 h-4" />
                </button>

                <!-- Menú Usuario -->
                <div class="relative z-50 float-3d">
                    <Dropdown align="right" width="56">
                        <template #trigger>
                            <button class="flex items-center gap-2 p-1.5 pr-4 rounded-full bg-white/40 dark:bg-black/40 border border-white/50 dark:border-white/10 hover:bg-white/80 dark:hover:bg-black/80 transition-all shadow-sm backdrop-blur-md">
                                <img :src="profilePhotoUrl" class="h-8 w-8 rounded-full object-cover ring-2 ring-white/50 dark:ring-white/10" :alt="user?.name" />
                                <span class="hidden md:inline text-xs font-bold text-gray-800 dark:text-gray-200">{{ user?.name?.split(' ')[0] }}</span>
                                <ChevronDown class="w-3 h-3 text-gray-500" />
                            </button>
                        </template>

                        <template #content>
                            <div class="glass-panel rounded-2xl overflow-hidden shadow-2xl border border-white/20 dark:border-white/10 !bg-white/90 dark:!bg-[#16161a]/90">
                                <div class="px-4 py-3 bg-brand-50/50 dark:bg-brand-500/10 border-b border-light-border dark:border-dark-border">
                                    <p class="text-[10px] font-black tracking-widest text-brand-600 dark:text-brand-400 uppercase">Mi Cuenta</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate mt-1">{{ user?.email }}</p>
                                </div>
                                <div class="py-1">
                                    <DropdownLink :href="route('profile.show')" class="!text-xs font-semibold">Configuración de Perfil</DropdownLink>
                                    <div class="border-t border-light-border dark:border-dark-border my-1" />
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button" class="!text-xs font-semibold !text-rose-600 dark:!text-rose-400 hover:!bg-rose-50 dark:hover:!bg-rose-500/10">
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <!-- Botón Publicar (Principal) -->
                <button 
                    @click="showCreateModal = true"
                    class="hidden sm:flex items-center gap-2 ml-1 px-5 py-2.5 text-xs font-black text-white bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 rounded-full shadow-[0_8px_20px_-6px_rgba(124,58,237,0.6)] hover:shadow-[0_12px_25px_-6px_rgba(124,58,237,0.8)] transition-all float-3d group border border-white/20"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    <span class="tracking-widest uppercase">Vender</span>
                </button>

                <!-- Menú Hamburguesa (Móvil) -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2.5 text-gray-600 dark:text-gray-300 bg-white/40 dark:bg-black/40 border border-white/50 dark:border-white/10 rounded-full shadow-sm backdrop-blur-md float-3d">
                    <X v-if="mobileMenuOpen" class="w-5 h-5" />
                    <Menu v-else class="w-5 h-5" />
                </button>
            </div>
        </header>

        <!-- Menú Móvil -->
        <Transition name="fade">
            <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-[90] glass-panel pt-24 px-6 flex flex-col gap-4 overflow-y-auto">
                <Link 
                    v-for="item in navigation" 
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center justify-between p-4 rounded-2xl bg-white/50 dark:bg-black/50 border border-white/50 dark:border-white/10 shadow-sm"
                    @click="mobileMenuOpen = false"
                >
                    <div class="flex items-center gap-3">
                        <component :is="item.icon" :class="['w-5 h-5', item.active ? 'text-brand-600' : 'text-gray-500']" />
                        <span :class="['font-bold', item.active ? 'text-brand-600' : 'text-gray-700 dark:text-gray-200']">{{ item.name }}</span>
                    </div>
                    <ChevronRight class="w-4 h-4 text-gray-400" />
                </Link>
                
                <button 
                    @click="mobileMenuOpen = false; showCreateModal = true"
                    class="mt-4 flex items-center justify-center gap-2 p-4 rounded-2xl text-white font-black bg-gradient-to-r from-brand-600 to-purple-600 shadow-lg w-full"
                >
                    <Plus class="w-5 h-5" /> Vender Producto
                </button>
            </div>
        </Transition>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-1 w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10 flex flex-col">
            <!-- Header dinámico (opcional) -->
            <div v-if="$slots.header" class="mb-8 animate-fade-in glass-panel p-6 sm:p-8 rounded-[2.5rem] relative overflow-hidden shadow-xl float-3d">
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent dark:from-white/5 pointer-events-none z-0"></div>
                <div class="relative z-10">
                    <slot name="header" />
                </div>
            </div>

            <!-- Main Slot -->
            <div class="animate-fade-in flex-1">
                <slot />
            </div>
        </main>

        <!-- FOOTER E-COMMERCE -->
        <footer class="mt-auto glass-panel border-t border-white/20 dark:border-white/5 py-8 relative z-10">
            <div class="max-w-[1400px] mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3 opacity-60">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="w-6 h-6 grayscale" />
                    <span class="text-xs font-black tracking-[0.2em] uppercase text-gray-500 dark:text-gray-400">Campus Market E-Commerce</span>
                </div>
                <div class="flex items-center gap-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <a href="#" class="hover:text-brand-500 transition-colors">Términos</a>
                    <a href="#" class="hover:text-brand-500 transition-colors">Privacidad</a>
                    <a href="#" class="hover:text-brand-500 transition-colors">Soporte</a>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">&copy; {{ new Date().getFullYear() }} Unifranz</p>
            </div>
        </footer>

        <!-- Modal Global de Venta -->
        <CreateModal :show="showCreateModal" @close="showCreateModal = false" />
    </div>
</template>

<style>
/* Los estilos de fade y scrollbar se mantienen, glass-panel y bg-dynamic ya están en app.css */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-brand-500/30 rounded-full hover:bg-brand-500/50; }

.animate-fade-in { animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeIn { 
    from { opacity: 0; transform: translateY(20px) scale(0.98); } 
    to { opacity: 1; transform: translateY(0) scale(1); } 
}

.fade-enter-active, .fade-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
