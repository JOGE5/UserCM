<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Building2, Plus, Edit2, Trash2, X, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    universidades: Array,
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    Nombre_Universidad: '',
    Sede_Universidad: '',
    imgen: null,
});

const imagePreview = ref(null);

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.imgen = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
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

const openEdit = (uni) => {
    editingId.value = uni.Cod_Universidad;
    form.Nombre_Universidad = uni.Nombre_Universidad;
    form.Sede_Universidad = uni.Sede_Universidad || '';
    form.imgen = null;
    form.clearErrors();
    
    if (uni.Universisdad_foto_de_perfil) {
        imagePreview.value = `/storage/${uni.Universisdad_foto_de_perfil}`;
    } else {
        imagePreview.value = null;
    }
    
    if (document.getElementById('imageInput')) document.getElementById('imageInput').value = '';
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        // En Inertia, para enviar archivos por PUT, a menudo se usa POST con _method='PUT'
        // Pero useForm lo maneja automáticamente si lo configuramos correctamente, o usamos POST
        router.post(route('admin.universidades.update', editingId.value), {
            _method: 'put',
            Nombre_Universidad: form.Nombre_Universidad,
            Sede_Universidad: form.Sede_Universidad,
            imgen: form.imgen,
        }, {
            preserveScroll: true,
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('admin.universidades.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteUni = (id) => {
    if (confirm('¿Estás seguro de eliminar esta universidad? Esta acción no se puede deshacer.')) {
        router.delete(route('admin.universidades.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout title="Universidades">
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white flex items-center gap-2">
                        <Building2 class="w-6 h-6 text-indigo-400" /> Gestión de Universidades
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Administra las universidades registradas en la plataforma.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all shrink-0">
                    <Plus class="w-4 h-4" />
                    Nueva Universidad
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="uni in universidades" :key="uni.Cod_Universidad" 
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-indigo-500/50 transition-all group flex flex-col">
                    
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-16 h-16 rounded-xl bg-gray-800 border border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                            <img v-if="uni.Universisdad_foto_de_perfil" :src="`/storage/${uni.Universisdad_foto_de_perfil}`" class="w-full h-full object-cover" />
                            <Building2 v-else class="w-8 h-8 text-gray-500" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-white line-clamp-2" :title="uni.Nombre_Universidad">{{ uni.Nombre_Universidad }}</h3>
                            <p class="text-xs text-gray-400 mt-1 line-clamp-1">{{ uni.Sede_Universidad || 'Sede Principal' }}</p>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-800 flex items-center justify-between">
                        <div class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-lg">
                            {{ uni.carreras_count }} Carreras asociadas
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openEdit(uni)" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all" title="Editar">
                                <Edit2 class="w-4 h-4" />
                            </button>
                            <button @click="deleteUni(uni.Cod_Universidad)" class="p-2 text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Eliminar">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="universidades.length === 0" class="col-span-full py-12 text-center text-gray-500 bg-gray-900 border border-gray-800 rounded-2xl">
                    No hay universidades registradas.
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="showModal = false">
            <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 bg-gray-900">
                    <h2 class="text-sm font-black text-white">
                        {{ editingId ? 'Editar Universidad' : 'Nueva Universidad' }}
                    </h2>
                    <button @click="showModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Nombre de la Universidad</label>
                        <input v-model="form.Nombre_Universidad" type="text" placeholder="Ej. Universidad Tecnológica"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                        <p v-if="form.errors.Nombre_Universidad" class="text-xs text-rose-400">{{ form.errors.Nombre_Universidad }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Sede (Opcional)</label>
                        <input v-model="form.Sede_Universidad" type="text" placeholder="Ej. Campus Central"
                            class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                        <p v-if="form.errors.Sede_Universidad" class="text-xs text-rose-400">{{ form.errors.Sede_Universidad }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Logo / Foto de perfil</label>
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

                    <div class="pt-4 flex gap-3">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50">
                            {{ editingId ? 'Guardar Cambios' : 'Crear Universidad' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
