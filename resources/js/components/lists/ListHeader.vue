<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    list: Object,
    count: Number,
})
</script>

<template>
    <section>
        <Link
            href="/lists"
            class="text-sm font-bold text-zinc-500 hover:text-white"
        >
            ← Back to lists
        </Link>

        <h1 class="mt-4 text-3xl font-black">
            {{ list.title }}
        </h1>

        <p class="mt-2 max-w-2xl text-zinc-400">
            {{ list.description || 'No description yet.' }}
        </p>

        <div class="mt-4 flex flex-wrap gap-2">
            <span
                class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400"
            >
                {{ list.visibility }}
            </span>

            <span
                class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400"
            >
                {{ count }} games
            </span>
        </div>
    </section>
</template>