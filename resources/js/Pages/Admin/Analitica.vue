<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Activity, Users, Clock, Server, TrendingUp, AlertTriangle, CheckCircle2, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    markov: Object, // { estados, matriz, estacionaria, distribucionActual, totalCalculados }
    colas: Object,
});

const recalculando = ref(false);
const recalcular = () => {
    recalculando.value = true;
    router.post(route('admin.analitica.recalcular'), {}, {
        preserveScroll: true,
        onFinish: () => { recalculando.value = false; },
    });
};

const stateColor = {
    Malo:      { text: 'text-rose-400',    bar: 'bg-rose-500' },
    Regular:   { text: 'text-amber-400',   bar: 'bg-amber-500' },
    Bueno:     { text: 'text-sky-400',     bar: 'bg-sky-500' },
    Excelente: { text: 'text-emerald-400', bar: 'bg-emerald-500' },
};
const color = (e) => stateColor[e] || { text: 'text-gray-300', bar: 'bg-gray-500' };

const cellStyle = (v) => ({ backgroundColor: `rgba(129,140,248,${0.06 + v * 0.85})` });
const pct = (v) => (v * 100).toFixed(1) + '%';

const totalActual = computed(() => props.markov.totalCalculados || 0);
const actualPct = (e) => {
    const t = totalActual.value;
    return t > 0 ? (props.markov.distribucionActual[e] / t) * 100 : 0;
};

const colaCards = computed(() => {
    const c = props.colas;
    return [
        { label: 'λ — Llegadas/día', value: c.lambda, icon: TrendingUp, color: 'bg-indigo-500/10 text-indigo-400' },
        { label: 'μ — Servicio/mod/día', value: c.mu, icon: Server, color: 'bg-sky-500/10 text-sky-400' },
        { label: 'c — Moderadores', value: c.c, icon: Users, color: 'bg-fuchsia-500/10 text-fuchsia-400' },
        { label: 'ρ — Utilización', value: (typeof c.rho === 'number' ? (c.rho * 100).toFixed(0) + '%' : c.rho), icon: Activity, color: 'bg-amber-500/10 text-amber-400' },
    ];
});
</script>

