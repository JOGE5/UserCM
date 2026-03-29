<template>
    <div class="md:col-span-1 flex flex-col gap-2">
        <div class="px-4 sm:px-0">
            <h3 class="text-xl font-black tracking-tight text-gray-900 dark:text-white">
                <slot name="title" />
            </h3>

            <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed max-w-[280px]">
                <slot name="description" />
            </p>
        </div>

        <div class="px-4 sm:px-0">
            <slot name="aside" />
        </div>
    </div>
</template>
