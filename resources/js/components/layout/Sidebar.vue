<script setup>
import { ref } from 'vue'

import { Link, usePage } from '@inertiajs/vue3'

import {
    LayoutDashboard,
    Library,
    PlayCircle,
    CheckCircle2,
    Heart,
    Sparkles,
    PlusCircle,
    ChevronDown,
} from 'lucide-vue-next'

const page = usePage()

const isCollectionOpen = ref(true)

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
        label: 'Wishlist',
        href: '/wishlist',
        icon: Heart,
    },
]

const utilityItems = [
    {
        label: 'Add Game',
        href: '/games/create',
        icon: PlusCircle,
    },
]
</script>

<template>
    <aside
        class="flex h-screen w-64 flex-col border-r border-zinc-800 bg-zinc-950"
    >
        <div class="border-b border-zinc-800 px-6 py-5">
            <h1 class="text-2xl font-bold tracking-tight text-white">
                backlog.gg
            </h1>

            <p class="mt-1 text-sm text-zinc-400">
                Beat your backlog.
            </p>
        </div>

        <nav class="flex flex-1 flex-col p-4">
            <div class="space-y-2">
                <Link
                    v-for="item in mainItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all"
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
                    class="mb-3 flex w-full items-center justify-between px-4 text-xs font-semibold uppercase tracking-wider text-zinc-500"
                    @click="isCollectionOpen = !isCollectionOpen"
                >
                    <span>Collection</span>

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
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all"
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
                    class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-zinc-500"
                >
                    Tools
                </p>

                <div class="space-y-2">
                    <Link
                        v-for="item in utilityItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all"
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