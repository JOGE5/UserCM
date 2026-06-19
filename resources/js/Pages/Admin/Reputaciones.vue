<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Star, Plus, Edit2, Trash2, X, Search } from 'lucide-vue-next';

const props = defineProps({
    reputaciones: Array,
    usuarios: Array,
});

const showModal = ref(false);
const editingId = ref(null);
const search = ref('');

const form = useForm({
    ID_Usuario_Calificador: '',
    ID_Usuario_Calificado: '',
    Puntuacion: 0,
    Comentario: '',
});

const userLabel = (u) => u ? `${u.name}${u.email ? ' (' + u.email + ')' : ''}` : '—';

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.reputaciones;
    return props.reputaciones.filter(r =>
        (r.usuario_calificador?.name || '').toLowerCase().includes(q) ||
        (r.usuario_calificado?.name || '').toLowerCase().includes(q)
    );
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (r) => {
    editingId.value = r.ID_Reputacion;
    form.ID_Usuario_Calificador = r.ID_Usuario_Calificador;
    form.ID_Usuario_Calificado = r.ID_Usuario_Calificado;
    form.Puntuacion = r.Puntuacion;
    form.Comentario = r.Comentario || '';
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.reputaciones.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('admin.reputaciones.store'), {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteReputacion = (r) => {
    if (confirm('¿Eliminar esta calificación de reputación? Esta acción no se puede deshacer.')) {
        router.delete(route('admin.reputaciones.destroy', r.ID_Reputacion), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout title="Reputación">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white flex items-center gap-2">
                        <Star class="w-6 h-6 text-amber-400" /> Reputación entre Usuarios
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Calificaciones que los usuarios se dan entre sí.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all shrink-0">
                    <Plus class="w-4 h-4" /> Nueva Calificación
                </button>
            </div>

            <!-- Search -->
            <div class="relative max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                <input v-model="search" type="text" placeholder="Buscar por usuario..."
                    class="w-full pl-10 pr-3 py-2.5 text-sm bg-gray-900 border border-gray-800 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none" />
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/50 text-[10px] uppercase tracking-widest text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-black">Calificador</th>
                                <th class="px-4 py-3 font-black">Calificado</th>
                                <th class="px-4 py-3 font-black text-center">Puntuación</th>
                                <th class="px-4 py-3 font-black">Comentario</th>
                                <th class="px-4 py-3 font-black">Fecha</th>
                                <th class="px-4 py-3 font-black text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="r in filtered" :key="r.ID_Reputacion" class="hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-bold text-white">{{ r.usuario_calificador?.name || '—' }}</span>
                                    <span class="block text-[10px] text-gray-500">{{ r.usuario_calificador?.email }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-bold text-white">{{ r.usuario_calificado?.name || '—' }}</span>
                                    <span class="block text-[10px] text-gray-500">{{ r.usuario_calificado?.email }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-400 bg-amber-500/10 px-2 py-1 rounded-lg">
                                        <Star class="w-3 h-3" /> {{ r.Puntuacion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <span class="text-xs text-gray-400 line-clamp-1" :title="r.Comentario">{{ r.Comentario || '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-400">{{ formatDate(r.created_at) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(r)" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all" title="Editar">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteReputacion(r)" class="p-2 text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Eliminar">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filtered.length === 0">
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">No hay calificaciones registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="showModal = false">
            <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 shrink-0">
                    <h2 class="text-sm font-black text-white">{{ editingId ? 'Editar Calificación' : 'Nueva Calificación' }}</h2>
                    <button @click="showModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4 overflow-y-auto">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Calificador</label>
                        <select v-model="form.ID_Usuario_Calificador"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                            <option value="" disabled>Selecciona un usuario</option>
                            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ userLabel(u) }}</option>
                        </select>
                        <p v-if="form.errors.ID_Usuario_Calificador" class="text-xs text-rose-400">{{ form.errors.ID_Usuario_Calificador }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Calificado</label>
                        <select v-model="form.ID_Usuario_Calificado"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                            <option value="" disabled>Selecciona un usuario</option>
                            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ userLabel(u) }}</option>
                        </select>
                        <p v-if="form.errors.ID_Usuario_Calificado" class="text-xs text-rose-400">{{ form.errors.ID_Usuario_Calificado }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Puntuación (0 - 255)</label>
                        <input v-model.number="form.Puntuacion" type="number" min="0" max="255" step="1"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                        <p v-if="form.errors.Puntuacion" class="text-xs text-rose-400">{{ form.errors.Puntuacion }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Comentario (opcional)</label>
                        <textarea v-model="form.Comentario" rows="3" placeholder="Comentario sobre la calificación"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                        <p v-if="form.errors.Comentario" class="text-xs text-rose-400">{{ form.errors.Comentario }}</p>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50">
                            {{ editingId ? 'Guardar Cambios' : 'Registrar Calificación' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
