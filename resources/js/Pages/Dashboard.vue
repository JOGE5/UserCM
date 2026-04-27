<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import CardPubli from '@/Components/CardPubli.vue';
import { 
    Filter, 
    Search, 
    Plus, 
    ArchiveX, 
    TrendingUp, 
    Sparkles, 
    AlertCircle,
    Layers,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    publicaciones: { type: Array, default: () => [] },
    mejores: { type: Array, default: () => [] },
    currentUserId: Number,
    userEstado: String,
});

const route = window.route;
const page = usePage();
const selectedCategory = ref(null);
const searchTerm = ref("");

const categories = computed(() => {
    if (!props.publicaciones?.length) return [];
    const unique = new Map();
    props.publicaciones.forEach(pub => {
        if (pub.categoria && pub.Cod_Categoria) {
            unique.set(pub.Cod_Categoria, pub.categoria.Nombre_Categoria);
        }
    });
    return Array.from(unique, ([id, name]) => ({ Cod_Categoria: id, Nombre_Categoria: name }));
});

const filteredPublicaciones = computed(() => {
    let pubs = props.publicaciones || [];
    if (selectedCategory.value) {
        pubs = pubs.filter(pub => pub.Cod_Categoria === selectedCategory.value);
    }
    if (searchTerm.value.trim() !== "") {
        const term = searchTerm.value.trim().toLowerCase();
        pubs = pubs.filter(pub => pub.Titulo_Publicacion?.toLowerCase().includes(term));
    }
    return pubs;
});

const contactingId = ref(null);

