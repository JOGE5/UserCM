<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Search, MessageSquare, Trash2, Shield, EyeOff, ExternalLink, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    foros: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const estadoFilter = ref(props.filters?.estado ?? '');

let debounce = null;
watch([search, estadoFilter], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.foros'), { search: search.value, estado: estadoFilter.value }, { preserveState: true, replace: true });
    }, 300);
});

const updateEstado = (foroId, estado) => {
    router.patch(route('admin.foros.update', foroId), { Estado_Foro: parseInt(estado) }, { preserveScroll: true });
};

const deleteForo = (id) => {
    if (confirm('¿Estás seguro de eliminar este foro? Se borrarán todos sus comentarios. Esta acción es irreversible.')) {
        router.delete(route('admin.foros.destroy', id), { preserveScroll: true });
    }
};

const estadoColors = {
    1: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    0: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
};
</script>

<template>
    <AdminLayout title="Moderación de Foros">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white flex items-center gap-2">
                        <MessageSquare class="w-6 h-6 text-indigo-400" /> Moderación de Foros
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">{{ foros.total }} discusiones en la plataforma</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input v-model="search" type="text" placeholder="Buscar por título..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-900 border border-gray-800 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                </div>
                <select v-model="estadoFilter"
                    class="px-4 py-2.5 text-sm bg-gray-900 border border-gray-800 rounded-xl text-white outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Ocultos (Moderados)</option>
                </select>
            </div>

            <!-- Lista de Foros -->
            <div class="grid grid-cols-1 gap-4">
                <div v-for="foro in foros.data" :key="foro.ID_Foro" class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-indigo-500/30 transition-all">
                    <div class="flex flex-col md:flex-row gap-4 md:items-start justify-between">
                        
                        <!-- Info Principal -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border" :class="estadoColors[foro.Estado_Foro] || 'bg-gray-800 text-gray-400'">
                                    {{ foro.Estado_Foro == 1 ? 'Activo' : 'Oculto' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ new Date(foro.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-1">{{ foro.Titulo_Foro }}</h3>
                            <p class="text-sm text-gray-400 line-clamp-2 mb-3">{{ foro.Descripcion_Foro }}</p>
                            
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center gap-1.5 text-gray-500">
                                    <div class="w-5 h-5 rounded-full bg-gray-800 flex items-center justify-center font-bold text-white text-[10px]">
                                        {{ foro.autor?.name?.charAt(0) || 'A' }}
                                    </div>
                                    <span>{{ foro.autor?.name || 'Autor Desconocido' }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-gray-500">
                                    <MessageSquare class="w-3.5 h-3.5" />
                                    <span>{{ foro.comentarios_count }} respuestas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-row md:flex-col items-center gap-2 shrink-0 md:w-48 bg-gray-800/30 p-3 rounded-xl border border-gray-800/50">
                            <h4 class="text-[10px] uppercase font-black tracking-widest text-gray-500 w-full mb-1 hidden md:block">Acciones de Moderador</h4>
                            
                            <!-- Select de Estado -->
                            <select :value="foro.Estado_Foro" @change="updateEstado(foro.ID_Foro, $event.target.value)"
                                class="w-full px-2 py-1.5 text-xs font-bold bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-1 focus:ring-indigo-500 outline-none cursor-pointer">
                                <option :value="1">✓ Mantener Activo</option>
                                <option :value="0">🚫 Ocultar (Inapropiado)</option>
                            </select>
                            
                            <div class="flex gap-2 w-full mt-1">
                                <Link :href="route('productos.show', foro.ID_Foro)" target="_blank"
                                    class="flex-1 flex items-center justify-center gap-1.5 p-1.5 text-[10px] font-bold text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-all" title="Ver en la plataforma">
                                    <ExternalLink class="w-3 h-3" /> Ver Foro
                                </Link>
                                <button @click="deleteForo(foro.ID_Foro)"
                                    class="flex items-center justify-center p-1.5 text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-lg transition-all" title="Eliminar Foro">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="foros.data.length === 0" class="py-12 text-center text-gray-500 bg-gray-900 border border-gray-800 rounded-2xl">
                    No se encontraron foros.
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="foros.last_page > 1" class="flex items-center justify-between px-5 py-4 border-t border-gray-800">
                <span class="text-xs text-gray-500">Página {{ foros.current_page }} de {{ foros.last_page }}</span>
                <div class="flex items-center gap-2">
                    <Link v-if="foros.prev_page_url" :href="foros.prev_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronLeft class="w-4 h-4" /></Link>
                    <Link v-if="foros.next_page_url" :href="foros.next_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronRight class="w-4 h-4" /></Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
