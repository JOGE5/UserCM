<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-10">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="px-8 py-10 sm:p-10 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border shadow-2xl shadow-black/5 rounded-[2.5rem] overflow-hidden">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
