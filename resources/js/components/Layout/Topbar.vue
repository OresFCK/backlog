<script setup>
import { Search, Bell } from 'lucide-vue-next'
</script>

<template>
    <header
        class="flex items-center justify-between border-b border-zinc-800 bg-zinc-950 px-8 py-4"
    >
        <div class="relative w-full max-w-md">
            <Search
                class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500"
            />

            <input
                type="text"
                placeholder="Search games..."
                class="w-full rounded-xl border border-zinc-800 bg-zinc-900 py-3 pl-11 pr-4 text-sm text-white outline-none transition-all placeholder:text-zinc-500 focus:border-zinc-700"
            />
        </div>

        <div class="flex items-center gap-4">
            <button
                class="rounded-xl border border-zinc-800 bg-zinc-900 p-3 text-zinc-400 transition hover:text-white"
            >
                <Bell class="h-5 w-5" />
            </button>

            <div
                class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500"
            />
        </div>
    </header>
</template>