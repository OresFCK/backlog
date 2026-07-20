<script setup>
import {
    BarChart3,
    Library,
    MessageSquareText,
    Tags,
    Trophy,
    UserRound,
} from 'lucide-vue-next';

const features = [
    {
        icon: Library,
        title: 'A backlog that stays useful',
        text: 'Move games between backlog, playing, finished and dropped. Search large libraries and keep the titles that matter visible.',
    },
    {
        icon: Tags,
        title: 'Organization that fits you',
        text: 'Use custom statuses, labels and lists instead of forcing every collection into the same rigid workflow.',
    },
    {
        icon: BarChart3,
        title: 'Statistics with context',
        text: 'See completion progress, playtime and ratings together, so the numbers explain your gaming habits rather than just counting games.',
    },
    {
        icon: MessageSquareText,
        title: 'Player-written reviews',
        text: 'Publish detailed reviews with ratings, recommendations, platforms and optional screenshots. Share each review outside the app.',
    },
    {
        icon: UserRound,
        title: 'A public gaming identity',
        text: 'Build a profile around favorite games, featured reviews, custom collections and the parts of your library you choose to show.',
    },
    {
        icon: Trophy,
        title: 'Community discovery',
        text: 'Find games through friends, community rankings, curators, premieres and challenges instead of relying on a storefront algorithm alone.',
    },
];
</script>

<template>
    <section
        id="features"
        class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24"
    >
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-black text-indigo-300 uppercase">
                Built for players with a backlog
            </p>
            <h2 class="mt-3 text-3xl font-black sm:text-5xl">
                More than a list of games.
            </h2>
            <p class="mt-5 leading-7 text-zinc-400">
                Your library tells a story: what you loved, what you finished
                and what deserves another chance. Curator.gg gives that story
                structure.
            </p>
        </div>

        <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="feature in features"
                :key="feature.title"
                class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 transition hover:border-zinc-700"
            >
                <component :is="feature.icon" class="h-7 w-7 text-indigo-300" />
                <h3 class="mt-5 text-xl font-black">{{ feature.title }}</h3>
                <p class="mt-3 text-sm leading-7 text-zinc-400">
                    {{ feature.text }}
                </p>
            </article>
        </div>
    </section>
</template>
