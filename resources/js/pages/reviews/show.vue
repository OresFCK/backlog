<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Gamepad2,
    Library,
    Share2,
    Sparkles,
    Star,
    Users,
} from 'lucide-vue-next';

const props = defineProps({
    review: { type: Object, required: true },
    seo: { type: Object, required: true },
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});

const pageTitle = computed(() => props.seo.title);
const reviewExpanded = ref(false);
const shouldCollapseReview = computed(
    () =>
        String(props.review.body || '').length > 700 ||
        String(props.review.body || '').split('\n').length > 10,
);

const shareReview = async () => {
    const data = {
        title: props.seo.title,
        text: `${props.review.user?.name || 'A Curator.gg user'} reviewed ${props.review.game_title}${props.review.rating ? ` ${props.review.rating}/10` : ''}.`,
        url: props.review.share_url,
    };

    if (navigator.share) {
        try {
            await navigator.share(data);
            return;
        } catch (error) {
            if (error.name === 'AbortError') return;
        }
    }

    await navigator.clipboard.writeText(props.review.share_url);
    window.alert('Review link copied to clipboard.');
};
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-[#09090b] text-white">
        <header
            class="sticky top-0 z-40 border-b border-white/10 bg-[#09090b]/90 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-8"
            >
                <Link href="/home" class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-zinc-950"
                    >
                        <Gamepad2 class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-black tracking-tight"
                        >Curator.gg</span
                    >
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-900 sm:px-4"
                        @click="shareReview"
                    >
                        <Share2 class="h-4 w-4" />
                        <span class="hidden sm:inline">Share review</span>
                    </button>

                    <a
                        :href="auth.user ? '/dashboard' : '/auth/steam'"
                        class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                    >
                        {{ auth.user ? 'Open dashboard' : 'Join with Steam' }}
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section
                class="relative overflow-hidden px-4 py-8 sm:px-8 sm:py-14"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 mx-auto h-96 max-w-5xl bg-indigo-600/10 blur-[120px]"
                />

                <article
                    class="relative mx-auto max-w-4xl overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl shadow-black/40"
                >
                    <img
                        v-if="seo.image"
                        :src="seo.image"
                        :alt="seo.image_alt"
                        class="h-48 w-full border-b border-zinc-800 object-cover sm:h-72"
                        loading="eager"
                        fetchpriority="high"
                    />

                    <div class="p-5 sm:p-9">
                        <div
                            class="flex flex-col gap-5 border-b border-zinc-800 pb-6 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <Link
                                v-if="review.user?.profile_url"
                                :href="review.user.profile_url"
                                class="group flex items-center gap-3"
                            >
                                <img
                                    v-if="review.user.avatar"
                                    :src="review.user.avatar"
                                    :alt="review.user.name"
                                    class="h-12 w-12 rounded-xl object-cover sm:h-14 sm:w-14"
                                />
                                <div>
                                    <p
                                        class="flex items-center gap-1 font-bold group-hover:underline"
                                    >
                                        {{ review.user.name }}
                                        <ArrowUpRight class="h-4 w-4" />
                                    </p>
                                    <p class="text-sm text-zinc-500">
                                        Reviewed {{ review.created_at }}
                                    </p>
                                </div>
                            </Link>

                            <div v-else class="flex items-center gap-3">
                                <img
                                    v-if="review.user?.avatar"
                                    :src="review.user.avatar"
                                    :alt="review.user.name"
                                    class="h-12 w-12 rounded-xl object-cover sm:h-14 sm:w-14"
                                />
                                <div>
                                    <p class="font-bold">
                                        {{ review.user?.name }}
                                    </p>
                                    <p class="text-sm text-zinc-500">
                                        Reviewed {{ review.created_at }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="review.user?.stats"
                                class="grid grid-cols-3 gap-2 sm:min-w-80"
                            >
                                <div
                                    class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2"
                                >
                                    <p class="text-base font-black">
                                        {{ review.user.stats.reviews_count }}
                                    </p>
                                    <p class="text-[11px] text-zinc-500">
                                        Reviews
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2"
                                >
                                    <p class="text-base font-black">
                                        {{
                                            review.user.stats.average_rating ??
                                            '—'
                                        }}
                                    </p>
                                    <p class="text-[11px] text-zinc-500">
                                        Avg. rating
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2"
                                >
                                    <p class="text-base font-black">
                                        {{
                                            review.user.stats
                                                .recommendation_rate
                                        }}%
                                    </p>
                                    <p class="text-[11px] text-zinc-500">
                                        Recommend
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p class="mt-6 font-bold text-indigo-300">
                            {{ review.game_title }}
                        </p>
                        <h1
                            class="mt-2 text-3xl font-black tracking-tight break-words sm:text-5xl"
                        >
                            {{ review.title || 'Untitled review' }}
                        </h1>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span
                                v-if="review.rating"
                                class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-1 text-sm font-bold"
                            >
                                {{ review.rating }}/10
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
                                Not recommended
                            </span>
                        </div>

                        <div class="relative mt-7">
                            <p
                                class="text-base leading-8 break-words whitespace-pre-wrap text-zinc-300 transition-[max-height] duration-300 sm:text-lg"
                                :class="
                                    shouldCollapseReview && !reviewExpanded
                                        ? 'max-h-64 overflow-hidden sm:max-h-72'
                                        : 'max-h-none'
                                "
                            >
                                {{ review.body }}
                            </p>

                            <div
                                v-if="shouldCollapseReview && !reviewExpanded"
                                class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-zinc-900 to-transparent"
                            />
                        </div>

                        <button
                            v-if="shouldCollapseReview"
                            type="button"
                            class="mt-4 rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-2 text-sm font-black text-white transition hover:border-zinc-500 hover:bg-zinc-800"
                            :aria-expanded="reviewExpanded"
                            @click="reviewExpanded = !reviewExpanded"
                        >
                            {{
                                reviewExpanded
                                    ? 'Show less'
                                    : 'Read full review'
                            }}
                        </button>
                    </div>
                </article>
            </section>

            <section class="mx-auto max-w-6xl px-4 pb-10 sm:px-8 sm:pb-16">
                <div
                    class="rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-zinc-950 p-6 sm:p-10"
                >
                    <div class="max-w-2xl">
                        <p class="text-sm font-black text-indigo-300 uppercase">
                            Your games. Your taste. Your profile.
                        </p>
                        <h2 class="mt-3 text-3xl font-black sm:text-4xl">
                            Curator.gg turns your gaming history into something
                            worth sharing.
                        </h2>
                        <p class="mt-4 leading-7 text-zinc-400">
                            Organize your Steam library, review the games you
                            play, discover recommendations and build a public
                            gaming profile that actually feels like yours.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div
                            class="rounded-2xl border border-zinc-800 bg-black/30 p-5"
                        >
                            <Library class="h-6 w-6 text-indigo-300" />
                            <h3 class="mt-4 font-black">
                                Organize your library
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">
                                Track your backlog, playing, finished and
                                dropped games.
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-zinc-800 bg-black/30 p-5"
                        >
                            <Star class="h-6 w-6 text-amber-300" />
                            <h3 class="mt-4 font-black">Publish reviews</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">
                                Share your ratings and opinions with a public
                                link.
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-zinc-800 bg-black/30 p-5"
                        >
                            <Sparkles class="h-6 w-6 text-fuchsia-300" />
                            <h3 class="mt-4 font-black">Find what to play</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">
                                Get recommendations shaped by players you trust.
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-zinc-800 bg-black/30 p-5"
                        >
                            <Users class="h-6 w-6 text-emerald-300" />
                            <h3 class="mt-4 font-black">Build your profile</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">
                                Show your favorite games, reviews and
                                collections.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-8 flex flex-col gap-4 rounded-2xl border border-indigo-500/20 bg-indigo-500/10 p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="text-xl font-black">
                                Ready to build yours?
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                Connect Steam and turn your library into your
                                gaming identity.
                            </p>
                        </div>
                        <a
                            :href="auth.user ? '/dashboard' : '/auth/steam'"
                            class="shrink-0 rounded-xl bg-white px-5 py-3 text-center text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                        >
                            {{
                                auth.user
                                    ? 'Go to dashboard'
                                    : 'Get started with Steam'
                            }}
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer
            class="border-t border-zinc-800 px-4 py-8 text-sm text-zinc-500 sm:px-8"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>© {{ new Date().getFullYear() }} Curator.gg</p>
                <div class="flex gap-5">
                    <Link href="/terms" class="hover:text-white">Terms</Link>
                    <Link href="/privacy" class="hover:text-white"
                        >Privacy</Link
                    >
                </div>
            </div>
        </footer>
    </div>
</template>
