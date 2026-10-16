<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Gamepad2, ListPlus, RefreshCw, Trash2, Unplug } from 'lucide-vue-next';
import { Browser } from '@capacitor/browser';
import { Capacitor } from '@capacitor/core';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';
import GameAddHeader from '@/components/game/GameAddHeader.vue';
import GameSearchResults from '@/components/game/GameSearchResults.vue';
import GameManualForm from '@/components/game/GameManualForm.vue';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },

    games: {
        type: Array,
        default: () => [],
    },
    nintendo: {
        type: Object,
        default: () => ({ connected: false, last_synced_at: null }),
    },
});

const title = ref('');
const publisher = ref('');
const developer = ref('');
const description = ref('');
const releaseDate = ref('');

const coverUrl = ref('');
const headerImageUrl = ref('');

const steamAppId = ref(null);
const igdbId = ref(null);
const igdbSlug = ref(null);
const igdbUrl = ref(null);

const source = ref('manual');
const platform = ref(null);
const nintendoTitleId = ref(null);

const steamResults = ref([]);
const igdbResults = ref([]);

const loadingSteam = ref(false);
const loadingIgdb = ref(false);
const submitting = ref(false);
const selectingResult = ref(false);

const errors = ref({});
const successMessage = ref('');
const bulkOpen = ref(false);
const bulkQuery = ref('');
const bulkSteamResults = ref([]);
const bulkIgdbResults = ref([]);
const bulkSelectedGames = ref([]);
const bulkSearching = ref(false);
const bulkSubmitting = ref(false);
const bulkErrors = ref({});
const bulkSuccess = ref('');
const bulkDuplicateNotice = ref('');
const nintendoLoginOpen = ref(false);
const nintendoCallbackUrl = ref('');
const nintendoError = ref('');
const nintendoProcessing = ref(false);
const catalogOpen = ref(false);
const catalogLoading = ref(false);
const catalogGames = ref([]);
const catalogPage = ref(1);
const catalogTotalPages = ref(1);
const catalogPlatform = ref('switch');
const catalogSearch = ref('');
const catalogError = ref('');

const filteredCatalogGames = computed(() => {
    const query = catalogSearch.value.trim().toLocaleLowerCase();

    if (!query) {
        return catalogGames.value;
    }

    return catalogGames.value.filter((game) =>
        [game.title, game.publisher, game.developer].some((value) =>
            String(value ?? '').toLocaleLowerCase().includes(query),
        ),
    );
});

const nintendoLastSynced = computed(() => {
    if (!props.nintendo.last_synced_at) {
        return null;
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(props.nintendo.last_synced_at));
});

let searchTimeout = null;
let searchController = null;
let bulkSearchTimeout = null;
let bulkSearchController = null;

const normalizeTitle = (value) => {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(
            /\b(the|game|edition|standard|deluxe|ultimate|complete)\b/g,
            '',
        )
        .trim()
        .replace(/\s+/g, ' ');
};

const countLettersAndNumbers = (value) => {
    return (String(value || '').match(/[\p{L}\p{N}]/gu) ?? []).length;
};

const containsExcessiveRepetition = (value) => {
    return /(.)\1{7,}/u.test(String(value || ''));
};

const hasTooManySymbols = (value) => {
    const text = String(value || '').trim();

    if (!text) {
        return false;
    }

    const symbols = (text.match(/[^\p{L}\p{N}\s]/gu) ?? []).length;

    return symbols / text.length > 0.4;
};

const isMeaningfulText = (value, minimumCharacters = 2) => {
    const text = String(value || '').trim();

    return (
        countLettersAndNumbers(text) >= minimumCharacters &&
        !containsExcessiveRepetition(text) &&
        !hasTooManySymbols(text)
    );
};

const isSearchableTitle = (value) => {
    const text = String(value || '').trim();

    return text.length >= 2 && text.length <= 255 && isMeaningfulText(text);
};

const isValidUrl = (value) => {
    if (!value) {
        return true;
    }

    try {
        const url = new URL(String(value).trim());

        return ['http:', 'https:'].includes(url.protocol);
    } catch {
        return false;
    }
};

