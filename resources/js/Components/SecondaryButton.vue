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
        class="inline-flex items-center px-8 py-3.5 bg-white dark:bg-black/20 border border-light-border dark:border-dark-border rounded-2xl font-black text-[10px] tracking-widest text-gray-700 dark:text-gray-300 uppercase shadow-sm hover:bg-gray-50 dark:hover:bg-white/5 active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-gray-300/50 disabled:opacity-25 transition-all duration-300"
    >
        <slot />
    </button>
</template>
