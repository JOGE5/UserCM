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
    ArrowRight,
    SlidersHorizontal,
    X,
    ChevronDown,
} from 'lucide-vue-next';

const props = defineProps({
    // Objeto paginado: { data: [], total, last_page, links, ... }
    publicaciones: { type: Object, default: () => ({ data: [], total: 0, last_page: 1, links: [] }) },
    mejores:       { type: Array,  default: () => [] },
    currentUserId: Number,
    userEstado:    String,
    filters:       { type: Object, default: () => ({}) },
});

import { onMounted, watch } from 'vue';

const route = window.route;
const page  = usePage();

const filterSearch    = ref(props.filters?.search ?? '');
const filterPrecioMin = ref(props.filters?.precio_min ?? '');
const filterPrecioMax = ref(props.filters?.precio_max ?? '');
const filterCondicion = ref(props.filters?.condicion ?? '');
const filterOrden     = ref(props.filters?.orden ?? 'fecha_desc');

const hasActiveFilters = computed(() =>
    !!filterSearch.value ||
    !!filterPrecioMin.value ||
    !!filterPrecioMax.value ||
    !!filterCondicion.value ||
    filterOrden.value !== 'fecha_desc'
);

function applyFilters() {
    router.get(route('dashboard'), {
        ...(filterSearch.value ? { search: filterSearch.value } : {}),
        ...(filterPrecioMin.value ? { precio_min: filterPrecioMin.value } : {}),
        ...(filterPrecioMax.value ? { precio_max: filterPrecioMax.value } : {}),
        ...(filterCondicion.value ? { condicion: filterCondicion.value } : {}),
        ...(filterOrden.value !== 'fecha_desc' ? { orden: filterOrden.value } : {}),
    }, { preserveState: true, preserveScroll: true, replace: true });
}

let filterTimeout = null;
watch([filterSearch, filterPrecioMin, filterPrecioMax, filterCondicion, filterOrden], () => {
    if (filterTimeout) clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

function clearFilters() {
    filterSearch.value    = '';
    filterPrecioMin.value = '';
    filterPrecioMax.value = '';
    filterCondicion.value = '';
    filterOrden.value     = 'fecha_desc';
    router.get(route('dashboard'), {}, { preserveState: false });
}

function goToPage(url) {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: false });
}

// Contacto/Chat logic
const contactingId = ref(null);