watch(bulkQuery, (value) => {
    clearTimeout(bulkSearchTimeout);
    bulkSearchController?.abort();
    bulkErrors.value = {};
    bulkDuplicateNotice.value = '';

    if (value.trim()) {
        bulkSuccess.value = '';
    }

    if (!isSearchableTitle(value)) {
        bulkSteamResults.value = [];
        bulkIgdbResults.value = [];
        bulkSearching.value = false;

        return;
    }

    bulkSearchTimeout = setTimeout(async () => {
        bulkSearchController = new AbortController();
        bulkSearching.value = true;

        try {
            const query = encodeURIComponent(value.trim());
            const [steamResponse, igdbResponse] = await Promise.all([
                fetch(`/steam/search?q=${query}`, {
                    signal: bulkSearchController.signal,
                }),
                fetch(`/igdb/search?q=${query}`, {
                    signal: bulkSearchController.signal,
                }),
            ]);

            bulkSteamResults.value = steamResponse.ok
                ? await steamResponse.json()
                : [];
            bulkIgdbResults.value = igdbResponse.ok
                ? await igdbResponse.json()
                : [];
        } catch (error) {
            if (error.name !== 'AbortError') {
                bulkErrors.value = {
                    general: 'The game search could not be loaded.',
                };
            }
        } finally {
            bulkSearching.value = false;
            bulkSearchController = null;
        }
    }, 350);
});

const duplicate = computed(() => {
    const current = normalizeTitle(title.value);

    if (!current) {
        return false;
    }

    return props.games.some((game) => {
        const existingTitle = game.name ?? game.title ?? '';

        return normalizeTitle(existingTitle) === current;
    });
});

const loading = computed(() => {
    return loadingSteam.value || loadingIgdb.value;
});

const clearFieldError = (field) => {
    if (!errors.value[field]) {
        return;
    }

    const nextErrors = {
        ...errors.value,
    };

    delete nextErrors[field];

    errors.value = nextErrors;
};

const clearSearchResults = () => {
    steamResults.value = [];
    igdbResults.value = [];
};

const cancelPendingSearch = () => {
    clearTimeout(searchTimeout);

    if (searchController) {
        searchController.abort();
        searchController = null;
    }
};

const resetExternalSelection = () => {
    steamAppId.value = null;

    igdbId.value = null;
    igdbSlug.value = null;
    igdbUrl.value = null;
    nintendoTitleId.value = null;
    platform.value = null;

    source.value = 'manual';
};

const validate = () => {
    const validationErrors = {};

    const trimmedTitle = title.value.trim();
    const trimmedPublisher = publisher.value.trim();

    if (!trimmedTitle) {
        validationErrors.title = 'Game title is required.';
    } else if (trimmedTitle.length < 2) {
        validationErrors.title =
            'Game title must contain at least 2 characters.';
    } else if (trimmedTitle.length > 255) {
        validationErrors.title = 'Game title cannot exceed 255 characters.';
    } else if (!isMeaningfulText(trimmedTitle)) {
        validationErrors.title =
            'Enter a valid game title containing letters or numbers.';
    }

    if (trimmedPublisher.length > 255) {
        validationErrors.publisher = 'Publisher cannot exceed 255 characters.';
    } else if (trimmedPublisher && !isMeaningfulText(trimmedPublisher)) {
        validationErrors.publisher = 'Enter a valid publisher name.';
    }

    if (!isValidUrl(coverUrl.value)) {
        validationErrors.cover_url =
            'Cover URL must be a valid HTTP or HTTPS URL.';
    }

    errors.value = validationErrors;

    return Object.keys(validationErrors).length === 0;
};

watch(title, (value) => {
    cancelPendingSearch();
    clearFieldError('title');

    successMessage.value = '';

    if (selectingResult.value) {
        selectingResult.value = false;
        return;
    }

    resetExternalSelection();

    if (!isSearchableTitle(value)) {
        clearSearchResults();

        loadingSteam.value = false;
        loadingIgdb.value = false;

        return;
    }

    searchTimeout = setTimeout(async () => {
        const query = value.trim();

        searchController = new AbortController();

        loadingSteam.value = true;
        loadingIgdb.value = true;

        try {
            const encodedQuery = encodeURIComponent(query);

            const [steamResponse, igdbResponse] = await Promise.all([
                fetch(`/steam/search?q=${encodedQuery}`, {
                    signal: searchController.signal,
                }),

                fetch(`/igdb/search?q=${encodedQuery}`, {
                    signal: searchController.signal,
                }),
            ]);

            steamResults.value = steamResponse.ok
                ? await steamResponse.json()
                : [];

            igdbResults.value = igdbResponse.ok
                ? await igdbResponse.json()
                : [];
        } catch (error) {
            if (error.name !== 'AbortError') {
                clearSearchResults();
            }
        } finally {
            loadingSteam.value = false;
            loadingIgdb.value = false;
            searchController = null;
        }
    }, 350);
});

