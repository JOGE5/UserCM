<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { ChevronDown, Search, Check } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { default: null },
    options: { type: Array, default: () => [] }, // [{ value, label }]
    placeholder: { type: String, default: 'Seleccionar...' },
    accent: { type: String, default: 'fuchsia' }, // tailwind color name
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const search = ref('');
const root = ref(null);
const searchInput = ref(null);

const selected = computed(() => props.options.find(o => o.value === props.modelValue));
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(o => (o.label || '').toLowerCase().includes(q));
});

const toggle = () => {
    open.value = !open.value;
    if (open.value) {
        search.value = '';
        nextTick(() => searchInput.value?.focus());
    }
};
const choose = (o) => { emit('update:modelValue', o.value); open.value = false; };

const onClickOutside = (e) => { if (root.value && !root.value.contains(e.target)) open.value = false; };
onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="root" class="relative">
        <button type="button" @click="toggle"
            class="w-full flex items-center justify-between gap-2 px-3 py-2.5 text-sm bg-gray-800 border border-gray-700 rounded-xl text-left text-white hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/40 focus:border-fuchsia-500 transition-all"
            :class="open ? 'border-fuchsia-500 ring-2 ring-fuchsia-500/40' : ''">
            <span class="truncate" :class="selected ? 'text-white' : 'text-gray-500'">{{ selected ? selected.label : placeholder }}</span>
            <ChevronDown class="w-4 h-4 text-gray-500 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" />
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1">
            <div v-if="open" class="absolute z-50 mt-2 w-full min-w-[16rem] bg-[#0f0f14] border border-gray-700 rounded-2xl shadow-2xl shadow-black/50 overflow-hidden">
                <div class="p-2 border-b border-gray-800">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500" />
                        <input ref="searchInput" v-model="search" type="text" placeholder="Buscar..."
                            class="w-full pl-8 pr-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-fuchsia-500/40 focus:border-fuchsia-500" />
                    </div>
                </div>
                <div class="max-h-64 overflow-y-auto py-1 ss-scroll">
                    <button v-for="o in filtered" :key="String(o.value)" type="button" @click="choose(o)"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-fuchsia-500/10"
                        :class="o.value === modelValue ? 'text-fuchsia-400 bg-fuchsia-500/5 font-bold' : 'text-gray-300'">
                        <span class="truncate">{{ o.label }}</span>
                        <Check v-if="o.value === modelValue" class="w-4 h-4 shrink-0" />
                    </button>
                    <div v-if="filtered.length === 0" class="px-3 py-5 text-center text-xs text-gray-500">Sin resultados</div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ss-scroll::-webkit-scrollbar { width: 6px; }
.ss-scroll::-webkit-scrollbar-track { background: transparent; }
.ss-scroll::-webkit-scrollbar-thumb { background: rgba(217,70,239,0.3); border-radius: 10px; }
.ss-scroll::-webkit-scrollbar-thumb:hover { background: rgba(217,70,239,0.5); }
</style>
