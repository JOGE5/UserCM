<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import InputError from '@/Components/InputError.vue';
import { ShieldCheck, Loader2 } from 'lucide-vue-next';

defineProps({
    status: String,
});

const codeInputs = ref([]);
const codeValues = ref(['', '', '', '', '', '']); // 6 chars

const form = useForm({
    code: '',
});

const updateFormCode = () => {
    form.code = codeValues.value.join('');
};

const handleInput = (index, event) => {
    const value = event.target.value.replace(/[^0-9]/g, '');
    
    if (value) {
        codeValues.value[index] = value.charAt(value.length - 1);
        
        if (index < 5) {
            nextTick(() => {
                codeInputs.value[index + 1]?.focus();
            });
        }
    } else {
        codeValues.value[index] = '';
    }
    
    updateFormCode();
};

const handleKeydown = (index, event) => {
    if (event.key === 'Backspace' && !codeValues.value[index] && index > 0) {
        codeInputs.value[index - 1]?.focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').replace(/[^0-9]/g, '');
    
    if (pastedData) {
        for (let i = 0; i < 6; i++) {
            if (i < pastedData.length) {
                codeValues.value[i] = pastedData.charAt(i);
            }
        }
        updateFormCode();
        
        const focusIndex = Math.min(pastedData.length, 5);
        nextTick(() => {
            codeInputs.value[focusIndex]?.focus();
        });
    }
};

const submit = () => {
    updateFormCode();
    form.post(route('device.verification.verify'));
};

const countdown = ref(60);
const isResending = ref(false);
let timer = null;

const startCountdown = () => {
    countdown.value = 60;
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(timer);
        }
    }, 1000);
};

const resendCode = () => {
    if (countdown.value > 0 || isResending.value) return;
    
    isResending.value = true;
    router.post(route('device.verification.resend'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            codeValues.value = ['', '', '', '', '', ''];
            updateFormCode();
            startCountdown();
            nextTick(() => codeInputs.value[0]?.focus());
        },
        onFinish: () => {
            isResending.value = false;
        }
    });
};

onMounted(() => {
    startCountdown();
    if (codeInputs.value[0]) {
        codeInputs.value[0].focus();
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="Verificación de Dispositivo | Campus Market" />

    <div class="relative flex flex-col items-center justify-center min-h-screen px-4 overflow-hidden bg-black sm:px-6 lg:px-8">
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-60">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>
        
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[100px] mix-blend-screen opacity-50 pointer-events-none"></div>

        <div v-if="status" class="relative z-20 w-full max-w-md p-4 mb-6 text-sm font-medium text-emerald-400 border rounded-xl bg-emerald-500/10 border-emerald-500/20 backdrop-blur-md">
            {{ status }}
        </div>

        <div class="relative z-20 w-full max-w-md p-8 sm:p-10 transition-all duration-500 bg-white/5 shadow-[0_0_40px_rgba(0,0,0,0.5)] rounded-[2.5rem] border border-white/10 backdrop-blur-xl group hover:border-white/20">
            <div class="flex flex-col items-center mb-8 text-center">
                <div class="flex items-center justify-center w-20 h-20 mb-6 rounded-full bg-indigo-500/10 border border-indigo-500/20 shadow-[0_0_20px_rgba(79,70,229,0.15)]">
                    <ShieldCheck class="w-10 h-10 text-indigo-400" />
                </div>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300">
                    Dispositivo Nuevo
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Hemos detectado un acceso desde un dispositivo nuevo. Por favor, ingresa el código numérico enviado a tu correo.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Código Numérico -->
                <div class="space-y-2">
                    <div class="flex justify-center gap-3 mt-4" @paste="handlePaste">
                        <template v-for="(val, index) in codeValues" :key="index">
                            <input
                                :ref="el => codeInputs[index] = el"
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                v-model="codeValues[index]"
                                @input="handleInput(index, $event)"
                                @keydown="handleKeydown(index, $event)"
                                class="w-12 h-14 text-2xl font-bold text-center text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            />
                        </template>
                    </div>

                    <InputError class="mt-4 text-xs text-center text-red-500" :message="form.errors.code" />
                </div>

                <div class="flex flex-col items-center justify-center pt-2 gap-2">
                    <button 
                        type="button" 
                        @click="resendCode" 
                        :disabled="countdown > 0 || isResending"
                        class="text-sm font-medium transition-colors focus:outline-none flex items-center"
                        :class="countdown > 0 ? 'text-gray-500 cursor-not-allowed' : 'text-indigo-400 hover:text-indigo-300'"
                    >
                        <Loader2 v-if="isResending" class="w-4 h-4 mr-2 animate-spin" />
                        <span v-if="countdown > 0">Reenviar código en {{ countdown }}s</span>
                        <span v-else>Reenviar código</span>
                    </button>
                    
                    <Link :href="route('logout')" method="post" as="button" class="text-xs text-gray-500 hover:text-gray-300 transition-colors mt-2">
                        Cancelar inicio de sesión
                    </Link>
                </div>

                <button type="submit" :disabled="form.processing || form.code.length !== 6" class="relative flex items-center justify-center w-full h-14 overflow-hidden font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-6 shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_40px_rgba(79,70,229,0.5)]">
                    <span class="relative z-10">Verificar Dispositivo</span>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                </button>
            </form>
        </div>
    </div>
</template>