watch(publisher, () => {
    clearFieldError('publisher');
});

watch(developer, () => {
    clearFieldError('developer');
});

watch(description, () => {
    clearFieldError('description');
});

watch(releaseDate, () => {
    clearFieldError('release_date');
});

watch(coverUrl, () => {
    clearFieldError('cover_url');
});

watch(headerImageUrl, () => {
    clearFieldError('header_image_url');
});

function selectSteamGame(game) {
    cancelPendingSearch();

    selectingResult.value = true;
    errors.value = {};
    successMessage.value = '';

    title.value = game.title ?? '';

    coverUrl.value = game.cover_url ?? '';

    headerImageUrl.value = game.header_image_url ?? game.cover_url ?? '';

    steamAppId.value = game.appid ?? null;

    igdbId.value = null;
    igdbSlug.value = null;
    igdbUrl.value = null;

    publisher.value = game.publisher ?? '';

    developer.value = game.developer ?? '';

    description.value = game.description ?? '';

    releaseDate.value = game.release_date ?? '';

    source.value = 'steam';
    platform.value = 'PC';

    clearSearchResults();
}

function selectIgdbGame(game) {
    cancelPendingSearch();

    selectingResult.value = true;
    errors.value = {};
    successMessage.value = '';

    title.value = game.title ?? '';

    coverUrl.value = game.cover_url ?? '';

    headerImageUrl.value = game.header_image_url ?? game.cover_url ?? '';

    steamAppId.value = null;

    igdbId.value = game.igdb_id ?? null;

    igdbSlug.value = game.slug ?? game.igdb_slug ?? null;

    igdbUrl.value =
        game.igdb_url ??
        (igdbSlug.value
            ? `https://www.igdb.com/games/${igdbSlug.value}`
            : null);

    description.value = game.description ?? '';

    releaseDate.value = game.release_date ?? '';

    developer.value = game.developer ?? '';

    publisher.value = game.publisher ?? '';

    source.value = 'igdb';

    clearSearchResults();
}

function submit() {
    successMessage.value = '';

    if (submitting.value || !validate()) {
        return;
    }

    submitting.value = true;

    router.post(
        '/games',
        {
            title: title.value.trim(),

            publisher: publisher.value.trim() || null,

            developer: developer.value.trim() || null,

            description: description.value.trim() || null,

            release_date: releaseDate.value || null,

            cover_url: coverUrl.value.trim() || null,

            header_image_url: headerImageUrl.value.trim() || null,

            steam_app_id: steamAppId.value,

            igdb_id: igdbId.value,

            igdb_slug: igdbSlug.value,

            igdb_url: igdbUrl.value,

            nintendo_title_id: nintendoTitleId.value,

            platform: platform.value,

            source: source.value,
        },
        {
            preserveScroll: true,

            onError(serverErrors) {
                errors.value = {
                    ...serverErrors,
                    general:
                        Object.values(serverErrors)[0] ??
                        'The game could not be added.',
                };
            },

            onSuccess() {
                errors.value = {};

                successMessage.value = 'Game added successfully.';
            },

            onFinish() {
                submitting.value = false;
            },
        },
    );
}

function submitBulk() {
    if (!bulkSelectedGames.value.length || bulkSubmitting.value) {
        return;
    }

    bulkSubmitting.value = true;
    bulkErrors.value = {};
    bulkSuccess.value = '';
    bulkDuplicateNotice.value = '';
    const submittedCount = bulkSelectedGames.value.length;

    router.post(
        '/games/bulk',
        { games: bulkSelectedGames.value },
        {
            preserveScroll: true,
            onError(serverErrors) {
                bulkErrors.value = {
                    ...serverErrors,
                    general:
                        Object.values(serverErrors)[0] ??
                        'The games could not be added.',
                };
            },
            onSuccess() {
                bulkQuery.value = '';
                bulkSelectedGames.value = [];
                bulkSteamResults.value = [];
                bulkIgdbResults.value = [];
                bulkSuccess.value = `${submittedCount} ${submittedCount === 1 ? 'game was' : 'games were'} added successfully.`;
            },
            onFinish() {
                bulkSubmitting.value = false;
            },
        },
    );
}

