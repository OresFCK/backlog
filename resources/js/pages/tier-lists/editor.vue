<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Check,
    Gamepad2,
    LayoutDashboard,
    LogIn,
    Plus,
    Save,
    Search,
    Share2,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    user: Object,
    tierList: Object,
    seo: Object,
});

const defaultTiers = () => [
    { id: 's', name: 'S', color: '#ff7f7f' },
    { id: 'a', name: 'A', color: '#ffbf7f' },
    { id: 'b', name: 'B', color: '#ffdf7f' },
    { id: 'c', name: 'C', color: '#ffff7f' },
    { id: 'd', name: 'D', color: '#bfff7f' },
];

const title = ref(props.tierList?.title ?? 'My Game Tier List');
const description = ref(props.tierList?.description ?? '');
const isPublic = ref(props.tierList?.is_public ?? true);
const tiers = ref(
    props.tierList?.tiers?.length
        ? props.tierList.tiers.map(({ id, name, color }) => ({
              id,
              name,
              color,
          }))
        : defaultTiers(),
);
const items = ref((props.tierList?.items ?? []).map((item) => ({ ...item })));
const query = ref('');
const results = ref([]);
const searching = ref(false);
const selectedItemId = ref(null);
const draggedItemId = ref(null);
const saving = ref(false);
const copied = ref(false);
const hydrated = ref(false);
const isAuthenticated = computed(() => Boolean(props.auth?.user || props.user));

const draftKey = 'curator-tier-list-draft-v1';

const selectedItem = computed(() =>
    items.value.find((item) => item.id === selectedItemId.value),
);

const itemsForTier = (tierId) =>
    items.value
        .filter((item) => item.tier_id === tierId)
        .sort((a, b) => a.position - b.position);

const unrankedItems = computed(() => itemsForTier(null));

let searchTimer = null;

watch(query, (value) => {
    clearTimeout(searchTimer);
    results.value = [];

    if (value.trim().length < 2) {
        return;
    }

    searchTimer = setTimeout(async () => {
        searching.value = true;

        try {
            const response = await fetch(
                `/public-games/search?q=${encodeURIComponent(value.trim())}`,
            );
            results.value = response.ok ? await response.json() : [];
        } catch {
            results.value = [];
        } finally {
            searching.value = false;
        }
    }, 250);
});

watch(
    [title, description, isPublic, tiers, items],
    () => {
        if (!hydrated.value || props.tierList) {
            return;
        }

        localStorage.setItem(
            draftKey,
            JSON.stringify({
                title: title.value,
                description: description.value,
                is_public: isPublic.value,
                tiers: tiers.value,
                items: items.value,
            }),
        );
    },
    { deep: true },
);

onMounted(() => {
    if (!props.tierList) {
        try {
            const draft = JSON.parse(localStorage.getItem(draftKey));

            if (draft?.tiers?.length) {
                title.value = draft.title || title.value;
                description.value = draft.description || '';
                isPublic.value = draft.is_public ?? true;
                tiers.value = draft.tiers;
                items.value = draft.items ?? [];
            }
        } catch {
            localStorage.removeItem(draftKey);
        }
    }

    hydrated.value = true;
});

const addGame = (game) => {
    if (
        items.value.length >= 200 ||
        items.value.some((item) => item.id === game.id)
    ) {
        return;
    }

    items.value.push({
        id: game.id,
        title: game.title,
        slug: game.slug,
        cover_url: game.cover_url,
        tier_id: null,
        position: unrankedItems.value.length,
    });

    query.value = '';
    results.value = [];
};

const removeGame = (gameId) => {
    items.value = items.value.filter((item) => item.id !== gameId);

    if (selectedItemId.value === gameId) {
        selectedItemId.value = null;
    }
};

const placeGame = (gameId, tierId) => {
    const game = items.value.find((item) => item.id === gameId);

    if (!game) {
        return;
    }

    game.tier_id = tierId;
    game.position = itemsForTier(tierId).filter(
        (item) => item.id !== gameId,
    ).length;
    selectedItemId.value = null;
};

const dropGame = (tierId) => {
    if (draggedItemId.value !== null) {
        placeGame(draggedItemId.value, tierId);
    }

    draggedItemId.value = null;
};

const addTier = () => {
    if (tiers.value.length >= 12) {
        return;
    }

    tiers.value.push({
        id: `tier-${Date.now()}`,
        name: `Tier ${tiers.value.length + 1}`,
        color: '#a78bfa',
    });
};

