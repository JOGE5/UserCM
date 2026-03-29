<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button 
        :type="type" 
        class="inline-flex items-center px-8 py-3.5 bg-brand-600 dark:bg-brand-500 border border-transparent rounded-2xl font-black text-[10px] tracking-widest text-white uppercase shadow-lg shadow-brand-500/30 hover:bg-brand-500 dark:hover:bg-brand-400 hover:scale-[1.02] active:scale-[0.98] outline-none focus:ring-4 focus:ring-brand-500/50 disabled:opacity-50 transition-all duration-300"
    >
        <slot />
    </button>
</template>
