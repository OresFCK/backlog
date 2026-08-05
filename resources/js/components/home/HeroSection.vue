<script setup>
import { ArrowRight, Check, Gamepad2, LockKeyhole } from 'lucide-vue-next';
import { track } from '@/lib/analytics';

defineProps({
    auth: { type: Object, default: () => ({ user: null }) },
});

const resultUrl = '/recommendations?onboarding=connected';
const steamUrl = `/auth/steam?intended=${encodeURIComponent(resultUrl)}`;

const startSteamConnection = () => {
    track('steam_connection_started', {
        funnel: 'next_three_games',
        source: 'homepage_hero',
    });
};
</script>

<template>
    <section class="border-b border-zinc-800">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:py-24">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_0.9fr]">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-indigo-400">
                        One decision, made for you
                    </p>
                    <h1
                        class="mt-4 text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-6xl"
                    >
                        Your next three games.
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-base leading-7 text-zinc-400 sm:text-lg"
                    >
                        Connect Steam and get three personal picks from the
                        games you already own — ranked and ready to play.
                    </p>
                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <a
                            :href="auth.user ? '/recommendations' : steamUrl"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-black transition hover:bg-zinc-200"
                            @click="!auth.user && startSteamConnection()"
                        >
                            {{
                                auth.user
                                    ? 'Show my three games'
                                    : 'Find my next three games'
                            }}
                            <ArrowRight class="h-4 w-4" />
                        </a>
                    </div>
                    <p v-if="!auth.user" class="mt-4 text-xs text-zinc-600">
                        Free · takes about 20 seconds · your password stays with
                        Steam
                    </p>
                </div>

                <div
                    class="relative rounded-3xl border border-zinc-800 bg-zinc-900/80 p-5 shadow-2xl shadow-indigo-950/20"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold tracking-widest text-indigo-400 uppercase"
                            >
                                Your result
                            </p>
                            <h2 class="mt-1 text-xl font-bold">
                                Three games. No scrolling.
                            </h2>
                        </div>
                        <Gamepad2 class="h-6 w-6 text-zinc-500" />
                    </div>
                    <div
                        v-for="number in 3"
                        :key="number"
                        class="mb-3 flex items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 last:mb-0"
                    >
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 font-black text-indigo-300"
                            >{{ number }}</span
                        >
                        <div class="min-w-0 flex-1">
                            <div
                                class="h-3 rounded-full bg-zinc-700"
                                :class="number === 2 ? 'w-2/3' : 'w-4/5'"
                            />
                            <div
                                class="mt-2 h-2 w-1/2 rounded-full bg-zinc-800"
                            />
                        </div>
                        <Check class="h-5 w-5 text-emerald-400" />
                    </div>
                    <div
                        class="mt-5 flex items-center gap-2 text-xs text-zinc-500"
                    >
                        <LockKeyhole class="h-3.5 w-3.5" /> Based on your own
                        Steam library
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
