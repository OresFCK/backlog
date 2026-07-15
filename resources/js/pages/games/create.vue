<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'
import GameAddHeader from '@/components/game/GameAddHeader.vue'
import GameSearchResults from '@/components/game/GameSearchResults.vue'
import GameManualForm from '@/components/game/GameManualForm.vue'

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },

    games: {
        type: Array,
        default: () => [],
    },
})

const title = ref('')
const publisher = ref('')
const developer = ref('')
const description = ref('')
const releaseDate = ref('')

const coverUrl = ref('')
const headerImageUrl = ref('')

const steamAppId = ref(null)
const igdbId = ref(null)
const igdbSlug = ref(null)
const igdbUrl = ref(null)

const source = ref('manual')

const steamResults = ref([])
const igdbResults = ref([])

const loadingSteam = ref(false)
const loadingIgdb = ref(false)
const submitting = ref(false)
const selectingResult = ref(false)

const errors = ref({})
const successMessage = ref('')

let searchTimeout = null
let searchController = null

const normalizeTitle = (value) => {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(
            /\b(the|game|edition|standard|deluxe|ultimate|complete)\b/g,
            ''
        )
        .trim()
        .replace(/\s+/g, ' ')
}

const countLettersAndNumbers = (value) => {
    return (
        String(value || '').match(/[\p{L}\p{N}]/gu) ?? []
    ).length
}

const containsExcessiveRepetition = (value) => {
    return /(.)\1{7,}/u.test(String(value || ''))
}

const hasTooManySymbols = (value) => {
    const text = String(value || '').trim()

    if (!text) {
        return false
    }

    const symbols = (
        text.match(/[^\p{L}\p{N}\s]/gu) ?? []
    ).length

    return symbols / text.length > 0.4
}

const isMeaningfulText = (
    value,
    minimumCharacters = 2
) => {
    const text = String(value || '').trim()

    return (
        countLettersAndNumbers(text) >=
            minimumCharacters &&
        !containsExcessiveRepetition(text) &&
        !hasTooManySymbols(text)
    )
}

const isSearchableTitle = (value) => {
    const text = String(value || '').trim()

    return (
        text.length >= 2 &&
        text.length <= 255 &&
        isMeaningfulText(text)
    )
}

const isValidUrl = (value) => {
    if (!value) {
        return true
    }

    try {
        const url = new URL(String(value).trim())

        return ['http:', 'https:'].includes(
            url.protocol
        )
    } catch {
        return false
    }
}

const isValidDate = (value) => {
    if (!value) {
        return true
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return false
    }

    const date = new Date(`${value}T00:00:00`)

    return (
        !Number.isNaN(date.getTime()) &&
        date.toISOString().slice(0, 10) === value
    )
}

const duplicate = computed(() => {
    const current = normalizeTitle(title.value)

    if (!current) {
        return false
    }

    return props.games.some((game) => {
        const existingTitle =
            game.name ??
            game.title ??
            ''

        return (
            normalizeTitle(existingTitle) === current
        )
    })
})

const loading = computed(() => {
    return (
        loadingSteam.value ||
        loadingIgdb.value
    )
})

const clearFieldError = (field) => {
    if (!errors.value[field]) {
        return
    }

    const nextErrors = {
        ...errors.value,
    }

    delete nextErrors[field]

    errors.value = nextErrors
}

const clearSearchResults = () => {
    steamResults.value = []
    igdbResults.value = []
}

const cancelPendingSearch = () => {
    clearTimeout(searchTimeout)

    if (searchController) {
        searchController.abort()
        searchController = null
    }
}

const resetExternalSelection = () => {
    steamAppId.value = null

    igdbId.value = null
    igdbSlug.value = null
    igdbUrl.value = null

    source.value = 'manual'
}

const validate = () => {
    const validationErrors = {}

    const trimmedTitle = title.value.trim()
    const trimmedPublisher =
        publisher.value.trim()
    const trimmedDeveloper =
        developer.value.trim()
    const trimmedDescription =
        description.value.trim()

    if (!trimmedTitle) {
        validationErrors.title =
            'Game title is required.'
    } else if (trimmedTitle.length < 2) {
        validationErrors.title =
            'Game title must contain at least 2 characters.'
    } else if (trimmedTitle.length > 255) {
        validationErrors.title =
            'Game title cannot exceed 255 characters.'
    } else if (!isMeaningfulText(trimmedTitle)) {
        validationErrors.title =
            'Enter a valid game title containing letters or numbers.'
    } else if (duplicate.value) {
        validationErrors.title =
            'This game already exists in your library.'
    }

    if (trimmedPublisher.length > 255) {
        validationErrors.publisher =
            'Publisher cannot exceed 255 characters.'
    } else if (
        trimmedPublisher &&
        !isMeaningfulText(trimmedPublisher)
    ) {
        validationErrors.publisher =
            'Enter a valid publisher name.'
    }

    if (trimmedDeveloper.length > 255) {
        validationErrors.developer =
            'Developer cannot exceed 255 characters.'
    } else if (
        trimmedDeveloper &&
        !isMeaningfulText(trimmedDeveloper)
    ) {
        validationErrors.developer =
            'Enter a valid developer name.'
    }

    if (trimmedDescription.length > 5000) {
        validationErrors.description =
            'Description cannot exceed 5,000 characters.'
    }

    if (!isValidDate(releaseDate.value)) {
        validationErrors.release_date =
            'Release date must be a valid date.'
    }

    if (!isValidUrl(coverUrl.value)) {
        validationErrors.cover_url =
            'Cover URL must be a valid HTTP or HTTPS URL.'
    }

    if (!isValidUrl(headerImageUrl.value)) {
        validationErrors.header_image_url =
            'Header image URL must be a valid HTTP or HTTPS URL.'
    }

    if (
        steamAppId.value !== null &&
        !/^\d+$/.test(String(steamAppId.value))
    ) {
        validationErrors.steam_app_id =
            'Steam App ID must be numeric.'
    }

    if (
        igdbId.value !== null &&
        !/^\d+$/.test(String(igdbId.value))
    ) {
        validationErrors.igdb_id =
            'IGDB ID must be numeric.'
    }

    if (!isValidUrl(igdbUrl.value)) {
        validationErrors.igdb_url =
            'IGDB URL must be a valid HTTP or HTTPS URL.'
    }

    errors.value = validationErrors

    return (
        Object.keys(validationErrors).length === 0
    )
}

