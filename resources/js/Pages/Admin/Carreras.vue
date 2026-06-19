<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { BookOpen, Plus, Edit2, Trash2, X, Image as ImageIcon, Search } from 'lucide-vue-next';

const props = defineProps({
    carreras: Array,
    universidades: Array,
});

const showModal = ref(false);
const editingId = ref(null);
const search = ref('');

const form = useForm({
    Nombre_Carrera: '',
    Cod_Universidad: '',
    Duracion_Carrera: '',
    Descripcion_Carrera: '',
    imgen: null,
});

const imagePreview = ref(null);

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.carreras;
    return props.carreras.filter(c =>
        (c.Nombre_Carrera || '').toLowerCase().includes(q) ||
        (c.universidad?.Nombre_Universidad || '').toLowerCase().includes(q)
    );
});

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.imgen = file;
        const reader = new FileReader();
        reader.onload = (ev) => { imagePreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
};

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    if (document.getElementById('imageInput')) document.getElementById('imageInput').value = '';
    showModal.value = true;
};

const openEdit = (c) => {
    editingId.value = c.Cod_Carrera;
    form.Nombre_Carrera = c.Nombre_Carrera;
    form.Cod_Universidad = c.Cod_Universidad;
    form.Duracion_Carrera = c.Duracion_Carrera || '';
    form.Descripcion_Carrera = c.Descripcion_Carrera || '';
    form.imgen = null;
    form.clearErrors();
    imagePreview.value = c.Foto_Carrera ? `/storage/${c.Foto_Carrera}` : null;
    if (document.getElementById('imageInput')) document.getElementById('imageInput').value = '';
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        router.post(route('admin.carreras.update', editingId.value), {
            _method: 'put',
            Nombre_Carrera: form.Nombre_Carrera,
            Cod_Universidad: form.Cod_Universidad,
            Duracion_Carrera: form.Duracion_Carrera,
            Descripcion_Carrera: form.Descripcion_Carrera,
            imgen: form.imgen,
        }, {
            preserveScroll: true,
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('admin.carreras.store'), {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteCarrera = (c) => {
    if (confirm(`¿Eliminar la carrera "${c.Nombre_Carrera}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.carreras.destroy', c.Cod_Carrera), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout title="Carreras">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white flex items-center gap-2">
                        <BookOpen class="w-6 h-6 text-amber-400" /> Gestión de Carreras
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Administra las carreras y su universidad asociada.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all shrink-0">
                    <Plus class="w-4 h-4" /> Nueva Carrera
                </button>
            </div>

            <!-- Search -->
            <div class="relative max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                <input v-model="search" type="text" placeholder="Buscar carrera o universidad..."
                    class="w-full pl-10 pr-3 py-2.5 text-sm bg-gray-900 border border-gray-800 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none" />
            </div>

            <!-- Tabla -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/50 text-[10px] uppercase tracking-widest text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-black">Carrera</th>
                                <th class="px-4 py-3 font-black">Universidad</th>
                                <th class="px-4 py-3 font-black">Duración</th>
                                <th class="px-4 py-3 font-black">Descripción</th>
                                <th class="px-4 py-3 font-black text-center">Usuarios</th>
                                <th class="px-4 py-3 font-black text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="c in filtered" :key="c.Cod_Carrera" class="hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-800 border border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                                            <img v-if="c.Foto_Carrera" :src="`/storage/${c.Foto_Carrera}`" class="w-full h-full object-cover" />
                                            <BookOpen v-else class="w-5 h-5 text-gray-500" />
                                        </div>
                                        <span class="text-sm font-bold text-white">{{ c.Nombre_Carrera }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-300">{{ c.universidad?.Nombre_Universidad || '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-400">{{ c.Duracion_Carrera || '—' }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <span class="text-xs text-gray-500 line-clamp-1" :title="c.Descripcion_Carrera">{{ c.Descripcion_Carrera || '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-lg">{{ c.usuarios_campus_market_count }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(c)" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all" title="Editar">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCarrera(c)" class="p-2 text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Eliminar">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filtered.length === 0">
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">No se encontraron carreras.</td>
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
                    <h2 class="text-sm font-black text-white">{{ editingId ? 'Editar Carrera' : 'Nueva Carrera' }}</h2>
                    <button @click="showModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4 overflow-y-auto">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Nombre de la carrera</label>
                        <input v-model="form.Nombre_Carrera" type="text" placeholder="Ej. Ingeniería de Sistemas"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                        <p v-if="form.errors.Nombre_Carrera" class="text-xs text-rose-400">{{ form.errors.Nombre_Carrera }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Universidad</label>
                        <select v-model="form.Cod_Universidad"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                            <option value="" disabled>Selecciona una universidad</option>
                            <option v-for="u in universidades" :key="u.Cod_Universidad" :value="u.Cod_Universidad">{{ u.Nombre_Universidad }}</option>
                        </select>
                        <p v-if="form.errors.Cod_Universidad" class="text-xs text-rose-400">{{ form.errors.Cod_Universidad }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Duración (opcional)</label>
                        <input v-model="form.Duracion_Carrera" type="text" placeholder="Ej. 10 semestres"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                        <p v-if="form.errors.Duracion_Carrera" class="text-xs text-rose-400">{{ form.errors.Duracion_Carrera }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Descripción (opcional)</label>
                        <textarea v-model="form.Descripcion_Carrera" rows="3" placeholder="Breve descripción de la carrera"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                        <p v-if="form.errors.Descripcion_Carrera" class="text-xs text-rose-400">{{ form.errors.Descripcion_Carrera }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Foto / Imagen (opcional)</label>
                        <div class="flex items-center gap-4">
                            <label class="flex-1 flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-700 rounded-xl hover:border-indigo-500 hover:bg-gray-800/50 transition-all cursor-pointer">
                                <ImageIcon class="w-6 h-6 text-gray-500 mb-2" />
                                <span class="text-xs text-gray-400">Subir imagen</span>
                                <input id="imageInput" type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
                            </label>
                            <div v-if="imagePreview" class="w-16 h-16 rounded-xl overflow-hidden bg-gray-800 shrink-0 border border-gray-700">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                            </div>
                        </div>
                        <p v-if="form.errors.imgen" class="text-xs text-rose-400">{{ form.errors.imgen }}</p>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50">
                            {{ editingId ? 'Guardar Cambios' : 'Crear Carrera' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
