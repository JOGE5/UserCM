<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    MessageSquare, 
    Search, 
    Plus, 
    TrendingUp, 
    Sparkles, 
    ArrowRight,
    MessageCircle,
    User,
    Calendar,
    ChevronRight,
    Hash
} from 'lucide-vue-next';

const page = usePage();
const items = computed(() => (page.props.foros ?? page.props.productos) ?? []);

const selectedCategory = ref(null);
const searchTerm = ref("");

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
</script>

<template>
    <AppLayout title="Foros">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
                    <MessageSquare class="w-3 h-3" />
                    <span>Comunidad Universitaria</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Foros de <span class="text-brand-600 dark:text-brand-400">Discusión</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl">
                    Comparte ideas, dudas y proyectos con compañeros de todas las facultades.
                </p>
            </div>
        </template>

        <div class="space-y-12 pb-20">
            <!-- Barra de Herramientas -->
            <div class="sticky top-24 z-30 flex flex-col gap-4 p-5 lg:flex-row lg:items-center bg-white/80 dark:bg-dark-surface/80 backdrop-blur-2xl border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-xl shadow-black/5">
                <div class="relative flex-1 group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                    <input
                        type="text"
                        v-model="searchTerm"
                        class="w-full py-3.5 pl-12 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500"
                        placeholder="Buscar en los foros..."
                    >
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:border-l lg:border-light-border dark:lg:border-dark-border lg:pl-4">
                    <button
                        @click="selectedCategory = null"
                        :class="[
                            'px-5 py-2.5 text-[10px] font-black tracking-widest uppercase rounded-xl border transition-all duration-300',
                            selectedCategory === null 
                                ? 'bg-brand-500 border-brand-400 text-white shadow-lg shadow-brand-500/30' 
                                : 'bg-white dark:bg-dark-surface border-light-border dark:border-dark-border text-gray-500 dark:text-gray-400 hover:border-brand-500/50'
                        ]"
                    >
                        Todos
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.Cod_Categoria"
                        @click="selectedCategory = cat.Cod_Categoria"
                        :class="[
                            'px-5 py-2.5 text-[10px] font-black tracking-widest uppercase rounded-xl border transition-all duration-300',
                            selectedCategory === cat.Cod_Categoria 
                                ? 'bg-brand-500 border-brand-400 text-white shadow-lg shadow-brand-500/30' 
                                : 'bg-white dark:bg-dark-surface border-light-border dark:border-dark-border text-gray-500 dark:text-gray-400 hover:border-brand-500/50'
                        ]"
                    >
                        {{ cat.Nombre_Categoria }}
                    </button>
                </div>

                <Link 
                    :href="route('productos.create')" 
                    class="flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-brand-500/20"
                >
                    <Plus class="w-4 h-4" />
                    Nueva Discusión
                </Link>
            </div>

            <!-- Grid de Foros -->
            <div v-if="filteredItems.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <article v-for="foro in filteredItems" :key="foro.ID_Foro || foro.id" class="group relative flex flex-col bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] overflow-hidden hover:shadow-2xl hover:shadow-brand-500/10 transition-all duration-500">
                    <!-- Imagen de cabecera del foro (opcional) -->
                    <div class="relative h-48 w-full overflow-hidden bg-gray-100 dark:bg-black/20">
                        <img 
                            v-if="foro.Imagen_Foro" 
                            :src="getImageUrl(foro.Imagen_Foro)" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" 
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <Hash class="w-12 h-12 text-gray-200 dark:text-white/5" />
                        </div>
                        
                        <!-- Badge de Categoría -->
                        <div class="absolute top-4 left-4 px-3 py-1.5 bg-white/90 dark:bg-black/60 backdrop-blur-md rounded-xl border border-white/20">
                            <span class="text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-tighter">
                                {{ foro.categoria?.Nombre_Categoria || 'General' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-brand-500/10 border border-brand-500/20 flex items-center justify-center">
                                <User class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-widest leading-none">Creador</span>
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ formatDate(foro.created_at) }}</span>
                            </div>
                        </div>

                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-3 line-clamp-2 min-h-[3.5rem] group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            {{ foro.Titulo_Foro || foro.Titulo || 'Sin Título' }}
                        </h3>

                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed line-clamp-3 mb-6 flex-1">
                            {{ foro.Descripcion_Foro || 'Inicia la conversación en este nuevo foro de la comunidad.' }}
                        </p>

                        <div class="flex items-center justify-between pt-6 border-t border-light-border/50 dark:border-dark-border/50">
                            <div class="flex items-center gap-4 text-gray-400">
                                <div class="flex items-center gap-1.5">
                                    <MessageCircle class="w-4 h-4" />
                                    <span class="text-[10px] font-black">24</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <TrendingUp class="w-4 h-4" />
                                    <span class="text-[10px] font-black">Activo</span>
                                </div>
                            </div>
                            
                            <Link :href="route('productos.show', foro.ID_Foro || foro.id)" class="flex items-center gap-2 text-[10px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest hover:translate-x-1 transition-transform">
                                Unirse al hilo
                                <ChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border border-light-border dark:border-dark-border rounded-[4rem] overflow-hidden">
                <div class="p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 border border-white dark:border-white/5">
                    <MessageSquare class="w-16 h-16 text-gray-300 dark:text-gray-600" />
                </div>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">No hay discusiones aún</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mb-10 text-lg font-medium">Sé el primero en iniciar un tema de conversación o ajusta los filtros de búsqueda.</p>
                <button @click="searchTerm = ''; selectedCategory = null" class="px-8 py-3.5 bg-brand-600 text-white font-black rounded-2xl hover:bg-brand-500 transition-all">Limpiar Filtros</button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-in {
    animation: slideUp 0.6s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
