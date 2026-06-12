<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import ChatArea from '@/Components/ChatArea.vue';
import {
    MessageSquare,
    Search,
    Inbox,
    Clock,
    ChevronRight,
    Users,
    BellOff,
    EyeOff,
    Eye,
    Globe,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    chats: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = page.props.auth.user.id;

const searchTerm = ref('');
const activeTab = ref('todos'); // 'todos', 'noleidos', 'archivados'
const activeChatId = ref(null);

// Leer chat_id de la URL si se redirigió
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const chatIdParam = urlParams.get('chat_id');
    if (chatIdParam) {
        activeChatId.value = parseInt(chatIdParam);
        // Limpiar la URL para que no quede el query string
        window.history.replaceState({}, '', route('mensajes.index'));
    }
});

const filteredChats = computed(() => {
    let list = props.chats;
    
    if (activeTab.value === 'archivados') {
        list = list.filter(c => c.is_hidden);
    } else if (activeTab.value === 'noleidos') {
        list = list.filter(c => !c.is_hidden && c.unread_count > 0);
    } else {
        list = list.filter(c => !c.is_hidden);
    }

    if (searchTerm.value.trim()) {
        const term = searchTerm.value.toLowerCase();
        list = list.filter(c => chatDisplayName(c).toLowerCase().includes(term));
    }

    return list;
});

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now  = new Date();
    const diff = now - date;
    if (diff < 86400000) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    return date.toLocaleDateString([], { day: '2-digit', month: 'short' });
};

const getOtherParticipant = (chat) => {
    if (!chat.users) return { name: 'Chat' };
    return chat.users.find(u => u.id !== currentUserId) || chat.users[0] || { name: 'Usuario' };
};

const chatDisplayName = (chat) => {
    if (chat.tipo === 'general') return chat.nombre || 'Chat General';
    return getOtherParticipant(chat).name;
};

const chatAvatar = (chat) => {
    if (chat.tipo === 'general') return null;
    return getOtherParticipant(chat).name?.charAt(0)?.toUpperCase() ?? '?';
};

const getLastMessage = (chat) => {
    if (!chat.messages || chat.messages.length === 0) return 'Sin mensajes aún';
    const msg = chat.messages[0];
    if (msg.type === 'product_card') return '🛒 Publicación compartida';
    if (msg.attachment_path) return '📎 Archivo adjunto';
    return msg.contenido || '...';
};

const toggleMute = async (chat, e) => {
    e.preventDefault(); e.stopPropagation();
    await axios.post(route('chats.mute', chat.id));
    reloadChats();
};

const toggleHide = async (chat, e) => {
    e.preventDefault(); e.stopPropagation();
    await axios.post(route('chats.hide', chat.id));
    if (activeChatId.value === chat.id && !chat.is_hidden) {
        activeChatId.value = null; // Cerrar si se oculta
    }
    reloadChats();
};

const selectChat = (chat) => {
    activeChatId.value = chat.id;
    // Si tenía unread_count, lo refrescamos
    if (chat.unread_count > 0) {
        // Optimistic UI update
        chat.unread_count = 0;
        // La actualización real se hará cuando ChatArea llame a /show (que marca como leído)
        setTimeout(() => reloadChats(), 500);
    }
};

const reloadChats = () => {
    router.reload({ only: ['chats'], preserveScroll: true });
};

// Escuchar evento del ChatArea
const onChatUpdated = (id) => {
    reloadChats();
};
</script>

