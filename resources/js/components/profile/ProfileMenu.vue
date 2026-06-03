<script setup>
defineProps({
    activeSection: String,
    items: Array,
})

defineEmits(['change'])
</script>

<template>
    <div class="border-b border-zinc-800 bg-zinc-950/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl gap-8 px-8">
            <button
                v-for="item in items"
                :key="item.key"
                type="button"
                class="relative py-4 text-sm font-medium transition"
                :class="
                    activeSection === item.key
                        ? 'text-white'
                        : 'text-zinc-500 hover:text-zinc-300'
                "
                @click="$emit('change', item.key)"
            >
                <span class="flex items-center gap-2">
                    <component
                        :is="item.icon"
                        class="h-4 w-4"
                    />

                    {{ item.label }}
                </span>

                <span
                    v-if="activeSection === item.key"
                    class="absolute bottom-0 left-0 h-[2px] w-full bg-white"
                />
            </button>
        </div>
    </div>
</template>