async function handleContact(publicationId) {
    const publication = props.publicaciones.find(pub => pub.id === publicationId)
                     || props.mejores?.find(pub => pub.id === publicationId);

    // vendedor.user.id es el user_id del vendedor (UsuarioCampusMarket -> User)
    const sellerId = publication?.vendedor?.user?.id ?? publication?.vendedor?.user_id;
    if (!sellerId) return;
    if (sellerId === page.props.auth.user.id) return;
    if (contactingId.value === publicationId) return;

    contactingId.value = publicationId;
    try {
        const { data } = await axios.post(route('chats.private.create'), { seller_id: sellerId });
        const { chat_id } = data;

        // Siempre enviar tarjeta del producto al iniciar conversación
        const imagen = publication.Imagen_Publicacion
            ? (() => { try { const p = JSON.parse(publication.Imagen_Publicacion); return `/files/publicaciones/${(Array.isArray(p) ? p[0] : p).split('/').pop()}`; } catch { return `/files/publicaciones/${String(publication.Imagen_Publicacion).split('/').pop()}`; } })()
            : null;

        await axios.post(route('chats.messages.store', chat_id), {
            type: 'product_card',
            metadata: {
                publicacion_id: publication.id,
                titulo:  publication.Titulo_Publicacion,
                precio:  publication.Precio_Publicacion,
                imagen,
                url: route('publicaciones.show', publication.id),
            },
        });

        router.visit(route('chats.show', chat_id));
    } catch {
        alert('No se pudo abrir el chat. Intenta de nuevo.');
    } finally {
        contactingId.value = null;
    }
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
                    <TrendingUp class="w-3 h-3" />
                    <span>Plataforma de Intercambio</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Explora el <span class="text-brand-600 dark:text-brand-400">Marketplace</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl">
                    Descubre artículos, libros y servicios exclusivos de la comunidad universitaria Unifranz.
                </p>
            </div>
        </template>

        <!-- Banner de Inactivo -->
        <div v-if="userEstado === 'Inactivo'" class="mb-10 overflow-hidden rounded-[2.5rem] bg-rose-500 shadow-[0_20px_40px_-10px_rgba(244,63,94,0.3)] border border-rose-400 animate-in fade-in slide-in-from-top-4 duration-700">
            <div class="flex flex-col md:flex-row items-center gap-8 p-10">
                <div class="p-5 rounded-3xl bg-white/20 backdrop-blur-md border border-white/20 shrink-0">
                    <AlertCircle class="w-12 h-12 text-white" />
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl font-black text-white mb-2">Acceso Restringido</h3>
                    <p class="text-rose-50 text-base font-medium leading-relaxed opacity-90">
                        Tu cuenta ha sido inhabilitada temporalmente por la administración. Para resolver esta situación, por favor contacta a nuestro equipo de soporte.
                    </p>
                </div>
                <a href="https://wa.me/73527947" target="_blank" class="flex items-center gap-3 px-8 py-4 bg-white text-rose-600 font-black rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-2xl">
                    Soporte Técnico
                    <ArrowRight class="w-5 h-5" />
                </a>
            </div>
        </div>

        <div class="space-y-16">
            <!-- Barra de Filtros Glassmorphism -->
            <div class="sticky top-24 z-30 flex flex-col gap-4 p-5 lg:flex-row lg:items-center bg-white/80 dark:bg-dark-surface/80 backdrop-blur-2xl border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-xl shadow-black/5">
                <!-- Buscador Interno -->
                <div class="relative flex-1 group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                    <input
                        type="text"
                        v-model="searchTerm"
                        class="w-full py-3.5 pl-12 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500"
                        placeholder="Filtrar por título de publicación..."
                    >
                </div>

                <!-- Chips de Categoría -->
                <div class="flex flex-wrap items-center gap-2 lg:border-l lg:border-light-border dark:lg:border-dark-border lg:pl-4">
                    <button
                        @click="selectedCategory = null"
                        :class="[
                            'px-5 py-2.5 text-[10px] font-black tracking-widest uppercase rounded-xl border transition-all duration-300',
                            selectedCategory === null 
                                ? 'bg-brand-500 border-brand-400 text-white shadow-lg shadow-brand-500/30 scale-105' 
                                : 'bg-white dark:bg-dark-surface border-light-border dark:border-dark-border text-gray-500 dark:text-gray-400 hover:border-brand-500/50'
                        ]"
                    >
                        Todo
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.Cod_Categoria"
                        @click="selectedCategory = cat.Cod_Categoria"
                        :class="[
                            'px-5 py-2.5 text-[10px] font-black tracking-widest uppercase rounded-xl border transition-all duration-300',
                            selectedCategory === cat.Cod_Categoria 
                                ? 'bg-brand-500 border-brand-400 text-white shadow-lg shadow-brand-500/30 scale-105' 
                                : 'bg-white dark:bg-dark-surface border-light-border dark:border-dark-border text-gray-500 dark:text-gray-400 hover:border-brand-500/50'
                        ]"
                    >
                        {{ cat.Nombre_Categoria }}
                    </button>
                </div>
            </div>

            <!-- Sección Destacados (Aurora Style) -->
            <section v-if="!selectedCategory && !searchTerm && mejores.length" class="animate-in fade-in slide-in-from-bottom-4 duration-1000">
                <div class="flex items-center gap-4 mb-10">
                    <div class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 shadow-inner">
                        <Sparkles class="w-6 h-6 text-amber-500" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Selección Destacada</h2>
                        <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">Recomendaciones del sistema</p>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-light-border dark:from-dark-border via-light-border/50 dark:via-dark-border/50 to-transparent ml-4"></div>
                </div>

                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <CardPubli
                        v-for="pub in mejores"
                        :key="pub.id"
                        :title="pub.Titulo_Publicacion"
                        :subtitle="`Bs ${pub.Precio_Publicacion}`"
                        :description="pub.Descripcion_Publicacion"
                        :category="pub.categoria?.Nombre_Categoria"
                        :id="pub.id"
                        :user="pub.vendedor?.user"
                        :currentUserId="page.props.auth.user.id"
                        :isOwner="pub.vendedor?.user_id === page.props.auth.user.id"
                        :estado="pub.estado"
                        :publicacion="pub"
                        @contact="handleContact"
                    />
                </div>
            </section>

            <!-- Grid Principal -->
            <section class="animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-200">
                <div class="flex items-center gap-4 mb-10">
                    <div class="p-2.5 rounded-2xl bg-brand-500/10 border border-brand-500/20 shadow-inner">
                        <Layers class="w-6 h-6 text-brand-600 dark:text-brand-400" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                            {{ searchTerm || selectedCategory ? 'Resultados Encontrados' : 'Novedades del Market' }}
                        </h2>
                        <p class="text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase tracking-widest">
                            {{ filteredPublicaciones.length }} Ofertas disponibles
                        </p>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-light-border dark:from-dark-border via-light-border/50 dark:via-dark-border/50 to-transparent ml-4"></div>
                </div>

                <div v-if="filteredPublicaciones.length" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <CardPubli
                        v-for="pub in filteredPublicaciones"
                        :key="pub.id"
                        :title="pub.Titulo_Publicacion"
                        :subtitle="`Bs ${pub.Precio_Publicacion}`"
                        :description="pub.Descripcion_Publicacion"
                        :category="pub.categoria?.Nombre_Categoria"
                        :id="pub.id"
                        :user="pub.vendedor?.user"
                        :currentUserId="page.props.auth.user.id"
                        :isOwner="pub.vendedor?.user_id === page.props.auth.user.id"
                        :estado="pub.estado"
                        :publicacion="pub"
                        @contact="handleContact"
                    />
                </div>

                <!-- Empty State (Diseño Pulido) -->
                <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border-2 border-dashed border-light-border dark:border-dark-border rounded-[4rem] group overflow-hidden">
                    <!-- Decoración de fondo -->
                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                    
                    <div class="relative p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                        <ArchiveX class="w-16 h-16 text-gray-300 dark:text-gray-600 animate-pulse" />
                    </div>
                    
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Vaya, qué vacío está esto...</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto mb-10 text-lg font-medium leading-relaxed">
                        No hemos podido encontrar ninguna coincidencia para "<span class="text-brand-600 dark:text-brand-400 italic">{{ searchTerm || 'la categoría seleccionada' }}</span>". Intenta con términos más genéricos o limpia los filtros.
                    </p>
                    
                    <button 
                        @click="selectedCategory = null; searchTerm = ''"
                        class="group/btn relative px-10 py-4 bg-brand-600 text-white font-black rounded-2xl shadow-2xl shadow-brand-500/40 hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all flex items-center gap-3 overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                        Reiniciar Búsqueda
                        <ArrowRight class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" />
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Optimizaciones de rendimiento para animaciones */
.animate-in {
    will-change: transform, opacity;
}
</style>
