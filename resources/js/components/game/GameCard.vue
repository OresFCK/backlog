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
    if (props.game.is_custom) {
        return null
    }

    return props.game.appid ?? props.game.id
})

const imageUrls = computed(() => {
    const urls = [
        props.game.cover_url,
        props.game.header_image,
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

    urls.push('https://placehold.co/600x338?text=No+Image')

    return [...new Set(urls)]
})

const imageUrl = computed(() => imageUrls.value[imageIndex.value])

function handleImageError() {
    if (imageIndex.value < imageUrls.value.length - 1) {
        imageIndex.value++
    }
}
</script>

<template>
    <Link
        :href="`/games/${game.id ?? game.appid}`"
        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800"
    >
        <div class="aspect-[16/9] overflow-hidden bg-zinc-950">
            <img
                :src="imageUrl"
                :alt="game.title || game.name"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                @error="handleImageError"
            />
        </div>

        <div class="flex flex-1 items-center justify-between gap-3 p-4">
            <h3 class="line-clamp-1 text-sm font-bold text-white">
                {{ game.title || game.name }}
            </h3>

            <StatusBadge
                v-if="game.status"
                :status="game.status"
                :color="game.status_color"
            />
        </div>
    </Link>
</template>