function addBulkGame(game, selectedSource) {
    const title = String(game.title ?? '').trim();
    const normalized = normalizeTitle(title);

    if (!normalized) {
        return;
    }

    if (bulkSelectedGames.value.length >= 100) {
        bulkErrors.value = { general: 'You can add up to 100 games at once.' };

        return;
    }

    const alreadyInLibrary = props.games.some((existingGame) =>
        normalizeTitle(existingGame.name ?? existingGame.title ?? '') ===
        normalized,
    );
    const alreadySelected = bulkSelectedGames.value.some(
        (selectedGame) => normalizeTitle(selectedGame.title) === normalized,
    );

    if (alreadyInLibrary || alreadySelected) {
        bulkErrors.value = {};
        bulkDuplicateNotice.value = alreadyInLibrary
            ? `“${title}” is already in your library.`
            : `“${title}” is already on your selected games list.`;

        return;
    }

    bulkSelectedGames.value.push({
        title,
        igdb_id: selectedSource === 'igdb' ? game.igdb_id ?? null : null,
        igdb_slug:
            selectedSource === 'igdb'
                ? game.slug ?? game.igdb_slug ?? null
                : null,
        igdb_url:
            selectedSource === 'igdb'
                ? game.igdb_url ??
                  (game.slug
                      ? `https://www.igdb.com/games/${game.slug}`
                      : null)
                : null,
        publisher: game.publisher ?? null,
        developer: game.developer ?? null,
        description: game.description ?? null,
        release_date: game.release_date ?? null,
        cover_url: game.igdb_cover_url ?? game.cover_url ?? null,
        header_image_url:
            game.header_image_url ??
            game.igdb_cover_url ??
            game.cover_url ??
            null,
        source: selectedSource,
    });
    bulkErrors.value = {};
    bulkSuccess.value = '';
    bulkDuplicateNotice.value = '';
    bulkQuery.value = '';
    bulkSteamResults.value = [];
    bulkIgdbResults.value = [];
}

function removeBulkGame(index) {
    bulkSelectedGames.value.splice(index, 1);
}

async function startNintendoLogin() {
    const loginWindow = Capacitor.isNativePlatform()
        ? null
        : window.open('', '_blank');
    nintendoError.value = '';

    try {
        const response = await fetch('/games/nintendo/connect', {
            headers: { Accept: 'application/json' },
        });
        const payload = response.ok ? await response.json() : null;

        if (!payload?.login_url) {
            throw new Error();
        }

        if (Capacitor.isNativePlatform()) {
            await Browser.open({ url: payload.login_url });
        } else if (loginWindow) {
            loginWindow.opener = null;
            loginWindow.location.href = payload.login_url;
        } else {
            window.location.href = payload.login_url;
        }

        nintendoLoginOpen.value = true;
    } catch {
        loginWindow?.close();
        nintendoError.value = 'Could not start Nintendo login. Try again.';
    }
}

function finishNintendoLogin() {
    if (!nintendoCallbackUrl.value.trim() || nintendoProcessing.value) {
        return;
    }

    nintendoProcessing.value = true;
    nintendoError.value = '';
    router.post(
        '/games/nintendo/connect',
        { callback_url: nintendoCallbackUrl.value.trim() },
        {
            preserveScroll: true,
            onError: (serverErrors) => {
                nintendoError.value = serverErrors.callback_url ?? 'Nintendo connection failed.';
            },
            onFinish: () => (nintendoProcessing.value = false),
        },
    );
}

function syncNintendo() {
    nintendoProcessing.value = true;
    nintendoError.value = '';
    router.post('/games/nintendo/sync', {}, {
        preserveScroll: true,
        onError: (serverErrors) => {
            nintendoError.value = serverErrors.nintendo ?? 'Nintendo sync failed.';
        },
        onFinish: () => (nintendoProcessing.value = false),
    });
}

function disconnectNintendo() {
    router.delete('/games/nintendo', { preserveScroll: true });
}