const deleteTier = (tierId) => {
    if (tiers.value.length <= 2) {
        return;
    }

    items.value
        .filter((item) => item.tier_id === tierId)
        .forEach((item) => {
            item.tier_id = null;
        });
    tiers.value = tiers.value.filter((tier) => tier.id !== tierId);
};

const moveTier = (index, direction) => {
    const destination = index + direction;

    if (destination < 0 || destination >= tiers.value.length) {
        return;
    }

    const next = [...tiers.value];
    const [tier] = next.splice(index, 1);
    next.splice(destination, 0, tier);
    tiers.value = next;
};

const payload = () => ({
    title: title.value,
    description: description.value || null,
    is_public: isPublic.value,
    tiers: tiers.value.map(({ id, name, color }) => ({ id, name, color })),
    items: [
        ...tiers.value.flatMap((tier) =>
            itemsForTier(tier.id).map((item, position) => ({
                id: item.id,
                tier_id: tier.id,
                position,
            })),
        ),
        ...unrankedItems.value.map((item, position) => ({
            id: item.id,
            tier_id: null,
            position,
        })),
    ],
});

const save = () => {
    if (!isAuthenticated.value) {
        return;
    }

    saving.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => localStorage.removeItem(draftKey),
        onFinish: () => {
            saving.value = false;
        },
    };

    if (props.tierList) {
        router.patch(`/tier-lists/${props.tierList.slug}`, payload(), options);

        return;
    }

    router.post('/tier-lists', payload(), options);
};

