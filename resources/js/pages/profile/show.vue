<script setup>
import { computed, ref } from 'vue';

import { Home, Image, History, Gamepad2 } from 'lucide-vue-next';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';

import ProfileHero from '@/components/profile/ProfileHero.vue';
import ProfileStats from '@/components/profile/ProfileStats.vue';
import ProfileShowcase from '@/components/profile/ProfileShowcase.vue';
import ProfileActivity from '@/components/profile/ProfileActivity.vue';
import ProfileLibrary from '@/components/profile/ProfileLibrary.vue';

import { normalizeStatus } from '@/lib/profile';

const props = defineProps({
    user: Object,
    games: Array,
    activity: Array,

    equippedItems: {
        type: Array,
        default: () => [],
    },

    featuredGames: {
        type: Array,
        default: () => [],
    },

    featuredReviews: {
        type: Array,
        default: () => [],
    },

    featuredWardrobeItems: {
        type: Array,
        default: () => [],
    },
});

const activeSection = ref('showcase');

const menuItems = [
    {
        key: 'showcase',
        label: 'Showcase',
        icon: Image,
    },
    {
        key: 'overview',
        label: 'Overview',
        icon: Home,
    },
    {
        key: 'activity',
        label: 'Activity',
        icon: History,
    },
    {
        key: 'library',
        label: 'Library',
        icon: Gamepad2,
    },
];

const equippedByType = computed(() => {
    return props.equippedItems.reduce((items, item) => {
        items[item.type] = item;

        return items;
    }, {});
});

const profileTheme = computed(() => {
    return equippedByType.value.theme;
});

const usernameFontStyle = computed(() => {
    const font = equippedByType.value.username_font?.metadata?.font_family;

    return font
        ? {
              fontFamily: font,
          }
        : {};
});

const profileBackgroundStyle = computed(() => {
    if (!profileTheme.value?.image_url) {
        return {};
    }

    return {
        backgroundImage: `url(${profileTheme.value.image_url})`,
        backgroundSize: 'cover',
        backgroundAttachment: 'fixed',
        backgroundPosition: 'center',
    };
});

const groupedGames = computed(() => {
    return props.games.reduce((groups, game) => {
        const status = normalizeStatus(game.status);

        if (!groups[status]) {
            groups[status] = [];
        }

        groups[status].push(game);

        return groups;
    }, {});
});

const stats = computed(() => {
    const finishedStatuses = ['Finished', 'Completed'];

    const finished = props.games.filter((game) =>
        finishedStatuses.includes(normalizeStatus(game.status)),
    ).length;

    return {
        played: props.games.filter((game) => game.playtime_forever > 0).length,

        playing: props.games.filter(
            (game) => normalizeStatus(game.status) === 'Playing',
        ).length,

        reviews: props.games.filter((game) => game.rating || game.note).length,

        backlog: props.games.filter(
            (game) => normalizeStatus(game.status) === 'Backlog',
        ).length,

        wishlist: props.games.filter(
            (game) => normalizeStatus(game.status) === 'Wishlist',
        ).length,

        lists: Object.keys(groupedGames.value).length,

        finished,

        completionRate: props.games.length
            ? Math.round((finished / props.games.length) * 1000) / 10
            : 0,
    };
});
</script>

<template>
    <div
        class="flex min-h-screen bg-zinc-950 text-white"
        :style="profileBackgroundStyle"
    >
        <Sidebar />

        <div
            class="flex min-h-screen min-w-0 flex-1 flex-col bg-zinc-950/80 backdrop-blur-sm"
        >
            <Topbar :user="user" />

            <main class="flex-1">
                <div
                    class="mx-auto max-w-7xl space-y-5 p-3 sm:space-y-8 sm:p-6 lg:p-8"
                >
                    <ProfileHero
                        :user="user"
                        :equipped-by-type="equippedByType"
                        :username-font-style="usernameFontStyle"
                    />

                    <div
                        class="-mx-3 flex max-w-[calc(100vw-1.5rem)] gap-2 overflow-x-auto px-3 pb-1 sm:mx-0 sm:-mt-4 sm:mb-4 sm:max-w-none sm:flex-wrap sm:justify-center sm:gap-3 sm:px-0"
                    >
                        <button
                            v-for="item in menuItems"
                            :key="item.key"
                            type="button"
                            class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition sm:rounded-2xl sm:px-6 sm:py-3"
                            :class="
                                activeSection === item.key
                                    ? 'bg-white text-zinc-950 shadow-lg'
                                    : 'text-zinc-400 hover:text-white'
                            "
                            @click="activeSection = item.key"
                        >
                            <component :is="item.icon" class="h-4 w-4" />

                            {{ item.label }}
                        </button>
                    </div>

                    <template v-if="activeSection === 'showcase'">
                        <ProfileShowcase
                            :showcase="equippedByType.profile_showcase"
                            :featured-games="featuredGames"
                            :featured-reviews="featuredReviews"
                            :featured-wardrobe-items="featuredWardrobeItems"
                        />
                    </template>

                    <template v-else-if="activeSection === 'overview'">
                        <ProfileStats
                            :games-count="games.length"
                            :stats="stats"
                        />
                    </template>

                    <template v-else-if="activeSection === 'activity'">
                        <ProfileActivity :activity="activity" />
                    </template>

                    <template v-else-if="activeSection === 'library'">
                        <ProfileLibrary
                            :games="games"
                            :grouped-games="groupedGames"
                        />
                    </template>
                </div>
            </main>
        </div>
    </div>
</template>
