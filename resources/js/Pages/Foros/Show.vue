<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Hash, Lock, Users, Send, ChevronLeft, Calendar, User, Trash2, Clock, Crown, Edit3
} from 'lucide-vue-next';

const props = defineProps({
    foro: Object,
    currentUserId: Number,
    canEdit: Boolean,
});

const mensajes = ref(Array.isArray(props.foro.comentarios) ? [...props.foro.comentarios] : []);
const newMessage = ref('');
const messagesEnd = ref(null);
const processing = ref(false);

// Discord-style online users
const onlineUsers = ref([]);

const scrollToBottom = () => {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
};

onMounted(() => {
    scrollToBottom();
    
    if (window.Echo) {
        window.Echo.join(`forum.${props.foro.ID_Foro}`)
            .here((users) => {
                onlineUsers.value = users;
            })
            .joining((user) => {
                if (!onlineUsers.value.find(u => u.id === user.id)) {
                    onlineUsers.value.push(user);
                }
            })
            .leaving((user) => {
                onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);
            })
            .listen('ForumMessageSent', (e) => {
                // Prevenir duplicados si somos nosotros mismos (aunque ya se previene en backend con toOthers)
                if (!mensajes.value.find(m => m.id === e.message.id)) {
                    mensajes.value.push(e.message);
                    scrollToBottom();
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`forum.${props.foro.ID_Foro}`);
    }
});

const sendComentario = async () => {
    if (!newMessage.value.trim() || processing.value) return;
    
    processing.value = true;
    const texto = newMessage.value.trim();
    newMessage.value = ''; // Limpieza instantánea para sensación de velocidad
    
    try {
        const { data } = await axios.post(route('productos.comentarios.store', props.foro.ID_Foro), { texto });
        mensajes.value.push(data);
        scrollToBottom();
    } catch (e) {
        console.error('Error al enviar mensaje', e);
        newMessage.value = texto; // Restaurar si falla
    } finally {
        processing.value = false;
    }
};

const formatTime = (d) => d
    ? new Intl.DateTimeFormat('es-ES', { hour: '2-digit', minute: '2-digit' }).format(new Date(d))
    : '';
const formatDate = (d) => d
    ? new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d))
    : '';

const isHost = (userId) => {
    // Si el Creador del foro es el usuario actual
    return props.foro.creador?.user?.id === userId;
};

const showDeleteModal = ref(false);
const confirmDelete = () => {
    router.delete(route('productos.destroy', props.foro.ID_Foro));
};

const SENDER_COLORS = ['#7c3aed','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#06b6d4'];
const senderColor = (senderId) => SENDER_COLORS[senderId % SENDER_COLORS.length];

</script>

