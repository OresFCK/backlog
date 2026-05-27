<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        required: true,
    },

    items: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <section>
        <div class="mb-6">
            <h2
                class="text-3xl font-black text-white"
            >
                {{ title }}
            </h2>

            <p
                class="mt-2 text-zinc-400"
            >
                {{ subtitle }}
            </p>
        </div>

        <div
            class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="item in items"
                :key="item.game.id"
                class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
            >
                <img
                    :src="item.game.header_image_url"
                    class="h-44 w-full object-cover"
                />

                <div class="p-5">
                    <h3
                        class="text-xl font-black text-white"
                    >
                        {{ item.game.title }}
                    </h3>

                    <p
                        class="mt-3 text-sm text-zinc-400"
                    >
                        {{ item.reason }}
                    </p>

                    <div
                        class="mt-4 flex flex-wrap gap-2"
                    >
                        <span
                            class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300"
                        >
                            Score {{ item.score }}
                        </span>

                        <span
                            class="rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-bold text-yellow-300"
                        >
                            ★ {{ item.average_rating }}
                        </span>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>