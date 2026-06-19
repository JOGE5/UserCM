<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Mail, Send, Image as ImageIcon, CheckCircle, XCircle, Clock } from 'lucide-vue-next';

const props = defineProps({
    notificaciones: Object,
    roles: Array,
    usuarios: Array,
});

const form = useForm({
    tipo_envio: 'A todos los usuarios',
    ID_Usuario: '',
    Cod_Rol: '',
    Titulo_Notificacion: '',
    Mensaje_Notificacion: '',
    imgen: null,
});

const submit = () => {
    form.post(route('admin.notificaciones.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
            document.getElementById('imageInput').value = '';
        },
    });
};

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

const statusIcon = (status) => {
    if (status.includes('Enviado')) return CheckCircle;
    if (status === 'Error') return XCircle;
    return Clock;
};

const statusColor = (status) => {
    if (status === 'Enviado') return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
    if (status === 'Enviado (con errores)') return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
    if (status === 'Error') return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
    return 'text-gray-400 bg-gray-500/10 border-gray-500/20';
};
</script>

<template>
    <AdminLayout title="Notificaciones">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-black text-white flex items-center gap-2">
                    <Mail class="w-6 h-6 text-indigo-400" /> Notificaciones Masivas
                </h1>
                <p class="text-xs text-gray-500 mt-1">Envía correos electrónicos a usuarios, roles o a toda la plataforma.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulario -->
                <div class="lg:col-span-1 bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-bold text-white mb-4">Redactar Notificación</h2>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Enviar a</label>
                            <select v-model="form.tipo_envio" class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                                <option value="A todos los usuarios">A todos los usuarios</option>
                                <option value="Por rol">Por rol</option>
                                <option value="Usuario especifico">Usuario específico</option>
                            </select>
                            <p v-if="form.errors.tipo_envio" class="text-xs text-rose-400">{{ form.errors.tipo_envio }}</p>
                        </div>

                        <!-- Condicional: Por rol -->
                        <div v-if="form.tipo_envio === 'Por rol'" class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Seleccionar Rol</label>
                            <select v-model="form.Cod_Rol" class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                                <option value="" disabled>Selecciona un rol...</option>
                                <option v-for="r in roles" :key="r.Cod_Rol" :value="r.Cod_Rol">{{ r.Nombre_Rol }}</option>
                            </select>
                            <p v-if="form.errors.Cod_Rol" class="text-xs text-rose-400">{{ form.errors.Cod_Rol }}</p>
                        </div>

                        <!-- Condicional: Usuario especifico -->
                        <div v-if="form.tipo_envio === 'Usuario especifico'" class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Seleccionar Usuario</label>
                            <select v-model="form.ID_Usuario" class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all">
                                <option value="" disabled>Buscar usuario...</option>
                                <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                            <p v-if="form.errors.ID_Usuario" class="text-xs text-rose-400">{{ form.errors.ID_Usuario }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Asunto / Título</label>
                            <input v-model="form.Titulo_Notificacion" type="text" placeholder="Escribe el asunto del correo"
                                class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all" />
                            <p v-if="form.errors.Titulo_Notificacion" class="text-xs text-rose-400">{{ form.errors.Titulo_Notificacion }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Mensaje (Soporta múltiples líneas)</label>
                            <textarea v-model="form.Mensaje_Notificacion" rows="4" placeholder="Cuerpo del correo electrónico..."
                                class="w-full px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                            <p v-if="form.errors.Mensaje_Notificacion" class="text-xs text-rose-400">{{ form.errors.Mensaje_Notificacion }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500">Imagen Adjunta (Opcional)</label>
                            <div class="flex items-center gap-4">
                                <label class="flex-1 flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-700 rounded-xl hover:border-indigo-500 hover:bg-gray-800/50 transition-all cursor-pointer">
                                    <ImageIcon class="w-6 h-6 text-gray-500 mb-2" />
                                    <span class="text-xs text-gray-400">Clic para subir imagen</span>
                                    <input id="imageInput" type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
                                </label>
                                <div v-if="imagePreview" class="w-16 h-16 rounded-xl overflow-hidden bg-gray-800 shrink-0 border border-gray-700">
                                    <img :src="imagePreview" class="w-full h-full object-cover" />
                                </div>
                            </div>
                            <p v-if="form.errors.imgen" class="text-xs text-rose-400">{{ form.errors.imgen }}</p>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <Send class="w-4 h-4" />
                            Enviar Notificación
                        </button>
                    </form>
                </div>

                <!-- Historial -->
                <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-bold text-white mb-4">Historial de Envíos</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Asunto</th>
                                    <th class="py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Destinatario(s)</th>
                                    <th class="py-3 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Estado</th>
                                    <th class="py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr v-for="n in notificaciones.data" :key="n.ID_Notificacion" class="hover:bg-gray-800/50 transition-colors">
                                    <td class="py-4 pr-4">
                                        <p class="font-bold text-white text-xs">{{ n.Titulo_Notificacion }}</p>
                                        <p class="text-[10px] text-gray-500 truncate max-w-[200px]">{{ n.Mensaje_Notificacion }}</p>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-800 text-gray-300 border border-gray-700">
                                            {{ n.Destinatario_Notificacion }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-2 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md border text-[10px] font-bold"
                                             :class="statusColor(n.Estado_Notificacion)">
                                            <component :is="statusIcon(n.Estado_Notificacion)" class="w-3 h-3" />
                                            {{ n.Estado_Notificacion }}
                                        </div>
                                    </td>
                                    <td class="py-4 pl-4 text-right">
                                        <span class="text-[10px] text-gray-500">{{ new Date(n.created_at).toLocaleString() }}</span>
                                    </td>
                                </tr>
                                <tr v-if="!notificaciones.data.length">
                                    <td colspan="4" class="py-8 text-center text-gray-500 text-sm">No hay notificaciones enviadas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