watch(title, (value) => {
    cancelPendingSearch()
    clearFieldError('title')

    successMessage.value = ''

    if (selectingResult.value) {
        selectingResult.value = false
        return
    }

    resetExternalSelection()

    if (!isSearchableTitle(value)) {
        clearSearchResults()

        loadingSteam.value = false
        loadingIgdb.value = false

        return
    }

    searchTimeout = setTimeout(async () => {
        const query = value.trim()

        searchController = new AbortController()

        loadingSteam.value = true
        loadingIgdb.value = true

        try {
            const encodedQuery =
                encodeURIComponent(query)

            const [
                steamResponse,
                igdbResponse,
            ] = await Promise.all([
                fetch(
                    `/steam/search?q=${encodedQuery}`,
                    {
                        signal:
                            searchController.signal,
                    }
                ),

                fetch(
                    `/igdb/search?q=${encodedQuery}`,
                    {
                        signal:
                            searchController.signal,
                    }
                ),
            ])

            steamResults.value =
                steamResponse.ok
                    ? await steamResponse.json()
                    : []

            igdbResults.value =
                igdbResponse.ok
                    ? await igdbResponse.json()
                    : []
        } catch (error) {
            if (error.name !== 'AbortError') {
                clearSearchResults()
            }
        } finally {
            loadingSteam.value = false
            loadingIgdb.value = false
            searchController = null
        }
    }, 350)
})

watch(publisher, () => {
    clearFieldError('publisher')
})

watch(developer, () => {
    clearFieldError('developer')
})

watch(description, () => {
    clearFieldError('description')
})

watch(releaseDate, () => {
    clearFieldError('release_date')
})

watch(coverUrl, () => {
    clearFieldError('cover_url')
})

watch(headerImageUrl, () => {
    clearFieldError('header_image_url')
})

function selectSteamGame(game) {
    cancelPendingSearch()

    selectingResult.value = true
    errors.value = {}
    successMessage.value = ''

    title.value = game.title ?? ''

    coverUrl.value =
        game.cover_url ??
        ''

    headerImageUrl.value =
        game.header_image_url ??
        game.cover_url ??
        ''

    steamAppId.value =
        game.appid ??
        null

    igdbId.value = null
    igdbSlug.value = null
    igdbUrl.value = null

    publisher.value =
        game.publisher ??
        ''

    developer.value =
        game.developer ??
        ''

    description.value =
        game.description ??
        ''

    releaseDate.value =
        game.release_date ??
        ''

    source.value = 'steam'

    clearSearchResults()
}

function selectIgdbGame(game) {
    cancelPendingSearch()

    selectingResult.value = true
    errors.value = {}
    successMessage.value = ''

    title.value = game.title ?? ''

    coverUrl.value =
        game.cover_url ??
        ''

    headerImageUrl.value =
        game.header_image_url ??
        game.cover_url ??
        ''

    steamAppId.value = null

    igdbId.value =
        game.igdb_id ??
        null

    igdbSlug.value =
        game.slug ??
        game.igdb_slug ??
        null

    igdbUrl.value =
        game.igdb_url ??
        (
            igdbSlug.value
                ? `https://www.igdb.com/games/${igdbSlug.value}`
                : null
        )

    description.value =
        game.description ??
        ''

    releaseDate.value =
        game.release_date ??
        ''

    developer.value =
        game.developer ??
        ''

    publisher.value =
        game.publisher ??
        ''

    source.value = 'igdb'

    clearSearchResults()
}

function submit() {
    successMessage.value = ''

    if (submitting.value || !validate()) {
        return
    }

    submitting.value = true

    router.post(
        '/games',
        {
            title: title.value.trim(),

            publisher:
                publisher.value.trim() ||
                null,

            developer:
                developer.value.trim() ||
                null,

            description:
                description.value.trim() ||
                null,

            release_date:
                releaseDate.value ||
                null,

            cover_url:
                coverUrl.value.trim() ||
                null,

            header_image_url:
                headerImageUrl.value.trim() ||
                null,

            steam_app_id:
                steamAppId.value,

            igdb_id:
                igdbId.value,

            igdb_slug:
                igdbSlug.value,

            igdb_url:
                igdbUrl.value,

            source:
                source.value,
        },
        {
            preserveScroll: true,

            onError(serverErrors) {
                errors.value = serverErrors
            },

            onSuccess() {
                errors.value = {}

                successMessage.value =
                    'Game added successfully.'
            },

            onFinish() {
                submitting.value = false
            },
        }
    )
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main
                class="mx-auto w-full max-w-7xl flex-1 p-8"
            >
                <GameAddHeader />

                <div
                    class="grid gap-8 2xl:grid-cols-[minmax(0,1fr)_380px]"
                >
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
                                :aria-invalid="
                                    Boolean(errors.title)
                                "
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
                                v-else-if="
                                    title &&
                                    !isSearchableTitle(title)
                                "
                                class="mt-2 text-sm text-amber-400"
                            >
                                Enter a meaningful title
                                containing letters or numbers.
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
                        v-model:header-image-url="
                            headerImageUrl
                        "
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