<script setup>
import { ref, computed } from 'vue';
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
    Layers,
    CheckCircle2,
    Clock
} from 'lucide-vue-next';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    publicaciones: { type: Array, default: () => [] },
    currentUserId: Number,
});

const page = usePage();

const currentTab = ref('activa');

const tabs = [
    { id: 'activa', name: 'Activas', icon: Sparkles },
    { id: 'borrador', name: 'Borradores', icon: Archive },
    { id: 'vendida', name: 'Vendidas', icon: CheckCircle2 },
    { id: 'oculta', name: 'Pausadas', icon: Clock },
];

const filteredPublicaciones = computed(() => {
    return props.publicaciones.filter(pub => pub.estado === currentTab.value);
});

function handleEdit(id) {
    router.visit(route('publicaciones.edit', id));
}

function openCreateModal() {
    window.dispatchEvent(new CustomEvent('open-create-modal'));
}
</script>

<template>
    <AppLayout title="Mis Publicaciones">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
                    <Layers class="w-3 h-3" />
                    <span>Gestión de Contenido</span>
                    <ChevronRight class="w-2 h-2" />
                    <span>Mis Publicaciones</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            Mis <span class="text-brand-600 dark:text-brand-400">Publicaciones</span>
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl mt-1">
                            Administra todo tu inventario: lo que estás vendiendo, lo que vendiste y tus borradores.
                        </p>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="px-6 py-3 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white rounded-2xl font-black shadow-lg hover:shadow-xl transition-all float-3d flex items-center justify-center gap-2 text-sm"
                    >
                        <Plus class="w-4 h-4" />
                        Vender Producto
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-10 pb-20">
            <!-- Pestañas de Filtrado (Tabs) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-4 custom-scrollbar">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="currentTab = tab.id"
                    :class="[
                        'flex items-center gap-2 px-6 py-3 rounded-[1.5rem] text-sm font-black transition-all whitespace-nowrap border',
                        currentTab === tab.id 
                            ? 'bg-brand-600 text-white border-brand-500 shadow-lg shadow-brand-500/30' 
                            : 'bg-white/40 dark:bg-black/30 text-gray-500 dark:text-gray-400 border-white/50 dark:border-white/10 hover:bg-white/60 dark:hover:bg-white/10'
                    ]"
                >
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.name }}
                    <span :class="[
                        'ml-1 px-2 py-0.5 rounded-full text-[10px]',
                        currentTab === tab.id ? 'bg-white/20' : 'bg-gray-200 dark:bg-white/10'
                    ]">
                        {{ publicaciones.filter(p => p.estado === tab.id).length }}
                    </span>
                </button>
            </div>

            <!-- Grid de Publicaciones -->
            <div v-if="filteredPublicaciones.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 animate-in fade-in slide-in-from-bottom-8 duration-1000">
                <CardPubli
                    v-for="pub in filteredPublicaciones"
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
            <div v-else class="relative flex flex-col items-center justify-center py-24 px-6 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border-2 border-dashed border-light-border dark:border-dark-border rounded-[3rem] group overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl group-hover:bg-purple-500/10 transition-colors"></div>
                
                <div class="relative p-6 rounded-full bg-gray-50 dark:bg-black/40 mb-6 shadow-inner border border-white dark:border-white/5">
                    <component :is="tabs.find(t => t.id === currentTab)?.icon" class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                </div>
                
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">No hay publicaciones en "{{ tabs.find(t => t.id === currentTab)?.name }}"</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8 text-sm font-medium leading-relaxed">
                    {{ currentTab === 'borrador' ? 'No tienes ningún borrador pendiente. ¡Tu inventario está al día!' : 'No se encontraron publicaciones con este estado. ¿Listo para publicar algo nuevo?' }}
                </p>
                
                <button 
                    @click="openCreateModal"
                    class="group/btn relative px-8 py-3.5 bg-brand-600 text-white font-black rounded-2xl shadow-[0_8px_20px_-6px_rgba(124,58,237,0.6)] hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all flex items-center gap-2 overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                    <Plus class="w-4 h-4" />
                    Crear Publicación
                    <ArrowRight class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-in {
    will-change: transform, opacity;
}
</style>
