<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { 
    Send, 
    ArrowLeft, 
    User, 
    MoreVertical, 
    Image, 
    Paperclip,
    ChevronLeft,
    Clock,
    Circle,
    Info
} from 'lucide-vue-next';

const props = defineProps({
  chat: Object,
});

const page = usePage();
const chat = props.chat;
const newMessage = ref('');
const messagesEnd = ref(null);
const processing = ref(false);
const currentUserId = page.props.auth.user.id;

// Copia reactiva de los mensajes
const messages = ref(chat.messages && Array.isArray(chat.messages) ? [...chat.messages] : []);

const otherParticipant = computed(() => {
    return chat.users.find(u => u.id !== currentUserId) || chat.users[0] || { name: 'Usuario' };
});

const sendMessage = () => {
    if (!newMessage.value.trim() || processing.value) return;

    processing.value = true;
    axios.post(`/chats/${chat.id}/messages`, { contenido: newMessage.value })
        .then(response => {
            messages.value.push(response.data);
            newMessage.value = '';
            scrollToBottom();
        })
        .finally(() => {
            processing.value = false;
        });
};

const scrollToBottom = () => {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
};

nextTick(scrollToBottom);

onMounted(() => {
  if (window.Echo) {
    window.Echo.private(`chat.${chat.id}`)
      .listen('MessageSent', (e) => {
        const exists = messages.value.some(m => m.id === e.message.id);
        if (!exists) {
          messages.value.push(e.message);
          scrollToBottom();
        }
      });
  }
});

onBeforeUnmount(() => {
    if (window.Echo) {
        window.Echo.leave(`chat.${chat.id}`);
    }
});

const formatMsgTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout :title="`Chat con ${otherParticipant.name}`">
        <template #header>
            <div class="flex items-center gap-4">
                <button @click="router.visit(route('mensajes.index'))" class="p-2 -ml-2 text-gray-400 hover:text-brand-600 transition-colors">
                    <ChevronLeft class="w-6 h-6" />
                </button>
                <div class="flex items-center gap-3">
                    <div class="relative w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-sm font-black ring-2 ring-brand-500/20">
                        {{ otherParticipant.name.charAt(0).toUpperCase() }}
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-dark-border rounded-full"></div>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-base font-black text-gray-900 dark:text-white leading-none mb-1">
                            {{ chat.tipo === 'privado' ? otherParticipant.name : chat.nombre }}
                        </h2>
                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">En línea</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto h-[calc(100vh-280px)] min-h-[500px] flex flex-col bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3rem] shadow-2xl shadow-black/5 overflow-hidden relative mb-10 overflow-hidden">
            <!-- Capa de decoración de fondo -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Área de Mensajes -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-gray-50/30 dark:bg-black/10 relative z-10">
                <div v-for="(msg, idx) in messages" :key="msg.id" 
                    :class="[
                        'flex flex-col max-w-[80%]',
                        msg.sender_id === currentUserId ? 'ml-auto items-end' : 'mr-auto items-start'
                    ]"
                >
                    <!-- Indicador de fecha/hora si es primer mensaje o cambio de bloque -->
                    <div v-if="idx === 0" class="w-full flex justify-center my-6">
                        <span class="px-3 py-1 bg-white/50 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-full text-[9px] font-black text-gray-400 uppercase tracking-widest">Chat Iniciado</span>
                    </div>

                    <div :class="[
                        'relative px-5 py-3.5 rounded-[2rem] shadow-sm font-medium text-sm leading-relaxed transition-all duration-300',
                        msg.sender_id === currentUserId 
                            ? 'bg-brand-600 text-white rounded-tr-none' 
                            : 'bg-white dark:bg-black/40 text-gray-800 dark:text-gray-200 border border-light-border dark:border-dark-border rounded-tl-none'
                    ]">
                        {{ msg.contenido }}
                        
                        <div :class="[
                            'absolute top-full mt-1.5 flex items-center gap-1.2 text-[8px] font-bold uppercase tracking-widest opacity-60',
                            msg.sender_id === currentUserId ? 'right-0' : 'left-0'
                        ]">
                            <Clock class="w-2.5 h-2.5" />
                            {{ formatMsgTime(msg.created_at) }}
                        </div>
                    </div>
                </div>
                
                <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center">
                    <div class="p-6 rounded-full bg-brand-500/5 border border-brand-500/10 mb-6">
                        <MessageSquare class="w-12 h-12 text-brand-500/50" />
                    </div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Aún no hay mensajes</p>
                    <p class="text-xs text-gray-400 mt-2">Dile hola a {{ otherParticipant.name }}</p>
                </div>
                <div ref="messagesEnd"></div>
            </div>

            <!-- Input Area -->
            <div class="p-6 bg-white dark:bg-dark-surface border-t border-light-border dark:border-dark-border relative z-20">
                <div class="relative group">
                    <textarea 
                        v-model="newMessage"
                        rows="1"
                        @keydown.enter.exact.prevent="sendMessage"
                        class="w-full pl-6 pr-16 py-4 bg-gray-100/50 dark:bg-black/40 border-2 border-transparent focus:border-brand-500/50 focus:ring-4 focus:ring-brand-500/10 rounded-[2rem] text-sm text-gray-900 dark:text-white placeholder-gray-400 transition-all resize-none leading-relaxed outline-none"
                        placeholder="Escribe algo brillante..."
                    ></textarea>
                    
                    <button 
                        @click="sendMessage"
                        :disabled="!newMessage.trim() || processing"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-3 bg-brand-600 text-white rounded-full shadow-lg shadow-brand-500/30 hover:bg-brand-500 disabled:opacity-30 disabled:grayscale transition-all hover:scale-105 active:scale-95"
                    >
                        <Send class="w-5 h-5" />
                    </button>
                    
                    <div class="absolute left-6 -top-2 px-2 bg-white dark:bg-dark-surface text-[8px] font-black text-brand-500 uppercase tracking-tighter opacity-0 group-focus-within:opacity-100 transition-opacity">
                        Mensaje Directo
                    </div>
                </div>
                
                <div class="flex items-center gap-4 mt-4 px-4">
                    <button class="flex items-center gap-1.5 text-[9px] font-black text-gray-400 hover:text-brand-500 uppercase tracking-widest transition-colors">
                        <Paperclip class="w-3 h-3" />
                        Archivo
                    </button>
                    <button class="flex items-center gap-1.5 text-[9px] font-black text-gray-400 hover:text-brand-500 uppercase tracking-widest transition-colors">
                        <Image class="w-3 h-3" />
                        Imagen
                    </button>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-1 text-[9px] font-bold text-gray-300 dark:text-gray-600 italic">
                        <Info class="w-3 h-3" />
                        Presiona Enter para enviar
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(124, 58, 237, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(124, 58, 237, 0.3);
}
</style>
