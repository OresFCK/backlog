<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import AdSlot from '@/components/ads/AdSlot.vue';
import TierListBoard from '@/components/tier-lists/TierListBoard.vue';
import TierListHeader from '@/components/tier-lists/TierListHeader.vue';

const props = defineProps({
    template: { type: Object, default: null },
    auth: { type: Object, default: () => ({ user: null }) },
});

const defaultTiers = [
    { id: 's', name: 'S', color: '#f87171' },
    { id: 'a', name: 'A', color: '#fb923c' },
    { id: 'b', name: 'B', color: '#facc15' },
    { id: 'c', name: 'C', color: '#a3e635' },
    { id: 'd', name: 'D', color: '#4ade80' },
];

const title = ref(
    props.template
        ? `${props.template.title} — my ranking`
        : 'My game tier list',
);
const description = ref(props.template?.description ?? '');
const tiers = ref(structuredClone(props.template?.tiers ?? defaultTiers));
const items = ref(structuredClone(props.template?.items ?? []));
const query = ref('');
const results = ref([]);
const searching = ref(false);
const searchOpen = ref(false);
const searchContainer = ref(null);
let timer = null;
let controller = null;

const canPublish = computed(
    () =>
        title.value.trim() && tiers.value.length >= 2 && items.value.length > 0,
);

watch(query, () => {
    clearTimeout(timer);
    searchOpen.value = query.value.trim().length >= 2;
    timer = setTimeout(searchGames, 300);
});

const closeSearch = () => {
    searchOpen.value = false;
    controller?.abort();
};

const clearSearch = () => {
    query.value = '';
    results.value = [];
    closeSearch();
};

const handleOutsideClick = (event) => {
    if (!searchContainer.value?.contains(event.target)) closeSearch();
};

onMounted(() => document.addEventListener('pointerdown', handleOutsideClick));

onBeforeUnmount(() => {
    clearTimeout(timer);
    controller?.abort();
    document.removeEventListener('pointerdown', handleOutsideClick);
});

const searchGames = async () => {
    if (query.value.trim().length < 2) {
        results.value = [];
        return;
    }

    controller?.abort();
    controller = new AbortController();
    searching.value = true;

    try {
        const response = await fetch(
            `/public-games/search?q=${encodeURIComponent(query.value.trim())}`,
            { signal: controller.signal },
        );
        results.value = response.ok ? await response.json() : [];
    } catch (error) {
        if (error.name !== 'AbortError') results.value = [];
    } finally {
        searching.value = false;
    }
};

const addGame = (game) => {
    if (items.value.some((item) => item.game_id === game.id)) {
        clearSearch();
        return;
    }

    items.value.push({
        game_id: game.id,
        title: game.title,
        slug: game.slug,
        image_url: game.cover_url,
        tier_id: null,
        position: items.value.length,
    });
    clearSearch();
};

const moveItem = (gameId, tierId) => {
    const item = items.value.find((entry) => entry.game_id === gameId);
    if (!item) return;
    item.tier_id = tierId;
    item.position = items.value.filter(
        (entry) => entry.tier_id === tierId,
    ).length;
};

const removeItem = (gameId) => {
    items.value = items.value.filter((item) => item.game_id !== gameId);
};

const addTier = () => {
    if (tiers.value.length >= 12) return;
    tiers.value.push({
        id: `tier-${Date.now()}`,
        name: `Tier ${tiers.value.length + 1}`,
        color: '#a1a1aa',
    });
};

const removeTier = (tierId) => {
    if (tiers.value.length <= 2) return;
    items.value.forEach((item) => {
        if (item.tier_id === tierId) item.tier_id = null;
    });
    tiers.value = tiers.value.filter((tier) => tier.id !== tierId);
};

const moveTier = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= tiers.value.length) return;
    [tiers.value[index], tiers.value[target]] = [
        tiers.value[target],
        tiers.value[index],
    ];
};

