<script setup>
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowDownToLine,
    ArrowLeft,
    Check,
    Clock3,
    Gamepad2,
    Heart,
    HelpCircle,
    Play,
    RotateCcw,
    Share2,
    Sparkles,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';
import { track } from '@/lib/analytics';

const props = defineProps({
    user: { type: Object, required: true },
    backlogRecommendations: { type: Array, default: () => [] },
    steamRecommendations: { type: Array, default: () => [] },
    friendsRanking: { type: Array, default: () => [] },
    globalRanking: { type: Array, default: () => [] },
});

const moods = [
    { id: 'short', label: 'Short and focused', icon: Clock3 },
    { id: 'immersive', label: 'Something immersive', icon: Sparkles },
    { id: 'chill', label: 'Chill', icon: Heart },
    { id: 'friends', label: 'With friends', icon: Users },
    { id: 'surprise', label: 'Surprise me', icon: Gamepad2 },
];

const screen = ref('result');
const mood = ref(null);
const swipeIndex = ref(0);
const tuning = ref({});
const resultOffset = ref(0);
const selectedGame = ref(null);
const expandedReason = ref(null);
const shared = ref(false);
const picking = ref(false);
const failedImages = ref(new Set());
const touchStart = ref(null);

const rawPool = computed(() => {
    const seen = new Set();

    return [
        ...props.backlogRecommendations,
        ...props.steamRecommendations,
        ...props.friendsRanking,
        ...props.globalRanking,
    ].filter((item) => {
        const id = item.game?.id ?? item.game?.title;

        if (!id || seen.has(id)) {
            return false;
        }

        seen.add(id);

        return true;
    });
});

const moodBonus = (item) => {
    const minutes = Number(item.game?.average_playtime_minutes ?? 0);

    if (mood.value === 'short') {
        return minutes > 0 ? Math.max(0, 30 - minutes / 120) : 0;
    }

    if (mood.value === 'immersive') {
        return Math.min(30, minutes / 300);
    }

    if (mood.value === 'friends') {
        return Number(item.friend_recommendations ?? 0) * 15;
    }

    if (mood.value === 'chill') {
        return Number(item.average_rating ?? 0) * 1.5;
    }

    if (mood.value === 'surprise') {
        return (Number(item.game?.id ?? 0) % 17) - 8;
    }

    return 0;
};

const rankedPool = computed(() =>
    rawPool.value
        .map((item) => ({
            ...item,
            tunedScore:
                Number(item.score ?? 0) +
                moodBonus(item) +
                Number(tuning.value[item.game.id] ?? 0),
        }))
        .sort((a, b) => b.tunedScore - a.tunedScore),
);

const nextThree = computed(() => {
    if (!rankedPool.value.length) {
        return [];
    }

    return Array.from(
        { length: Math.min(3, rankedPool.value.length) },
        (_, index) =>
            rankedPool.value[
                (resultOffset.value + index) % rankedPool.value.length
            ],
    );
});

const swipeCandidates = computed(() => rawPool.value.slice(0, 10));
const swipeGame = computed(() => swipeCandidates.value[swipeIndex.value]);

const imageUrl = (item, portrait = false) => {
    if (failedImages.value.has(item.game.id)) {
        return item.game.image_fallback_url;
    }

    return portrait
        ? item.game.cover_image_url || item.game.header_image_url
        : item.game.header_image_url;
};

const imageFailed = (item) => {
    failedImages.value = new Set([...failedImages.value, item.game.id]);
};

const chooseMood = (id) => {
    mood.value = id;
    track('recommendation_mood_selected', {
        mood: id,
        funnel: 'next_three_games',
    });
};

const showInstantPicks = () => {
    screen.value = 'result';
    track('recommendation_path_selected', {
        path: 'instant',
        mood: mood.value,
    });
    track('recommendation_result_viewed', {
        path: 'instant',
        mood: mood.value,
        result_count: nextThree.value.length,
    });
};

const tunePicks = () => {
    screen.value = 'swipe';
    swipeIndex.value = 0;
    track('recommendation_path_selected', { path: 'tune', mood: mood.value });
};

