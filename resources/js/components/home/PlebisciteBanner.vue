<script setup>
import { ArrowRight, Sparkles, Trophy } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const covers = [
    ['The Witcher 3', 292030],
    ['Hades', 1145360],
    ['Stardew Valley', 413150],
    ['Outer Wilds', 753640],
    ['Disco Elysium', 632360],
];
</script>

<template>
    <section class="px-4 py-10 sm:px-6 sm:py-14">
        <div
            class="relative mx-auto grid max-w-7xl overflow-hidden border-4 border-zinc-700 bg-[#171823] shadow-[8px_8px_0_#07070a] md:grid-cols-[1.1fr_.9fr]"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-20 [background-image:linear-gradient(#8585aa_1px,transparent_1px),linear-gradient(90deg,#8585aa_1px,transparent_1px)] [background-size:24px_24px]"
            />
            <div class="relative z-10 p-7 sm:p-10 lg:p-14">
                <p
                    class="inline-flex items-center gap-2 border-2 border-amber-300 bg-amber-300 px-3 py-1 font-mono text-xs font-black tracking-widest text-zinc-950 uppercase shadow-[3px_3px_0_#000]"
                >
                    <Sparkles class="h-4 w-4" /> Community vote is open
                </p>
                <h2
                    class="mt-6 max-w-2xl font-mono text-3xl leading-tight font-black text-white sm:text-5xl"
                >
                    THE GREAT<br />
                    <span class="text-cyan-300">GIERECZKOWY</span><br />
                    PLEBISCITE
                </h2>
                <p class="mt-5 max-w-xl text-sm leading-6 text-zinc-300 sm:text-base">
                    Choose your champions across 22 very serious categories.
                    One vote per category. All opinions are final-ish.
                </p>
                <Link
                    href="/plebiscite"
                    class="mt-7 inline-flex items-center gap-3 border-2 border-white bg-cyan-300 px-5 py-3 font-mono text-sm font-black text-zinc-950 shadow-[4px_4px_0_#fff] transition hover:translate-x-1 hover:translate-y-1 hover:shadow-[2px_2px_0_#fff]"
                >
                    <Trophy class="h-5 w-5" /> Enter the plebiscite
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <p class="mt-4 font-mono text-xs text-zinc-500">
                    ◆ 22 CATEGORIES &nbsp; ◆ 6 NOMINEES EACH &nbsp; ◆ COMMUNITY VOTES
                </p>
            </div>
            <div class="relative flex min-h-64 items-center justify-center p-8 sm:min-h-80">
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    <div
                        v-for="([title, appId], index) in covers"
                        :key="title"
                        class="relative border-2 border-white/80 bg-zinc-800 p-1 shadow-[4px_4px_0_#000]"
                        :class="index === 1 || index === 3 ? 'translate-y-5' : '-translate-y-3'"
                    >
                        <img
                            :src="`https://cdn.cloudflare.steamstatic.com/steam/apps/${appId}/library_600x900.jpg`"
                            :alt="title"
                            class="h-36 w-24 object-cover [image-rendering:pixelated] sm:h-48 sm:w-32"
                        />
                        <span class="absolute -right-2 -bottom-2 bg-amber-300 px-1.5 py-1 font-mono text-xs font-black text-zinc-950 shadow-[2px_2px_0_#000]">
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-center border-2 border-dashed border-cyan-300/60 bg-zinc-950/70 font-mono text-xs font-black text-cyan-200 shadow-[4px_4px_0_#000]">
                        YOUR<br />PICKS?
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
