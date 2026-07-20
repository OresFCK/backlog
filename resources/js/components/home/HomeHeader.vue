<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Gamepad2, Menu, X } from 'lucide-vue-next';
import GameSearch from '@/components/home/GameSearch.vue';

const menuOpen = ref(false);

const closeMenu = () => {
    menuOpen.value = false;
};
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-white/5 bg-[#0b0b0d]/90 backdrop-blur-xl"
    >
        <div
            class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-4 sm:px-6"
        >
            <Link
                href="/home"
                class="flex shrink-0 items-center gap-2 text-lg font-black"
            >
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-black"
                >
                    <Gamepad2 class="h-5 w-5" />
                </span>
                Curator.gg
            </Link>

            <GameSearch class="hidden flex-1 sm:block" />

            <nav
                class="ml-auto hidden shrink-0 items-center gap-5 text-sm font-medium text-zinc-400 lg:flex"
            >
                <a href="#features" class="transition hover:text-white"
                    >Features</a
                >
                <a href="#how-it-works" class="transition hover:text-white"
                    >How it works</a
                >
                <Link href="/about" class="transition hover:text-white"
                    >About</Link
                >
                <a href="#faq" class="transition hover:text-white">FAQ</a>
                <Link
                    href="/auth/steam"
                    class="rounded-xl bg-white px-4 py-2 font-black text-black hover:bg-zinc-200"
                >
                    Join with Steam
                </Link>
            </nav>

            <button
                type="button"
                class="ml-auto rounded-xl border border-zinc-800 p-2 text-zinc-300 lg:hidden"
                :aria-expanded="menuOpen"
                aria-label="Toggle navigation"
                @click="menuOpen = !menuOpen"
            >
                <X v-if="menuOpen" class="h-5 w-5" />
                <Menu v-else class="h-5 w-5" />
            </button>
        </div>

        <div
            v-if="menuOpen"
            class="border-t border-zinc-800 px-4 py-4 lg:hidden"
        >
            <div class="mb-4 sm:hidden"><GameSearch /></div>
            <nav class="grid gap-1 text-sm font-bold text-zinc-300">
                <a
                    href="#features"
                    class="rounded-xl px-3 py-3 hover:bg-zinc-900"
                    @click="closeMenu"
                    >Features</a
                >
                <a
                    href="#how-it-works"
                    class="rounded-xl px-3 py-3 hover:bg-zinc-900"
                    @click="closeMenu"
                    >How it works</a
                >
                <Link
                    href="/about"
                    class="rounded-xl px-3 py-3 hover:bg-zinc-900"
                    >About</Link
                >
                <Link
                    href="/contact"
                    class="rounded-xl px-3 py-3 hover:bg-zinc-900"
                    >Contact</Link
                >
                <a
                    href="#faq"
                    class="rounded-xl px-3 py-3 hover:bg-zinc-900"
                    @click="closeMenu"
                    >FAQ</a
                >
                <Link
                    href="/auth/steam"
                    class="mt-2 rounded-xl bg-white px-4 py-3 text-center text-black"
                    >Join with Steam</Link
                >
            </nav>
        </div>
    </header>
</template>
