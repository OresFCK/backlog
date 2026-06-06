<script setup>
defineProps({
    showcase: Object,

    featuredGames: {
        type: Array,
        default: () => [],
    },

    featuredReviews: {
        type: Array,
        default: () => [],
    },

    featuredWardrobeItems: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <section class="space-y-8">
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

        <div v-if="featuredGames.length">
            <h2 class="mb-4 text-xl font-black text-white">
                Featured games
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="game in featuredGames"
                    :key="game.id"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4"
                >
                    <img
                        v-if="game.cover_url"
                        :src="game.cover_url"
                        :alt="game.title"
                        class="mb-3 h-48 w-full rounded-xl object-cover"
                    />

                    <h3 class="font-bold text-white">
                        {{ game.title }}
                    </h3>

                    <p
                        v-if="game.status"
                        class="mt-1 text-sm text-zinc-500"
                    >
                        {{ game.status }}
                    </p>

                    <p
                        v-if="game.rating"
                        class="mt-1 text-sm text-amber-400"
                    >
                        {{ game.rating }}/10
                    </p>
                </div>
            </div>
        </div>

        <div v-if="featuredReviews.length">
            <h2 class="mb-4 text-xl font-black text-white">
                Featured reviews
            </h2>

            <div class="grid gap-4 lg:grid-cols-2">
                <div
                    v-for="review in featuredReviews"
                    :key="review.id"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                >
                    <p class="text-sm font-bold text-indigo-300">
                        {{ review.game_title }}
                    </p>

                    <h3 class="mt-1 font-bold text-white">
                        {{ review.title }}
                    </h3>

                    <p class="mt-3 line-clamp-4 text-sm text-zinc-400">
                        {{ review.body }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="featuredWardrobeItems.length">
            <h2 class="mb-4 text-xl font-black text-white">
                Featured items
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="item in featuredWardrobeItems"
                    :key="item.id"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4"
                >
                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.name"
                        class="mb-3 h-32 w-full rounded-xl object-cover"
                    />

                    <h3 class="font-bold text-white">
                        {{ item.name }}
                    </h3>

                    <p
                        v-if="item.type"
                        class="text-sm text-zinc-500"
                    >
                        {{ item.type }}
                    </p>
                </div>
            </div>
        </div>

        <div
            v-if="
                !showcase?.image_url &&
                !featuredGames.length &&
                !featuredReviews.length &&
                !featuredWardrobeItems.length
            "
            class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-900 p-10 text-center text-zinc-500"
        >
            No showcase equipped.
        </div>
    </section>
</template>