const share = async () => {
    if (!props.tierList?.is_public) {
        return;
    }

    const shareData = {
        title: props.tierList.title,
        text: `Check out ${props.tierList.title} on Curator.gg`,
        url: props.tierList.share_url,
    };

    if (navigator.share) {
        try {
            await navigator.share(shareData);

            return;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(props.tierList.share_url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 1800);
};
</script>

<template>
    <Head :title="`${title} | Tier List Maker`" />

    <div class="flex min-h-screen bg-zinc-950 text-white">
        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950/95 backdrop-blur"
            >
                <div
                    class="mx-auto flex max-w-[1500px] items-center gap-3 px-4 py-4 sm:px-6"
                >
                    <Link
                        href="/home"
                        class="flex items-center gap-2 text-lg font-black"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-zinc-950"
                        >
                            <Gamepad2 class="h-5 w-5" />
                        </span>
                        Curator.gg
                    </Link>
                    <nav class="ml-auto flex items-center gap-2">
                        <a
                            href="/tier-lists"
                            class="rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-200 hover:bg-zinc-900"
                        >
                            Community lists
                        </a>
                        <a
                            :href="
                                isAuthenticated
                                    ? '/dashboard'
                                    : '/auth/steam?intended=/tier-list-maker'
                            "
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                        >
                            <LogIn class="h-4 w-4" />
                            <span class="hidden sm:inline">
                                {{
                                    isAuthenticated
                                        ? 'Dashboard'
                                        : 'Sign in to save'
                                }}
                            </span>
                        </a>
                    </nav>
                </div>
            </header>

            <main class="flex-1 px-3 py-5 sm:px-6 sm:py-8">
                <div
                    class="mx-auto grid max-w-[1500px] gap-6 xl:grid-cols-[minmax(0,1fr)_260px]"
                >
                    <div class="min-w-0 space-y-6">
                        <section
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
                        >
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="min-w-0 flex-1">
                                    <input
                                        v-model="title"
                                        type="text"
                                        required
                                        maxlength="120"
                                        aria-label="Tier list title"
                                        class="w-full border-0 bg-transparent p-0 text-2xl font-black text-white outline-none placeholder:text-zinc-600 sm:text-4xl"
                                        placeholder="Name your tier list"
                                    />
                                    <textarea
                                        v-model="description"
                                        rows="2"
                                        maxlength="1000"
                                        class="mt-3 w-full resize-none border-0 bg-transparent p-0 text-sm leading-6 text-zinc-400 outline-none placeholder:text-zinc-600"
                                        placeholder="Optional description..."
                                    />
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <label
                                        v-if="isAuthenticated"
                                        class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-300"
                                    >
                                        <input
                                            v-model="isPublic"
                                            type="checkbox"
                                            class="rounded border-zinc-700 bg-zinc-950"
                                        />
                                        Public
                                    </label>

                                    <button
                                        v-if="isAuthenticated"
                                        type="button"
                                        :disabled="saving"
                                        class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 disabled:opacity-50"
                                        @click="save"
                                    >
                                        <Save class="h-4 w-4" />
                                        {{ saving ? 'Saving...' : 'Save' }}
                                    </button>

                                    <button
                                        v-if="tierList?.is_public"
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold"
                                        @click="share"
                                    >
                                        <Check
                                            v-if="copied"
                                            class="h-4 w-4 text-emerald-400"
                                        />
                                        <Share2 v-else class="h-4 w-4" />
                                        {{ copied ? 'Copied' : 'Share' }}
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="!isAuthenticated"
                                class="mt-4 rounded-xl border border-indigo-500/20 bg-indigo-500/10 px-4 py-3 text-sm text-indigo-200"
                            >
                                Your draft is saved in this browser. Sign in
                                when you want to publish and share it.
                            </p>
                        </section>

                        <section
                            class="overflow-hidden rounded-2xl border border-zinc-800 bg-black sm:rounded-3xl"
                        >
                            <div
                                v-for="(tier, index) in tiers"
                                :key="tier.id"
                                class="grid min-h-28 grid-cols-[76px_minmax(0,1fr)] border-b border-zinc-800 last:border-b-0 sm:grid-cols-[112px_minmax(0,1fr)_52px]"
                                @dragover.prevent
                                @drop="dropGame(tier.id)"
                            >
                                <div
                                    class="flex min-w-0 flex-col items-center justify-center gap-2 p-2 text-center font-black text-zinc-950"
                                    :style="{ backgroundColor: tier.color }"
                                >
                                    <input
                                        v-model="tier.name"
                                        type="text"
                                        maxlength="40"
                                        class="w-full border-0 bg-transparent text-center text-sm font-black outline-none sm:text-base"
                                        aria-label="Tier name"
                                    />
                                    <input
                                        v-model="tier.color"
                                        type="color"
                                        class="h-5 w-8 cursor-pointer border-0 bg-transparent"
                                        aria-label="Tier color"
                                    />
                                </div>

                                <div
                                    class="flex min-w-0 flex-wrap content-start gap-2 bg-zinc-900/30 p-2 sm:p-3"
                                    :class="
                                        selectedItem
                                            ? 'outline-1 -outline-offset-1 outline-indigo-400/30'
                                            : ''
                                    "
                                >
                                    <button
                                        v-if="selectedItem"
                                        type="button"
                                        class="flex h-24 items-center rounded-xl border border-dashed border-indigo-400/50 px-4 text-xs font-bold text-indigo-300"
                                        @click="
                                            placeGame(selectedItem.id, tier.id)
                                        "
                                    >
                                        Place here
                                    </button>

                                    <article
                                        v-for="game in itemsForTier(tier.id)"
                                        :key="game.id"
                                        draggable="true"
                                        class="group relative h-24 w-17 cursor-grab overflow-hidden rounded-lg border bg-zinc-800 active:cursor-grabbing sm:h-28 sm:w-20"
                                        :class="
                                            selectedItemId === game.id
                                                ? 'border-indigo-400 ring-2 ring-indigo-400/30'
                                                : 'border-zinc-700'
                                        "
                                        @dragstart="draggedItemId = game.id"
                                        @dragend="draggedItemId = null"
                                        @click="
                                            selectedItemId =
                                                selectedItemId === game.id
                                                    ? null
                                                    : game.id
                                        "
                                    >
                                        <img
                                            v-if="game.cover_url"
                                            :src="game.cover_url"
                                            :alt="game.title"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full items-center justify-center p-2 text-center text-[10px] text-zinc-400"
                                        >
                                            {{ game.title }}
                                        </div>
                                        <button
                                            type="button"
                                            class="absolute top-1 right-1 block rounded-md bg-black/80 p-1 text-white sm:hidden sm:group-hover:block"
                                            aria-label="Remove game"
                                            @click.stop="removeGame(game.id)"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </article>
                                </div>

                                <div
                                    class="col-span-2 flex items-center justify-center gap-1 border-t border-zinc-800 bg-zinc-950 p-1 sm:col-span-1 sm:flex-col sm:border-t-0 sm:border-l"
                                >
                                    <button
                                        type="button"
                                        :disabled="index === 0"
                                        class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white disabled:opacity-20"
                                        aria-label="Move tier up"
                                        @click="moveTier(index, -1)"
                                    >
                                        <ArrowUp class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="index === tiers.length - 1"
                                        class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white disabled:opacity-20"
                                        aria-label="Move tier down"
                                        @click="moveTier(index, 1)"
                                    >
                                        <ArrowDown class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="tiers.length <= 2"
                                        class="rounded-lg p-2 text-zinc-500 hover:bg-red-500/10 hover:text-red-400 disabled:opacity-20"
                                        aria-label="Delete tier"
                                        @click="deleteTier(tier.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </section>

                        <button
                            type="button"
                            :disabled="tiers.length >= 12"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-zinc-300 hover:bg-zinc-900"
                            :class="
                                tiers.length >= 12
                                    ? 'cursor-not-allowed opacity-40'
                                    : ''
                            "
                            @click="addTier"
                        >
                            <Plus class="h-4 w-4" /> Add tier
                        </button>

                        <section
                            class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:rounded-3xl sm:p-6"
                            @dragover.prevent
                            @drop="dropGame(null)"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <h2 class="text-xl font-black">
                                        Unranked games
                                    </h2>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        Drag a cover or tap it, then choose a
                                        tier.
                                    </p>
                                </div>

                                <div class="relative w-full sm:max-w-sm">
                                    <Search
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-500"
                                    />
                                    <input
                                        v-model="query"
                                        type="search"
                                        maxlength="100"
                                        placeholder="Search games..."
                                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950 py-3 pr-4 pl-10 text-sm outline-none focus:border-zinc-500"
                                    />
                                    <div
                                        v-if="
                                            searching ||
                                            results.length ||
                                            query.trim().length >= 2
                                        "
                                        class="absolute top-full right-0 left-0 z-30 mt-2 max-h-80 overflow-y-auto rounded-xl border border-zinc-700 bg-zinc-950 p-2 shadow-2xl"
                                    >
                                        <p
                                            v-if="searching"
                                            class="p-3 text-sm text-zinc-500"
                                        >
                                            Searching...
                                        </p>
                                        <button
                                            v-for="game in results"
                                            :key="game.id"
                                            type="button"
                                            :disabled="
                                                items.some(
                                                    (item) =>
                                                        item.id === game.id,
                                                )
                                            "
                                            class="flex w-full items-center gap-3 rounded-lg p-2 text-left hover:bg-zinc-900 disabled:opacity-40"
                                            @click="addGame(game)"
                                        >
                                            <img
                                                v-if="game.cover_url"
                                                :src="game.cover_url"
                                                :alt="game.title"
                                                class="h-12 w-9 rounded object-cover"
                                            />
                                            <div
                                                v-else
                                                class="h-12 w-9 rounded bg-zinc-800"
                                            />
                                            <span class="text-sm font-bold">{{
                                                game.title
                                            }}</span>
                                        </button>
                                        <p
                                            v-if="!searching && !results.length"
                                            class="p-3 text-sm text-zinc-500"
                                        >
                                            No games found.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex min-h-28 flex-wrap gap-2">
                                <article
                                    v-for="game in unrankedItems"
                                    :key="game.id"
                                    draggable="true"
                                    class="group relative h-24 w-17 cursor-grab overflow-hidden rounded-lg border bg-zinc-800 active:cursor-grabbing sm:h-28 sm:w-20"
                                    :class="
                                        selectedItemId === game.id
                                            ? 'border-indigo-400 ring-2 ring-indigo-400/30'
                                            : 'border-zinc-700'
                                    "
                                    @dragstart="draggedItemId = game.id"
                                    @dragend="draggedItemId = null"
                                    @click="
                                        selectedItemId =
                                            selectedItemId === game.id
                                                ? null
                                                : game.id
                                    "
                                >
                                    <img
                                        v-if="game.cover_url"
                                        :src="game.cover_url"
                                        :alt="game.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center p-2 text-center text-[10px] text-zinc-400"
                                    >
                                        {{ game.title }}
                                    </div>
                                    <button
                                        type="button"
                                        class="absolute top-1 right-1 block rounded-md bg-black/80 p-1 text-white sm:hidden sm:group-hover:block"
                                        @click.stop="removeGame(game.id)"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </article>

                                <div
                                    v-if="!unrankedItems.length"
                                    class="flex w-full items-center justify-center rounded-xl border border-dashed border-zinc-700 text-sm text-zinc-500"
                                >
                                    Search and add games to start ranking.
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-4">
                        <div
                            class="flex min-h-40 items-center justify-center rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/30 text-xs font-bold tracking-[0.2em] text-zinc-600 uppercase xl:min-h-72"
                        >
                            Advertisement
                        </div>
                        <Link
                            href="/tier-lists"
                            class="flex items-center justify-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-zinc-300 hover:bg-zinc-900"
                        >
                            <LayoutDashboard class="h-4 w-4" />
                            Community tier lists
                        </Link>
                    </aside>
                </div>
            </main>
        </div>
    </div>
</template>