const respondToSwipe = (action) => {
    const item = swipeGame.value;

    if (!item) {
        return;
    }

    const changes = { ...tuning.value };

    if (action === 'interested') {
        changes[item.game.id] = 35;
    }

    if (action === 'not_now') {
        changes[item.game.id] = -1000;
    }

    if (action === 'similar') {
        changes[item.game.id] = 45;
        const genres = new Set(item.game.genres ?? []);
        rawPool.value.forEach((candidate) => {
            if (
                (candidate.game.genres ?? []).some((genre) => genres.has(genre))
            ) {
                changes[candidate.game.id] = Math.max(
                    changes[candidate.game.id] ?? 0,
                    22,
                );
            }
        });
    }

    tuning.value = changes;
    track('recommendation_swiped', { action, game_id: item.game.id });

    if (swipeIndex.value + 1 >= swipeCandidates.value.length) {
        screen.value = 'result';
        track('recommendation_tuning_completed', {
            swipes: swipeIndex.value + 1,
        });
        track('recommendation_result_viewed', {
            path: 'tuned',
            mood: mood.value,
            result_count: nextThree.value.length,
        });
    } else {
        swipeIndex.value += 1;
    }
};

const skipSwipes = () => {
    screen.value = 'result';
    track('recommendation_tuning_skipped', {
        completed_swipes: swipeIndex.value,
    });
    track('recommendation_result_viewed', {
        path: 'tuning_skipped',
        mood: mood.value,
        result_count: nextThree.value.length,
    });
};

const onTouchStart = (event) => {
    const touch = event.touches[0];
    touchStart.value = { x: touch.clientX, y: touch.clientY };
};

const onTouchEnd = (event) => {
    if (!touchStart.value) {
        return;
    }

    const touch = event.changedTouches[0];
    const dx = touch.clientX - touchStart.value.x;
    const dy = touch.clientY - touchStart.value.y;
    touchStart.value = null;

    if (dy < -60 && Math.abs(dy) > Math.abs(dx)) {
        respondToSwipe('similar');
    } else if (dx > 60) {
        respondToSwipe('interested');
    } else if (dx < -60) {
        respondToSwipe('not_now');
    }
};

const pickGame = (item) => {
    picking.value = true;
    router.post(
        `/games/${item.library_game_id}/meta`,
        { status: 'Playing' },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedGame.value = item;
                screen.value = 'picked';
                track('recommendation_game_picked', {
                    game_id: item.game.id,
                    mood: mood.value,
                });
            },
            onFinish: () => (picking.value = false),
        },
    );
};

const differentGames = () => {
    resultOffset.value =
        (resultOffset.value + 3) % Math.max(1, rankedPool.value.length);
    expandedReason.value = null;
    track('recommendation_results_refreshed', { offset: resultOffset.value });
};

const shareResults = async () => {
    const text = `My next three games on Curator.gg:\n${nextThree.value
        .map((item, index) => `${index + 1}. ${item.game.title}`)
        .join('\n')}`;

    try {
        if (navigator.share) {
            await navigator.share({
                title: 'My next three games',
                text,
                url: location.origin,
            });
        } else {
            await navigator.clipboard.writeText(`${text}\n${location.origin}`);
        }

        shared.value = true;
        track('recommendation_result_shared', { funnel: 'next_three_games' });
        window.setTimeout(() => (shared.value = false), 2000);
    } catch (error) {
        if (error?.name !== 'AbortError') {
            track('recommendation_share_failed');
        }
    }
};

