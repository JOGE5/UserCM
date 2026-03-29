<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CardPubli from '@/Components/CardPubli.vue';
import { 
    Archive, 
    Plus, 
    ChevronRight,
    Search,
    Sparkles,
    TrendingUp,
    ArrowRight,
    Layers
} from 'lucide-vue-next';
import { usePage, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    borradores: { type: Array, default: () => [] },
    currentUserId: Number,
});

const page = usePage();

function handleEdit(id) {
    router.visit(route('publicaciones.edit', id));
}
</script>

<template>
    <AppLayout title="Mis Borradores">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
                    <Archive class="w-3 h-3" />
                    <span>Gestión de Contenido</span>
                    <ChevronRight class="w-2 h-2" />
                    <span>Borradores</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Tus <span class="text-brand-600 dark:text-brand-400">Borradores</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl">
                    Aquí se guardan tus publicaciones que aún no han sido lanzadas al Marketplace.
                </p>
            </div>
        </template>

        <div class="space-y-16 pb-20">
            <!-- Sección de Encabezado de Lista -->
            <div v-if="borradores.length > 0" class="flex items-center gap-4 mb-10 animate-in fade-in slide-in-from-left-4 duration-700">
                <div class="p-2.5 rounded-2xl bg-brand-500/10 border border-brand-500/20 shadow-inner">
                    <Layers class="w-6 h-6 text-brand-600 dark:text-brand-400" />
                </div>
                <div class="flex flex-col">
                    <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Publicaciones en Pausa</h2>
                    <p class="text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase tracking-widest">{{ borradores.length }} Borradores pendientes</p>
                </div>
                <div class="flex-1 h-px bg-gradient-to-r from-light-border dark:from-dark-border via-light-border/50 dark:via-dark-border/50 to-transparent ml-4"></div>
            </div>

            <!-- Grid de Borradores -->
            <div v-if="borradores.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 animate-in fade-in slide-in-from-bottom-8 duration-1000">
                <CardPubli
                    v-for="pub in borradores"
                    :key="pub.id"
                    :title="pub.Titulo_Publicacion"
                    :subtitle="`Bs ${pub.Precio_Publicacion}`"
                    :description="pub.Descripcion_Publicacion"
                    :category="pub.categoria?.Nombre_Categoria"
                    :id="pub.id"
                    :user="pub.vendedor?.user"
                    :currentUserId="currentUserId"
                    :isOwner="true"
                    :estado="pub.estado"
                    :publicacion="pub"
                    @edit="handleEdit"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border-2 border-dashed border-light-border dark:border-dark-border rounded-[4rem] group overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                
                <div class="relative p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                    <Archive class="w-16 h-16 text-gray-300 dark:text-gray-600" />
                </div>
                
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">No tienes ningún borrador</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto mb-10 text-lg font-medium leading-relaxed">
                    Todas tus publicaciones están activas o aún no has empezado a redactar tu próxima gran oferta.
                </p>
                
                <Link 
                    :href="route('dashboard.create')" 
                    class="group/btn relative px-10 py-4 bg-brand-600 text-white font-black rounded-2xl shadow-2xl shadow-brand-500/20 hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all flex items-center gap-3 overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                    <Plus class="w-5 h-5" />
                    Iniciar Nueva Publicación
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
