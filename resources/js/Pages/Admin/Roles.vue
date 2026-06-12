<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { KeyRound, Plus, Pencil, Trash2, Users, X, Lock } from 'lucide-vue-next';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    Nombre_Rol: '',
    Descripcion: '',
});

const editingId = ref(null);
const isEditing = computed(() => editingId.value !== null);
const savedFlash = ref('');

const SYSTEM_ROLES = [1, 2, 3];
const isSystem = (rol) => SYSTEM_ROLES.includes(rol.Cod_Rol);
const userCount = (rol) => rol.usuarios_campus_market_count ?? 0;
const canDelete = (rol) => !isSystem(rol) && userCount(rol) === 0;

const accent = (rol) => {
    if (rol.Cod_Rol === 1) return 'bg-rose-500/10 border-rose-500/30 text-rose-400';
    if (rol.Cod_Rol === 2) return 'bg-amber-500/10 border-amber-500/30 text-amber-400';
    if (rol.Cod_Rol === 3) return 'bg-gray-700/40 border-gray-600 text-gray-300';
    return 'bg-indigo-500/10 border-indigo-500/30 text-indigo-400';
};

const flash = (msg) => {
    savedFlash.value = msg;
    setTimeout(() => (savedFlash.value = ''), 2500);
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    editingId.value = null;
};

const submit = () => {
    if (isEditing.value) {
        form.patch(route('admin.roles.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => { resetForm(); flash('Rol actualizado.'); },
        });
    } else {
        form.post(route('admin.roles.store'), {
            preserveScroll: true,
            onSuccess: () => { resetForm(); flash('Rol creado.'); },
        });
    }
};

const startEdit = (rol) => {
    editingId.value = rol.Cod_Rol;
    form.Nombre_Rol = rol.Nombre_Rol;
    form.Descripcion = rol.Descripcion ?? '';
    form.clearErrors();
};

const destroy = (rol) => {
    if (!canDelete(rol)) return;
    if (!confirm(`¿Eliminar el rol "${rol.Nombre_Rol}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('admin.roles.destroy', rol.Cod_Rol), {
        preserveScroll: true,
        onSuccess: () => flash('Rol eliminado.'),
    });
};
</script>

<template>
    <AdminLayout title="Roles">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-white">Gestión de Roles</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ roles.length }} roles definidos</p>
                </div>
                <Transition name="fade">
                    <span v-if="savedFlash" class="px-3 py-1.5 text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                        {{ savedFlash }}
                    </span>
                </Transition>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulario crear/editar -->
                <div class="lg:col-span-1">
                    <form @submit.prevent="submit" class="bg-gray-900 border border-gray-800 rounded-2xl p-5 space-y-4 lg:sticky lg:top-6">
                        <div class="flex items-center gap-2">
                            <component :is="isEditing ? Pencil : Plus" class="w-4 h-4 text-indigo-400" />
                            <h2 class="text-sm font-bold text-white">{{ isEditing ? 'Editar rol' : 'Nuevo rol' }}</h2>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Nombre del rol</label>
                            <input
                                v-model="form.Nombre_Rol"
                                type="text"
                                maxlength="255"
                                placeholder="Ej. Soporte"
                                class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all"
                            />
                            <p v-if="form.errors.Nombre_Rol" class="text-xs text-rose-400">{{ form.errors.Nombre_Rol }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Descripción</label>
                            <textarea
                                v-model="form.Descripcion"
                                rows="3"
                                maxlength="1000"
                                placeholder="¿Para qué sirve este rol?"
                                class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all resize-none"
                            />
                            <p v-if="form.errors.Descripcion" class="text-xs text-rose-400">{{ form.errors.Descripcion }}</p>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isEditing ? 'Guardar cambios' : 'Crear rol' }}
                            </button>
                            <button
                                v-if="isEditing"
                                type="button"
                                @click="resetForm"
                                class="px-4 py-2.5 text-sm font-bold text-gray-400 bg-gray-800 border border-gray-700 rounded-xl hover:text-white transition-all"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de roles -->
                <div class="lg:col-span-2 space-y-3">
                    <div
                        v-for="rol in roles"
                        :key="rol.Cod_Rol"
                        class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-start gap-4"
                    >
                        <div class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center border" :class="accent(rol)">
                            <KeyRound class="w-5 h-5" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-black text-white">{{ rol.Nombre_Rol }}</h3>
                                <span v-if="isSystem(rol)" class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-gray-400 bg-gray-700/50 border border-gray-600 rounded-md">
                                    <Lock class="w-2.5 h-2.5" /> Sistema
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold text-gray-400 bg-gray-800 border border-gray-700 rounded-md">
                                    <Users class="w-3 h-3" /> {{ userCount(rol) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                {{ rol.Descripcion || 'Sin descripción.' }}
                            </p>
                        </div>

                        <div class="shrink-0 flex items-center gap-1.5">
                            <button
                                @click="startEdit(rol)"
                                class="p-2 text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all"
                                title="Editar"
                            >
                                <Pencil class="w-4 h-4" />
                            </button>
                            <button
                                @click="destroy(rol)"
                                :disabled="!canDelete(rol)"
                                class="p-2 rounded-lg transition-all"
                                :class="canDelete(rol)
                                    ? 'text-gray-400 hover:text-rose-400 hover:bg-rose-500/10'
                                    : 'text-gray-700 cursor-not-allowed'"
                                :title="isSystem(rol) ? 'Rol del sistema, no se puede eliminar' : (userCount(rol) > 0 ? 'Tiene usuarios asignados' : 'Eliminar')"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
