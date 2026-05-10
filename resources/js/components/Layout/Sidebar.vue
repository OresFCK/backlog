<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard,
    Library,
    PlayCircle,
    CheckCircle2,
    Heart,
    Sparkles,
} from 'lucide-vue-next'

const page = usePage()

const items = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
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
    {
        label: 'Recommendations',
        href: '/recommendations',
        icon: Sparkles,
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

        <nav class="flex flex-1 flex-col gap-2 p-4">
            <Link
                v-for="item in items"
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
        </nav>
    </aside>
</template>