<template>
    <AppLayout title="Mensajes">
        <!-- Eliminamos el header estándar para darle el 100% de la altura al Workspace -->
        
        <div class="h-[calc(100vh-73px)] pt-6 pb-6 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full flex overflow-hidden">
            
            <!-- Panel Izquierdo: Lista de Chats -->
            <div 
                class="flex flex-col w-full md:w-[380px] lg:w-[450px] shrink-0 bg-white/80 dark:bg-dark-surface/80 backdrop-blur-xl border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl shadow-brand-500/5 overflow-hidden transition-all duration-300 relative z-20"
                :class="activeChatId ? 'hidden md:flex' : 'flex'"
            >
                <div class="p-6 pb-4 border-b border-light-border dark:border-dark-border bg-white/50 dark:bg-black/20">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                            Mensajes
                            <span class="px-2 py-1 bg-brand-500/10 text-brand-600 dark:text-brand-400 text-[10px] uppercase tracking-widest rounded-full font-bold border border-brand-500/20">{{ props.chats.length }}</span>
                        </h1>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative group mb-4">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                        <input
                            type="text"
                            v-model="searchTerm"
                            class="w-full py-3 pl-11 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500"
                            placeholder="Buscar chats..."
                        >
                    </div>

                    <!-- Filtros Inteligentes -->
                    <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1">
                        <button @click="activeTab = 'todos'" :class="['px-4 py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap', activeTab === 'todos' ? 'bg-brand-600 text-white shadow-md shadow-brand-500/30' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-gray-900 dark:hover:text-white']">
                            Todos
                        </button>
                        <button @click="activeTab = 'noleidos'" :class="['px-4 py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap flex items-center gap-1', activeTab === 'noleidos' ? 'bg-brand-600 text-white shadow-md shadow-brand-500/30' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-gray-900 dark:hover:text-white']">
                            No Leídos
                            <span v-if="props.chats.filter(c => !c.is_hidden && c.unread_count > 0).length > 0" class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        </button>
                        <button @click="activeTab = 'archivados'" :class="['px-4 py-2 rounded-xl text-xs font-black transition-all whitespace-nowrap', activeTab === 'archivados' ? 'bg-brand-600 text-white shadow-md shadow-brand-500/30' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-gray-900 dark:hover:text-white']">
                            Archivados
                        </button>
                    </div>
                </div>

                <!-- Lista de Chats -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
                    <div v-for="chat in filteredChats" :key="chat.id" class="group relative">
                        <button
                            @click="selectChat(chat)"
                            class="w-full text-left p-4 rounded-3xl transition-all relative overflow-hidden"
                            :class="activeChatId === chat.id ? 'bg-brand-500/10 border-brand-500/30 shadow-inner' : 'hover:bg-gray-50 dark:hover:bg-white/5 border-transparent'"
                            style="border-width: 1px"
                        >
                            <div class="relative flex items-center gap-4">
                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div v-if="chat.tipo === 'general'" class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 shadow-md flex items-center justify-center">
                                        <Globe class="w-6 h-6 text-white" />
                                    </div>
                                    <div v-else class="w-12 h-12 rounded-full bg-brand-500 shadow-md flex items-center justify-center text-white font-black text-lg">
                                        {{ chatAvatar(chat) }}
                                    </div>
                                    <div v-if="chat.unread_count > 0" class="absolute -top-1 -right-1 min-w-[1.2rem] h-[1.2rem] flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full px-1 shadow-lg border-2 border-white dark:border-dark-border">
                                        {{ chat.unread_count > 99 ? '99+' : chat.unread_count }}
                                    </div>
                                    <div v-else-if="chat.is_muted" class="absolute -bottom-1 -right-1 w-4 h-4 flex items-center justify-center bg-gray-400 border-2 border-white dark:border-dark-border rounded-full">
                                        <BellOff class="w-2.5 h-2.5 text-white" />
                                    </div>
                                    <div v-else class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-dark-border rounded-full"></div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-sm font-black truncate" :class="[chat.unread_count > 0 ? 'text-brand-600 dark:text-brand-400' : 'text-gray-900 dark:text-white']">
                                            {{ chatDisplayName(chat) }}
                                        </h3>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-2 shrink-0">
                                            {{ formatTime(chat.updated_at) }}
                                        </span>
                                    </div>
                                    <p class="text-xs truncate max-w-[200px]" :class="chat.unread_count > 0 ? 'font-black text-gray-800 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400'">
                                        {{ getLastMessage(chat) }}
                                    </p>
                                </div>
                            </div>
                        </button>
                        
                        <!-- Hover Actions (Solo Desktop) -->
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 hidden md:flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 bg-white/80 dark:bg-dark-surface/80 backdrop-blur-sm p-1 rounded-2xl shadow-sm border border-white/20">
                            <button v-if="chat.tipo !== 'general'" @click="toggleHide(chat, $event)" class="p-1.5 rounded-xl hover:bg-rose-500/10 text-gray-400 hover:text-rose-500 transition-colors" :title="chat.is_hidden ? 'Desarchivar' : 'Archivar'">
                                <component :is="chat.is_hidden ? Eye : EyeOff" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Empty States -->
                    <div v-if="filteredChats.length === 0" class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                            <MessageSquare class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500">No hay chats aquí</p>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Chat Area -->
            <div 
                class="flex-1 flex flex-col relative bg-transparent overflow-hidden"
                :class="activeChatId ? 'flex' : 'hidden md:flex'"
            >
                <div v-if="activeChatId" class="absolute inset-0 md:ml-4">
                    <!-- Componente Inyectado Dinámicamente -->
                    <ChatArea :chat-id="activeChatId" @close="activeChatId = null" @chat-updated="onChatUpdated" class="h-full rounded-[2.5rem] shadow-2xl shadow-brand-500/10 border border-light-border dark:border-dark-border" />
                </div>
                
                <!-- Estado Vacío (Sin chat seleccionado) -->
                <div v-else class="absolute inset-0 md:ml-4 flex flex-col items-center justify-center text-center bg-white/40 dark:bg-dark-surface/40 backdrop-blur-md rounded-[2.5rem] border border-light-border dark:border-dark-border">
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brand-500/5 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="relative z-10 p-8 rounded-full bg-white/50 dark:bg-black/20 mb-8 shadow-inner border border-white dark:border-white/5">
                        <MessageSquare class="w-16 h-16 text-brand-500/50 animate-pulse" />
                    </div>
                    <h2 class="relative z-10 text-3xl font-black text-gray-900 dark:text-white mb-2">Mensajería Unifranz</h2>
                    <p class="relative z-10 text-sm text-gray-500 font-medium max-w-sm">
                        Selecciona una conversación de la izquierda para comenzar a chatear o busca un producto en la tienda.
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(124,58,237,0.3); }
</style>
