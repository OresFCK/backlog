<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import GameHero from '@/components/games/GameHero.vue'
import GameNotes from '@/components/games/GameNotes.vue'
import GameMetaPanel from '@/components/games/GameMetaPanel.vue'
import GameStats from '@/components/games/GameStats.vue'
import GameGallery from '@/components/games/GameGallery.vue'
import GameInfoSidebar from '@/components/games/GameInfoSidebar.vue'
import PublicReviewModal from '@/components/games/PublicReviewModal.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    game: {
        type: Object,
        required: true,
    },

    statuses: {
        type: Array,
        default: () => [],
    },
})

const note = ref(props.game.note ?? '')
const rating = ref(props.game.rating ? String(props.game.rating) : '')
const recommended = ref(props.game.recommended ?? false)
const notRecommended = ref(props.game.not_recommended ?? false)
const status = ref('')
const showOnPublicProfile = ref(props.game.show_on_public_profile ?? false)

const isReviewModalOpen = ref(false)

watch(
    () => props.statuses,
    (statuses) => {
        if (!statuses.length) {
            return
        }

        status.value = props.game.has_meta
            ? props.game.status
            : statuses[0].name
    },
    {
        immediate: true,
    }
)

const saveMeta = () => {
    router.post(
        `/games/${props.game.id}/meta`,
        {
            note: note.value,
            rating: rating.value ? Number(rating.value) : null,
            recommended: recommended.value,
            not_recommended: notRecommended.value,
            status: status.value,
            show_on_public_profile: showOnPublicProfile.value,
        },
        {
            preserveScroll: true,
        }
    )
}

const toggleRecommended = () => {
    recommended.value = !recommended.value

    if (recommended.value) {
        notRecommended.value = false
    }

    saveMeta()
}

const toggleNotRecommended = () => {
    notRecommended.value = !notRecommended.value

    if (notRecommended.value) {
        recommended.value = false
    }

    saveMeta()
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
                    <GameHero
                        :game="game"
                        @create-review="isReviewModalOpen = true"
                    />

                    <div class="grid gap-8 p-10 lg:grid-cols-[1fr_360px]">
                        <section class="space-y-8">
                            <div class="space-y-5">
                                <div class="grid gap-5 lg:grid-cols-[1fr_260px]">
                                    <GameNotes
                                        v-model="note"
                                        v-model:show-on-public-profile="showOnPublicProfile"
                                        @save="saveMeta"
                                    />

                                    <GameMetaPanel
                                        v-model:rating="rating"
                                        v-model:status="status"
                                        :statuses="statuses"
                                        :recommended="recommended"
                                        :not-recommended="notRecommended"
                                        @save="saveMeta"
                                        @toggle-recommended="toggleRecommended"
                                        @toggle-not-recommended="toggleNotRecommended"
                                    />
                                </div>

                                <GameStats :game="game" />
                            </div>

                            <GameGallery :screenshots="game.screenshots" />

                            <div>
                                <h2 class="text-2xl font-bold text-white">
                                    About
                                </h2>

                                <p class="mt-3 whitespace-pre-line text-zinc-400">
                                    {{
                                        game.about ||
                                        game.description ||
                                        'No details available.'
                                    }}
                                </p>
                            </div>
                        </section>

                        <GameInfoSidebar :game="game" />
                    </div>
                </div>
            </main>
        </div>

        <PublicReviewModal
            v-if="isReviewModalOpen"
            :game="game"
            :note="note"
            :rating="rating"
            :recommended="recommended"
            :not-recommended="notRecommended"
            @close="isReviewModalOpen = false"
        />
    </div>
</template>