async function handleContact(publicationId) {
    const publication = props.publicaciones?.data?.find(pub => pub.id === publicationId)
                     || props.mejores?.find(pub => pub.id === publicationId);

    const sellerId = publication?.vendedor?.user?.id ?? publication?.vendedor?.user_id;
    if (!sellerId) return;
    if (sellerId === page.props.auth.user.id) return;
    if (contactingId.value === publicationId) return;

    contactingId.value = publicationId;
    try {
        const { data } = await axios.post(route('chats.private.create'), { seller_id: sellerId });
        const { chat_id } = data;

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

        <div class="space-y-10">
            <!-- Barra de Filtros Unificada, Compacta y en Tiempo Real -->
            <div class="sticky top-[80px] z-50 p-2 glass-panel border border-white/30 dark:border-white/10 rounded-full shadow-2xl shadow-brand-500/10 flex flex-wrap lg:flex-nowrap items-center gap-2 backdrop-blur-3xl bg-white/40 dark:bg-[#0a0a0d]/60 transition-all hover:shadow-brand-500/20">
                <!-- Búsqueda -->
                <div class="relative flex-1 min-w-[200px] group/input">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within/input:text-brand-500 transition-colors" />
                    <input type="text" v-model="filterSearch" placeholder="Buscar producto, foro o novedad..." class="w-full py-2.5 pl-10 pr-4 text-xs font-medium bg-white/60 dark:bg-black/40 border border-transparent focus:border-brand-500/50 focus:bg-white dark:focus:bg-black/60 focus:ring-4 focus:ring-brand-500/10 rounded-full dark:text-white dark:placeholder-gray-500 transition-all shadow-inner">
                </div>
                
                <!-- Precio Min -->
                <div class="relative min-w-[100px] group/input hidden sm:block">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400">Bs.</span>
                    <input type="number" v-model="filterPrecioMin" min="0" placeholder="Mín" class="w-full py-2.5 pl-8 pr-3 text-xs font-medium bg-white/60 dark:bg-black/40 border border-transparent focus:border-brand-500/50 focus:bg-white dark:focus:bg-black/60 focus:ring-4 focus:ring-brand-500/10 rounded-full dark:text-white dark:placeholder-gray-500 transition-all shadow-inner text-center">
                </div>
                
                <!-- Precio Max -->
                <div class="relative min-w-[100px] group/input hidden sm:block">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400">Bs.</span>
                    <input type="number" v-model="filterPrecioMax" min="0" placeholder="Máx" class="w-full py-2.5 pl-8 pr-3 text-xs font-medium bg-white/60 dark:bg-black/40 border border-transparent focus:border-brand-500/50 focus:bg-white dark:focus:bg-black/60 focus:ring-4 focus:ring-brand-500/10 rounded-full dark:text-white dark:placeholder-gray-500 transition-all shadow-inner text-center">
                </div>
                
                <!-- Condición -->
                <div class="relative min-w-[120px]">
                    <select v-model="filterCondicion" class="w-full py-2.5 px-4 text-xs font-bold bg-white/60 dark:bg-black/40 border border-transparent focus:border-brand-500/50 focus:bg-white dark:focus:bg-black/60 focus:ring-4 focus:ring-brand-500/10 rounded-full dark:text-white transition-all shadow-inner appearance-none cursor-pointer">
                        <option value="">Cualquier Condición</option>
                        <option value="nuevo">Nuevo</option>
                        <option value="usado">Usado</option>
                    </select>
                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                </div>
                
                <!-- Ordenar -->
                <div class="relative min-w-[140px]">
                    <select v-model="filterOrden" class="w-full py-2.5 px-4 text-xs font-bold bg-white/60 dark:bg-black/40 border border-transparent focus:border-brand-500/50 focus:bg-white dark:focus:bg-black/60 focus:ring-4 focus:ring-brand-500/10 rounded-full dark:text-white transition-all shadow-inner appearance-none cursor-pointer">
                        <option value="fecha_desc">✨ Más Recientes</option>
                        <option value="fecha_asc">🕰️ Más Antiguos</option>
                        <option value="precio_asc">📉 Menor Precio</option>
                        <option value="precio_desc">📈 Mayor Precio</option>
                    </select>
                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                </div>
                
                <!-- Limpiar -->
                <button v-if="hasActiveFilters" @click="clearFilters" class="p-2.5 text-gray-500 hover:text-rose-500 hover:bg-rose-500/10 bg-white/60 dark:bg-black/40 rounded-full transition-all border border-transparent hover:border-rose-500/30" title="Limpiar Filtros">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Sección Destacados — se oculta si hay filtros del servidor activos -->
            <section v-if="!hasActiveFilters && mejores.length" class="animate-in fade-in slide-in-from-bottom-4 duration-1000">
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

            <!-- Grid Principal — "Novedades del Market" -->
            <section class="animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-200">
                <div class="flex items-center gap-4 mb-10">
                    <div class="p-2.5 rounded-2xl bg-brand-500/10 border border-brand-500/20 shadow-inner">
                        <Layers class="w-6 h-6 text-brand-600 dark:text-brand-400" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                            {{ filterSearch || hasActiveFilters ? 'Resultados Encontrados' : 'Novedades del Market' }}
                    </h2>
                    <p class="text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase tracking-widest">
                        {{ publicaciones.total }} Ofertas disponibles
                    </p>
                </div>
                <div class="flex-1 h-px bg-gradient-to-r from-light-border dark:from-dark-border via-light-border/50 dark:via-dark-border/50 to-transparent ml-4"></div>
            </div>

            <div v-if="publicaciones.data.length" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <CardPubli
                    v-for="pub in publicaciones.data"
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

                <!-- Empty State -->
                <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border-2 border-dashed border-light-border dark:border-dark-border rounded-[4rem] group overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>

                    <div class="relative p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                        <ArchiveX class="w-16 h-16 text-gray-300 dark:text-gray-600 animate-pulse" />
                    </div>

                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Vaya, qué vacío está esto...</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto mb-10 text-lg font-medium leading-relaxed">
                    No hemos podido encontrar ninguna coincidencia para "<span class="text-brand-600 dark:text-brand-400 italic">{{ filterSearch || 'los filtros seleccionados' }}</span>". Intenta con términos más genéricos o limpia los filtros.
                </p>

                <button
                    @click="clearFilters"
                    class="group/btn relative px-10 py-4 bg-brand-600 text-white font-black rounded-2xl shadow-2xl shadow-brand-500/40 hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all flex items-center gap-3 overflow-hidden"
                >
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                        Reiniciar Búsqueda
                        <ArrowRight class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" />
                    </button>
                </div>

                <!-- Controles de paginación -->
                <div v-if="publicaciones.last_page > 1" class="flex flex-wrap items-center justify-center gap-2 mt-12">
                    <button
                        v-for="link in publicaciones.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        v-html="link.label"
                        :class="[
                            'min-w-[2.5rem] h-10 px-3 text-sm font-bold rounded-xl border transition-all duration-200',
                            link.active
                                ? 'bg-brand-500 border-brand-400 text-white shadow-lg shadow-brand-500/30 scale-105'
                                : link.url
                                    ? 'bg-white dark:bg-dark-surface border-light-border dark:border-dark-border text-gray-600 dark:text-gray-300 hover:border-brand-500/50 hover:scale-105'
                                    : 'bg-white/50 dark:bg-dark-surface/50 border-light-border dark:border-dark-border text-gray-300 dark:text-gray-600 cursor-not-allowed'
                        ]"
                    />
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-in {
    will-change: transform, opacity;
}
</style>
