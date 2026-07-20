<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Copy, Share2 } from 'lucide-vue-next';
import AdSlot from '@/components/ads/AdSlot.vue';
import TierListBoard from '@/components/tier-lists/TierListBoard.vue';
import TierListHeader from '@/components/tier-lists/TierListHeader.vue';

const props = defineProps({
    tierList: { type: Object, required: true },
    auth: { type: Object, default: () => ({ user: null }) },
    seo: { type: Object, required: true },
});

const share = async () => {
    const data = {
        title: props.tierList.title,
        text: `Check out my ${props.tierList.title} tier list on Curator.gg`,
        url: props.tierList.result_url,
    };

    if (navigator.share) {
        try {
            await navigator.share(data);
            return;
        } catch (error) {
            if (error.name === 'AbortError') return;
        }
    }

    await navigator.clipboard.writeText(data.url);
    window.alert('Tier list link copied.');
};

const copyTemplate = async () => {
    await navigator.clipboard.writeText(props.tierList.template_url);
    window.alert('Blank template link copied.');
};
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <Head :title="seo.title" />
        <TierListHeader :auth="auth" />
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-8 sm:py-10">
            <AdSlot label="Advertisement · leaderboard" />

            <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_260px]">
                <div class="min-w-0">
                    <p class="text-sm font-black text-indigo-300 uppercase">
                        Community tier list
                    </p>
                    <h1 class="mt-2 text-3xl font-black sm:text-5xl">
                        {{ tierList.title }}
                    </h1>
                    <p
                        v-if="tierList.description"
                        class="mt-3 max-w-3xl text-zinc-400"
                    >
                        {{ tierList.description }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950"
                            @click="share"
                        >
                            <Share2 class="h-4 w-4" /> Share result
                        </button>
                        <Link
                            :href="tierList.template_url"
                            class="rounded-xl border border-indigo-500/40 bg-indigo-500/10 px-4 py-3 text-sm font-black text-indigo-200"
                        >
                            Fill this tier list
                        </Link>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-zinc-300"
                            @click="copyTemplate"
                        >
                            <Copy class="h-4 w-4" /> Copy blank template link
                        </button>
                    </div>

                    <div class="mt-8">
                        <TierListBoard
                            :tiers="tierList.tiers"
                            :items="tierList.items"
                        />
                    </div>

                    <AdSlot class="mt-8" label="Advertisement · in-content" />
                </div>

                <aside class="hidden xl:block">
                    <div class="sticky top-6 space-y-6">
                        <AdSlot
                            format="sidebar"
                            label="Advertisement · sidebar"
                        />
                        <div
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                        >
                            <h2 class="font-black">Make your own ranking</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">
                                Start from this game set, change the tiers and
                                publish your version.
                            </p>
                            <Link
                                :href="tierList.template_url"
                                class="mt-4 block rounded-xl bg-white px-4 py-3 text-center text-sm font-black text-zinc-950"
                            >
                                Use this template
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</template>
