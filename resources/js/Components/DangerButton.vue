<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button 
        :type="type" 
        class="inline-flex items-center justify-center px-8 py-3.5 bg-rose-600 border border-transparent rounded-2xl font-black text-[10px] tracking-widest text-white uppercase shadow-lg shadow-rose-500/30 hover:bg-rose-500 hover:scale-[1.02] active:scale-[0.98] outline-none focus:ring-4 focus:ring-rose-500/50 disabled:opacity-25 transition-all duration-300"
    >
        <slot />
    </button>
</template>
