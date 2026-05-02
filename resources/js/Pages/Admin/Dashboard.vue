<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Users, FileText, MessageSquare, Flag } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
});

const cards = [
    { label: 'Usuarios',      value: props.stats.total_usuarios,      icon: Users,        color: 'bg-indigo-500/10 text-indigo-400' },
    { label: 'Publicaciones', value: props.stats.total_publicaciones, icon: FileText,     color: 'bg-emerald-500/10 text-emerald-400' },
    { label: 'Foros',         value: props.stats.total_foros,         icon: MessageSquare,color: 'bg-purple-500/10 text-purple-400' },
    { label: 'Reportes',      value: props.stats.total_reportes,      icon: Flag,         color: 'bg-red-500/10 text-red-400' },
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
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="card in cards" :key="card.label"
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center gap-4">
                    <div :class="['p-3 rounded-xl', card.color]">
                        <component :is="card.icon" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white">{{ card.value }}</p>
                        <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Fila inferior -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Usuarios por rol -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-bold text-white mb-4">Usuarios por Rol</h2>
                    <div class="space-y-3">
                        <div v-for="item in stats.usuarios_por_rol" :key="item.Nombre_Rol"
                            class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">{{ item.Nombre_Rol }}</span>
                            <span class="text-sm font-bold text-white bg-gray-800 px-3 py-0.5 rounded-full">
                                {{ item.total }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Top universidades -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-bold text-white mb-4">Top Universidades</h2>
                    <div class="space-y-3">
                        <div v-for="(item, i) in stats.universidades_top" :key="item.Nombre_Universidad"
                            class="flex items-center gap-3">
                            <span class="text-xs font-black text-gray-600 w-4">{{ i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-300 truncate">{{ item.Nombre_Universidad }}</p>
                                <div class="mt-1 h-1.5 rounded-full bg-gray-800 overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full"
                                        :style="{ width: `${(item.total / stats.total_usuarios) * 100}%` }" />
                                </div>
                            </div>
                            <span class="text-xs font-bold text-gray-400">{{ item.total }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
