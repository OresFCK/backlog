<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Trophy, Calendar, ThumbsUp, X } from 'lucide-vue-next'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    game: {
        type: Object,
        required: true,
    },

    statuses: {
        type: Array,
        default: () => [],
    },
})

const note = ref(props.game.note ?? '')
const rating = ref(props.game.rating ? String(props.game.rating) : '')
const recommended = ref(props.game.recommended ?? false)
const notRecommended = ref(props.game.not_recommended ?? false)
const status = ref('')

const isReviewModalOpen = ref(false)

const publicReviewTitle = ref('')
const publicReviewBody = ref('')
const publicReviewRating = ref(
    props.game.rating ? String(props.game.rating) : ''
)
const publicReviewRecommended = ref(props.game.recommended ?? false)
const publicReviewNotRecommended = ref(props.game.not_recommended ?? false)

watch(
    () => props.statuses,
    (statuses) => {
        if (!statuses.length) {
            return
        }

        status.value = props.game.has_meta
            ? props.game.status
            : statuses[0].name
    },
    {
        immediate: true,
    }
)

const saveMeta = () => {
    router.post(
        `/games/${props.game.id}/meta`,
        {
            note: note.value,
            rating: rating.value ? Number(rating.value) : null,
            recommended: recommended.value,
            not_recommended: notRecommended.value,
            status: status.value,
        },
        {
            preserveScroll: true,
        }
    )
}

const toggleRecommended = () => {
    recommended.value = !recommended.value

    if (recommended.value) {
        notRecommended.value = false
    }

    saveMeta()
}

const toggleNotRecommended = () => {
    notRecommended.value = !notRecommended.value

    if (notRecommended.value) {
        recommended.value = false
    }

    saveMeta()
}

const togglePublicRecommended = () => {
    publicReviewRecommended.value = !publicReviewRecommended.value

    if (publicReviewRecommended.value) {
        publicReviewNotRecommended.value = false
    }
}

const togglePublicNotRecommended = () => {
    publicReviewNotRecommended.value = !publicReviewNotRecommended.value

    if (publicReviewNotRecommended.value) {
        publicReviewRecommended.value = false
    }
}

const openReviewModal = () => {
    publicReviewTitle.value = props.game.title
    publicReviewBody.value = note.value ?? ''
    publicReviewRating.value = rating.value
    publicReviewRecommended.value = recommended.value
    publicReviewNotRecommended.value = notRecommended.value

    isReviewModalOpen.value = true
}

const closeReviewModal = () => {
    isReviewModalOpen.value = false
}

const submitPublicReview = () => {
    router.post(
        '/reviews/public',
        {
            game_id: props.game.id,
            title: publicReviewTitle.value,
            body: publicReviewBody.value,
            rating: publicReviewRating.value
                ? Number(publicReviewRating.value)
                : null,
            recommended: publicReviewRecommended.value,
            not_recommended: publicReviewNotRecommended.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                closeReviewModal()
            },
        }
    )
}

const blockInvalidKeys = (event) => {
    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
    ]

    if (allowedKeys.includes(event.key)) {
        return
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault()
    }
}

const normalizeRating = (value) => {
    let normalizedValue = value.replace(/\D/g, '').slice(0, 2)

    if (normalizedValue === '') {
        return ''
    }

    if (Number(normalizedValue) > 10) {
        return '10'
    }

    if (Number(normalizedValue) < 1) {
        return '1'
    }

    return normalizedValue
}

const handleRatingInput = (event) => {
    rating.value = normalizeRating(event.target.value)
}

