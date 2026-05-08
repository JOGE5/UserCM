<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Search, BadgeCheck, ShieldCheck, User, ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';

const props = defineProps({
    usuarios: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const rolFilter = ref(props.filters?.rol ?? '');

let debounce = null;
watch([search, rolFilter], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.usuarios'), { search: search.value, rol: rolFilter.value }, { preserveState: true, replace: true });
    }, 300);
});

const rolColors = {
    1: 'bg-rose-500/10 border-rose-500/20 text-rose-400',
    2: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
    3: 'bg-gray-700/50 border-gray-600 text-gray-400',
};

const toggleVerificado = (userId) => {
    router.patch(route('admin.usuarios.verificado', userId), {}, { preserveScroll: true });
};

const updateRol = (userId, codRol) => {
    router.patch(route('admin.usuarios.rol', userId), { Cod_Rol: codRol }, { preserveScroll: true });
};

const openMenuId = ref(null);
const toggleMenu = (id) => { openMenuId.value = openMenuId.value === id ? null : id; };
</script>

<template>
    <AdminLayout title="Usuarios">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white">Gestión de Usuarios</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ usuarios.total }} usuarios registrados</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o email..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all"
                    />
                </div>
                <select
                    v-model="rolFilter"
                    class="px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all"
                >
                    <option value="">Todos los roles</option>
                    <option v-for="r in roles" :key="r.Cod_Rol" :value="r.Cod_Rol">{{ r.Nombre_Rol }}</option>
                </select>
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Usuario</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Universidad</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Rol</th>
                                <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Verificado</th>
                                <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-700 shrink-0">
                                            <img v-if="u.profile_photo_url" :src="u.profile_photo_url" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold text-gray-400">{{ u.name?.charAt(0) }}</div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white text-xs">{{ u.name }}</p>
                                            <p class="text-[10px] text-gray-500">{{ u.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-xs text-gray-400">{{ u.usuario_campus_market?.universidad?.Nombre_Universidad ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <select
                                        :value="u.usuario_campus_market?.Cod_Rol"
                                        @change="updateRol(u.id, $event.target.value)"
                                        class="px-2 py-1 text-[10px] font-black rounded-lg border outline-none transition-all cursor-pointer"
                                        :class="rolColors[u.usuario_campus_market?.Cod_Rol] ?? 'bg-gray-700 border-gray-600 text-gray-400'"
                                    >
                                        <option v-for="r in roles" :key="r.Cod_Rol" :value="r.Cod_Rol" class="bg-gray-800 text-white">{{ r.Nombre_Rol }}</option>
                                    </select>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button @click="toggleVerificado(u.id)" class="group relative inline-flex items-center gap-1.5">
                                        <div class="w-10 h-5 rounded-full transition-colors" :class="u.usuario_campus_market?.verificado ? 'bg-indigo-600' : 'bg-gray-700'">
                                            <div class="w-4 h-4 rounded-full bg-white shadow mt-0.5 transition-transform" :class="u.usuario_campus_market?.verificado ? 'translate-x-5' : 'translate-x-0.5'"></div>
                                        </div>
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <Link :href="route('usuarios.show', u.id)" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-black text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-all">
                                        <User class="w-3 h-3" />
                                        Ver perfil
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!usuarios.data.length">
                                <td colspan="5" class="px-5 py-12 text-center text-gray-500 text-sm">No se encontraron usuarios.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="usuarios.last_page > 1" class="flex items-center justify-between px-5 py-4 border-t border-gray-800">
                    <span class="text-xs text-gray-500">Página {{ usuarios.current_page }} de {{ usuarios.last_page }}</span>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="usuarios.prev_page_url"
                            :href="usuarios.prev_page_url"
                            class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </Link>
                        <Link
                            v-if="usuarios.next_page_url"
                            :href="usuarios.next_page_url"
                            class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