async function loadNintendoCatalog(page = 1) {
    catalogLoading.value = true;
    catalogError.value = '';

    try {
        const response = await fetch(`/games/nintendo/catalog?page=${page}&platform=${catalogPlatform.value}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload?.games) {
            throw new Error(payload?.message ?? 'Could not load the Nintendo catalog.');
        }

        catalogGames.value = payload.games;
        catalogPage.value = payload.pagination?.page ?? page;
        catalogTotalPages.value = payload.pagination?.totalPages ?? 1;
        catalogOpen.value = true;
    } catch (error) {
        catalogError.value =
            error instanceof Error
                ? error.message
                : 'Could not load the Nintendo catalog.';
    } finally {
        catalogLoading.value = false;
    }
}

function selectNintendoCatalogGame(game) {
    selectingResult.value = true;
    title.value = game.title ?? '';
    publisher.value = game.publisher ?? '';
    developer.value = game.developer ?? '';
    description.value = game.description ?? '';
    releaseDate.value = game.release_date ?? '';
    coverUrl.value = game.cover_url ?? '';
    headerImageUrl.value = game.header_image_url ?? game.cover_url ?? '';
    platform.value = game.platform ?? 'Nintendo Switch';
    nintendoTitleId.value = game.nintendo_title_id ?? null;
    steamAppId.value = null;
    igdbId.value = null;
    igdbSlug.value = null;
    igdbUrl.value = null;
    source.value = 'nintendo';
    catalogOpen.value = false;
    window.scrollTo({ top: 650, behavior: 'smooth' });
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="mx-auto w-full max-w-7xl flex-1 p-8">
                <GameAddHeader />

                <!-- Nintendo integration temporarily disabled.
                <section class="mb-8 rounded-3xl border border-red-950/70 bg-gradient-to-br from-red-950/30 to-zinc-900/60 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-5">
                        <div class="flex gap-3">
                            <span class="h-fit rounded-xl bg-red-500/10 p-2 text-red-300"><Gamepad2 class="h-5 w-5" /></span>
                            <div>
                                <strong class="block text-white">Nintendo Switch play history</strong>
                                <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-400">
                                    Import games missing from your Curator library as custom games, including Nintendo artwork, platform and playtime. Existing Steam and custom titles are skipped.
                                </p>
                                <p v-if="nintendoLastSynced" class="mt-2 text-xs text-zinc-500">Last synced {{ nintendoLastSynced }}</p>
                            </div>
                        </div>
                        <div v-if="nintendo.connected" class="flex flex-wrap gap-2">
                            <button type="button" :disabled="nintendoProcessing" class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50" @click="syncNintendo">
                                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': nintendoProcessing }" />
                                {{ nintendoProcessing ? 'Syncing...' : 'Sync now' }}
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2.5 text-sm font-bold text-zinc-300" @click="disconnectNintendo">
                                <Unplug class="h-4 w-4" /> Disconnect
                            </button>
                        </div>
                        <button v-else type="button" class="rounded-xl bg-red-500 px-4 py-2.5 text-sm font-bold text-white" @click="startNintendoLogin">Connect Nintendo</button>
                    </div>

                    <form v-if="nintendoLoginOpen && !nintendo.connected" class="mt-5 border-t border-zinc-800 pt-5" @submit.prevent="finishNintendoLogin">
                        <label for="nintendo-callback" class="block text-sm font-semibold text-zinc-200">Paste the “Select this person” link</label>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">After signing in, right-click or hold the red button on Nintendo's page, copy its link address, and paste the complete <code>npf...://auth</code> URL here.</p>
                        <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                            <input id="nintendo-callback" v-model="nintendoCallbackUrl" type="text" autocomplete="off" placeholder="npf5c38e31cd085304b://auth#..." class="min-w-0 flex-1 rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-red-400" />
                            <button type="submit" :disabled="!nintendoCallbackUrl.trim() || nintendoProcessing" class="rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950 disabled:opacity-40">{{ nintendoProcessing ? 'Importing...' : 'Finish and import' }}</button>
                        </div>
                    </form>
                    <p v-if="nintendoError" class="mt-3 text-sm text-red-400">{{ nintendoError }}</p>
                </section>

                <section class="mb-8 overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
                        <div>
                            <strong class="block text-white">Add from Nintendo catalog</strong>
                            <small class="text-zinc-500">Choose a game to fill its title, artwork and details automatically.</small>
                        </div>
                        <div class="flex flex-1 flex-wrap justify-end gap-2">
                            <label class="relative min-w-56 flex-1 sm:max-w-xs">
                                <span class="sr-only">Search Nintendo games</span>
                                <input v-model="catalogSearch" type="search" placeholder="Search games..." class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-2 text-sm text-white outline-none placeholder:text-zinc-600 focus:border-red-400" />
                            </label>
                            <select v-model="catalogPlatform" class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white" @change="loadNintendoCatalog(1)">
                                <option value="switch">Nintendo Switch</option>
                                <option value="switch2">Nintendo Switch 2</option>
                            </select>
                            <button type="button" :disabled="catalogLoading" class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 disabled:opacity-50" @click="loadNintendoCatalog(catalogPage)">
                                {{ catalogLoading ? 'Loading...' : catalogOpen ? 'Refresh' : 'Browse games' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="catalogOpen" class="border-t border-zinc-800 p-5 sm:p-6">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <button v-for="game in filteredCatalogGames" :key="game.nintendo_title_id" type="button" class="group flex gap-3 rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3 text-left hover:border-red-500/60" @click="selectNintendoCatalogGame(game)">
                                <img v-if="game.cover_url" :src="game.cover_url" :alt="game.title" class="h-20 w-16 shrink-0 rounded-lg object-cover" loading="lazy" />
                                <div class="min-w-0">
                                    <strong class="line-clamp-2 text-sm text-white group-hover:text-red-300">{{ game.title }}</strong>
                                    <span class="mt-1 block truncate text-xs text-zinc-500">{{ game.publisher || game.platform }}</span>
                                    <span class="mt-2 block text-xs font-bold text-red-400">Select game</span>
                                </div>
                            </button>
                        </div>
                        <div v-if="catalogSearch && !filteredCatalogGames.length" class="rounded-2xl border border-dashed border-zinc-800 px-5 py-10 text-center text-sm text-zinc-500">
                            No matching games on this page. Try another page or platform.
                        </div>
                        <div class="mt-5 flex items-center justify-between">
                            <button type="button" :disabled="catalogPage <= 1 || catalogLoading" class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 disabled:opacity-30" @click="loadNintendoCatalog(catalogPage - 1)">Previous</button>
                            <span class="text-xs text-zinc-500">Page {{ catalogPage }} of {{ catalogTotalPages }}</span>
                            <button type="button" :disabled="catalogPage >= catalogTotalPages || catalogLoading" class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 disabled:opacity-30" @click="loadNintendoCatalog(catalogPage + 1)">Next</button>
                        </div>
                    </div>
                    <p v-if="catalogError" class="border-t border-red-950/60 bg-red-950/20 px-5 py-3 text-sm text-red-300 sm:px-6">
                        {{ catalogError }}
                    </p>
                </section>
                -->

                <section
                    class="mb-8 overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/60"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 p-5 text-left sm:p-6"
                        @click="bulkOpen = !bulkOpen"
                    >
                        <span class="flex items-center gap-3">
                            <span
                                class="rounded-xl bg-indigo-500/10 p-2 text-indigo-300"
                                ><ListPlus class="h-5 w-5"
                            /></span>
                            <span
                                ><strong class="block text-white"
                                    >Add multiple custom games</strong
                                ><small class="text-zinc-500"
                                    >Build a list by selecting games from
                                    search.</small
                                ></span
                            >
                        </span>
                        <span class="text-sm font-bold text-indigo-300">{{
                            bulkOpen ? 'Hide' : 'Open'
                        }}</span>
                    </button>

                    <form
                        v-if="bulkOpen"
                        class="space-y-5 border-t border-zinc-800 p-5 sm:p-6"
                        @submit.prevent="submitBulk"
                    >
                        <div>
                            <label for="bulk-game-search" class="mb-2 block text-sm font-semibold text-zinc-200">
                                Search for a game
                            </label>
                            <input
                                id="bulk-game-search"
                                v-model="bulkQuery"
                                type="search"
                                maxlength="255"
                                autocomplete="off"
                                placeholder="Start typing a game title..."
                                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-white outline-none placeholder:text-zinc-600 focus:border-indigo-400"
                            />
                        </div>

                        <GameSearchResults
                            :steam-results="bulkSteamResults"
                            :igdb-results="bulkIgdbResults"
                            :loading="bulkSearching"
                            :duplicate="false"
                            compact
                            @select-steam="addBulkGame($event, 'steam')"
                            @select-igdb="addBulkGame($event, 'igdb')"
                        />

                        <div v-if="bulkSelectedGames.length" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm text-white">Selected games</strong>
                                <span class="text-xs text-zinc-500">{{ bulkSelectedGames.length }}/100</span>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="(game, index) in bulkSelectedGames"
                                    :key="`${game.source}-${game.igdb_id || game.title}`"
                                    class="flex min-w-0 items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-950 p-3"
                                >
                                    <img
                                        v-if="game.cover_url"
                                        :src="game.cover_url"
                                        :alt="game.title"
                                        class="h-14 w-14 shrink-0 rounded-xl object-cover"
                                    />
                                    <div v-else class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-[10px] font-bold uppercase text-zinc-500">
                                        {{ game.source }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-white">{{ game.title }}</p>
                                        <p class="mt-1 text-xs uppercase text-zinc-500">{{ game.source }}</p>
                                    </div>
                                    <button type="button" :aria-label="`Remove ${game.title}`" class="shrink-0 rounded-lg p-2 text-zinc-500 hover:bg-red-500/10 hover:text-red-400" @click="removeBulkGame(index)">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="bulkDuplicateNotice"
                            role="status"
                            class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-200"
                        >
                            {{ bulkDuplicateNotice }}
                        </div>
                        <div
                            v-if="bulkErrors.general || bulkErrors.games"
                            role="alert"
                            class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300"
                        >
                            {{ bulkErrors.general || bulkErrors.games }}
                        </div>
                        <div v-if="bulkSuccess" role="status" class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-300">
                            {{ bulkSuccess }}
                        </div>
                        <button
                            type="submit"
                            :disabled="
                                !bulkSelectedGames.length || bulkSubmitting
                            "
                            class="rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950 disabled:opacity-40"
                        >
                            {{
                                bulkSubmitting
                                    ? 'Adding games...'
                                    : `Add ${bulkSelectedGames.length} games`
                            }}
                        </button>
                    </form>
                </section>

                <div class="grid gap-8 2xl:grid-cols-[minmax(0,1fr)_380px]">
                    <GameSearchResults
                        :steam-results="steamResults"
                        :igdb-results="igdbResults"
                        :loading="loading"
                        :duplicate="duplicate"
                        @select-steam="selectSteamGame"
                        @select-igdb="selectIgdbGame"
                    >
                        <div>
                            <label
                                for="game-title"
                                class="mb-2 block text-sm font-medium text-zinc-300"
                            >
                                Game title
                            </label>

                            <input
                                id="game-title"
                                v-model="title"
                                type="text"
                                maxlength="255"
                                autocomplete="off"
                                placeholder="e.g. Pokémon Shield"
                                :aria-invalid="Boolean(errors.title)"
                                :class="[
                                    'w-full rounded-2xl border bg-zinc-900 px-5 py-4 text-white outline-none placeholder:text-zinc-500',
                                    errors.title
                                        ? 'border-red-500 focus:border-red-400'
                                        : 'border-zinc-800 focus:border-zinc-600',
                                ]"
                            />

                            <p
                                v-if="errors.title"
                                class="mt-2 text-sm text-red-400"
                            >
                                {{ errors.title }}
                            </p>

                            <p
                                v-else-if="title && !isSearchableTitle(title)"
                                class="mt-2 text-sm text-amber-400"
                            >
                                Enter a meaningful title containing letters or
                                numbers.
                            </p>
                        </div>
                    </GameSearchResults>

                    <GameManualForm
                        v-model:title="title"
                        v-model:publisher="publisher"
                        v-model:developer="developer"
                        v-model:description="description"
                        v-model:release-date="releaseDate"
                        v-model:cover-url="coverUrl"
                        v-model:header-image-url="headerImageUrl"
                        :steam-app-id="steamAppId"
                        :igdb-id="igdbId"
                        :duplicate="duplicate"
                        :errors="errors"
                        :success="successMessage"
                        :submitting="submitting"
                        @submit="submit"
                    />
                </div>
            </main>
        </div>
    </div>
</template>
