<script setup>
import { ref } from 'vue'

defineProps({
    game: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },
})

const expandedReviews = ref([])

const platformLabel = (platform) => {
    const labels = {
        pc: 'PC',
        steam_deck: 'Steam Deck',
        playstation_5: 'PlayStation 5',
        playstation_4: 'PlayStation 4',
        xbox_series: 'Xbox Series X/S',
        xbox_one: 'Xbox One',
        nintendo_switch: 'Nintendo Switch',
        nintendo_switch_2: 'Nintendo Switch 2',
        ios: 'iOS',
        android: 'Android',
        other: 'Other',
    }

    return labels[platform] ?? platform
}

const shouldTruncate = (body) => {
    return String(body ?? '').length > 360
}

const isExpanded = (review) => {
    return expandedReviews.value.includes(review.id)
}

const displayedBody = (review) => {
    const body = String(review.body ?? '')

    if (!shouldTruncate(body) || isExpanded(review)) {
        return body
    }

    return `${body.slice(0, 360)}...`
}

const toggleExpanded = (review) => {
    if (isExpanded(review)) {
        expandedReviews.value = expandedReviews.value.filter(
            id => id !== review.id
        )

        return
    }

    expandedReviews.value.push(review.id)
}
</script>

<template>
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
        <div class="flex flex-col gap-6 lg:flex-row">
            <img
                v-if="game.cover_url"
                :src="game.cover_url"
                :alt="game.title"
                class="h-48 w-full rounded-2xl object-cover lg:w-80"
            >

            <div
                v-else
                class="flex h-48 w-full items-center justify-center rounded-2xl bg-zinc-800 text-zinc-500 lg:w-80"
            >
                No cover
            </div>

            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-zinc-500">
                    {{ game.source }}
                </p>

                <h2 class="mt-2 text-3xl font-bold text-white">
                    {{ game.title }}
                </h2>

                <p class="mt-2 text-sm text-zinc-500">
                    Game ID: {{ game.id }}
                </p>

                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                        <p class="text-sm text-zinc-500">
                            Average rating
                        </p>

                        <p class="mt-2 text-3xl font-bold text-white">
                            {{ stats?.average_rating ? `${stats.average_rating}/10` : '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                        <p class="text-sm text-zinc-500">
                            Reviews
                        </p>

                        <p class="mt-2 text-3xl font-bold text-white">
                            {{ stats?.reviews_count ?? '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                        <p class="text-sm text-zinc-500">
                            Recommend
                        </p>

                        <p class="mt-2 text-3xl font-bold text-white">
                            {{
                                stats?.recommended_percent !== null && stats?.recommended_percent !== undefined
                                    ? `${stats.recommended_percent}%`
                                    : '—'
                            }}
                        </p>
                    </div>
                </div>

                <p
                    v-if="loading"
                    class="mt-6 text-sm text-zinc-500"
                >
                    Loading curator data...
                </p>
            </div>
        </div>

        <div
            v-if="stats?.reviews?.length"
            class="mt-8 space-y-4"
        >
            <h3 class="text-xl font-bold text-white">
                Community reviews
            </h3>

            <article
                v-for="review in stats.reviews"
                :key="review.id"
                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="review.user?.avatar"
                            :src="review.user.avatar"
                            :alt="review.user.name"
                            class="h-10 w-10 rounded-full object-cover"
                        >

                        <div
                            v-else
                            class="h-10 w-10 rounded-full bg-zinc-800"
                        />

                        <div>
                            <p class="font-bold text-white">
                                {{ review.user?.name ?? 'Unknown user' }}
                            </p>

                            <p class="text-xs text-zinc-500">
                                {{ review.created_at }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-lg font-bold text-white">
                            {{ review.rating }}/10
                        </p>

                        <p
                            v-if="review.platform"
                            class="text-xs font-semibold text-indigo-300"
                        >
                            {{ platformLabel(review.platform) }}
                        </p>

                        <p class="text-xs text-zinc-500">
                            {{ review.recommended ? 'Recommended' : 'Not recommended' }}
                        </p>
                    </div>
                </div>

                <h4 class="mt-4 font-bold text-white">
                    {{ review.title }}
                </h4>

                <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:gap-6">
                    <a
                        v-if="review.screenshot_url"
                        :href="review.screenshot_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="shrink-0"
                    >
                        <img
                            :src="review.screenshot_url"
                            :alt="review.title"
                            class="h-56 w-full rounded-2xl border border-zinc-800 object-cover lg:w-80"
                        >
                    </a>

                    <div class="min-w-0 flex-1">
                        <p class="whitespace-pre-line break-words text-sm leading-6 text-zinc-300">
                            {{ displayedBody(review) }}
                        </p>

                        <button
                            v-if="shouldTruncate(review.body)"
                            type="button"
                            class="mt-3 text-sm font-bold text-white underline underline-offset-4 transition hover:text-zinc-300"
                            @click="toggleExpanded(review)"
                        >
                            {{ isExpanded(review) ? 'Show less' : 'Read more' }}
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div
            v-else-if="stats && !stats.reviews?.length"
            class="mt-8 rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
        >
            No community reviews for this game yet.
        </div>
    </section>
</template>