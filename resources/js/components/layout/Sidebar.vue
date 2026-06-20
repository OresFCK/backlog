<script setup>
import { computed, ref } from 'vue'

import {
    Link,
    usePage,
} from '@inertiajs/vue3'

import {
    LayoutDashboard,
    Library,
    PlayCircle,
    CheckCircle2,
    Ban,
    Sparkles,
    Star,
    CalendarDays,
    ChevronDown,
    Settings,
    Tags,
    Users,
    Trophy,
    MessageSquareText,
    ShoppingBag,
    Shirt,
    BarChart3,
    Bug,
    Lightbulb,
    UserCog,
    ListOrdered,
} from 'lucide-vue-next'

const page = usePage()

const initialSection = computed(() => {
    if (
        ['/curators', '/premieres']
            .some(route => page.url.startsWith(route))
    ) {
        return 'curators'
    }

    if (
        ['/backlog', '/playing', '/finished', '/dropped', '/stats']
            .some(route => page.url.startsWith(route))
    ) {
        return 'collection'
    }

    if (
        ['/reviews', '/challenges', '/people']
            .some(route => page.url.startsWith(route))
    ) {
        return 'community'
    }

    if (
        ['/games/create', '/shop', '/wardrobe']
            .some(route => page.url.startsWith(route))
    ) {
        return 'tools'
    }

    if (page.url.startsWith('/settings')) {
        return 'settings'
    }

    return null
})

const activeSection = ref(initialSection.value)

const toggleSection = (section) => {
    activeSection.value = activeSection.value === section
        ? null
        : section
}

const isSectionOpen = (section) => activeSection.value === section

const navItemClass = (href) =>
    page.url.startsWith(href)
        ? 'border border-zinc-700 bg-zinc-800 text-white shadow-sm'
        : 'border border-transparent text-zinc-300 hover:border-zinc-800 hover:bg-zinc-900 hover:text-white'

const sectionButtonClass =
    'mb-3 flex w-full items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-300 transition-all duration-200 hover:border-zinc-700 hover:bg-zinc-900 hover:text-white'

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

const curatorsItems = [
    {
        label: 'Curators Index',
        href: '/curators',
        icon: Star,
    },
    {
        label: 'Premieres',
        href: '/premieres',
        icon: CalendarDays,
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
    {
        label: 'Stats',
        href: '/stats',
        icon: BarChart3,
    },
]

const communityItems = [
    {
        label: 'Reviews',
        href: '/reviews',
        icon: MessageSquareText,
    },
    {
        label: 'Challenges',
        href: '/challenges',
        icon: Trophy,
    },
    {
        label: 'People',
        href: '/people',
        icon: Users,
    },
]

const toolItems = [
    {
        label: 'Shop',
        href: '/shop',
        icon: ShoppingBag,
    },
    {
        label: 'Wardrobe',
        href: '/wardrobe',
        icon: Shirt,
    },
    {
        label: 'Custom Lists',
        href: '/lists',
        icon: ListOrdered,
    },
]

const settingsItems = [
    {
        label: 'Account',
        href: '/settings/account',
        icon: UserCog,
    },
    {
        label: 'Custom Statuses',
        href: '/settings/labels',
        icon: Tags,
    },
    {
        label: 'Report bug',
        href: '/settings/report-bug',
        icon: Bug,
    },
    {
        label: 'Send suggestion',
        href: '/settings/suggestion',
        icon: Lightbulb,
    },
]
</script>

<template>
    <aside class="sticky top-0 flex h-screen w-64 shrink-0 flex-col border-r border-zinc-800 bg-zinc-950">
        <div class="flex h-[89px] flex-col justify-center border-b border-zinc-800 px-6">
            <h1 class="text-2xl font-bold tracking-tight text-white">
                <Link href="/dashboard">Curator.gg</Link>
            </h1>

            <p class="mt-1 text-sm text-zinc-400">
                Beat your backlog.
            </p>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-5">
            <div class="space-y-2">
                <Link
                    v-for="item in mainItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                    :class="navItemClass(item.href)"
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 shrink-0"
                    />

                    <span>{{ item.label }}</span>
                </Link>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    :class="sectionButtonClass"
                    @click="toggleSection('curators')"
                >
                    <span>Curators</span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="isSectionOpen('curators') ? 'rotate-180' : ''"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSectionOpen('curators')"
                        class="space-y-2 border-l border-zinc-800 pl-3"
                    >
                        <Link
                            v-for="item in curatorsItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                            :class="navItemClass(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 shrink-0"
                            />

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </Transition>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    :class="sectionButtonClass"
                    @click="toggleSection('collection')"
                >
                    <span>Collection</span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="isSectionOpen('collection') ? 'rotate-180' : ''"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSectionOpen('collection')"
                        class="space-y-2 border-l border-zinc-800 pl-3"
                    >
                        <Link
                            v-for="item in collectionItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                            :class="navItemClass(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 shrink-0"
                            />

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </Transition>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    :class="sectionButtonClass"
                    @click="toggleSection('community')"
                >
                    <span>Community</span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="isSectionOpen('community') ? 'rotate-180' : ''"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSectionOpen('community')"
                        class="space-y-2 border-l border-zinc-800 pl-3"
                    >
                        <Link
                            v-for="item in communityItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                            :class="navItemClass(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 shrink-0"
                            />

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </Transition>
            </div>

            <div class="mt-8">
                <button
                    type="button"
                    :class="sectionButtonClass"
                    @click="toggleSection('tools')"
                >
                    <span>Tools</span>

                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="isSectionOpen('tools') ? 'rotate-180' : ''"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSectionOpen('tools')"
                        class="space-y-2 border-l border-zinc-800 pl-3"
                    >
                        <Link
                            v-for="item in toolItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                            :class="navItemClass(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 shrink-0"
                            />

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </Transition>
            </div>

            <div class="mt-8 pb-6">
                <button
                    type="button"
                    :class="sectionButtonClass"
                    @click="toggleSection('settings')"
                >
                    <div class="flex items-center gap-2">
                        <Settings class="h-4 w-4" />
                        <span>Settings</span>
                    </div>

                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="isSectionOpen('settings') ? 'rotate-180' : ''"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="isSectionOpen('settings')"
                        class="space-y-2 border-l border-zinc-800 pl-3"
                    >
                        <Link
                            v-for="item in settingsItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                            :class="navItemClass(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 shrink-0"
                            />

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </Transition>
            </div>
        </nav>
    </aside>
</template>