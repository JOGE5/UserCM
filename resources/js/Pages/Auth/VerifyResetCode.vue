<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import InputError from '@/Components/InputError.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    email: String,
    status: String,
});

const codeInputs = ref([]);
const codeValues = ref(['', '', '', '', '', '', '', '']); // 8 chars

const form = useForm({
    email: props.email,
    code: '',
});

const updateFormCode = () => {
    form.code = codeValues.value.join('');
};

const handleInput = (index, event) => {
    const value = event.target.value.toUpperCase();
    
    if (value) {
        codeValues.value[index] = value.charAt(value.length - 1);
        
        if (index < 7) {
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
        // Move to previous input on backspace if current is empty
        codeInputs.value[index - 1]?.focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    if (pastedData) {
        for (let i = 0; i < 8; i++) {
            if (i < pastedData.length) {
                codeValues.value[i] = pastedData.charAt(i);
            }
        }
        updateFormCode();
        
        // Focus the appropriate input
        const focusIndex = Math.min(pastedData.length, 7);
        nextTick(() => {
            codeInputs.value[focusIndex]?.focus();
        });
    }
};

const submit = () => {
    updateFormCode();
    form.post(route('password.verify.code.submit'));
};

onMounted(() => {
    if (codeInputs.value[0]) {
        codeInputs.value[0].focus();
    }
});
</script>

<template>
    <Head title="Verificar Código | Campus Market" />

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
                <Link href="/" class="flex items-center justify-center w-20 h-20 mb-6 transition-transform duration-500 overflow-hidden rounded-[1.5rem] bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.05)] hover:scale-110">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                </Link>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300">
                    Verificar Código
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Hemos enviado un código alfanumérico a <span class="font-bold text-white">{{ email }}</span>.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Código -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-300 ml-1 text-center">Ingresa el código de 8 caracteres</label>
                    
                    <div class="flex justify-center gap-2 mt-4" @paste="handlePaste">
                        <template v-for="(val, index) in codeValues" :key="index">
                            <input
                                :ref="el => codeInputs[index] = el"
                                type="text"
                                maxlength="1"
                                v-model="codeValues[index]"
                                @input="handleInput(index, $event)"
                                @keydown="handleKeydown(index, $event)"
                                class="w-10 h-12 text-xl font-bold text-center text-white uppercase transition-all duration-300 bg-black/40 border border-white/10 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            />
                            <!-- Separador en medio -->
                            <span v-if="index === 3" class="flex items-center text-gray-500">-</span>
                        </template>
                    </div>

                    <InputError class="mt-2 text-xs text-center text-red-500" :message="form.errors.code" />
                    <InputError class="mt-2 text-xs text-center text-red-500" :message="form.errors.email" />
                </div>

                <div class="flex items-center justify-between pt-4">
                    <Link :href="route('password.request.custom')" class="flex items-center text-sm font-medium text-gray-400 transition-colors hover:text-white group">
                        <ArrowLeft class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" />
                        Atrás
                    </Link>
                </div>

                <button type="submit" :disabled="form.processing || form.code.length !== 8" class="relative flex items-center justify-center w-full h-14 overflow-hidden font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-6 shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_40px_rgba(79,70,229,0.5)]">
                    <span class="relative z-10">Validar Código</span>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                </button>
            </form>
        </div>
    </div>
</template>
