<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Search, EyeOff, ExternalLink, ChevronLeft, ChevronRight, Flag } from 'lucide-vue-next';

const props = defineProps({
    reportes: Object,
    filters: Object,
    queueMetrics: Object,
});

const search = ref(props.filters?.search ?? '');
const cInput = ref(props.queueMetrics?.c ?? 2);

let debounce = null;
let debounceC = null;

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.reportes'), { search: search.value, c: cInput.value }, { preserveState: true, replace: true });
    }, 300);
});

watch(cInput, () => {
    clearTimeout(debounceC);
    debounceC = setTimeout(() => {
        router.get(route('admin.reportes'), { search: search.value, c: cInput.value }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});
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

            <!-- Header y Filtros -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative max-w-sm w-full">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input v-model="search" type="text" placeholder="Buscar por publicación..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                </div>
            </div>

            <!-- Panel de Teoría de Colas M/M/c -->
            <div v-if="queueMetrics" class="p-6 bg-gradient-to-br from-indigo-900/40 to-slate-900 border border-indigo-500/30 rounded-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.1)_0%,transparent_60%)] pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-lg font-black text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Análisis de Colas de Moderación (M/M/c)
                            </h2>
                            <p class="text-xs text-indigo-200/70 mt-1">Disciplina de prioridades (Reportes Ofensivos vs Menores)</p>
                        </div>
                        <div class="flex items-center gap-3 bg-gray-900/50 p-2.5 rounded-xl border border-gray-800/50">
                            <label class="text-xs font-bold text-gray-400">Moderadores (c):</label>
                            <input v-model="cInput" type="number" min="1" max="20" class="w-16 h-8 bg-black/50 border border-gray-700 rounded-lg text-white text-center text-sm focus:ring-1 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <!-- Tasa Llegada -->
                        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-800/80">
                            <p class="text-[10px] uppercase font-black tracking-widest text-gray-500 mb-1">Llegada (λ)</p>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-black text-white">{{ queueMetrics.lambda }}</span>
                                <span class="text-xs text-gray-500 mb-1">rep/día</span>
                            </div>
                            <div class="text-[10px] mt-1 text-gray-400">
                                <span class="text-rose-400">{{ queueMetrics.lambda1 }}</span> Urg. / <span class="text-emerald-400">{{ queueMetrics.lambda2 }}</span> Menores
                            </div>
                        </div>

                        <!-- Tasa Servicio -->
                        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-800/80">
                            <p class="text-[10px] uppercase font-black tracking-widest text-gray-500 mb-1">Servicio (μ)</p>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-black text-white">{{ queueMetrics.mu }}</span>
                                <span class="text-xs text-gray-500 mb-1">rep/día/mod</span>
                            </div>
                        </div>

                        <!-- Utilización -->
                        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-800/80">
                            <p class="text-[10px] uppercase font-black tracking-widest text-gray-500 mb-1">Utilización (ρ)</p>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-black" :class="queueMetrics.estable ? 'text-emerald-400' : 'text-rose-500'">{{ queueMetrics.rho }}</span>
                            </div>
                            <p class="text-[10px] mt-1 font-bold" :class="queueMetrics.estable ? 'text-emerald-500/70' : 'text-rose-500/70'">
                                {{ queueMetrics.estable ? 'Sistema Estable (< 1)' : 'Inestable (Cola infinita)' }}
                            </p>
                        </div>

                        <!-- En Cola -->
                        <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-800/80">
                            <p class="text-[10px] uppercase font-black tracking-widest text-gray-500 mb-1">En Cola (Lq)</p>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-black text-white">{{ queueMetrics.Lq }}</span>
                                <span class="text-xs text-gray-500 mb-1">reportes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tiempos de espera -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-rose-500/10 border border-rose-500/20 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-rose-400 uppercase tracking-widest">Espera: Ofensivos (Clase 1)</h3>
                                <p class="text-[10px] text-rose-300/70 mt-0.5">Prioridad Alta</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-black text-rose-400">{{ queueMetrics.Wq1 !== '∞' ? (queueMetrics.Wq1 * 24).toFixed(1) : '∞' }} hrs</span>
                                <p class="text-[10px] text-rose-400/50 font-bold mt-0.5">en cola (Wq1)</p>
                            </div>
                        </div>
                        <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Espera: Bugs/Spam (Clase 2)</h3>
                                <p class="text-[10px] text-emerald-300/70 mt-0.5">Prioridad Normal</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-black text-emerald-400">{{ queueMetrics.Wq2 !== '∞' ? (queueMetrics.Wq2 * 24).toFixed(1) : '∞' }} hrs</span>
                                <p class="text-[10px] text-emerald-400/50 font-bold mt-0.5">en cola (Wq2)</p>
                            </div>
                        </div>
                    </div>
                </div>
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
