<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import {
    Send,
    Paperclip,
    Image,
    ChevronLeft,
    Clock,
    Smile,
    X,
    FileText,
    Download,
    ShoppingBag,
    ExternalLink,
    BellOff,
    Bell,
    Globe,
} from 'lucide-vue-next';

// route is set globally by Ziggy's @routes blade directive
const route = window.route;

const props = defineProps({
    chat:      Object,
    is_muted:  { type: Boolean, default: false },
    is_hidden: { type: Boolean, default: false },
});

const page = usePage();
const currentUserId = page.props.auth.user.id;

const isMuted = ref(props.is_muted);
const muteLoading = ref(false);

const toggleMute = async () => {
    if (muteLoading.value) return;
    muteLoading.value = true;
    try {
        const { data } = await axios.post(route('chats.mute', props.chat.id));
        isMuted.value = data.is_muted;
    } finally {
        muteLoading.value = false;
    }
};

const messages   = ref(Array.isArray(props.chat.messages) ? [...props.chat.messages] : []);
const newMessage = ref('');
const processing = ref(false);
const messagesEnd = ref(null);

// Typing indicator
const typingUsers   = ref({});
const typingTimeout = ref(null);
const isTypingSent  = ref(false);

const typingLabel = computed(() => {
    const names = Object.values(typingUsers.value);
    if (!names.length) return null;
    return names.length === 1
        ? `${names[0]} está escribiendo...`
        : `${names.join(', ')} están escribiendo...`;
});

// Emoji picker
const showEmojiPicker = ref(false);
const EMOJIS = ['😀','😂','😍','🥰','😎','🤔','😢','😡','👍','👎','❤️','🔥','🎉','✅','⭐','🙏','💪','🤝','👀','💬','📎','🖼️','🎵','🚀','⚡','💡','🛒','🏪','💰','📦'];

// File attachment
const fileInput    = ref(null);
const imageInput   = ref(null);
const pendingFile  = ref(null);
const pendingPreview = ref(null);

const isGeneralChat = computed(() => props.chat.tipo === 'general');

const otherParticipant = computed(() =>
    props.chat.users.find(u => u.id !== currentUserId) || props.chat.users[0] || { name: 'Usuario' }
);

const showSenderName = (msg, idx) => {
    if (!isGeneralChat.value) return false;
    if (msg.sender_id === currentUserId) return false;
    if (idx === 0) return true;
    return messages.value[idx - 1]?.sender_id !== msg.sender_id;
};

const SENDER_COLORS = ['#7c3aed','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#06b6d4'];
const senderColor = (senderId) => SENDER_COLORS[senderId % SENDER_COLORS.length];

// ─── Scroll ───────────────────────────────────────────────────────────────────

const scrollToBottom = () => nextTick(() => messagesEnd.value?.scrollIntoView({ behavior: 'smooth' }));

// ─── Typing ───────────────────────────────────────────────────────────────────

const sendTypingStart = () => {
    if (isTypingSent.value) return;
    isTypingSent.value = true;
    axios.post(route('chats.typing', props.chat.id), { is_typing: true }).catch(() => {});
};

const sendTypingStop = () => {
    isTypingSent.value = false;
    axios.post(route('chats.typing', props.chat.id), { is_typing: false }).catch(() => {});
};

const onInput = () => {
    sendTypingStart();
    clearTimeout(typingTimeout.value);
    typingTimeout.value = setTimeout(sendTypingStop, 2000);
};

// ─── Emoji ────────────────────────────────────────────────────────────────────

const insertEmoji = (emoji) => {
    newMessage.value += emoji;
    showEmojiPicker.value = false;
};

// ─── File handling ────────────────────────────────────────────────────────────

const triggerFile  = () => fileInput.value?.click();
const triggerImage = () => imageInput.value?.click();

const onFileSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    pendingFile.value = file;
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (ev) => { pendingPreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    } else {
        pendingPreview.value = null;
    }
    e.target.value = '';
};

const removePendingFile = () => {
    pendingFile.value    = null;
    pendingPreview.value = null;
};

// ─── Send ─────────────────────────────────────────────────────────────────────

