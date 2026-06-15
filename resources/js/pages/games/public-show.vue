<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicReviewModal from '@/components/games/PublicReviewModal.vue'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },

    reviews: {
        type: Array,
        default: () => [],
    },

    stats: {
        type: Object,
        required: true,
    },

    auth: {
        type: Object,
        default: () => ({
            user: null,
        }),
    },
})

const selectedPlatform = ref('all')
const isReviewModalOpen = ref(false)

const siteName = 'Curator.gg'
const siteUrl = 'https://curator.gg'

const platformLabels = {
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

const canonicalUrl = computed(() => {
    return `${siteUrl}/${props.game.slug}`
})

const pageTitle = computed(() => {
    return `${props.game.title} Reviews, Ratings & Player Opinions | ${siteName}`
})

const metaDescription = computed(() => {
    if (props.stats.total_reviews > 0) {
        return `Read ${props.stats.total_reviews} player ${props.stats.total_reviews === 1 ? 'review' : 'reviews'} for ${props.game.title}. Average rating: ${props.stats.average_rating || 'N/A'}/10. Filter opinions by platform and see whether players recommend it.`
    }

    return `Read player reviews, ratings, recommendations and platform opinions for ${props.game.title} on ${siteName}.`
})

const heroImage = computed(() => {
    return props.game.header_image_url || props.game.cover_url || null
})

const filteredReviews = computed(() => {
    if (selectedPlatform.value === 'all') {
        return props.reviews
    }

    return props.reviews.filter((review) => review.platform === selectedPlatform.value)
})

const platforms = computed(() => {
    return Object.entries(props.stats.platforms || {}).map(([value, count]) => ({
        value,
        count,
        label: platformLabels[value] || value,
    }))
})

const recommendationPercent = computed(() => {
    if (!props.stats.total_reviews) {
        return 0
    }

    return Math.round((props.stats.recommended_count / props.stats.total_reviews) * 100)
})

const schemaOrg = computed(() => {
    const aggregateRating = props.stats.total_reviews > 0 && props.stats.average_rating
        ? {
            '@type': 'AggregateRating',
            ratingValue: props.stats.average_rating,
            bestRating: 10,
            worstRating: 1,
            ratingCount: props.stats.total_reviews,
            reviewCount: props.stats.total_reviews,
        }
        : undefined

    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'VideoGame',
        name: props.game.title,
        description: props.game.summary || metaDescription.value,
        image: heroImage.value || undefined,
        url: canonicalUrl.value,
        datePublished: props.game.release_date || undefined,
        aggregateRating,
        review: props.reviews.slice(0, 10).map((review) => ({
            '@type': 'Review',
            name: review.title,
            reviewBody: review.body,
            datePublished: review.created_at,
            author: {
                '@type': 'Person',
                name: review.user?.name || 'Curator.gg user',
            },
            reviewRating: {
                '@type': 'Rating',
                ratingValue: review.rating,
                bestRating: 10,
                worstRating: 1,
            },
        })),
    })
})
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>

        <link rel="canonical" :href="canonicalUrl">

        <meta name="description" :content="metaDescription">
        <meta name="robots" content="index, follow, max-image-preview:large">

        <meta property="og:type" content="website">
        <meta property="og:site_name" :content="siteName">
        <meta property="og:title" :content="pageTitle">
        <meta property="og:description" :content="metaDescription">
        <meta property="og:url" :content="canonicalUrl">
        <meta v-if="heroImage" property="og:image" :content="heroImage">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" :content="pageTitle">
        <meta name="twitter:description" :content="metaDescription">
        <meta v-if="heroImage" name="twitter:image" :content="heroImage">

    </Head>

    <div class="min-h-screen bg-zinc-950 text-white">
        <header class="border-b border-zinc-800 bg-zinc-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <Link
                    href="/home"
                    class="text-xl font-black tracking-tight"
                    aria-label="Curator.gg home"
                >
                    Curator.gg
                </Link>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="!auth.user"
                        href="/login"
                        class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 hover:bg-zinc-900 hover:text-white"
                    >
                        Login
                    </Link>

                    <button
                        v-if="auth.user"
                        type="button"
                        class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 hover:bg-zinc-200"
                        @click="isReviewModalOpen = true"
                    >
                        Write review
                    </button>
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden border-b border-zinc-800">
            <div
                v-if="heroImage"
                class="absolute inset-0 bg-cover bg-center opacity-30 blur-sm"
                :style="{ backgroundImage: `url(${heroImage})` }"
            />

            <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/60 via-zinc-950/90 to-zinc-950" />

            <div class="relative mx-auto grid max-w-7xl gap-8 px-6 py-14 lg:grid-cols-[260px_1fr]">
                <img
                    v-if="game.cover_url"
                    :src="game.cover_url"
                    :alt="`${game.title} cover art`"
                    class="aspect-[3/4] w-full rounded-3xl border border-zinc-800 object-cover shadow-2xl"
                    loading="eager"
                    fetchpriority="high"
                >

                <div class="flex flex-col justify-end">
                    <div
                        v-if="game.genres?.length"
                        class="mb-4 flex flex-wrap gap-2"
                    >
                        <span
                            v-for="genre in game.genres"
                            :key="genre"
                            class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-xs font-bold text-zinc-300"
                        >
                            {{ genre }}
                        </span>
                    </div>

                    <h1 class="text-5xl font-black tracking-tight">
                        {{ game.title }} Reviews
                    </h1>

                    <p
                        v-if="game.summary"
                        class="mt-4 max-w-3xl text-lg leading-8 text-zinc-300"
                    >
                        {{ game.summary }}
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-4">
                        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
                            <div class="text-3xl font-black">
                                {{ stats.average_rating || '—' }}
                            </div>
                            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                                Avg rating
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
                            <div class="text-3xl font-black">
                                {{ stats.total_reviews }}
                            </div>
                            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                                Reviews
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
                            <div class="text-3xl font-black">
                                {{ recommendationPercent }}%
                            </div>
                            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                                Recommend
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5">
                            <div class="text-3xl font-black">
                                {{ game.metacritic_score || '—' }}
                            </div>
                            <div class="mt-1 text-xs font-bold uppercase text-zinc-500">
                                Metacritic
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[1fr_340px]">
            <section class="space-y-8" :aria-label="`${game.title} player reviews`">
                <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black">
                                {{ game.title }} player reviews
                            </h2>

                            <p class="mt-1 text-sm text-zinc-400">
                                Browse ratings and recommendations by platform.
                            </p>
                        </div>

                        <button
                            v-if="auth.user"
                            type="button"
                            class="rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950 hover:bg-zinc-200"
                            @click="isReviewModalOpen = true"
                        >
                            Add review
                        </button>
                    </div>

                    <nav
                        v-if="platforms.length"
                        class="mt-6 flex flex-wrap gap-2"
                        aria-label="Filter reviews by platform"
                    >
                        <button
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-bold transition"
                            :class="selectedPlatform === 'all'
                                ? 'border-white bg-white text-zinc-950'
                                : 'border-zinc-700 text-zinc-300 hover:bg-zinc-800 hover:text-white'"
                            @click="selectedPlatform = 'all'"
                        >
                            All
                        </button>

                        <button
                            v-for="platform in platforms"
                            :key="platform.value"
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-bold transition"
                            :class="selectedPlatform === platform.value
                                ? 'border-white bg-white text-zinc-950'
                                : 'border-zinc-700 text-zinc-300 hover:bg-zinc-800 hover:text-white'"
                            @click="selectedPlatform = platform.value"
                        >
                            {{ platform.label }} · {{ platform.count }}
                        </button>
                    </nav>
                </div>

                <div
                    v-if="filteredReviews.length"
                    class="space-y-4"
                >
                    <article
                        v-for="review in filteredReviews"
                        :key="review.id"
                        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                        itemprop="review"
                        itemscope
                        itemtype="https://schema.org/Review"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="review.user.avatar"
                                    :src="review.user.avatar"
                                    :alt="`${review.user.name} avatar`"
                                    class="h-11 w-11 rounded-full"
                                    loading="lazy"
                                >

                                <div>
                                    <div class="font-bold" itemprop="author">
                                        {{ review.user.name || 'Unknown user' }}
                                    </div>

                                    <div class="text-xs text-zinc-500">
                                        {{ review.created_at }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-xl bg-zinc-950 px-4 py-2 text-xl font-black"
                                itemprop="reviewRating"
                                itemscope
                                itemtype="https://schema.org/Rating"
                            >
                                <meta itemprop="bestRating" content="10">
                                <meta itemprop="worstRating" content="1">
                                <span itemprop="ratingValue">{{ review.rating }}</span>/10
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span
                                v-if="review.platform"
                                class="rounded-full border border-zinc-700 px-3 py-1 text-xs font-bold text-zinc-300"
                            >
                                {{ platformLabels[review.platform] || review.platform }}
                            </span>

                            <span
                                v-if="review.recommended"
                                class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300"
                            >
                                Recommended
                            </span>

                            <span
                                v-if="review.not_recommended"
                                class="rounded-full border border-red-500/40 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300"
                            >
                                Not recommended
                            </span>
                        </div>

                        <h3 class="mt-5 text-xl font-black" itemprop="name">
                            {{ review.title }}
                        </h3>

                        <p class="mt-3 whitespace-pre-line leading-7 text-zinc-300" itemprop="reviewBody">
                            {{ review.body }}
                        </p>

                        <img
                            v-if="review.screenshot_url"
                            :src="review.screenshot_url"
                            :alt="`${game.title} screenshot from ${review.user.name || 'review'}`"
                            class="mt-5 max-h-96 rounded-2xl border border-zinc-800 object-cover"
                            loading="lazy"
                        >
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-700 bg-zinc-900 p-10 text-center"
                >
                    <h2 class="text-xl font-black">
                        No {{ game.title }} reviews yet
                    </h2>

                    <p class="mt-2 text-zinc-400">
                        Be the first curator to review this game.
                    </p>
                </div>
            </section>

            <aside class="space-y-4" aria-label="Game review summary">
                <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <h2 class="text-lg font-black">
                        {{ game.title }} review breakdown
                    </h2>

                    <div class="mt-5 space-y-3 text-sm text-zinc-300">
                        <div class="flex justify-between">
                            <span>Recommended</span>
                            <strong>{{ stats.recommended_count }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span>Not recommended</span>
                            <strong>{{ stats.not_recommended_count }}</strong>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                    <h2 class="text-lg font-black">
                        About this page
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-zinc-400">
                        This page collects public player reviews for {{ game.title }},
                        including ratings, recommendations and platform-specific opinions.
                    </p>
                </div>
            </aside>
        </main>

        <PublicReviewModal
            v-if="isReviewModalOpen"
            :game="game"
            @close="isReviewModalOpen = false"
        />
    </div>
</template>