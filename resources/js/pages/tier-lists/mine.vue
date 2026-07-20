<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AdSlot from '@/components/ads/AdSlot.vue';
import TierListHeader from '@/components/tier-lists/TierListHeader.vue';

defineProps({
    tierLists: { type: Array, default: () => [] },
    auth: { type: Object, default: () => ({ user: null }) },
});
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <Head title="My Tier Lists" />
        <TierListHeader :auth="auth" />
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-8 sm:py-12">
            <AdSlot label="Advertisement · leaderboard" />
            <div class="mt-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black text-indigo-300 uppercase">
                        Saved to your account
                    </p>
                    <h1 class="mt-2 text-4xl font-black">My tier lists</h1>
                </div>
                <Link
                    href="/tier-list-maker"
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950"
                >
                    <Plus class="h-4 w-4" /> New tier list
                </Link>
            </div>

            <div
                v-if="tierLists.length"
                class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="tierList in tierLists"
                    :key="tierList.id"
                    :href="tierList.result_url"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5 transition hover:-translate-y-1 hover:border-zinc-600"
                >
                    <p class="text-xs font-bold text-zinc-500">
                        {{ tierList.created_at }}
                    </p>
                    <h2 class="mt-2 text-xl font-black">
                        {{ tierList.title }}
                    </h2>
                    <p class="mt-3 text-sm text-zinc-400">
                        {{ tierList.items.length }} games ·
                        {{ tierList.tiers.length }} tiers
                    </p>
                </Link>
            </div>

            <div
                v-else
                class="mt-8 rounded-3xl border border-dashed border-zinc-800 p-12 text-center"
            >
                <h2 class="text-2xl font-black">No saved tier lists yet</h2>
                <p class="mt-2 text-zinc-400">
                    Create your first ranking and it will appear here.
                </p>
            </div>
        </main>
    </div>
</template>
