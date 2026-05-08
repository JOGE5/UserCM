<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Search, Eye, ChevronLeft, ChevronRight, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    publicaciones: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const estadoFilter = ref(props.filters?.estado ?? '');

let debounce = null;
watch([search, estadoFilter], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.publicaciones'), { search: search.value, estado: estadoFilter.value }, { preserveState: true, replace: true });
    }, 300);
});

const estadoColors = {
    activa:   'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
    vendida:  'bg-sky-500/10 border-sky-500/20 text-sky-400',
    borrador: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
    oculta:   'bg-rose-500/10 border-rose-500/20 text-rose-400',
};

const cambiarEstado = (id, estado) => {
    router.patch(route('admin.publicaciones.estado', id), { estado }, { preserveScroll: true });
};

const formatPrice = (p) => `Bs. ${Number(p).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;
</script>

<template>
    <AdminLayout title="Publicaciones">
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-black text-white">Gestión de Publicaciones</h1>
                <p class="text-xs text-gray-500 mt-1">{{ publicaciones.total }} publicaciones en total</p>
            </div>

            <!-- Filtros -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input v-model="search" type="text" placeholder="Buscar por título..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                </div>
                <select v-model="estadoFilter"
                    class="px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activa</option>
                    <option value="vendida">Vendida</option>
                    <option value="borrador">Borrador</option>
                    <option value="oculta">Oculta</option>
                </select>
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Publicación</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Vendedor</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Precio</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Estado</th>
                                <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="pub in publicaciones.data" :key="pub.id" class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="font-bold text-white text-xs truncate">{{ pub.Titulo_Publicacion }}</p>
                                    <p class="text-[10px] text-gray-500">{{ pub.categoria?.Nombre_Categoria ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-xs text-gray-300">{{ pub.vendedor?.user?.name ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-xs font-black text-emerald-400">{{ formatPrice(pub.Precio_Publicacion) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <select
                                        :value="pub.estado"
                                        @change="cambiarEstado(pub.id, $event.target.value)"
                                        class="px-2 py-1 text-[10px] font-black rounded-lg border outline-none transition-all cursor-pointer"
                                        :class="estadoColors[pub.estado] ?? 'bg-gray-700 border-gray-600 text-gray-400'"
                                    >
                                        <option value="activa" class="bg-gray-800 text-white">Activa</option>
                                        <option value="oculta" class="bg-gray-800 text-white">Oculta</option>
                                        <option value="borrador" class="bg-gray-800 text-white">Borrador</option>
                                        <option value="vendida" class="bg-gray-800 text-white">Vendida</option>
                                    </select>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <Link :href="route('publicaciones.show', pub.id)" target="_blank"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-black text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-all">
                                        <ExternalLink class="w-3 h-3" />
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!publicaciones.data.length">
                                <td colspan="5" class="px-5 py-12 text-center text-gray-500 text-sm">No se encontraron publicaciones.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="publicaciones.last_page > 1" class="flex items-center justify-between px-5 py-4 border-t border-gray-800">
                    <span class="text-xs text-gray-500">Página {{ publicaciones.current_page }} de {{ publicaciones.last_page }}</span>
                    <div class="flex gap-2">
                        <Link v-if="publicaciones.prev_page_url" :href="publicaciones.prev_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronLeft class="w-4 h-4" /></Link>
                        <Link v-if="publicaciones.next_page_url" :href="publicaciones.next_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronRight class="w-4 h-4" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
