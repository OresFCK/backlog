<script setup>
import { Library, ListChecks, PlugZap, Sparkles } from 'lucide-vue-next';

const steps = [
    {
        icon: PlugZap,
        number: '01',
        title: 'Connect Steam',
        text: 'Sign in through Steam OpenID. Curator.gg imports public library data, playtime and achievements without ever seeing your password.',
    },
    {
        icon: Library,
        number: '02',
        title: 'Clean up your library',
        text: 'Separate your backlog from games you are playing, finished titles and games you decided to drop.',
    },
    {
        icon: ListChecks,
        number: '03',
        title: 'Add your perspective',
        text: 'Rate games, write public reviews, create custom lists and publish tier lists other players can remix.',
    },
    {
        icon: Sparkles,
        number: '04',
        title: 'Discover what comes next',
        text: 'Use community scores, friends’ opinions and your own collection to find the next game worth your time.',
    },
];
</script>

<template>
    <section
        id="how-it-works"
        class="border-y border-zinc-800 bg-zinc-950 py-20 sm:py-24"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="max-w-2xl">
                <p class="text-sm font-black text-indigo-300 uppercase">
                    How it works
                </p>
                <h2 class="mt-3 text-3xl font-black sm:text-5xl">
                    From library chaos to a clear next game.
                </h2>
                <p class="mt-5 leading-7 text-zinc-400">
                    Curator.gg combines organization, community context and
                    public tools in one place. You stay in control of your
                    collection and decide what appears on your profile.
                </p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="step in steps"
                    :key="step.number"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6"
                >
                    <div class="flex items-center justify-between">
                        <component
                            :is="step.icon"
                            class="h-6 w-6 text-indigo-300"
                        />
                        <span class="text-xs font-black text-zinc-600">{{
                            step.number
                        }}</span>
                    </div>
                    <h3 class="mt-6 text-xl font-black">{{ step.title }}</h3>
                    <p class="mt-3 text-sm leading-6 text-zinc-400">
                        {{ step.text }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
