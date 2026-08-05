<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Check, Share2 } from 'lucide-vue-next';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';

import RecommendationsSection from '@/components/recommendations/RecommendationsSection.vue';
import RecommendationCarousel from '@/components/recommendations/RecommendationCarousel.vue';
import { track } from '@/lib/analytics';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    backlogRecommendations: {
        type: Array,
        default: () => [],
    },

    steamRecommendations: {
        type: Array,
        default: () => [],
    },

    friendsRanking: {
        type: Array,
        default: () => [],
    },

    globalRanking: {
        type: Array,
        default: () => [],
    },
});

const topRecommendation = computed(() => {
    return [
        ...props.backlogRecommendations,
        ...props.steamRecommendations,
        ...props.friendsRanking,
        ...props.globalRanking,
    ].sort((a, b) => Number(b.score ?? 0) - Number(a.score ?? 0))[0];
});

const nextThree = computed(() => {
    const seen = new Set();

    return [
        ...props.backlogRecommendations,
        ...props.steamRecommendations,
        ...props.friendsRanking,
        ...props.globalRanking,
    ]
        .sort((a, b) => Number(b.score ?? 0) - Number(a.score ?? 0))
        .filter((item) => {
            const id = item.game?.id ?? item.game?.title;
            if (!id || seen.has(id)) return false;
            seen.add(id);
            return true;
        })
        .slice(0, 3);
});

const shared = ref(false);
const failedImages = ref(new Set());
const hiddenImages = ref(new Set());

const recommendationImage = (item) => {
    const gameId = item.game.id;

    if (hiddenImages.value.has(gameId)) return null;

    return failedImages.value.has(gameId)
        ? item.game.image_fallback_url
        : item.game.cover_image_url || item.game.header_image_url;
};

const useFallbackImage = (item) => {
    if (failedImages.value.has(item.game.id) || !item.game.image_fallback_url) {
        hiddenImages.value = new Set([...hiddenImages.value, item.game.id]);
        return;
    }
    failedImages.value = new Set([...failedImages.value, item.game.id]);
};

onMounted(() => {
    const connected =
        new URLSearchParams(window.location.search).get('onboarding') ===
        'connected';

    if (connected) {
        track('steam_connection_completed', { funnel: 'next_three_games' });
    }

    track('recommendation_result_viewed', {
        funnel: 'next_three_games',
        result_count: nextThree.value.length,
        onboarding: connected,
    });
});

