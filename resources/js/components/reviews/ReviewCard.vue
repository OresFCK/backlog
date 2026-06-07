<script setup>
import { Flag, Star } from 'lucide-vue-next'

defineProps({
    review: {
        type: Object,
        required: true,
    },
})

defineEmits([
    'read-more',
    'toggle-vote',
    'toggle-featured',
    'report-review',
])

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
    return String(body ?? '').length > 420
}

const truncatedBody = (body) => {
    const text = String(body ?? '')

    if (text.length <= 420) {
        return text
    }

    return `${text.slice(0, 420)}...`
}
</script>

<template>
    <article class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="flex items-start gap-4">
            <img
                v-if="review.user?.avatar"
                :src="review.user.avatar"
                class="h-14 w-14 rounded-2xl object-cover"
            />

            <div
                v-else
                class="h-14 w-14 shrink-0 rounded-2xl bg-zinc-800"
            />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-lg font-bold text-white">
                        {{ review.user?.name ?? 'Unknown user' }}
                    </h2>

                    <span class="text-sm text-zinc-500">
                        {{ review.created_at }}
                    </span>

                    <span
                        v-if="review.rating"
                        class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-white"
                    >
                        {{ review.rating }}/10
                    </span>

                    <span
                        v-if="review.platform"
                        class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300"
                    >
                        {{ platformLabel(review.platform) }}
                    </span>

                    <span
                        v-if="review.recommended"
                        class="rounded-xl bg-emerald-500/10 px-3 py-1 text-sm font-bold text-emerald-300"
                    >
                        Recommended
                    </span>

                    <span
                        v-if="review.not_recommended"
                        class="rounded-xl bg-red-500/10 px-3 py-1 text-sm font-bold text-red-300"
                    >
                        Not Recommended
                    </span>
                </div>

                <p class="mt-4 text-sm font-bold text-indigo-300">
                    {{ review.game_title || 'Unknown game' }}
                </p>

                <h3 class="mt-1 text-2xl font-black text-white">
                    {{ review.title || 'Untitled review' }}
                </h3>

                <a
                    v-if="review.screenshot_url"
                    :href="review.screenshot_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950"
                >
                    <img
                        :src="review.screenshot_url"
                        :alt="review.title || review.game_title"
                        class="max-h-96 w-full object-cover"
                    >
                </a>

                <p class="mt-4 whitespace-pre-line text-zinc-300">
                    {{ truncatedBody(review.body) }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <template v-if="review.can_vote">
                        <button
                            type="button"
                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                            :class="
                                review.user_vote === 1
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-300'
                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                            "
                            @click="$emit('toggle-vote', review, 1)"
                        >
                            +1
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border px-3 py-1 text-sm font-bold transition"
                            :class="
                                review.user_vote === -1
                                    ? 'border-red-500 bg-red-500/10 text-red-300'
                                    : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                            "
                            @click="$emit('toggle-vote', review, -1)"
                        >
                            -1
                        </button>
                    </template>

                    <span class="text-sm font-bold text-zinc-400">
                        Score: {{ review.votes_score ?? 0 }}
                    </span>

                    <button
                        v-if="review.is_owner"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border px-3 py-1 text-sm font-bold transition"
                        :class="
                            review.is_featured_on_profile
                                ? 'border-indigo-500/40 bg-indigo-500/10 text-indigo-300'
                                : 'border-zinc-700 bg-zinc-950 text-zinc-300 hover:text-white'
                        "
                        @click="$emit('toggle-featured', review)"
                    >
                        <Star class="h-4 w-4" />

                        {{
                            review.is_featured_on_profile
                                ? 'Featured on Profile'
                                : 'Feature on Profile'
                        }}
                    </button>

                    <button
                        v-if="!review.is_owner"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold text-zinc-300 transition hover:border-red-500/60 hover:text-red-300"
                        @click="$emit('report-review', review)"
                    >
                        <Flag class="h-4 w-4" />
                        Report
                    </button>
                </div>

                <button
                    v-if="shouldTruncate(review.body)"
                    type="button"
                    class="mt-4 text-sm font-bold text-white underline underline-offset-4 transition hover:text-zinc-300"
                    @click="$emit('read-more', review)"
                >
                    Read more
                </button>
            </div>
        </div>
    </article>
</template>