<template>
    <AdminLayout title="Analítica de Modelos">
        <div class="space-y-8">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <Activity class="w-6 h-6 text-indigo-400" /> Analítica de Modelos
                </h1>
                <p class="text-sm text-gray-500 mt-1">Modelos de Investigación de Operaciones aplicados a Campus Market.</p>
            </div>

            <!-- ===================== CADENA DE MARKOV ===================== -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <TrendingUp class="w-5 h-5 text-fuchsia-400" />
                    <h2 class="text-lg font-black text-white">Tendencia de Reputación</h2>
                </div>
                <p class="text-xs text-gray-500 max-w-3xl">
                    Cada usuario está en uno de 4 estados de reputación. La matriz <strong class="text-gray-300">P</strong> da la probabilidad
                    de pasar de un estado a otro. La <strong class="text-gray-300">distribución estacionaria π</strong> (πP = π) indica el
                    porcentaje de usuarios en cada estado <strong class="text-gray-300">a largo plazo</strong>, sin importar dónde empezaron.
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Matriz P -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <h3 class="text-sm font-bold text-white mb-4">Matriz de transición P</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-center">
                                <thead>
                                    <tr>
                                        <th class="p-2"></th>
                                        <th v-for="e in markov.estados" :key="e" class="p-2 text-[10px] font-black uppercase tracking-wider" :class="color(e).text">{{ e }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(fila, i) in markov.matriz" :key="i">
                                        <th class="p-2 text-[10px] font-black uppercase tracking-wider text-right whitespace-nowrap" :class="color(markov.estados[i]).text">{{ markov.estados[i] }}</th>
                                        <td v-for="(val, j) in fila" :key="j" class="p-0">
                                            <div class="m-0.5 rounded-lg py-3 text-xs font-bold text-white" :style="cellStyle(val)">
                                                {{ val.toFixed(2) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] text-gray-600 mt-3">Fila = estado actual · Columna = estado siguiente · cada fila suma 1.</p>
                    </div>

                    <!-- Distribución estacionaria -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <h3 class="text-sm font-bold text-white mb-4">Distribución estacionaria π <span class="text-gray-500 font-medium">(largo plazo)</span></h3>
                        <div class="space-y-4">
                            <div v-for="e in markov.estados" :key="e">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold" :class="color(e).text">{{ e }}</span>
                                    <span class="text-xs font-black text-white">{{ pct(markov.estacionaria[e]) }}</span>
                                </div>
                                <div class="h-2.5 rounded-full bg-gray-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500" :class="color(e).bar" :style="{ width: pct(markov.estacionaria[e]) }"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-600 mt-4">Calculada por iteración de potencias sobre P. Es independiente del estado inicial.</p>
                    </div>
                </div>

                <!-- Distribución actual real -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="text-sm font-bold text-white">Distribución actual de usuarios <span class="text-gray-500 font-medium">({{ totalActual }} con reputación calculada)</span></h3>
                        <button @click="recalcular" :disabled="recalculando"
                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-bold text-white bg-fuchsia-600 rounded-xl hover:bg-fuchsia-500 transition-all disabled:opacity-50 shrink-0">
                            <RefreshCw class="w-3.5 h-3.5" :class="recalculando ? 'animate-spin' : ''" /> Recalcular
                        </button>
                    </div>
                    <div v-if="totalActual > 0" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div v-for="e in markov.estados" :key="e" class="bg-gray-800/40 border border-gray-800 rounded-xl p-4">
                            <p class="text-2xl font-black text-white">{{ markov.distribucionActual[e] }}</p>
                            <p class="text-xs font-bold" :class="color(e).text">{{ e }}</p>
                            <div class="mt-2 h-1.5 rounded-full bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full" :class="color(e).bar" :style="{ width: actualPct(e) + '%' }"></div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">{{ actualPct(e).toFixed(0) }}%</p>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-500 py-4 text-center">
                        Aún no hay usuarios con estado de reputación calculado. Se llenará cuando los usuarios reciban calificaciones.
                        <br>Compara esta distribución real vs. la estacionaria π para ver hacia dónde tiende el sistema.
                    </p>
                </div>

                <p class="text-[10px] text-gray-600">Modelo: cadena de Markov de 4 estados · la distribución estacionaria se obtiene por iteración de potencias sobre P.</p>
            </section>

            <!-- ===================== TEORÍA DE COLAS ===================== -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Clock class="w-5 h-5 text-sky-400" />
                    <h2 class="text-lg font-black text-white">Teoría de Colas — Cola de Reportes (M/M/c)</h2>
                    <span v-if="colas.usando_ejemplo" class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full">datos de ejemplo</span>
                </div>
                <p class="text-xs text-gray-500 max-w-3xl">
                    Los reportes llegan a una tasa <strong class="text-gray-300">λ</strong> y los moderadores los resuelven a tasa
                    <strong class="text-gray-300">μ</strong>. El modelo M/M/c con prioridades estima la saturación y los tiempos de espera.
                </p>

                <!-- Parámetros -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="m in colaCards" :key="m.label" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex items-center gap-3">
                        <div :class="['p-2.5 rounded-xl', m.color]"><component :is="m.icon" class="w-5 h-5" /></div>
                        <div>
                            <p class="text-xl font-black text-white leading-none">{{ m.value }}</p>
                            <p class="text-[10px] text-gray-500 font-medium mt-1">{{ m.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Estabilidad + métricas -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center gap-4">
                        <div :class="['p-3 rounded-xl', colas.estable ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400']">
                            <CheckCircle2 v-if="colas.estable" class="w-7 h-7" />
                            <AlertTriangle v-else class="w-7 h-7" />
                        </div>
                        <div>
                            <p class="text-sm font-black text-white">{{ colas.estable ? 'Sistema estable' : 'Sistema saturado' }}</p>
                            <p class="text-xs text-gray-500">{{ colas.estable ? 'Los moderadores dan abasto (ρ < 1).' : 'La cola crece sin control (ρ ≥ 1): faltan moderadores.' }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-3">Cola y espera</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-400">Lq — Reportes en cola</span><span class="font-bold text-white">{{ colas.Lq }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Wq — Espera en cola</span><span class="font-bold text-white">{{ colas.Wq }} días</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">W — Tiempo total</span><span class="font-bold text-white">{{ colas.W }} días</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">P₀ — Sistema vacío</span><span class="font-bold text-white">{{ colas.P0 }}</span></div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-3">Espera por prioridad</p>
                        <div class="space-y-3 text-sm">
                            <div>
                                <div class="flex justify-between mb-1"><span class="text-rose-400 font-bold">Urgentes (ofensivo)</span><span class="font-bold text-white">{{ colas.Wq1 }} días</span></div>
                                <p class="text-[10px] text-gray-500">Reportes de alta prioridad se atienden primero.</p>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1"><span class="text-amber-400 font-bold">Normales</span><span class="font-bold text-white">{{ colas.Wq2 }} días</span></div>
                                <p class="text-[10px] text-gray-500">Reportes menores esperan más.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
