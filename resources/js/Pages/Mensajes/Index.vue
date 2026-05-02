<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    MessageSquare,
    Search,
    MessageCircle,
    Inbox,
    Clock,
    ChevronRight,
    Users,
    BellOff,
    Bell,
    EyeOff,
    Eye,
    Globe,
} from 'lucide-vue-next';

const route = window.route;

const props = defineProps({
    chats: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = page.props.auth.user.id;
const searchTerm = ref('');
const showHidden = ref(false);

const visibleChats = computed(() =>
    props.chats.filter(c => !c.is_hidden && (
        !searchTerm.value.trim() ||
        chatDisplayName(c).toLowerCase().includes(searchTerm.value.toLowerCase())
    ))
);

const hiddenChats = computed(() =>
    props.chats.filter(c => c.is_hidden)
);

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

const muteLoading = ref(null);
const hideLoading = ref(null);

const toggleMute = async (chat, e) => {
    e.preventDefault();
    e.stopPropagation();
    if (muteLoading.value === chat.id) return;
    muteLoading.value = chat.id;
    try {
        await axios.post(route('chats.mute', chat.id));
        router.reload({ only: ['chats'] });
    } finally {
        muteLoading.value = null;
    }
};

const toggleHide = async (chat, e) => {
    e.preventDefault();
    e.stopPropagation();
    if (hideLoading.value === chat.id) return;
    hideLoading.value = chat.id;
    try {
        await axios.post(route('chats.hide', chat.id));
        router.reload({ only: ['chats'] });
    } finally {
        hideLoading.value = null;
    }
};
</script>

<template>
    <AppLayout title="Mensajes">
        <template #header>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
                    <MessageCircle class="w-3 h-3" />
                    <span>Centro de Comunicación</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Bandeja de <span class="text-brand-600 dark:text-brand-400">Entrada</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl">
                    Gestiona tus conversaciones con la comunidad Unifranz.
                </p>
            </div>
        </template>

        <div class="space-y-12 pb-20">
            <!-- Barra de Herramientas -->
            <div class="sticky top-24 z-30 flex flex-col gap-4 p-5 lg:flex-row lg:items-center bg-white/80 dark:bg-dark-surface/80 backdrop-blur-2xl border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-xl shadow-black/5">
                <div class="relative flex-1 group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                    <input
                        type="text"
                        v-model="searchTerm"
                        class="w-full py-3.5 pl-12 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500"
                        placeholder="Buscar en tus conversaciones..."
                    >
                </div>
                <div class="flex items-center gap-4 px-4 py-2 bg-brand-500/5 rounded-2xl border border-brand-500/10">
                    <Inbox class="w-4 h-4 text-brand-500" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-brand-600 dark:text-brand-400">
                        {{ visibleChats.length }} Chats Activos
                    </span>
                </div>
            </div>

            <!-- Lista de Chats -->
            <div v-if="visibleChats.length" class="max-w-4xl mx-auto space-y-4">
                <div v-for="chat in visibleChats" :key="chat.id" class="group relative">
                    <Link
                        :href="route('chats.show', chat.id)"
                        class="block bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] p-6 hover:shadow-2xl hover:shadow-brand-500/10 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden"
                        :class="chat.tipo === 'general' ? 'border-brand-500/20' : ''"
                    >
                        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/5 rounded-full -mr-16 -mt-16 pointer-events-none group-hover:bg-brand-500/10 transition-colors"></div>

                        <div class="relative flex items-center gap-6">
                            <!-- Avatar -->
                            <div class="relative flex-shrink-0">
                                <div v-if="chat.tipo === 'general'"
                                    class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 border-4 border-white dark:border-dark-border shadow-lg flex items-center justify-center ring-2 ring-brand-500/20"
                                >
                                    <Globe class="w-8 h-8 text-white" />
                                </div>
                                <div v-else
                                    class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 border-4 border-white dark:border-dark-border shadow-lg flex items-center justify-center text-white ring-2 ring-brand-500/20"
                                >
                                    <span class="text-xl font-black">{{ chatAvatar(chat) }}</span>
                                </div>

                                <!-- Badge de no-leídos -->
                                <div v-if="chat.unread_count > 0"
                                    class="absolute -top-1 -right-1 min-w-[1.4rem] h-[1.4rem] flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full px-1 shadow-lg shadow-rose-500/40 border-2 border-white dark:border-dark-border"
                                >
                                    {{ chat.unread_count > 99 ? '99+' : chat.unread_count }}
                                </div>
                                <div v-else-if="chat.is_muted"
                                    class="absolute -bottom-1 -right-1 w-5 h-5 flex items-center justify-center bg-gray-400 border-2 border-white dark:border-dark-border rounded-full"
                                >
                                    <BellOff class="w-2.5 h-2.5 text-white" />
                                </div>
                                <div v-else class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-dark-border rounded-full shadow-sm"></div>
                            </div>

                            <!-- Info del Chat -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <h3 class="text-lg font-black truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors"
                                            :class="chat.unread_count > 0 ? 'text-brand-700 dark:text-brand-300' : 'text-gray-900 dark:text-white'"
                                        >
                                            {{ chatDisplayName(chat) }}
                                        </h3>
                                        <span v-if="chat.tipo === 'general'"
                                            class="shrink-0 px-2 py-0.5 bg-brand-500/10 text-brand-600 dark:text-brand-400 text-[9px] font-black uppercase tracking-widest rounded-full border border-brand-500/20"
                                        >
                                            General
                                        </span>
                                        <span v-if="chat.tipo === 'general' && chat.users"
                                            class="shrink-0 flex items-center gap-1 text-[9px] font-bold text-gray-400"
                                        >
                                            <Users class="w-3 h-3" />
                                            {{ chat.users.length }}
                                        </span>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5 text-gray-400 font-bold text-[10px] uppercase tracking-tighter ml-2">
                                        <Clock class="w-3 h-3" />
                                        {{ formatTime(chat.updated_at) }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="text-sm truncate max-w-xs"
                                        :class="chat.unread_count > 0
                                            ? 'text-gray-800 dark:text-gray-200 font-bold'
                                            : 'text-gray-500 dark:text-gray-400 font-medium'"
                                    >
                                        <span v-if="chat.is_muted" class="text-gray-400 italic text-xs mr-1">Silenciado · </span>
                                        {{ getLastMessage(chat) }}
                                    </p>
                                    <ChevronRight class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:translate-x-1 group-hover:text-brand-500 transition-all shrink-0" />
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Acciones al hacer hover -->
                    <div class="absolute right-20 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <button @click="toggleMute(chat, $event)"
                            class="p-2 rounded-xl bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border shadow-md hover:border-amber-400 text-gray-400 hover:text-amber-500 transition-all"
                            :title="chat.is_muted ? 'Activar notificaciones' : 'Silenciar'"
                        >
                            <component :is="chat.is_muted ? Bell : BellOff" class="w-4 h-4" />
                        </button>
                        <button v-if="chat.tipo !== 'general'"
                            @click="toggleHide(chat, $event)"
                            class="p-2 rounded-xl bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border shadow-md hover:border-rose-400 text-gray-400 hover:text-rose-500 transition-all"
                            title="Archivar conversación"
                        >
                            <EyeOff class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chats archivados -->
            <div v-if="hiddenChats.length" class="max-w-4xl mx-auto">
                <button @click="showHidden = !showHidden"
                    class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand-500 transition-colors mb-4"
                >
                    <component :is="showHidden ? Eye : EyeOff" class="w-3.5 h-3.5" />
                    {{ showHidden ? 'Ocultar' : 'Mostrar' }} archivados ({{ hiddenChats.length }})
                </button>

                <div v-if="showHidden" class="space-y-3 opacity-60">
                    <div v-for="chat in hiddenChats" :key="chat.id" class="group relative">
                        <Link :href="route('chats.show', chat.id)"
                            class="block bg-white/50 dark:bg-dark-surface/50 border border-dashed border-light-border dark:border-dark-border rounded-[2rem] p-5 hover:opacity-100 transition-all"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-sm font-black">
                                    {{ chatAvatar(chat) ?? '🌐' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-black text-gray-700 dark:text-gray-300 truncate">{{ chatDisplayName(chat) }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ getLastMessage(chat) }}</p>
                                </div>
                            </div>
                        </Link>
                        <button @click="toggleHide(chat, $event)"
                            class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-xl bg-white dark:bg-dark-surface border border-light-border text-gray-400 hover:text-brand-500 opacity-0 group-hover:opacity-100 transition-all"
                            title="Restaurar"
                        >
                            <Eye class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!visibleChats.length && !hiddenChats.length"
                class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border border-light-border dark:border-dark-border rounded-[4rem] overflow-hidden"
            >
                <div class="p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                    <MessageSquare class="w-16 h-16 text-gray-300 dark:text-gray-600 animate-pulse" />
                </div>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Tu buzón está impecable</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mb-10 text-lg font-medium">
                    Inicia conversaciones desde las publicaciones del marketplace para contactar a los vendedores.
                </p>
                <Link :href="route('dashboard')" class="px-10 py-4 bg-brand-600 text-white font-black rounded-2xl hover:bg-brand-500 shadow-xl shadow-brand-500/30 transition-all">
                    Explorar Ecosistema
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-in {
    animation: slideUp 0.6s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
