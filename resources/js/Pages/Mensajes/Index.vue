<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    MessageSquare, 
    Search, 
    Plus, 
    TrendingUp, 
    User, 
    Calendar,
    ChevronRight,
    MessageCircle,
    Inbox,
    Clock
} from 'lucide-vue-next';

const props = defineProps({
    chats: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = page.props.auth.user.id;

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 86400000) { // Menos de 24h
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleDateString([], { day: '2-digit', month: 'short' });
};

const getOtherParticipant = (chat) => {
    if (!chat.users) return { name: 'Chat' };
    return chat.users.find(u => u.id !== currentUserId) || chat.users[0] || { name: 'Usuario' };
};

const getLastMessage = (chat) => {
    if (!chat.messages || chat.messages.length === 0) return 'Sin mensajes aún';
    return chat.messages[chat.messages.length - 1].contenido;
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
                    Gestiona tus conversaciones con compradores y vendedores de la comunidad.
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
                        class="w-full py-3.5 pl-12 pr-4 text-sm bg-gray-100/50 dark:bg-black/40 border-0 focus:ring-2 focus:ring-brand-500/50 rounded-2xl transition-all dark:text-white dark:placeholder-gray-500"
                        placeholder="Buscar en tus conversaciones..."
                    >
                </div>
                
                <div class="flex items-center gap-4 px-4 py-2 bg-brand-500/5 rounded-2xl border border-brand-500/10">
                    <Inbox class="w-4 h-4 text-brand-500" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-brand-600 dark:text-brand-400">{{ chats.length }} Chats Activos</span>
                </div>
            </div>

            <!-- Lista de Chats -->
            <div v-if="chats.length" class="max-w-4xl mx-auto space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <Link 
                    v-for="chat in chats" 
                    :key="chat.id"
                    :href="route('chats.show', chat.id)"
                    class="group block bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] p-6 hover:shadow-2xl hover:shadow-brand-500/10 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/5 rounded-full -mr-16 -mt-16 pointer-events-none group-hover:bg-brand-500/10 transition-colors"></div>
                    
                    <div class="relative flex items-center gap-6">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 border-4 border-white dark:border-dark-border shadow-lg flex items-center justify-center text-white ring-2 ring-brand-500/20">
                                <span class="text-xl font-black uppercase">{{ getOtherParticipant(chat).name.charAt(0) }}</span>
                            </div>
                            <!-- Indicador Online (Simulado) -->
                            <div class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-dark-border rounded-full shadow-sm"></div>
                        </div>

                        <!-- Info del Chat -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-lg font-black text-gray-900 dark:text-white truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                    {{ chat.tipo === 'privado' ? getOtherParticipant(chat).name : chat.nombre }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-gray-400 font-bold text-[10px] uppercase tracking-tighter">
                                    <Clock class="w-3 h-3" />
                                    {{ formatTime(chat.updated_at) }}
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate max-w-md pr-10">
                                    {{ getLastMessage(chat) }}
                                </p>
                                <ChevronRight class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:translate-x-1 group-hover:text-brand-500 transition-all" />
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="relative flex flex-col items-center justify-center py-32 px-10 text-center bg-white/30 dark:bg-dark-surface/30 backdrop-blur-md border border-light-border dark:border-dark-border rounded-[4rem] group overflow-hidden">
                <div class="p-8 rounded-full bg-gray-50 dark:bg-black/40 mb-8 shadow-inner border border-white dark:border-white/5">
                    <MessageSquare class="w-16 h-16 text-gray-300 dark:text-gray-600 animate-pulse" />
                </div>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Tu buzón está impecable</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mb-10 text-lg font-medium">Inicia conversaciones desde las publicaciones del marketplace para contactar a los vendedores.</p>
                <Link :href="route('dashboard')" class="px-10 py-4 bg-brand-600 text-white font-black rounded-2xl hover:bg-brand-500 shadow-xl shadow-brand-500/30 transition-all">Explorar Ecosistema</Link>
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
    to { opacity: 1; transform: translateY(0); }
}
</style>