<template>
    <AppLayout :title="foro.Titulo_Foro">
        <!-- Reemplazamos todo el contenido por un layout Full Height Split-Pane -->
        <div class="h-[calc(100vh-73px)] w-full flex flex-col md:flex-row bg-white dark:bg-dark-surface relative overflow-hidden">
            
            <!-- Panel Central: Área de Chat -->
            <div class="flex-1 flex flex-col h-full relative z-10 border-r border-light-border dark:border-dark-border">
                
                <!-- Header del Chat -->
                <div class="h-16 px-6 bg-white/80 dark:bg-dark-surface/80 backdrop-blur-xl border-b border-light-border dark:border-dark-border flex items-center justify-between shrink-0 relative z-30">
                    <div class="flex items-center gap-4">
                        <Link :href="route('productos')" class="text-gray-400 hover:text-brand-500 transition-colors">
                            <ChevronLeft class="w-6 h-6" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <Hash v-if="foro.tipo_acceso === 'abierto'" class="w-5 h-5 text-gray-400" />
                                <Lock v-else class="w-5 h-5 text-brand-500" />
                                <h1 class="text-lg font-black text-gray-900 dark:text-white leading-none">
                                    {{ foro.Titulo_Foro }}
                                </h1>
                                <span v-if="foro.tipo_acceso === 'exclusivo'" class="px-2 py-0.5 bg-brand-500/10 text-brand-600 dark:text-brand-400 text-[9px] font-black uppercase tracking-widest rounded-full border border-brand-500/20">
                                    Exclusivo Carrera
                                </span>
                            </div>
                            <p class="text-[10px] font-bold text-gray-500 mt-0.5 truncate max-w-md">
                                Creado por {{ foro.creador?.user?.name ?? 'Anfitrión' }} el {{ formatDate(foro.created_at) }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <Link v-if="canEdit" :href="route('productos.edit', foro.ID_Foro)" class="p-2 text-gray-400 hover:text-indigo-500 bg-gray-100 dark:bg-white/5 rounded-xl transition-colors">
                            <Edit3 class="w-4 h-4" />
                        </Link>
                        <button v-if="canEdit" @click="showDeleteModal = true" class="p-2 text-gray-400 hover:text-rose-500 bg-gray-100 dark:bg-white/5 rounded-xl transition-colors">
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <!-- Mostrar usuarios en mobile -->
                        <button class="md:hidden p-2 text-gray-400 hover:text-brand-500 bg-gray-100 dark:bg-white/5 rounded-xl transition-colors flex items-center gap-1">
                            <Users class="w-4 h-4" />
                            <span class="text-xs font-bold">{{ onlineUsers.length }}</span>
                        </button>
                    </div>
                </div>

                <!-- Lista de Mensajes (Scrollable) -->
                <div class="flex-1 overflow-y-auto px-4 py-6 bg-gray-50/50 dark:bg-black/20 custom-scrollbar relative z-10 space-y-4">
                    
                    <!-- Mensaje Fijado (Descripción del Foro) -->
                    <div class="bg-brand-500/10 border border-brand-500/20 rounded-2xl p-5 mb-8 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <Crown class="w-4 h-4 text-brand-500" />
                            <span class="text-[10px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest">Tema Principal del Anfitrión</span>
                        </div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">{{ foro.Descripcion_Foro }}</p>
                    </div>

                    <!-- Mensajes de los usuarios -->
                    <div v-for="(msg, idx) in mensajes" :key="msg.id || idx" class="group relative flex gap-3 hover:bg-black/5 dark:hover:bg-white/5 p-2 -mx-2 rounded-xl transition-colors">
                        <div class="w-10 h-10 shrink-0 mt-1">
                            <div v-if="msg.usuario?.profile_photo_url" class="w-10 h-10 rounded-full overflow-hidden shadow-sm">
                                <img :src="msg.usuario.profile_photo_url" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-black shadow-sm" :style="{ backgroundColor: senderColor(msg.user_id) }">
                                {{ msg.usuario?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 mb-0.5">
                                <span class="text-sm font-black hover:underline cursor-pointer" :style="{ color: senderColor(msg.user_id) }">
                                    {{ msg.usuario?.name ?? 'Usuario' }}
                                </span>
                                <span v-if="isHost(msg.user_id)" class="px-1.5 py-0.5 bg-brand-500 text-white text-[8px] font-black uppercase tracking-widest rounded-md">
                                    Anfitrión
                                </span>
                                <span class="text-[10px] font-bold text-gray-400">
                                    {{ formatTime(msg.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">{{ msg.texto }}</p>
                        </div>
                    </div>
                    
                    <div v-if="mensajes.length === 0" class="flex flex-col items-center justify-center py-20 text-center opacity-60">
                        <MessageSquare class="w-12 h-12 text-gray-400 mb-4" />
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest">Sala vacía</p>
                        <p class="text-xs text-gray-400 mt-1">Sé el primero en participar en la conversación.</p>
                    </div>

                    <div ref="messagesEnd"></div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white dark:bg-dark-surface shrink-0">
                    <div class="relative flex items-end gap-2 bg-gray-100/50 dark:bg-black/40 border-2 border-transparent focus-within:border-brand-500/50 focus-within:ring-4 focus-within:ring-brand-500/10 rounded-3xl transition-all p-1">
                        <textarea
                            v-model="newMessage"
                            rows="1"
                            @keydown.enter.exact.prevent="sendComentario"
                            placeholder="Escribe un mensaje para la sala..."
                            class="flex-1 bg-transparent border-0 focus:ring-0 text-sm text-gray-900 dark:text-white placeholder-gray-400 resize-none py-3 pl-4 outline-none"
                        ></textarea>
                        <button
                            @click="sendComentario"
                            :disabled="!newMessage.trim() || processing"
                            class="flex-shrink-0 p-3 m-1 bg-brand-600 text-white rounded-2xl shadow-lg shadow-brand-500/30 hover:bg-brand-500 disabled:opacity-30 disabled:grayscale transition-all hover:scale-105 active:scale-95"
                        >
                            <Send class="w-5 h-5" />
                        </button>
                    </div>
                </div>

            </div>

            <!-- Panel Derecho: Usuarios Online (Discord Style) -->
            <div class="hidden md:flex w-64 flex-col bg-gray-50 dark:bg-[#1e1e24] shrink-0 border-l border-light-border dark:border-dark-border">
                <div class="h-16 px-4 flex items-center border-b border-light-border dark:border-dark-border shrink-0">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <Users class="w-4 h-4" /> En Línea — {{ onlineUsers.length }}
                    </h3>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
                    <div v-for="user in onlineUsers" :key="user.id" class="flex items-center gap-3 p-2 hover:bg-gray-200/50 dark:hover:bg-white/5 rounded-xl cursor-pointer transition-colors group">
                        <div class="relative shrink-0">
                            <div v-if="user.profile_photo_url" class="w-8 h-8 rounded-full overflow-hidden">
                                <img :src="user.profile_photo_url" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-black shadow-sm" :style="{ backgroundColor: senderColor(user.id) }">
                                {{ user.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <!-- Punto verde de estado -->
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-gray-50 dark:border-[#1e1e24] group-hover:border-gray-100 dark:group-hover:border-[#2a2a32] rounded-full transition-colors"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate" :class="{ 'text-brand-500': isHost(user.id) }">
                                {{ user.name }}
                            </h4>
                            <p v-if="isHost(user.id)" class="text-[9px] font-black uppercase tracking-widest text-brand-500/70">Anfitrión</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Eliminar -->
        <Transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="w-full max-w-sm bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-3xl p-8 shadow-2xl">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white text-center mb-2">¿Eliminar foro?</h3>
                    <div class="flex gap-3 mt-6">
                        <button @click="showDeleteModal = false" class="flex-1 px-4 py-3 text-xs font-black uppercase text-gray-600 bg-gray-100 rounded-xl">Cancelar</button>
                        <button @click="confirmDelete" class="flex-1 px-4 py-3 text-xs font-black uppercase text-white bg-rose-600 rounded-xl">Eliminar</button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.15); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(124,58,237,0.4); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
