<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Plus } from 'lucide-vue-next';

defineProps({
    tierLists: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section class="border-t border-white/5 px-4 py-16 sm:px-6 sm:py-24">
        <div class="mx-auto max-w-7xl">
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.2em] text-indigo-400 uppercase"
                    >
                        Made by the community
                    </p>
                    <h2 class="mt-3 text-3xl font-black sm:text-5xl">
                        Public game tier lists
                    </h2>
                    <p class="mt-3 max-w-2xl text-zinc-400">
                        See how other players rank their games, then build and
                        share your own list.
                    </p>
                </div>
                <Link
                    href="/tier-lists"
                    class="inline-flex items-center gap-2 text-sm font-black text-white"
                >
                    Browse all <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <div
                v-if="tierLists.length"
                class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <Link
                    v-for="tierList in tierLists"
                    :key="tierList.id"
                    :href="tierList.url"
                    class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:-translate-y-1 hover:border-zinc-700"
                >
                    <div
                        class="grid h-32 grid-cols-4 overflow-hidden bg-zinc-950"
                    >
                        <img
                            v-for="(cover, index) in tierList.covers"
                            :key="`${tierList.id}-${index}`"
                            :src="cover"
                            alt=""
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-for="index in Math.max(
                                0,
                                4 - tierList.covers.length,
                            )"
                            :key="`empty-${tierList.id}-${index}`"
                            class="border-l border-zinc-800 bg-zinc-900"
                        />
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <h3
                                class="min-w-0 truncate text-xl font-black group-hover:text-indigo-300"
                            >
                                {{ tierList.title }}
                            </h3>
                            <span
                                class="shrink-0 rounded-full border border-zinc-700 px-2.5 py-1 text-xs font-bold text-zinc-400"
                            >
                                {{ tierList.items_count }} games
                            </span>
                        </div>
                        <p class="mt-2 line-clamp-2 text-sm text-zinc-500">
                            {{
                                tierList.description ||
                                'A public game ranking made on Curator.gg.'
                            }}
                        </p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-400">
                                by {{ tierList.author || 'Curator.gg player' }}
                            </span>
                            <div class="flex gap-1">
                                <span
                                    v-for="tier in tierList.tiers"
                                    :key="tier.id"
                                    class="h-2 w-6 rounded-full"
                                    :style="{ backgroundColor: tier.color }"
                                />
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="mt-8 rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/40 px-6 py-14 text-center"
            >
                <h3 class="text-xl font-black">No public tier lists yet</h3>
                <p class="mt-2 text-sm text-zinc-500">
                    Create the first community ranking.
                </p>
                <Link
                    href="/tier-list-maker"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950"
                >
                    <Plus class="h-4 w-4" /> Create tier list
                </Link>
            </div>
        </div>
    </section>
</template>