onMounted(() => {
    const onboarding = new URLSearchParams(window.location.search).get(
        'onboarding',
    );

    if (onboarding === 'connected') {
        track('steam_connection_completed', { funnel: 'next_three_games' });
    }

    if (['connected', 'start'].includes(onboarding)) {
        screen.value = 'mood';
    } else {
        track('recommendation_result_viewed', {
            path: 'returning',
            result_count: nextThree.value.length,
        });
    }

    track('recommendations_opened', { onboarding: Boolean(onboarding) });
});
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />
        <div class="flex min-w-0 flex-1 flex-col">
            <Topbar :user="user" />
            <main class="flex flex-1 items-start justify-center p-4 sm:p-8">
                <section
                    v-if="screen === 'mood'"
                    class="w-full max-w-3xl py-8 sm:py-16"
                >
                    <p
                        class="text-sm font-bold tracking-widest text-indigo-400 uppercase"
                    >
                        Quick context
                    </p>
                    <h1 class="mt-3 text-4xl font-black sm:text-5xl">
                        What are you in the mood for?
                    </h1>
                    <p class="mt-3 text-zinc-400">
                        One tap helps us shape tonight's shortlist.
                    </p>
                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <button
                            v-for="option in moods"
                            :key="option.id"
                            type="button"
                            class="flex items-center gap-4 rounded-2xl border p-5 text-left font-bold transition"
                            :class="
                                mood === option.id
                                    ? 'border-indigo-400 bg-indigo-500/10 text-white'
                                    : 'border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-600'
                            "
                            @click="chooseMood(option.id)"
                        >
                            <component
                                :is="option.icon"
                                class="h-5 w-5 text-indigo-400"
                            />
                            {{ option.label }}
                            <Check
                                v-if="mood === option.id"
                                class="ml-auto h-5 w-5 text-indigo-400"
                            />
                        </button>
                    </div>
                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            :disabled="!mood"
                            class="rounded-xl bg-white px-5 py-4 text-sm font-black text-black disabled:cursor-not-allowed disabled:opacity-40"
                            @click="showInstantPicks"
                        >
                            Instant picks <span aria-hidden="true">→</span>
                        </button>
                        <button
                            type="button"
                            :disabled="!mood"
                            class="rounded-xl border border-zinc-700 px-5 py-4 text-sm font-black disabled:cursor-not-allowed disabled:opacity-40"
                            @click="tunePicks"
                        >
                            Tune my picks · 10 swipes
                        </button>
                    </div>
                </section>

                <section
                    v-else-if="screen === 'swipe'"
                    class="w-full max-w-xl py-4 sm:py-10"
                >
                    <div class="flex items-center justify-between">
                        <button
                            type="button"
                            class="text-sm font-bold text-zinc-400 hover:text-white"
                            @click="screen = 'mood'"
                        >
                            <ArrowLeft class="mr-1 inline h-4 w-4" /> Back
                        </button>
                        <span class="text-sm font-bold text-zinc-500"
                            >{{ swipeIndex + 1 }} /
                            {{ swipeCandidates.length }}</span
                        >
                        <button
                            type="button"
                            class="text-sm font-bold text-indigo-300 hover:text-indigo-200"
                            @click="skipSwipes"
                        >
                            Skip — pick instantly
                        </button>
                    </div>
                    <div
                        v-if="swipeGame"
                        class="mt-6 overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl"
                        @touchstart="onTouchStart"
                        @touchend="onTouchEnd"
                    >
                        <img
                            :src="imageUrl(swipeGame)"
                            :alt="swipeGame.game.title"
                            class="aspect-video w-full object-cover"
                            @error="imageFailed(swipeGame)"
                        />
                        <div class="p-6">
                            <h1 class="text-3xl font-black">
                                {{ swipeGame.game.title }}
                            </h1>
                            <p class="mt-3 text-zinc-400">
                                {{ swipeGame.reason }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <button
                            type="button"
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 text-sm font-bold text-zinc-300 hover:border-rose-500/60"
                            @click="respondToSwipe('not_now')"
                        >
                            <X class="mx-auto mb-2 h-6 w-6 text-rose-400" />Not
                            now
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 text-sm font-bold text-zinc-300 hover:border-indigo-500/60"
                            @click="respondToSwipe('similar')"
                        >
                            <ArrowDownToLine
                                class="mx-auto mb-2 h-6 w-6 rotate-180 text-indigo-400"
                            />More similar
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 text-sm font-bold text-zinc-300 hover:border-emerald-500/60"
                            @click="respondToSwipe('interested')"
                        >
                            <Heart
                                class="mx-auto mb-2 h-6 w-6 text-emerald-400"
                            />Interested
                        </button>
                    </div>
                    <p class="mt-4 text-center text-xs text-zinc-600">
                        Swipe left, right, or up on mobile.
                    </p>
                </section>

                <section
                    v-else-if="screen === 'picked' && selectedGame"
                    class="w-full max-w-3xl py-12 text-center sm:py-20"
                >
                    <div
                        class="mx-auto h-24 w-24 overflow-hidden rounded-2xl border border-zinc-700 shadow-xl"
                    >
                        <img
                            :src="imageUrl(selectedGame, true)"
                            :alt="selectedGame.game.title"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <p
                        class="mt-8 text-sm font-bold tracking-widest text-emerald-400 uppercase"
                    >
                        Decision made
                    </p>
                    <h1 class="mt-3 text-4xl font-black sm:text-6xl">
                        Tonight you're playing<br />{{
                            selectedGame.game.title
                        }}.
                    </h1>
                    <p class="mx-auto mt-5 max-w-xl text-lg text-zinc-400">
                        No more browsing. Your game is marked as Playing.
                    </p>
                    <div
                        class="mt-8 flex flex-col justify-center gap-3 sm:flex-row"
                    >
                        <a
                            :href="
                                selectedGame.game.steam_app_id
                                    ? `steam://run/${selectedGame.game.steam_app_id}`
                                    : selectedGame.game.public_url
                            "
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-4 font-black text-black"
                            ><Play class="h-5 w-5" /> Start on Steam</a
                        >
                        <Link
                            href="/challenges"
                            class="inline-flex items-center justify-center rounded-xl border border-zinc-700 px-6 py-4 font-black hover:bg-zinc-900"
                            >Add to current challenge</Link
                        >
                    </div>
                </section>

                <section v-else class="w-full max-w-7xl py-2">
                    <div
                        class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-bold tracking-widest text-indigo-400 uppercase"
                            >
                                Your personal shortlist
                            </p>
                            <h1 class="mt-1 text-4xl font-black">
                                Your next three games
                            </h1>
                            <p class="mt-2 text-zinc-400">
                                Three strong matches. Pick one and start
                                playing.
                            </p>
                        </div>
                        <button
                            v-if="nextThree.length"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold hover:bg-zinc-900"
                            @click="shareResults"
                        >
                            <Check
                                v-if="shared"
                                class="h-4 w-4 text-emerald-400"
                            /><Share2 v-else class="h-4 w-4" />{{
                                shared ? 'Copied' : 'Share my three'
                            }}
                        </button>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">
                        <article
                            v-for="(item, index) in nextThree"
                            :key="item.game.id"
                            class="flex overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                        >
                            <div class="relative w-32 shrink-0 sm:w-40">
                                <img
                                    :src="imageUrl(item, true)"
                                    :alt="item.game.title"
                                    class="absolute inset-0 h-full w-full object-cover"
                                    @error="imageFailed(item)"
                                />
                                <span
                                    class="absolute top-3 left-3 rounded-full bg-black/80 px-3 py-1 text-sm font-black"
                                    >#{{ index + 1 }}</span
                                >
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col p-5">
                                <p
                                    class="text-[10px] font-bold tracking-widest text-indigo-400 uppercase"
                                >
                                    {{
                                        index === 0
                                            ? 'Best match'
                                            : 'Strong match'
                                    }}
                                </p>
                                <h2 class="mt-2 text-xl font-black">
                                    {{ item.game.title }}
                                </h2>
                                <p class="mt-3 text-sm leading-6 text-zinc-400">
                                    {{ item.reason }}
                                </p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span
                                        v-for="signal in item.signals"
                                        :key="signal"
                                        class="rounded-full bg-zinc-800 px-2.5 py-1 text-[11px] font-semibold text-zinc-300"
                                        >{{ signal }}</span
                                    >
                                </div>
                                <div
                                    v-if="expandedReason === item.game.id"
                                    class="mt-4 rounded-xl border border-zinc-800 bg-zinc-950 p-3 text-xs leading-5 text-zinc-400"
                                >
                                    Based on your Steam playtime, recent
                                    activity, ratings, completion history, and
                                    community quality signals.
                                </div>
                                <div class="mt-auto grid gap-2 pt-5">
                                    <button
                                        type="button"
                                        :disabled="picking"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-black disabled:opacity-50"
                                        @click="pickGame(item)"
                                    >
                                        <Play class="h-4 w-4" /> Pick this game
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold text-zinc-400 hover:text-white"
                                        @click="
                                            expandedReason =
                                                expandedReason === item.game.id
                                                    ? null
                                                    : item.game.id
                                        "
                                    >
                                        <HelpCircle class="h-4 w-4" /> Why this?
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-5 py-3 text-sm font-bold hover:bg-zinc-900"
                            @click="differentGames"
                        >
                            <RotateCcw class="h-4 w-4" /> Give me three
                            different games
                        </button>
                        <button
                            type="button"
                            class="px-5 py-3 text-sm font-bold text-zinc-500 hover:text-white"
                            @click="screen = 'mood'"
                        >
                            Change mood
                        </button>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>
