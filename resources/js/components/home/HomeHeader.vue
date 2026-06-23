<script setup>
import GameSearch from '@/components/home/GameSearch.vue'
</script>

<template>
    <header class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-6">
        <div class="shrink-0 text-lg font-bold">
            Curator.gg
        </div>

        <GameSearch />

        <nav class="hidden shrink-0 gap-8 text-sm text-zinc-400 md:flex">
            <a href="#features" class="hover:text-white">
                Features
            </a>

            <a href="#showcase" class="hover:text-white">
                Showcase
            </a>

            <a href="#support" class="hover:text-white">
                Support
            </a>
        </nav>
    </header>
</template>