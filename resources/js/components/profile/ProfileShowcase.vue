<script setup>
defineProps({
    showcase: Object,
})
</script>

<template>
    <div
        v-if="showcase?.image_url"
        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
    >
        <img
            :src="showcase.image_url"
            :alt="showcase.name"
            class="max-h-[420px] w-full object-cover"
        />
    </div>

    <div
        v-else
        class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-900 p-10 text-center text-zinc-500"
    >
        No showcase equipped.
    </div>
</template>