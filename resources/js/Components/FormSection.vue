<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-10">
        <SectionTitle class="mb-6 md:mb-0">
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="md:col-span-2">
            <form @submit.prevent="$emit('submitted')" class="relative group">
                <!-- Decoración de fondo (Brillo sutil) -->
                <div class="absolute -inset-1 bg-gradient-to-r from-brand-500/10 to-transparent rounded-[2.5rem] blur opacity-0 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                
                <div
                    class="relative bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border shadow-2xl shadow-black/5 overflow-hidden"
                    :class="hasActions ? 'rounded-t-[2.5rem]' : 'rounded-[2.5rem]'"
                >
                    <div class="px-8 py-10 sm:p-10">
                        <div class="grid grid-cols-6 gap-8">
                            <slot name="form" />
                        </div>
                    </div>
                </div>

                <div v-if="hasActions" class="relative flex items-center justify-end px-8 py-5 bg-gray-50/50 dark:bg-black/20 border-x border-b border-light-border dark:border-dark-border text-end sm:px-10 rounded-b-[2.5rem]">
                    <slot name="actions" />
                </div>
            </form>
        </div>
    </div>
</template>
