<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

import {
    Trophy,
    Calendar,
    Check,
    X,
    ThumbsUp,
} from 'lucide-vue-next'

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
})

const note = ref(props.game.note ?? '')

const rating = ref(
    props.game.rating
        ? String(props.game.rating)
        : ''
)

const recommended = ref(
    props.game.recommended ?? false
)

const saveMeta = () => {

    router.post(
        `/games/${props.game.id}/meta`,
        {
            note: note.value,

            rating: rating.value
                ? Number(rating.value)
                : null,

            recommended: recommended.value,
        },
        {
            preserveScroll: true,
        }
    )
}

const resetMeta = () => {

    note.value = props.game.note ?? ''

    rating.value = props.game.rating
        ? String(props.game.rating)
        : ''

    recommended.value =
        props.game.recommended ?? false
}

const blockInvalidKeys = (event) => {

    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'Tab',
    ]

    if (
        allowedKeys.includes(event.key)
    ) {
        return
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault()
    }
}

const handleRatingInput = (event) => {

    let value = event.target.value
        .replace(/\D/g, '')
        .slice(0, 2)

    if (value === '') {
        rating.value = ''
        return
    }

    if (Number(value) > 10) {
        value = '10'
    }

    if (Number(value) < 1) {
        value = '1'
    }

    rating.value = value
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div
                    class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                >
                    <div
                        class="relative min-h-[360px] bg-cover bg-center"
                        :style="{
                            backgroundImage: game.header_image
                                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.55)), url(${game.header_image})`
                                : null,
                        }"
                    >
                        <div class="p-10">
                            <div class="mb-4 flex flex-wrap gap-2">
                                <span
                                    v-for="genre in game.genres"
                                    :key="genre"
                                    class="rounded-full border border-zinc-700 bg-zinc-950/70 px-3 py-1 text-xs font-semibold text-zinc-300"
                                >
                                    {{ genre }}
                                </span>
                            </div>

                            <h1
                                class="max-w-3xl text-5xl font-black text-white"
                            >
                                {{ game.title }}
                            </h1>

                            <p
                                class="mt-4 max-w-2xl text-zinc-300"
                            >
                                {{
                                    game.description ||
                                    'No description available.'
                                }}
                            </p>

                            <div class="mt-8 flex gap-4">
                                <a
                                    v-if="game.steam_url"
                                    :href="game.steam_url"
                                    target="_blank"
                                    class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-black hover:bg-zinc-200"
                                >
                                    Open Steam page
                                </a>

                                <button
                                    class="rounded-xl border border-zinc-700 bg-zinc-950 px-6 py-3 text-sm font-bold text-white hover:bg-zinc-800"
                                >
                                    Mark as playing
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid gap-8 p-10 lg:grid-cols-[1fr_360px]"
                    >
                        <section class="space-y-8">

                            <div class="space-y-5">

                                <div
                                    class="grid gap-5 lg:grid-cols-[1fr_260px]"
                                >
                                    <div
                                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <h2
                                                class="text-2xl font-bold text-white"
                                            >
                                                Your notes
                                            </h2>

                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <button
                                                    type="button"
                                                    @click="resetMeta"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-red-500 hover:text-red-400"
                                                >
                                                    <X class="h-4 w-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    @click="saveMeta"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-emerald-500 hover:text-emerald-400"
                                                >
                                                    <Check class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>

                                        <textarea
                                            v-model="note"
                                            placeholder="Write your thoughts about this game..."
                                            class="mt-5 min-h-40 w-full resize-none rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-sm text-zinc-200 outline-none placeholder:text-zinc-500 focus:border-zinc-600"
                                        />
                                    </div>

                                    <div
                                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-6 text-center"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wider text-zinc-500"
                                        >
                                            Your rating
                                        </p>

                                        <div
                                            class="mx-auto mt-6 flex h-36 w-36 items-center justify-center rounded-full border-[10px] border-zinc-200"
                                        >
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <input
                                                    :value="rating"
                                                    type="text"
                                                    inputmode="numeric"
                                                    maxlength="2"
                                                    placeholder="—"
                                                    @keydown="blockInvalidKeys"
                                                    @input="handleRatingInput"
                                                    class="w-14 bg-transparent text-center text-4xl font-black text-white outline-none"
                                                />

                                                <span
                                                    class="text-2xl font-bold text-white"
                                                >
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
                                            @click="recommended = !recommended"
                                        >
                                            <ThumbsUp
                                                class="h-4 w-4"
                                            />

                                            {{
                                                recommended
                                                    ? 'Recommended'
                                                    : 'Recommend'
                                            }}
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="grid gap-5 md:grid-cols-3"
                                >
                                    <div
                                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wider text-zinc-500"
                                        >
                                            Playtime
                                        </p>

                                        <p
                                            class="mt-3 text-3xl font-black text-white"
                                        >
                                            {{
                                                game.playtime_hours ?? '—'
                                            }}h
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            From Steam
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                                    >
                                        <Trophy
                                            class="h-6 w-6 text-zinc-400"
                                        />

                                        <p
                                            class="mt-3 text-3xl font-black text-white"
                                        >
                                            {{
                                                game.achievements_unlocked ?? '—'
                                            }}/{{ game.achievements_total ?? '—' }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            Achievements
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                                    >
                                        <Calendar
                                            class="h-6 w-6 text-zinc-400"
                                        />

                                        <p
                                            class="mt-3 text-2xl font-black text-white"
                                        >
                                            {{
                                                game.release_date || '—'
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            Release
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="game.screenshots.length"
                            >
                                <h2
                                    class="mb-4 text-2xl font-bold text-white"
                                >
                                    Gallery
                                </h2>

                                <div
                                    class="grid gap-4 md:grid-cols-2"
                                >
                                    <img
                                        v-for="screenshot in game.screenshots"
                                        :key="screenshot"
                                        :src="screenshot"
                                        class="h-52 w-full rounded-2xl object-cover"
                                    />
                                </div>
                            </div>

                            <div>
                                <h2
                                    class="text-2xl font-bold text-white"
                                >
                                    About
                                </h2>

                                <p
                                    class="mt-3 whitespace-pre-line text-zinc-400"
                                >
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

                            <div
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                            >
                                <h3
                                    class="font-bold text-white"
                                >
                                    Information
                                </h3>

                                <dl
                                    class="mt-4 space-y-4 text-sm"
                                >
                                    <div>
                                        <dt
                                            class="text-zinc-500"
                                        >
                                            Developer
                                        </dt>

                                        <dd
                                            class="text-zinc-200"
                                        >
                                            {{
                                                game.developers?.join(', ')
                                                || 'Unknown'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-zinc-500"
                                        >
                                            Publisher
                                        </dt>

                                        <dd
                                            class="text-zinc-200"
                                        >
                                            {{
                                                game.publishers?.join(', ')
                                                || game.publisher
                                                || 'Unknown'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-zinc-500"
                                        >
                                            Release date
                                        </dt>

                                        <dd
                                            class="text-zinc-200"
                                        >
                                            {{
                                                game.release_date ||
                                                'Unknown'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-zinc-500"
                                        >
                                            Platform
                                        </dt>

                                        <dd
                                            class="text-zinc-200"
                                        >
                                            <span
                                                v-if="game.platforms?.windows"
                                            >
                                                Windows
                                            </span>

                                            <span
                                                v-if="game.platforms?.mac"
                                            >
                                                , Mac
                                            </span>

                                            <span
                                                v-if="game.platforms?.linux"
                                            >
                                                , Linux
                                            </span>

                                            <span
                                                v-if="game.is_custom"
                                            >
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
    </div>
</template>