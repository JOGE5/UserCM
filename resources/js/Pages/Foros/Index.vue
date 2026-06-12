<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ForumArea from '@/Components/ForumArea.vue';
import { 
    MessageSquare, 
    Search, 
    Plus, 
    MessageCircle,
    User,
    Calendar,
    ChevronRight,
    Hash,
    Lock
} from 'lucide-vue-next';

const page = usePage();
const items = computed(() => (page.props.foros ?? page.props.productos) ?? []);

const selectedCategory = ref(null);
const searchTerm = ref("");
const activeForoId = ref(null);

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const foroIdParam = urlParams.get('foro_id');
    if (foroIdParam) {
        activeForoId.value = parseInt(foroIdParam);
        window.history.replaceState({}, '', route('productos'));
    }
});

const categories = computed(() => {
    if (!items.value || items.value.length === 0) return [];
    const unique = new Map();
    items.value.forEach(i => {
        if (i.categoria && i.Cod_Categoria) {
            unique.set(i.Cod_Categoria, i.categoria.Nombre_Categoria);
        }
    });
    return Array.from(unique, ([id, name]) => ({ Cod_Categoria: id, Nombre_Categoria: name }));
});

const filteredItems = computed(() => {
    let arr = items.value;
    if (selectedCategory.value) {
        arr = arr.filter(i => i.Cod_Categoria === selectedCategory.value);
    }
    if (searchTerm.value.trim() !== "") {
        const term = searchTerm.value.trim().toLowerCase();
        arr = arr.filter(i => {
            const titulo = (i.Titulo_Foro || i.Titulo_Publicacion || i.Titulo || i.nombre || '').toLowerCase();
            const desc = (i.Descripcion_Foro || i.Descripcion || '').toLowerCase();
            return titulo.includes(term) || desc.includes(term);
        });
    }
    return arr;
});

const getImageUrl = (img) => {
    if (!img) return null;
    return `/files/foros/${img.split('/').pop()}`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short' }).format(date);
};

const selectForo = (foro) => {
    activeForoId.value = foro.ID_Foro || foro.id;
};
</script>

