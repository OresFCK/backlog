<script setup>
import { ref } from 'vue'

import {
    Link,
    usePage,
} from '@inertiajs/vue3'

import {
    LayoutDashboard,
    Library,
    PlayCircle,
    CheckCircle2,
    Heart,
    Sparkles,
    PlusCircle,
    ChevronDown,
    Settings,
    Tags,
    Users,
    Trophy,
    UserPlus,
    Ban
} from 'lucide-vue-next'

const page = usePage()

const isCollectionOpen = ref(true)
const isCommunityOpen = ref(true)
const isSettingsOpen = ref(true)

const mainItems = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },

    {
        label: 'Recommendations',
        href: '/recommendations',
        icon: Sparkles,
    },
]

const collectionItems = [
    {
        label: 'Backlog',
        href: '/backlog',
        icon: Library,
    },

    {
        label: 'Playing',
        href: '/playing',
        icon: PlayCircle,
    },

    {
        label: 'Finished',
        href: '/finished',
        icon: CheckCircle2,
    },

    {
        label: 'Dropped',
        href: '/dropped',
        icon: Ban,
    },
]

const communityItems = [
    {
        label: 'Friends',
        href: '/friends',
        icon: Users,
    },

    {
        label: 'Challenges',
        href: '/challenges',
        icon: Trophy,
    },

    {
        label: 'Followed',
        href: '/followed',
        icon: UserPlus,
    },
]

const toolItems = [
    {
        label: 'Add Game',
        href: '/games/create',
        icon: PlusCircle,
    },
]

const settingsItems = [
    {
        label: 'Custom Statuses',
        href: '/settings/labels',
        icon: Tags,
    },
]
</script>

<template>
    <aside
        class="sticky top-0 flex h-screen w-64 shrink-0 flex-col border-r border-zinc-800 bg-zinc-950"
    >
        <div
            class="flex h-[89px] flex-col justify-center border-b border-zinc-800 px-6"
        >
            <h1
                class="text-2xl font-bold tracking-tight text-white"
            >
                Curator.gg
            </h1>

            <p
                class="mt-1 text-sm text-zinc-400"
            >
                Beat your backlog.
            </p>
        </div>

        <nav
            class="flex-1 overflow-y-auto px-4 py-5"
        >
            <div class="space-y-2">
                <Link
                    v-for="item in mainItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all"
                    :class="
                        page.url.startsWith(item.href)
                            ? 'bg-zinc-800 text-white'
                            : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    "
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5"
                    />

                    {{ item.label }}
                </Link>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    class="mb-3 flex w-full items-center justify-between px-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"
                    @click="isCollectionOpen = !isCollectionOpen"
                >
                    <span>
                        Collection
                    </span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform"
                        :class="
                            isCollectionOpen
                                ? 'rotate-180'
                                : ''
                        "
                    />
                </button>

                <div
                    v-if="isCollectionOpen"
                    class="space-y-2"
                >
                    <Link
                        v-for="item in collectionItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all"
                        :class="
                            page.url.startsWith(item.href)
                                ? 'bg-zinc-800 text-white'
                                : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5"
                        />

                        {{ item.label }}
                    </Link>
                </div>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    class="mb-3 flex w-full items-center justify-between px-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"
                    @click="isCommunityOpen = !isCommunityOpen"
                >
                    <span>
                        Community
                    </span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform"
                        :class="
                            isCommunityOpen
                                ? 'rotate-180'
                                : ''
                        "
                    />
                </button>

                <div
                    v-if="isCommunityOpen"
                    class="space-y-2"
                >
                    <Link
                        v-for="item in communityItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all"
                        :class="
                            page.url.startsWith(item.href)
                                ? 'bg-zinc-800 text-white'
                                : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5"
                        />

                        {{ item.label }}
                    </Link>
                </div>
            </div>

            <div class="mt-8">
                <p
                    class="mb-3 px-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"
                >
                    Tools
                </p>

                <div class="space-y-2">
                    <Link
                        v-for="item in toolItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all"
                        :class="
                            page.url.startsWith(item.href)
                                ? 'bg-zinc-800 text-white'
                                : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5"
                        />

                        {{ item.label }}
                    </Link>
                </div>
            </div>

            <div class="mt-8 pb-6">
                <button
                    type="button"
                    class="mb-3 flex w-full items-center justify-between px-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"
                    @click="isSettingsOpen = !isSettingsOpen"
                >
                    <div
                        class="flex items-center gap-2"
                    >
                        <Settings
                            class="h-4 w-4"
                        />

                        <span>
                            Settings
                        </span>
                    </div>

                    <ChevronDown
                        class="h-4 w-4 transition-transform"
                        :class="
                            isSettingsOpen
                                ? 'rotate-180'
                                : ''
                        "
                    />
                </button>

                <div
                    v-if="isSettingsOpen"
                    class="space-y-2"
                >
                    <Link
                        v-for="item in settingsItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all"
                        :class="
                            page.url.startsWith(item.href)
                                ? 'bg-zinc-800 text-white'
                                : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5"
                        />

                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </nav>
    </aside>
</template>