const handlePublicRatingInput = (event) => {
    publicReviewRating.value = normalizeRating(event.target.value)
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
                    <div
                        class="relative min-h-[360px] bg-cover bg-center"
                        :style="{
                            backgroundImage: game.header_image
                                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.55)), url(${game.header_image})`
                                : null,
                        }"
                    >
                        <div class="flex min-h-[360px] flex-col justify-between p-10">
                            <div>
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <span
                                        v-for="genre in game.genres"
                                        :key="genre"
                                        class="rounded-full border border-zinc-700 bg-zinc-950/70 px-3 py-1 text-xs font-semibold text-zinc-300"
                                    >
                                        {{ genre }}
                                    </span>
                                </div>

                                <h1 class="max-w-3xl text-5xl font-black text-white">
                                    {{ game.title }}
                                </h1>

                                <p class="mt-4 max-w-2xl text-zinc-300">
                                    {{ game.description || 'No description available.' }}
                                </p>
                            </div>

                            <div class="mt-8">
                                <button
                                    type="button"
                                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="openReviewModal"
                                >
                                    Create public review
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-8 p-10 lg:grid-cols-[1fr_360px]">
                        <section class="space-y-8">
                            <div class="space-y-5">
                                <div class="grid gap-5 lg:grid-cols-[1fr_260px]">
                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6">
                                        <h2 class="text-2xl font-bold text-white">
                                            Your notes
                                        </h2>

                                        <textarea
                                            v-model="note"
                                            placeholder="Write your thoughts about this game..."
                                            class="mt-5 min-h-40 w-full resize-none rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-sm text-zinc-200 outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                                            @blur="saveMeta"
                                        />
                                    </div>

                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6 text-center">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                            Your rating
                                        </p>

                                        <div class="mx-auto mt-6 flex h-36 w-36 items-center justify-center rounded-full border-[10px] border-zinc-200">
                                            <div class="flex items-center gap-1">
                                                <input
                                                    :value="rating"
                                                    type="text"
                                                    inputmode="numeric"
                                                    maxlength="2"
                                                    placeholder="—"
                                                    class="w-14 bg-transparent text-center text-4xl font-black text-white outline-none"
                                                    @keydown="blockInvalidKeys"
                                                    @input="handleRatingInput"
                                                    @blur="saveMeta"
                                                />

                                                <span class="text-2xl font-bold text-white">
                                                    /10
                                                </span>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="mt-6 flex w-full items-center justify-center gap-2 text-sm font-semibold transition"
                                            :class="
                                                recommended
                                                    ? 'text-emerald-300'
                                                    : 'text-zinc-300 hover:text-white'
                                            "
                                            @click="toggleRecommended"
                                        >
                                            <ThumbsUp class="h-4 w-4" />

                                            {{ recommended ? 'Recommended' : 'Recommend' }}
                                        </button>

                                        <button
                                            type="button"
                                            class="mt-3 flex w-full items-center justify-center gap-2 text-sm font-semibold transition"
                                            :class="
                                                notRecommended
                                                    ? 'text-red-300'
                                                    : 'text-zinc-300 hover:text-white'
                                            "
                                            @click="toggleNotRecommended"
                                        >
                                            <X class="h-4 w-4" />

                                            {{
                                                notRecommended
                                                    ? 'Not Recommended'
                                                    : 'Do Not Recommend'
                                            }}
                                        </button>

                                        <select
                                            v-model="status"
                                            class="mt-5 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm font-semibold text-white outline-none focus:border-zinc-600"
                                            @change="saveMeta"
                                        >
                                            <option
                                                v-for="item in statuses"
                                                :key="item.id"
                                                :value="item.name"
                                            >
                                                {{ item.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-3">
                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                            Playtime
                                        </p>

                                        <p class="mt-3 text-3xl font-black text-white">
                                            {{ game.playtime_hours ?? '—' }}h
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            From Steam
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <Trophy class="h-6 w-6 text-zinc-400" />

                                        <p class="mt-3 text-3xl font-black text-white">
                                            {{ game.achievements_unlocked ?? '—' }}/{{ game.achievements_total ?? '—' }}
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            Achievements
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                        <Calendar class="h-6 w-6 text-zinc-400" />

                                        <p class="mt-3 text-2xl font-black text-white">
                                            {{ game.release_date || '—' }}
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            Release
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="game.screenshots.length">
                                <h2 class="mb-4 text-2xl font-bold text-white">
                                    Gallery
                                </h2>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <img
                                        v-for="screenshot in game.screenshots"
                                        :key="screenshot"
                                        :src="screenshot"
                                        class="h-52 w-full rounded-2xl object-cover"
                                    />
                                </div>
                            </div>

                            <div>
                                <h2 class="text-2xl font-bold text-white">
                                    About
                                </h2>

                                <p class="mt-3 whitespace-pre-line text-zinc-400">
                                    {{
                                        game.about ||
                                        game.description ||
                                        'No details available.'
                                    }}
                                </p>
                            </div>
                        </section>

                        <aside class="space-y-4">
                            <img
                                v-if="game.cover_url"
                                :src="game.cover_url"
                                class="w-full rounded-2xl object-cover"
                            />

                            <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                                <h3 class="font-bold text-white">
                                    Information
                                </h3>

                                <dl class="mt-4 space-y-4 text-sm">
                                    <div>
                                        <dt class="text-zinc-500">
                                            Developer
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{ game.developers?.join(', ') || 'Unknown' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Publisher
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{
                                                game.publishers?.join(', ') ||
                                                game.publisher ||
                                                'Unknown'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Release date
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{ game.release_date || 'Unknown' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Platform
                                        </dt>

                                        <dd class="text-zinc-200">
                                            <span v-if="game.platforms?.windows">
                                                Windows
                                            </span>

                                            <span v-if="game.platforms?.mac">
                                                , Mac
                                            </span>

                                            <span v-if="game.platforms?.linux">
                                                , Linux
                                            </span>

                                            <span v-if="game.is_custom">
                                                Custom
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </aside>
                    </div>
                </div>
            </main>
        </div>

        <div
            v-if="isReviewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
        >
            <div class="w-full max-w-2xl rounded-3xl border border-zinc-800 bg-zinc-950 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-white">
                            Create public review
                        </h2>

                        <p class="mt-1 text-sm text-zinc-400">
                            This review will be visible publicly.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        @click="closeReviewModal"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label class="text-sm font-semibold text-zinc-300">
                            Review title
                        </label>

                        <input
                            v-model="publicReviewTitle"
                            type="text"
                            maxlength="120"
                            class="mt-2 w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                            placeholder="Short title"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-zinc-300">
                            Review
                        </label>

                        <textarea
                            v-model="publicReviewBody"
                            class="mt-2 min-h-48 w-full resize-none rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-sm text-zinc-200 outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                            placeholder="Write your public review..."
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-[160px_1fr]">
                        <div>
                            <label class="text-sm font-semibold text-zinc-300">
                                Rating
                            </label>

                            <div class="mt-2 flex items-center rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-3">
                                <input
                                    :value="publicReviewRating"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="2"
                                    placeholder="—"
                                    class="w-10 bg-transparent text-sm font-bold text-white outline-none"
                                    @keydown="blockInvalidKeys"
                                    @input="handlePublicRatingInput"
                                />

                                <span class="text-sm font-semibold text-zinc-400">
                                    /10
                                </span>
                            </div>
                        </div>

                        <div class="flex items-end gap-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-4 py-3 text-sm font-semibold transition"
                                :class="
                                    publicReviewRecommended
                                        ? 'bg-emerald-500/10 text-emerald-300'
                                        : 'bg-zinc-900 text-zinc-300 hover:text-white'
                                "
                                @click="togglePublicRecommended"
                            >
                                <ThumbsUp class="h-4 w-4" />

                                {{
                                    publicReviewRecommended
                                        ? 'Recommended'
                                        : 'Recommend'
                                }}
                            </button>

                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 px-4 py-3 text-sm font-semibold transition"
                                :class="
                                    publicReviewNotRecommended
                                        ? 'bg-red-500/10 text-red-300'
                                        : 'bg-zinc-900 text-zinc-300 hover:text-white'
                                "
                                @click="togglePublicNotRecommended"
                            >
                                <X class="h-4 w-4" />

                                {{
                                    publicReviewNotRecommended
                                        ? 'Not Recommended'
                                        : 'Do Not Recommend'
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-zinc-800 px-5 py-3 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white"
                        @click="closeReviewModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                        @click="submitPublicReview"
                    >
                        Publish review
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>