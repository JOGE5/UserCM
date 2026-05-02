<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CardPubli from '@/Components/CardPubli.vue';
import {
    Heart,
    ChevronRight,
    Search,
    Sparkles,
    ArrowRight
} from 'lucide-vue-next';
import { usePage, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const route = window.route;

const props = defineProps({
    favoritos: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = page.props.auth.user.id;
const contactingId = ref(null);

async function handleContact(publicationId) {
    const fav = props.favoritos.find(f => f.publicacion.id === publicationId);
    if (!fav?.publicacion?.vendedor?.user_id) return;

    const sellerId = fav.publicacion.vendedor.user_id;
    if (sellerId === currentUserId) return;
    if (contactingId.value === publicationId) return;

    contactingId.value = publicationId;
    try {
        const { data } = await axios.post(route('chats.private.create'), { seller_id: sellerId });
        const { chat_id } = data;

        const pub = fav.publicacion;
        const imgRaw = pub.Imagen_Publicacion;
        let imagen = null;
        if (imgRaw) {
            try { const p = JSON.parse(imgRaw); imagen = `/files/publicaciones/${(Array.isArray(p) ? p[0] : p).split('/').pop()}`; }
            catch { imagen = `/files/publicaciones/${String(imgRaw).split('/').pop()}`; }
        }

        await axios.post(route('chats.messages.store', chat_id), {
            type: 'product_card',
            metadata: {
                publicacion_id: pub.id,
                titulo:  pub.Titulo_Publicacion,
                precio:  pub.Precio_Publicacion,
                imagen,
                url: route('publicaciones.show', pub.id),
            },
        });

        router.visit(route('chats.show', chat_id));
    } catch {
        alert('Error al iniciar el chat. Intenta de nuevo.');
    } finally {
        contactingId.value = null;
    }
}
</script>

<template>
    <AppLayout title="Mis Favoritos">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-rose-500 dark:text-rose-400 font-black tracking-widest text-[10px] uppercase">
                    <Heart class="w-3 h-3 fill-current" />
                    <span>Mi Selección</span>
                    <ChevronRight class="w-2 h-2" />
                    <span>Favoritos</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Artículos <span class="text-rose-600 dark:text-rose-400">Favoritos</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl">
                    Aquí encontrarás todos los artículos que has marcado con un corazón para darles seguimiento.
                </p>
            </div>
        </template>

        <div class="space-y-16 pb-20">
            <!-- Sección de Encabezado de Lista -->
            <div v-if="favoritos.length > 0" class="flex items-center gap-4 mb-10 animate-in fade-in slide-in-from-left-4 duration-700">
                <div class="p-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 shadow-inner">
                    <Sparkles class="w-6 h-6 text-rose-500" />
                </div>
                <div class="flex flex-col">
                    <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Tu Colección</h2>
                    <p class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest">{{ favoritos.length }} Artículos guardados</p>
                </div>
                <div class="flex-1 h-px bg-gradient-to-r from-light-border dark:from-dark-border via-light-border/50 dark:via-dark-border/50 to-transparent ml-4"></div>
            </div>

            <!-- Grid de Favoritos -->
            <div v-if="favoritos.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 animate-in fade-in slide-in-from-bottom-8 duration-1000">
                <CardPubli
                    v-for="fav in favoritos"
                    :key="fav.id"
                    :title="fav.publicacion.Titulo_Publicacion"
                    :subtitle="`Bs ${fav.publicacion.Precio_Publicacion}`"
                    :description="fav.publicacion.Descripcion_Publicacion"
                    :category="fav.publicacion.categoria?.Nombre_Categoria"
                    :id="fav.publicacion.id"
                    :user="fav.publicacion.vendedor?.user"
                    :currentUserId="currentUserId"
                    :initialIsFavorite="true"
                    :publicacion="fav.publicacion"
                    @contact="handleContact"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border-2 border-dashed border-light-border dark:border-dark-border rounded-[4rem] group overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-colors"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-colors"></div>
                
                <div class="relative p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                    <Heart class="w-16 h-16 text-rose-300 dark:text-rose-900/40 animate-pulse" />
                </div>
                
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Tu lista de deseos está vacía</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto mb-10 text-lg font-medium leading-relaxed">
                    Navega por el marketplace y guarda los artículos que más te gusten para verlos después.
                </p>
                
                <Link 
                    :href="route('dashboard')" 
                    class="group/btn relative px-10 py-4 bg-brand-600 text-white font-black rounded-2xl shadow-2xl shadow-brand-500/20 hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all flex items-center gap-3 overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                    <Search class="w-5 h-5" />
                    Explorar Marketplace
                    <ArrowRight class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" />
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-in {
    will-change: transform, opacity;
}
</style>
