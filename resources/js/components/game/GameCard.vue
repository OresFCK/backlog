<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

import StatusBadge from '@/components/game/StatusBadge.vue'

const props = defineProps({
    game: {
        type: Object,
        required: true,
    },
})

const imageIndex = ref(0)

const steamAppId = computed(() => {
    const appid = props.game.appid

    if (!appid) {
        return null
    }

    return /^\d+$/.test(String(appid))
        ? String(appid)
        : null
})

const imageUrls = computed(() => {
    const urls = [
        props.game.cover_url,
        props.game.header_image,
        props.game.header_image_url,
        props.game.capsule,
    ].filter(Boolean)

    if (steamAppId.value) {
        urls.push(
            `https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/${steamAppId.value}/header.jpg`,
            `https://cdn.cloudflare.steamstatic.com/steam/apps/${steamAppId.value}/header.jpg`,
            `https://cdn.cloudflare.steamstatic.com/steam/apps/${steamAppId.value}/library_600x900.jpg`,
            `https://cdn.cloudflare.steamstatic.com/steam/apps/${steamAppId.value}/capsule_616x353.jpg`
        )
    }

    urls.push(
        'https://placehold.co/600x338?text=No+Image'
    )

    return [...new Set(urls)]
})

const imageUrl = computed(() => {
    return imageUrls.value[
        Math.min(
            imageIndex.value,
            imageUrls.value.length - 1
        )
    ]
})

function handleImageError() {
    if (
        imageIndex.value <
        imageUrls.value.length - 1
    ) {
        imageIndex.value++
    }
}

const gameTitle = computed(() => {
    return (
        props.game.title ??
        props.game.name ??
        'Unknown game'
    )
})

const gameHref = computed(() => {
    const id =
        props.game.id ??
        props.game.appid

    return `/games/${id}`
})
</script>

<template>
    <Link
        :href="gameHref"
        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800"
    >
        <div
            class="aspect-[16/9] overflow-hidden bg-zinc-950"
        >
            <img
                :src="imageUrl"
                :alt="gameTitle"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                @error="handleImageError"
            />
        </div>

        <div
            class="flex flex-1 items-center justify-between gap-3 p-4"
        >
            <h3
                class="line-clamp-1 text-sm font-bold text-white"
            >
                {{ gameTitle }}
            </h3>

            <StatusBadge
                v-if="game.status"
                :status="game.status"
                :color="
                    game.status_color ??
                    '#71717a'
                "
            />
        </div>
    </Link>
</template>