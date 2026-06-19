<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Tags, Plus, Edit2, Trash2, X, Search, Globe, GraduationCap, Layers } from 'lucide-vue-next';

const props = defineProps({
    categorias: Array,
    carreras: Array,
});

const showModal = ref(false);
const editingId = ref(null);
const search = ref('');
const filterCarrera = ref('all'); // 'all' | 'global' | <Cod_Carrera>

const form = useForm({
    Nombre_Categoria: '',
    Cod_Carrera: null,
});

// Distingue carreras con el mismo nombre en distintas universidades.
const carreraLabel = (ca) => ca.Nombre_Carrera + (ca.universidad ? ` (${ca.universidad.Nombre_Universidad})` : '');

const filterOptions = computed(() => [
    { value: 'all', label: 'Todas las carreras' },
    { value: 'global', label: 'Solo globales' },
    ...props.carreras.map(ca => ({ value: ca.Cod_Carrera, label: carreraLabel(ca) })),
]);

const carreraOptions = computed(() => [
    { value: null, label: 'Global (todas las carreras)' },
    ...props.carreras.map(ca => ({ value: ca.Cod_Carrera, label: carreraLabel(ca) })),
]);

const stats = computed(() => ({
    total: props.categorias.length,
    globales: props.categorias.filter(c => !c.Cod_Carrera).length,
    porCarrera: props.categorias.filter(c => c.Cod_Carrera).length,
}));

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.categorias.filter(c => {
        const matchSearch = !q
            || (c.Nombre_Categoria || '').toLowerCase().includes(q)
            || (c.carrera?.Nombre_Carrera || '').toLowerCase().includes(q);
        let matchCarrera = true;
        if (filterCarrera.value === 'global') matchCarrera = !c.Cod_Carrera;
        else if (filterCarrera.value !== 'all') matchCarrera = c.Cod_Carrera === filterCarrera.value;
        return matchSearch && matchCarrera;
    });
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (c) => {
    editingId.value = c.Cod_Categoria;
    form.Nombre_Categoria = c.Nombre_Categoria;
    form.Cod_Carrera = c.Cod_Carrera ?? null;
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.categorias.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('admin.categorias.store'), {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteCategoria = (c) => {
    if (confirm(`¿Eliminar la categoría "${c.Nombre_Categoria}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.categorias.destroy', c.Cod_Categoria), { preserveScroll: true });
    }
};

const statCards = computed(() => [
    { label: 'Total categorías', value: stats.value.total,     icon: Layers,        color: 'bg-indigo-500/10 text-indigo-400' },
    { label: 'Globales',         value: stats.value.globales,  icon: Globe,         color: 'bg-gray-500/10 text-gray-300' },
    { label: 'Por carrera',      value: stats.value.porCarrera,icon: GraduationCap, color: 'bg-amber-500/10 text-amber-400' },
]);
</script>

<template>
    <AdminLayout title="Categorías">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white flex items-center gap-2">
                        <Tags class="w-6 h-6 text-fuchsia-400" /> Categorías de Artículos
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Categorías del marketplace. Cada una puede ser de una carrera o global (todas).</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-fuchsia-600 rounded-xl hover:bg-fuchsia-500 transition-all shrink-0 shadow-lg shadow-fuchsia-500/20">
                    <Plus class="w-4 h-4" /> Nueva Categoría
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="s in statCards" :key="s.label"
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
                    <div :class="['p-3 rounded-xl', s.color]">
                        <component :is="s.icon" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white leading-none">{{ s.value }}</p>
                        <p class="text-[11px] text-gray-500 font-medium mt-1">{{ s.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input v-model="search" type="text" placeholder="Buscar categoría o carrera..."
                        class="w-full pl-10 pr-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-fuchsia-500/40 focus:border-fuchsia-500 outline-none" />
                </div>
                <div class="w-full sm:w-80">
                    <SearchableSelect v-model="filterCarrera" :options="filterOptions" placeholder="Filtrar por carrera" />
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/50 text-[10px] uppercase tracking-widest text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-black">Categoría</th>
                                <th class="px-4 py-3 font-black">Ámbito</th>
                                <th class="px-4 py-3 font-black text-center">Publicaciones</th>
                                <th class="px-4 py-3 font-black">Creado</th>
                                <th class="px-4 py-3 font-black text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="c in filtered" :key="c.Cod_Categoria" class="hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-fuchsia-500/10 border border-fuchsia-500/20 shrink-0 flex items-center justify-center">
                                            <Tags class="w-4 h-4 text-fuchsia-400" />
                                        </div>
                                        <span class="text-sm font-bold text-white">{{ c.Nombre_Categoria }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="c.carrera" class="flex flex-col gap-0.5">
                                        <span class="inline-flex items-center gap-1 w-fit text-xs font-bold text-amber-400 bg-amber-500/10 px-2 py-1 rounded-lg">
                                            <GraduationCap class="w-3 h-3" /> {{ c.carrera.Nombre_Carrera }}
                                        </span>
                                        <span v-if="c.carrera.universidad" class="text-[10px] text-gray-500 pl-1 truncate max-w-[220px]">{{ c.carrera.universidad.Nombre_Universidad }}</span>
                                    </div>
                                    <span v-else class="inline-flex items-center gap-1 text-xs font-bold text-gray-400 bg-gray-700/40 px-2 py-1 rounded-lg">
                                        <Globe class="w-3 h-3" /> Global
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-lg">{{ c.publicaciones_count }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-400">{{ formatDate(c.created_at) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(c)" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all" title="Editar">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCategoria(c)" class="p-2 text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Eliminar">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filtered.length === 0">
                                <td colspan="5" class="px-4 py-12 text-center text-gray-500">No se encontraron categorías.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="showModal = false">
            <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-visible">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                    <h2 class="text-sm font-black text-white flex items-center gap-2">
                        <Tags class="w-4 h-4 text-fuchsia-400" />
                        {{ editingId ? 'Editar Categoría' : 'Nueva Categoría' }}
                    </h2>
                    <button @click="showModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Nombre de la categoría</label>
                        <input v-model="form.Nombre_Categoria" type="text" placeholder="Ej. Electrónica"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-fuchsia-500/40 focus:border-fuchsia-500 outline-none transition-all" />
                        <p v-if="form.errors.Nombre_Categoria" class="text-xs text-rose-400">{{ form.errors.Nombre_Categoria }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Carrera</label>
                        <SearchableSelect v-model="form.Cod_Carrera" :options="carreraOptions" placeholder="Selecciona una carrera" />
                        <p class="text-[10px] text-gray-500">Déjala en "Global" para que aparezca en todas las carreras.</p>
                        <p v-if="form.errors.Cod_Carrera" class="text-xs text-rose-400">{{ form.errors.Cod_Carrera }}</p>
                    </div>

                    <div class="pt-1 flex gap-3">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-fuchsia-600 rounded-xl hover:bg-fuchsia-500 transition-all disabled:opacity-50 shadow-lg shadow-fuchsia-500/20">
                            {{ editingId ? 'Guardar Cambios' : 'Crear Categoría' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
