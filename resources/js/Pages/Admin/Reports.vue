<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Search, EyeOff, ExternalLink, ChevronLeft, ChevronRight, Flag } from 'lucide-vue-next';

const props = defineProps({
    reportes: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

let debounce = null;
watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.reportes'), { search: search.value }, { preserveState: true, replace: true });
    }, 300);
});

const ocultarPublicacion = (pubId) => {
    router.patch(route('admin.reportes.ocultar', pubId), {}, { preserveScroll: true });
};

const formatDate = (d) => d ? new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d)) : '—';
</script>

<template>
    <AdminLayout title="Reportes">
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-black text-white">Centro de Reportes</h1>
                <p class="text-xs text-gray-500 mt-1">{{ reportes.total }} reportes registrados</p>
            </div>

            <!-- Filtros -->
            <div class="relative max-w-sm">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                <input v-model="search" type="text" placeholder="Buscar por publicación..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Publicación reportada</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Reportado por</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Razón</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Fecha</th>
                                <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="r in reportes.data" :key="r.id" class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-4 max-w-xs">
                                    <div class="flex items-center gap-2">
                                        <Flag class="w-3.5 h-3.5 text-rose-500 shrink-0" />
                                        <div>
                                            <p class="font-bold text-white text-xs truncate max-w-[180px]">{{ r.Titulo_Publicacion ?? 'Sin título' }}</p>
                                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded border"
                                                :class="r.pub_estado === 'oculta' ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'">
                                                {{ r.pub_estado ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-xs text-gray-300">{{ r.reporter_name ?? '—' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ r.reporter_email ?? '' }}</p>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="text-xs text-gray-400 line-clamp-2">{{ r.reason ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-[10px] text-gray-500">{{ formatDate(r.created_at) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link v-if="r.reportable_id" :href="route('publicaciones.show', r.reportable_id)" target="_blank"
                                            class="p-1.5 text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-all" title="Ver publicación">
                                            <ExternalLink class="w-3.5 h-3.5" />
                                        </Link>
                                        <button v-if="r.pub_estado !== 'oculta' && r.reportable_id" @click="ocultarPublicacion(r.reportable_id)"
                                            class="p-1.5 text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-lg hover:bg-rose-500/20 transition-all" title="Ocultar publicación">
                                            <EyeOff class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!reportes.data.length">
                                <td colspan="5" class="px-5 py-12 text-center text-gray-500 text-sm">No hay reportes.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="reportes.last_page > 1" class="flex items-center justify-between px-5 py-4 border-t border-gray-800">
                    <span class="text-xs text-gray-500">Página {{ reportes.current_page }} de {{ reportes.last_page }}</span>
                    <div class="flex gap-2">
                        <Link v-if="reportes.prev_page_url" :href="reportes.prev_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronLeft class="w-4 h-4" /></Link>
                        <Link v-if="reportes.next_page_url" :href="reportes.next_page_url" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"><ChevronRight class="w-4 h-4" /></Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