const publish = () => {
    const form = useForm({
        title: title.value.trim(),
        description: description.value.trim() || null,
        tiers: tiers.value,
        items: items.value.map((item, index) => ({ ...item, position: index })),
    });
    form.post('/tier-lists');
};
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-white">
        <Head title="Tier List Maker" />
        <TierListHeader :auth="auth" />

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-8 sm:py-10">
            <AdSlot label="Advertisement · leaderboard" />

            <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_260px]">
                <div class="min-w-0">
                    <div class="mb-7">
                        <p class="text-sm font-black text-indigo-300 uppercase">
                            Tier List Maker
                        </p>
                        <h1 class="mt-2 text-3xl font-black sm:text-5xl">
                            Rank games your way.
                        </h1>
                        <p class="mt-3 max-w-2xl text-zinc-400">
                            Drag games between tiers, publish your ranking or
                            share a blank version for friends to complete.
                        </p>
                    </div>

                    <div
                        class="grid gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-6"
                    >
                        <input
                            v-model="title"
                            maxlength="120"
                            class="rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-lg font-black outline-none focus:border-indigo-500"
                            placeholder="Tier list title"
                        />
                        <textarea
                            v-model="description"
                            maxlength="1000"
                            rows="2"
                            class="resize-none rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm outline-none focus:border-indigo-500"
                            placeholder="Optional description"
                        />
                    </div>

                    <section
                        class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-6"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
                            <h2 class="text-lg font-black">Customize tiers</h2>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold hover:bg-zinc-800"
                                @click="addTier"
                            >
                                <Plus class="h-4 w-4" /> Add tier
                            </button>
                        </div>
                        <div class="mt-4 grid gap-2">
                            <div
                                v-for="(tier, index) in tiers"
                                :key="tier.id"
                                class="grid grid-cols-[44px_minmax(0,1fr)_auto] items-center gap-2"
                            >
                                <input
                                    v-model="tier.color"
                                    type="color"
                                    class="h-10 w-11 rounded border-0 bg-transparent"
                                />
                                <input
                                    v-model="tier.name"
                                    maxlength="40"
                                    class="min-w-0 rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm font-bold"
                                />
                                <div class="flex">
                                    <button
                                        type="button"
                                        class="p-2 text-zinc-400"
                                        @click="moveTier(index, -1)"
                                    >
                                        <ChevronUp class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-2 text-zinc-400"
                                        @click="moveTier(index, 1)"
                                    >
                                        <ChevronDown class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-2 text-zinc-400 hover:text-red-300"
                                        @click="removeTier(tier.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        ref="searchContainer"
                        class="relative mt-6"
                        @keydown.esc="closeSearch"
                    >
                        <Search
                            class="absolute top-3.5 left-4 h-5 w-5 text-zinc-500"
                        />
                        <input
                            v-model="query"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-900 py-3 pr-12 pl-12 outline-none focus:border-indigo-500"
                            placeholder="Search and add games..."
                            @focus="searchOpen = query.trim().length >= 2"
                        />
                        <button
                            v-if="query"
                            type="button"
                            class="absolute top-2 right-2 rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-white"
                            aria-label="Close search results"
                            @click="clearSearch"
                        >
                            <X class="h-5 w-5" />
                        </button>
                        <div
                            v-if="searchOpen && query.trim().length >= 2"
                            class="absolute z-30 mt-2 w-full overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-950 shadow-2xl"
                        >
                            <p
                                v-if="searching"
                                class="p-4 text-sm text-zinc-500"
                            >
                                Searching...
                            </p>
                            <button
                                v-for="game in results"
                                :key="game.id"
                                type="button"
                                class="flex w-full items-center gap-3 p-3 text-left hover:bg-zinc-900"
                                @click="addGame(game)"
                            >
                                <img
                                    v-if="game.cover_url"
                                    :src="game.cover_url"
                                    class="h-12 w-10 rounded object-cover"
                                />
                                <div
                                    v-else
                                    class="h-12 w-10 rounded bg-zinc-800"
                                />
                                <span
                                    class="min-w-0 flex-1 truncate text-sm font-bold"
                                    >{{ game.title }}</span
                                >
                                <span class="text-xs text-indigo-300">Add</span>
                            </button>
                        </div>
                    </section>

                    <div class="mt-6">
                        <TierListBoard
                            :tiers="tiers"
                            :items="items"
                            editable
                            @move="moveItem"
                            @remove-item="removeItem"
                        />
                    </div>

                    <div
                        class="mt-6 flex flex-col gap-3 rounded-2xl border border-indigo-500/20 bg-indigo-500/10 p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2 class="font-black">Publish your tier list</h2>
                            <p class="mt-1 text-sm text-zinc-400">
                                You will receive a result link and a blank
                                template link.
                                <span v-if="auth.user"
                                    >It will also be saved to your
                                    account.</span
                                >
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="!canPublish"
                            @click="publish"
                        >
                            Publish & share
                        </button>
                    </div>
                </div>

                <div class="hidden xl:block">
                    <div class="sticky top-6 space-y-6">
                        <AdSlot
                            format="sidebar"
                            label="Advertisement · sidebar"
                        />
                        <div
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
                        >
                            <h2 class="font-black">How sharing works</h2>
                            <p class="mt-3 text-sm leading-6 text-zinc-400">
                                Share the finished ranking, or send the template
                                link so everyone can create their own version.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