<template>
    <AppLayout title="Foros">
        <!-- Eliminamos el header estándar para darle el 100% de la altura al Workspace -->
        
        <div class="h-[calc(100vh-73px)] pt-6 pb-6 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full flex overflow-hidden">
            
            <!-- Panel Izquierdo: Lista de Salas -->
            <div 
                class="flex flex-col w-full md:w-[380px] lg:w-[450px] shrink-0 bg-white/80 dark:bg-dark-surface/80 backdrop-blur-xl border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl shadow-brand-500/5 overflow-hidden transition-all duration-300 relative z-20"
                :class="activeForoId ? 'hidden md:flex' : 'flex'"
            >
                <div class="p-6 pb-4 border-b border-light-border dark:border-dark-border bg-white/50 dark:bg-black/20 shrink-0">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                                Salas de Chat
                                <span class="px-2 py-1 bg-brand-500/10 text-brand-600 dark:text-brand-400 text-[10px] uppercase tracking-widest rounded-full font-bold border border-brand-500/20">{{ items.length }}</span>
                            </h1>
                            <p class="text-[10px] text-gray-500 mt-1 font-bold">Comunidad Universitaria</p>
                        </div>
                        <Link 
                            :href="route('productos.create')" 
                            class="flex items-center justify-center w-10 h-10 bg-brand-600 text-white rounded-full hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-brand-500/20"
                            title="Nueva Sala"
                        >
                            <Plus class="w-5 h-5" />
                        </Link>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative group mb-4">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                        <input
                            type="text"
                            v-model="searchTerm"
                            class="w-full py-3 pl-11 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500 outline-none"
                            placeholder="Buscar temas o salas..."
                        >
                    </div>

                    <!-- Filtros Inteligentes -->
                    <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1">
                        <button @click="selectedCategory = null" :class="['px-4 py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap', selectedCategory === null ? 'bg-brand-600 text-white shadow-md shadow-brand-500/30' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-gray-900 dark:hover:text-white']">
                            Todas
                        </button>
                        <button v-for="cat in categories" :key="cat.Cod_Categoria" @click="selectedCategory = cat.Cod_Categoria" :class="['px-4 py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center gap-1', selectedCategory === cat.Cod_Categoria ? 'bg-brand-600 text-white shadow-md shadow-brand-500/30' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-gray-900 dark:hover:text-white']">
                            {{ cat.Nombre_Categoria }}
                        </button>
                    </div>
                </div>

                <!-- Lista de Salas -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2 bg-gray-50/30 dark:bg-black/10">
                    <div v-for="foro in filteredItems" :key="foro.ID_Foro || foro.id" class="group relative">
                        <button
                            @click="selectForo(foro)"
                            class="w-full text-left p-4 rounded-3xl transition-all relative overflow-hidden"
                            :class="activeForoId === (foro.ID_Foro || foro.id) ? 'bg-brand-500/10 border-brand-500/30 shadow-inner' : 'hover:bg-white dark:hover:bg-white/5 border-transparent'"
                            style="border-width: 1px"
                        >
                            <div class="relative flex items-center gap-4">
                                <!-- Avatar de la sala -->
                                <div class="relative flex-shrink-0">
                                    <div v-if="foro.Imagen_Foro" class="w-12 h-12 rounded-full overflow-hidden shadow-md">
                                        <img :src="getImageUrl(foro.Imagen_Foro)" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 shadow-md flex items-center justify-center">
                                        <Hash class="w-6 h-6 text-white" />
                                    </div>
                                    
                                    <div v-if="foro.tipo_acceso === 'exclusivo'" class="absolute -bottom-1 -right-1 w-5 h-5 flex items-center justify-center bg-rose-500 border-2 border-white dark:border-dark-border rounded-full shadow-lg">
                                        <Lock class="w-3 h-3 text-white" />
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-sm font-black truncate text-gray-900 dark:text-white">
                                            {{ foro.Titulo_Foro || 'Sin Título' }}
                                        </h3>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-2 shrink-0">
                                            {{ foro.comentarios_count ?? 0 }} msjs
                                        </span>
                                    </div>
                                    <p class="text-xs truncate text-gray-500 dark:text-gray-400 font-medium">
                                        {{ foro.Descripcion_Foro }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Empty States -->
                    <div v-if="filteredItems.length === 0" class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                            <MessageSquare class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500">No hay salas disponibles</p>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Área de Chat -->
            <div 
                class="flex-1 flex flex-col relative bg-transparent overflow-hidden"
                :class="activeForoId ? 'flex' : 'hidden md:flex'"
            >
                <div v-if="activeForoId" class="absolute inset-0 md:ml-4">
                    <ForumArea 
                        :foro-id="activeForoId" 
                        @close="activeForoId = null" 
                        class="h-full rounded-[2.5rem] shadow-2xl shadow-brand-500/10 border border-light-border dark:border-dark-border" 
                    />
                </div>
                
                <!-- Estado Vacío -->
                <div v-else class="absolute inset-0 md:ml-4 flex flex-col items-center justify-center text-center bg-white/40 dark:bg-dark-surface/40 backdrop-blur-md rounded-[2.5rem] border border-light-border dark:border-dark-border">
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brand-500/5 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="relative z-10 p-8 rounded-full bg-white/50 dark:bg-black/20 mb-8 shadow-inner border border-white dark:border-white/5">
                        <Hash class="w-16 h-16 text-brand-500/50" />
                    </div>
                    <h2 class="relative z-10 text-3xl font-black text-gray-900 dark:text-white mb-2">Comunidad en Vivo</h2>
                    <p class="relative z-10 text-sm text-gray-500 font-medium max-w-sm mb-6">
                        Selecciona una sala de la izquierda para comenzar a chatear o crea un nuevo espacio de discusión.
                    </p>
                    <Link 
                        :href="route('productos.create')" 
                        class="relative z-10 flex items-center justify-center gap-2 px-6 py-3 bg-brand-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-brand-500 transition-all shadow-lg shadow-brand-500/20"
                    >
                        <Plus class="w-4 h-4" />
                        Nueva Sala
                    </Link>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(124,58,237,0.3); }
</style>
