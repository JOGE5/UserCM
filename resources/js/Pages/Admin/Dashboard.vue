<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Users, UserCheck, UserX, GraduationCap, Building2, BookOpen, Ratio, FileText, MessageSquare, Flag, TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    forecast: Object,
});

const cards = [
    { label: 'Usuarios',      value: props.stats.total_usuarios,      icon: Users,         color: 'bg-indigo-500/10 text-indigo-400' },
    { label: 'Universidades', value: props.stats.total_universidades, icon: GraduationCap, color: 'bg-blue-500/10 text-blue-400' },
    { label: 'Publicaciones', value: props.stats.total_publicaciones, icon: FileText,      color: 'bg-emerald-500/10 text-emerald-400' },
    { label: 'Foros',         value: props.stats.total_foros,         icon: MessageSquare, color: 'bg-purple-500/10 text-purple-400' },
    { label: 'Reportes',      value: props.stats.total_reportes,      icon: Flag,          color: 'bg-red-500/10 text-red-400' },
];
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="space-y-8">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-black text-white">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Resumen general del sistema</p>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <div v-for="(card, index) in cards" :key="card.label"
                    class="relative group bg-gray-900/60 backdrop-blur-xl rounded-2xl p-5 overflow-hidden shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    
                    <div class="flex items-center gap-4 relative z-10">
                        <div :class="['p-3 rounded-2xl shadow-inner backdrop-blur-md', card.color]">
                            <component :is="card.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white tracking-tight">{{ card.value }}</p>
                            <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mt-0.5">{{ card.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila inferior -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Forecast Section (Compacto) -->
                <div class="bg-gray-900/60 backdrop-blur-xl rounded-3xl p-7 shadow-lg relative overflow-hidden flex flex-col justify-between group">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 to-transparent z-0 pointer-events-none"></div>
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-[50px] rounded-full"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-sm font-black uppercase tracking-widest text-indigo-400 mb-2 flex items-center gap-2">
                            <TrendingUp class="w-4 h-4" /> Predicción de Actividad
                        </h2>
                        <p class="text-xs text-gray-400">Demanda esperada para la próxima semana.</p>
                    </div>
                    
                    <div class="relative z-10 mt-6 flex items-center justify-between">
                        <div>
                            <p class="text-5xl font-black drop-shadow-lg"
                                :class="{
                                    'text-emerald-400': forecast.trend === 'up',
                                    'text-rose-400': forecast.trend === 'down',
                                    'text-gray-300': forecast.trend === 'stable' || forecast.trend === 'insufficient_data'
                                }">
                                {{ forecast.prediction !== null ? forecast.prediction : 0 }}
                            </p>
                            <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-widest">Nuevas Pubs.</p>
                        </div>
                        
                        <div class="flex flex-col items-center justify-center gap-1">
                            <div class="p-3 rounded-xl shadow-inner" :class="{
                                'bg-emerald-500/20 text-emerald-400': forecast.trend === 'up',
                                'bg-rose-500/20 text-rose-400': forecast.trend === 'down',
                                'bg-gray-800 text-gray-400': forecast.trend === 'stable' || forecast.trend === 'insufficient_data'
                            }">
                                <TrendingUp v-if="forecast.trend === 'up'" class="w-6 h-6" />
                                <TrendingDown v-else-if="forecast.trend === 'down'" class="w-6 h-6" />
                                <Minus v-else class="w-6 h-6" />
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest" :class="{
                                'text-emerald-400': forecast.trend === 'up',
                                'text-rose-400': forecast.trend === 'down',
                                'text-gray-500': forecast.trend === 'stable' || forecast.trend === 'insufficient_data'
                            }">{{ forecast.trend === 'up' ? 'Al alza' : (forecast.trend === 'down' ? 'A la baja' : 'Estable') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Usuarios por rol -->
                <div class="bg-gray-900/60 backdrop-blur-xl rounded-3xl p-7 shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-[50px] rounded-full pointer-events-none"></div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-indigo-400 mb-6 flex items-center gap-2 relative z-10">
                        <Users class="w-4 h-4" /> Distribución
                    </h2>
                    <div class="space-y-4 relative z-10">
                        <div v-for="item in stats.usuarios_por_rol" :key="item.Nombre_Rol"
                            class="flex items-center justify-between group">
                            <span class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors">{{ item.Nombre_Rol }}</span>
                            <span class="text-sm font-black text-white bg-indigo-500/20 px-3 py-1 rounded-xl shadow-sm">
                                {{ item.total }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Top universidades -->
                <div class="bg-gray-900/60 backdrop-blur-xl rounded-3xl p-7 shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-blue-500/10 blur-[50px] rounded-full pointer-events-none"></div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-blue-400 mb-6 flex items-center gap-2 relative z-10">
                        <Building2 class="w-4 h-4" /> Universidades
                    </h2>
                    <div class="space-y-5 relative z-10">
                        <div v-for="(item, i) in stats.universidades_top" :key="item.Nombre_Universidad"
                            class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-gray-800 text-[10px] font-black text-gray-400 shadow-inner">
                                {{ i + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-end mb-1.5">
                                    <p class="text-xs font-bold text-gray-200 truncate">{{ item.Nombre_Universidad }}</p>
                                    <span class="text-[10px] font-black text-blue-400">{{ item.total }} <span class="text-gray-600 font-medium">usrs</span></span>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-800/80 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-400 rounded-full"
                                        :style="{ width: `${(item.total / stats.total_usuarios) * 100}%` }" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