const shareResults = async () => {
    const titles = nextThree.value
        .map((item, index) => `${index + 1}. ${item.game.title}`)
        .join('\n');
    const text = `My next three games on Curator.gg:\n${titles}`;
    const method = navigator.share ? 'native' : 'clipboard';

    try {
        if (navigator.share) {
            await navigator.share({
                title: 'My next three games',
                text,
                url: window.location.origin,
            });
        } else {
            await navigator.clipboard.writeText(
                `${text}\n${window.location.origin}`,
            );
        }

        shared.value = true;
        track('recommendation_result_shared', {
            funnel: 'next_three_games',
            method,
        });
        window.setTimeout(() => (shared.value = false), 2500);
    } catch (error) {
        if (error?.name !== 'AbortError') {
            track('recommendation_share_failed', {
                funnel: 'next_three_games',
            });
        }
    }
};
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-10 p-8">
                <section>
                    <div
                        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-bold text-indigo-400">
                                YOUR PERSONAL SHORTLIST
                            </p>
                            <h1 class="text-4xl font-black text-white">
                                Your next three games
                            </h1>

                            <p class="mt-2 text-zinc-400">
                                Pick any one. You cannot go wrong.
                            </p>
                        </div>
                        <button
                            v-if="nextThree.length"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-zinc-800"
                            @click="shareResults"
                        >
                            <Check
                                v-if="shared"
                                class="h-4 w-4 text-emerald-400"
                            />
                            <Share2 v-else class="h-4 w-4" />
                            {{ shared ? 'Copied' : 'Share my three' }}
                        </button>
                    </div>

                    <div
                        v-if="nextThree.length"
                        class="grid gap-4 xl:grid-cols-3"
                    >
                        <Link
                            v-for="(item, index) in nextThree"
                            :key="item.game.id ?? item.game.title"
                            :href="item.game.public_url"
                            class="group relative flex min-h-56 overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition duration-200 hover:-translate-y-1 hover:border-indigo-500/60 hover:shadow-xl hover:shadow-indigo-950/20"
                        >
                            <div class="relative w-36 shrink-0 sm:w-40">
                                <img
                                    v-if="recommendationImage(item)"
                                    :src="recommendationImage(item)"
                                    :alt="item.game.title"
                                    class="absolute inset-0 h-full w-full bg-zinc-950 object-cover object-center"
                                    loading="eager"
                                    @error="useFallbackImage(item)"
                                />
                                <div
                                    v-else
                                    class="absolute inset-0 bg-gradient-to-br from-zinc-800 via-zinc-900 to-indigo-950/60"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-zinc-900/60"
                                />
                            </div>
                            <span
                                class="absolute top-3 left-3 flex h-9 min-w-9 items-center justify-center rounded-full border border-white/15 bg-black/80 px-2 text-sm font-black text-white shadow-lg backdrop-blur"
                            >
                                {{ index + 1 }}
                            </span>
                            <div
                                class="flex min-w-0 flex-1 flex-col p-5 sm:p-6"
                            >
                                <p
                                    class="text-[10px] font-bold tracking-[0.16em] text-indigo-400 uppercase"
                                >
                                    {{
                                        index === 0
                                            ? 'Best match'
                                            : 'Personal pick'
                                    }}
                                </p>
                                <h2
                                    class="mt-2 text-xl leading-tight font-black text-white"
                                >
                                    {{ item.game.title }}
                                </h2>
                                <p
                                    class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400"
                                >
                                    {{ item.reason }}
                                </p>
                                <div
                                    class="mt-auto pt-5 text-xs font-bold text-zinc-300 transition group-hover:text-white"
                                >
                                    View game <span aria-hidden="true">→</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

                <Link
                    v-if="topRecommendation"
                    :href="topRecommendation.game.public_url"
                    class="relative block overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 p-8 transition hover:border-zinc-600 focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:outline-none"
                >
                    <img
                        :src="topRecommendation.game.header_image_url"
                        class="absolute inset-0 h-full w-full object-cover opacity-25"
                    />

                    <div
                        class="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black/50"
                    />

                    <div class="relative z-10 max-w-3xl">
                        <p class="text-sm font-bold text-indigo-300">
                            Top recommendation
                        </p>

                        <h2 class="mt-3 text-5xl font-black text-white">
                            {{ topRecommendation.game.title }}
                        </h2>

                        <p class="mt-4 text-zinc-300">
                            {{ topRecommendation.reason }}
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span
                                class="rounded-full bg-indigo-500/10 px-4 py-2 text-sm font-bold text-indigo-300"
                            >
                                Score {{ topRecommendation.score }}
                            </span>

                            <span
                                class="rounded-full bg-yellow-500/10 px-4 py-2 text-sm font-bold text-yellow-300"
                            >
                                ★ {{ topRecommendation.average_rating }}/10
                            </span>
                        </div>
                    </div>
                </Link>

                <RecommendationsSection
                    :friends-ranking="friendsRanking"
                    :global-ranking="globalRanking"
                />

                <RecommendationCarousel
                    title="From your backlog"
                    subtitle="Games you already own and should play next."
                    :items="backlogRecommendations"
                />

                <RecommendationCarousel
                    title="Steam discoveries"
                    subtitle="Games outside your backlog recommended by the community."
                    :items="steamRecommendations"
                />
            </main>
        </div>
    </div>
</template>
