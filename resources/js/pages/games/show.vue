<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';

import GameHero from '@/components/games/GameHero.vue';
import GameNotes from '@/components/games/GameNotes.vue';
import GameMetaPanel from '@/components/games/GameMetaPanel.vue';
import GameStats from '@/components/games/GameStats.vue';
import GameGallery from '@/components/games/GameGallery.vue';
import GameInfoSidebar from '@/components/games/GameInfoSidebar.vue';
import PublicReviewModal from '@/components/games/PublicReviewModal.vue';
import CustomGameDetailsEditor from '@/components/games/CustomGameDetailsEditor.vue';

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
});

const note = ref(props.game.note ?? '');
const rating = ref(props.game.rating ? String(props.game.rating) : '');
const recommended = ref(props.game.recommended ?? false);
const notRecommended = ref(props.game.not_recommended ?? false);
const status = ref(props.game.status ?? 'Backlog');
const showOnPublicProfile = ref(props.game.show_on_public_profile ?? false);

const isReviewModalOpen = ref(false);
const isAboutExpanded = ref(false);

const aboutText = computed(() => {
    return (
        props.game.about || props.game.description || 'No details available.'
    );
});

const shouldShowReadMore = computed(() => {
    return aboutText.value.length > 700;
});

watch(
    () => props.game,
    (game) => {
        note.value = game.note ?? '';
        rating.value = game.rating ? String(game.rating) : '';
        recommended.value = game.recommended ?? false;
        notRecommended.value = game.not_recommended ?? false;
        status.value = game.status ?? 'Backlog';
        showOnPublicProfile.value = game.show_on_public_profile ?? false;
    },
    {
        immediate: true,
    },
);

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
        },
    );
};

const toggleRecommended = () => {
    recommended.value = !recommended.value;

    if (recommended.value) {
        notRecommended.value = false;
    }

    saveMeta();
};

const toggleNotRecommended = () => {
    notRecommended.value = !notRecommended.value;

    if (notRecommended.value) {
        recommended.value = false;
    }

    saveMeta();
};
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-3 sm:p-6 lg:p-8">
                <div
                    class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 sm:rounded-3xl"
                >
                    <GameHero
                        :game="game"
                        @create-review="isReviewModalOpen = true"
                    />

                    <div
                        class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-8 lg:p-10 xl:grid-cols-[minmax(0,1fr)_360px]"
                    >
                        <section class="min-w-0 space-y-6 sm:space-y-8">
                            <div class="space-y-4 sm:space-y-5">
                                <div
                                    class="grid gap-5 lg:grid-cols-[1fr_260px]"
                                >
                                    <GameNotes
                                        v-model="note"
                                        v-model:show-on-public-profile="
                                            showOnPublicProfile
                                        "
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
                                        @toggle-not-recommended="
                                            toggleNotRecommended
                                        "
                                    />
                                </div>

                                <CustomGameDetailsEditor
                                    v-if="game.is_custom"
                                    :game="game"
                                />

                                <GameStats :game="game" />
                            </div>

                            <GameGallery :screenshots="game.screenshots" />

                            <div>
                                <h2
                                    class="text-xl font-bold text-white sm:text-2xl"
                                >
                                    About
                                </h2>

                                <div
                                    class="relative mt-3 overflow-hidden"
                                    :class="
                                        !isAboutExpanded && shouldShowReadMore
                                            ? 'max-h-48'
                                            : ''
                                    "
                                >
                                    <p
                                        class="text-sm leading-6 break-words whitespace-pre-line text-zinc-400 sm:text-base"
                                    >
                                        {{ aboutText }}
                                    </p>

                                    <div
                                        v-if="
                                            !isAboutExpanded &&
                                            shouldShowReadMore
                                        "
                                        class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-zinc-900 to-transparent"
                                    />
                                </div>

                                <button
                                    v-if="shouldShowReadMore"
                                    type="button"
                                    class="mt-4 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                                    @click="isAboutExpanded = !isAboutExpanded"
                                >
                                    {{
                                        isAboutExpanded
                                            ? 'Show less'
                                            : 'Read more'
                                    }}
                                </button>
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
