<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShieldCheck, LogOut, Check } from 'lucide-vue-next';
import CamaraLogin from '@/Components/CamaraLogin.vue';

defineProps({
    user: Object
});

const isVerifying = ref(true);
const success = ref(false);
const errorMsg = ref('');

const handleSuccess = () => {
    isVerifying.value = false;
    success.value = true;
    errorMsg.value = '';
    
    // Redirigir al origen (dashboard)
    setTimeout(() => {
        router.visit(route('dashboard'));
    }, 1500);
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head title="Verificación de Identidad | Campus Market" />

    <div class="relative flex items-center justify-center min-h-screen px-4 bg-black sm:px-6 lg:px-8">
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-40">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/80 to-black/60"></div>
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-600/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 w-full max-w-md p-8 transition-all bg-white/5 border border-white/10 shadow-2xl rounded-3xl backdrop-blur-xl">
            <div class="flex flex-col items-center mb-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-2xl" :class="success ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-400'">
                    <ShieldCheck v-if="success" class="w-8 h-8" />
                    <ShieldCheck v-else class="w-8 h-8" />
                </div>
                <h2 class="text-2xl font-black text-transparent bg-clip-text" :class="success ? 'bg-gradient-to-r from-emerald-400 to-emerald-200' : 'bg-gradient-to-r from-white to-indigo-200'">
                    {{ success ? 'Identidad Verificada' : 'Autenticación Facial (2FA)' }}
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    {{ success ? 'Iniciando sesión segura...' : 'Por motivos de seguridad comprobemos que eres tú verdaderamente.' }}
                </p>
            </div>

            <!-- Escáner -->
            <div v-if="!success" class="flex justify-center mb-6">
                <CamaraLogin :email="$page.props.auth.user.email" @success="handleSuccess" @error="(m) => errorMsg = m" />
            </div>

            <p v-if="errorMsg" class="mt-4 text-xs font-bold text-center text-rose-500">{{ errorMsg }}</p>

            <button type="button" @click="logout" class="flex items-center justify-center w-full px-4 py-3 mt-6 text-sm font-medium text-gray-400 transition-colors border rounded-xl border-white/10 hover:bg-white/5 hover:text-white">
                <LogOut class="w-4 h-4 mr-2" />
                Cerrar sesión de forma segura
            </button>
        </div>
    </div>
</template>