const sendMessage = async () => {
    if ((!newMessage.value.trim() && !pendingFile.value) || processing.value) return;

    processing.value = true;
    clearTimeout(typingTimeout.value);
    try { sendTypingStop(); } catch {}

    try {
        let payload;
        if (pendingFile.value) {
            const fd = new FormData();
            if (newMessage.value.trim()) fd.append('contenido', newMessage.value.trim());
            fd.append('attachment', pendingFile.value);
            payload = fd;
        } else {
            payload = { contenido: newMessage.value.trim() };
        }

        const { data } = await axios.post(
            route('chats.messages.store', props.chat.id),
            payload
        );
        messages.value.push(data);
        newMessage.value     = '';
        pendingFile.value    = null;
        pendingPreview.value = null;
        scrollToBottom();
    } catch (e) {
        alert('Error al enviar: ' + (e?.response?.data?.error ?? e?.message ?? 'intenta de nuevo'));
    } finally {
        processing.value = false;
    }
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatMsgTime = (d) => new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

const attachmentUrl = (path) => `/storage/${path}`;

const isImage = (msg) => msg.attachment_type === 'image';

// ─── WebSocket ────────────────────────────────────────────────────────────────

onMounted(() => {
    scrollToBottom();

    if (!window.Echo) return;

    window.Echo.join(`chat.${props.chat.id}`)
        .listen('.MessageSent', (e) => {
            if (!messages.value.some(m => m.id === e.message.id)) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        })
        .listen('.UserTyping', (e) => {
            if (e.user_id === currentUserId) return;
            if (e.is_typing) {
                typingUsers.value = { ...typingUsers.value, [e.user_id]: e.user_name };
            } else {
                const updated = { ...typingUsers.value };
                delete updated[e.user_id];
                typingUsers.value = updated;
            }
        });
});

onBeforeUnmount(() => {
    clearTimeout(typingTimeout.value);
    if (isTypingSent.value) sendTypingStop();
    window.Echo?.leave(`chat.${props.chat.id}`);
});
</script>

<template>
    <AppLayout :title="chat.tipo === 'general' ? chat.nombre : `Chat con ${otherParticipant.name}`">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <button @click="router.visit(route('mensajes.index'))" class="p-2 -ml-2 text-gray-400 hover:text-brand-600 transition-colors">
                        <ChevronLeft class="w-6 h-6" />
                    </button>
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div class="relative w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-black ring-2 ring-brand-500/20"
                            :class="chat.tipo === 'general' ? 'bg-gradient-to-br from-brand-500 to-indigo-600' : 'bg-brand-500'"
                        >
                            <Globe v-if="chat.tipo === 'general'" class="w-5 h-5" />
                            <template v-else>
                                {{ otherParticipant.name.charAt(0).toUpperCase() }}
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-dark-border rounded-full"></div>
                            </template>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-black text-gray-900 dark:text-white leading-none">
                                    {{ chat.tipo === 'general' ? chat.nombre : otherParticipant.name }}
                                </h2>
                                <span v-if="chat.tipo === 'general'"
                                    class="px-1.5 py-0.5 bg-brand-500/10 text-brand-600 dark:text-brand-400 text-[8px] font-black uppercase tracking-widest rounded-full border border-brand-500/20"
                                >General</span>
                            </div>
                            <span v-if="typingLabel" class="text-[10px] font-bold text-brand-500 uppercase tracking-widest animate-pulse">
                                {{ typingLabel }}
                            </span>
                            <span v-else class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">En línea</span>
                        </div>
                    </div>
                </div>

                <!-- Botón silenciar -->
                <button
                    @click="toggleMute"
                    :disabled="muteLoading"
                    class="flex items-center gap-2 px-4 py-2 rounded-2xl border transition-all text-xs font-black uppercase tracking-widest"
                    :class="isMuted
                        ? 'border-amber-400/40 bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20'
                        : 'border-light-border dark:border-dark-border text-gray-400 hover:text-amber-500 hover:border-amber-400/40'"
                >
                    <component :is="isMuted ? Bell : BellOff" class="w-4 h-4" />
                    <span class="hidden sm:inline">{{ isMuted ? 'Activar' : 'Silenciar' }}</span>
                </button>
            </div>
        </template>

        <!-- Emoji picker (hidden inputs) -->
        <input ref="fileInput"  type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,image/*" @change="onFileSelected" />
        <input ref="imageInput" type="file" class="hidden" accept="image/*" @change="onFileSelected" />

        <div class="max-w-4xl mx-auto h-[calc(100vh-280px)] min-h-[500px] flex flex-col bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3rem] shadow-2xl shadow-black/5 overflow-hidden relative mb-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Mensajes -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-gray-50/30 dark:bg-black/10 relative z-10">
                <div v-for="(msg, idx) in messages" :key="msg.id"
                    :class="['flex flex-col', msg.type === 'product_card' ? 'max-w-[85%]' : 'max-w-[80%]', msg.sender_id === currentUserId ? 'ml-auto items-end' : 'mr-auto items-start']"
                >
                    <div v-if="idx === 0" class="w-full flex justify-center my-6">
                        <span class="px-3 py-1 bg-white/50 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-full text-[9px] font-black text-gray-400 uppercase tracking-widest">Chat Iniciado</span>
                    </div>

                    <!-- Nombre del remitente (solo chat general, mensajes ajenos, primer mensaje del bloque) -->
                    <span v-if="showSenderName(msg, idx)"
                        class="text-[10px] font-black uppercase tracking-widest mb-1 ml-1"
                        :style="{ color: senderColor(msg.sender_id) }"
                    >
                        {{ msg.sender?.name ?? 'Usuario' }}
                    </span>

                    <!-- ── Tarjeta de Producto ── -->
                    <template v-if="msg.type === 'product_card' && msg.metadata">
                        <div :class="[
                            'relative overflow-hidden rounded-[1.8rem] shadow-md border transition-all duration-300 w-72',
                            msg.sender_id === currentUserId
                                ? 'rounded-tr-none border-brand-300/40 bg-brand-600'
                                : 'rounded-tl-none border-light-border dark:border-dark-border bg-white dark:bg-black/40'
                        ]">
                            <!-- Imagen del producto -->
                            <div class="relative h-40 overflow-hidden">
                                <img
                                    v-if="msg.metadata.imagen"
                                    :src="msg.metadata.imagen"
                                    :alt="msg.metadata.titulo"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-white/5">
                                    <ShoppingBag class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                </div>
                                <!-- Badge precio -->
                                <div class="absolute bottom-3 right-3 px-3 py-1 bg-black/60 backdrop-blur-sm rounded-xl">
                                    <span class="text-xs font-black text-white">
                                        Bs {{ parseFloat(msg.metadata.precio).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Info del producto -->
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <ShoppingBag :class="['w-3 h-3 flex-shrink-0', msg.sender_id === currentUserId ? 'text-white/70' : 'text-brand-500']" />
                                    <span :class="['text-[9px] font-black uppercase tracking-widest', msg.sender_id === currentUserId ? 'text-white/60' : 'text-brand-500']">
                                        Publicación compartida
                                    </span>
                                </div>
                                <p :class="['font-black text-sm leading-snug mb-3', msg.sender_id === currentUserId ? 'text-white' : 'text-gray-900 dark:text-white']">
                                    {{ msg.metadata.titulo }}
                                </p>
                                <a
                                    :href="msg.metadata.url"
                                    :class="[
                                        'flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all',
                                        msg.sender_id === currentUserId
                                            ? 'bg-white/20 hover:bg-white/30 text-white'
                                            : 'bg-brand-600 hover:bg-brand-500 text-white shadow-md shadow-brand-500/20'
                                    ]"
                                >
                                    <ExternalLink class="w-3.5 h-3.5" />
                                    Ver publicación
                                </a>
                            </div>
                        </div>
                        <div :class="['mt-1.5 flex items-center gap-1 text-[8px] font-bold uppercase tracking-widest opacity-50', msg.sender_id === currentUserId ? 'mr-1' : 'ml-1']">
                            <Clock class="w-2.5 h-2.5" />
                            {{ formatMsgTime(msg.created_at) }}
                        </div>
                    </template>

                    <!-- ── Mensaje normal (texto / archivo) ── -->
                    <template v-else>
                        <div :class="[
                            'relative px-5 py-3.5 rounded-[2rem] shadow-sm font-medium text-sm leading-relaxed transition-all duration-300',
                            msg.sender_id === currentUserId
                                ? 'bg-brand-600 text-white rounded-tr-none'
                                : 'bg-white dark:bg-black/40 text-gray-800 dark:text-gray-200 border border-light-border dark:border-dark-border rounded-tl-none'
                        ]">
                            <p v-if="msg.contenido" class="mb-1">{{ msg.contenido }}</p>

                            <a v-if="msg.attachment_path && isImage(msg)" :href="attachmentUrl(msg.attachment_path)" target="_blank" class="block mt-2">
                                <img :src="attachmentUrl(msg.attachment_path)" :alt="msg.attachment_name" class="max-w-[220px] max-h-[220px] rounded-2xl object-cover border border-white/20 hover:opacity-90 transition-opacity" />
                            </a>

                            <a v-else-if="msg.attachment_path" :href="attachmentUrl(msg.attachment_path)" target="_blank" download
                                :class="['flex items-center gap-2 mt-2 px-3 py-2 rounded-xl text-xs font-bold transition-colors',
                                    msg.sender_id === currentUserId ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200']"
                            >
                                <FileText class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate max-w-[160px]">{{ msg.attachment_name }}</span>
                                <Download class="w-3 h-3 flex-shrink-0 ml-auto" />
                            </a>

                            <div :class="['absolute top-full mt-1.5 flex items-center gap-1 text-[8px] font-bold uppercase tracking-widest opacity-60',
                                msg.sender_id === currentUserId ? 'right-0' : 'left-0']">
                                <Clock class="w-2.5 h-2.5" />
                                {{ formatMsgTime(msg.created_at) }}
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Estado vacío -->
                <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center pt-20">
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Aún no hay mensajes</p>
                    <p class="text-xs text-gray-400 mt-2">Dile hola a {{ otherParticipant.name }}</p>
                </div>

                <!-- Typing indicator -->
                <div v-if="typingLabel" class="flex items-end gap-2 mr-auto">
                    <div class="px-5 py-3.5 bg-white dark:bg-black/40 border border-light-border dark:border-dark-border rounded-[2rem] rounded-tl-none shadow-sm">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>

                <div ref="messagesEnd"></div>
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-white dark:bg-dark-surface border-t border-light-border dark:border-dark-border relative z-20">

                <!-- Preview de archivo pendiente -->
                <div v-if="pendingFile" class="flex items-center gap-3 mb-3 px-4 py-2.5 bg-brand-500/10 border border-brand-500/20 rounded-2xl">
                    <img v-if="pendingPreview" :src="pendingPreview" class="w-10 h-10 rounded-xl object-cover" />
                    <FileText v-else class="w-8 h-8 text-brand-500 flex-shrink-0" />
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate flex-1">{{ pendingFile.name }}</span>
                    <button @click="removePendingFile" class="p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-900/30 text-red-400 transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Emoji picker -->
                <div v-if="showEmojiPicker" class="absolute bottom-full left-4 mb-2 z-50 p-3 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-2xl shadow-2xl grid grid-cols-10 gap-1 w-80">
                    <button v-for="emoji in EMOJIS" :key="emoji" @click="insertEmoji(emoji)"
                        class="text-xl hover:scale-125 transition-transform p-0.5 leading-none">
                        {{ emoji }}
                    </button>
                </div>

                <div class="relative group flex items-end gap-2">
                    <textarea
                        v-model="newMessage"
                        rows="1"
                        @keydown.enter.exact.prevent="sendMessage"
                        @input="onInput"
                        @blur="() => { clearTimeout(typingTimeout); sendTypingStop(); }"
                        class="flex-1 pl-6 pr-4 py-4 bg-gray-100/50 dark:bg-black/40 border-2 border-transparent focus:border-brand-500/50 focus:ring-4 focus:ring-brand-500/10 rounded-[2rem] text-sm text-gray-900 dark:text-white placeholder-gray-400 transition-all resize-none leading-relaxed outline-none"
                        placeholder="Escribe algo..."
                    ></textarea>
                    <button
                        @click="sendMessage"
                        :disabled="(!newMessage.trim() && !pendingFile) || processing"
                        class="flex-shrink-0 p-3.5 bg-brand-600 text-white rounded-full shadow-lg shadow-brand-500/30 hover:bg-brand-500 disabled:opacity-30 disabled:grayscale transition-all hover:scale-105 active:scale-95"
                    >
                        <Send class="w-5 h-5" />
                    </button>
                </div>

                <!-- Toolbar -->
                <div class="flex items-center gap-3 mt-3 px-2">
                    <button @click="triggerImage" class="flex items-center gap-1.5 text-[9px] font-black text-gray-400 hover:text-brand-500 uppercase tracking-widest transition-colors">
                        <Image class="w-3.5 h-3.5" />
                        Imagen
                    </button>
                    <button @click="triggerFile" class="flex items-center gap-1.5 text-[9px] font-black text-gray-400 hover:text-brand-500 uppercase tracking-widest transition-colors">
                        <Paperclip class="w-3.5 h-3.5" />
                        Archivo
                    </button>
                    <button @click="showEmojiPicker = !showEmojiPicker" :class="['flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest transition-colors', showEmojiPicker ? 'text-brand-500' : 'text-gray-400 hover:text-brand-500']">
                        <Smile class="w-3.5 h-3.5" />
                        Emoji
                    </button>
                    <div class="flex-1"></div>
                    <span class="text-[9px] font-bold text-gray-300 dark:text-gray-600 italic">Enter para enviar</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(124,58,237,0.3); }
</style>
