<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Gamepad2, PenLine } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950/95 backdrop-blur"
    >
        <div
            class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-6"
        >
            <Link
                href="/home"
                class="flex items-center gap-2 text-lg font-black"
            >
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-zinc-950"
                >
                    <Gamepad2 class="h-5 w-5" />
                </span>
                Curator.gg
            </Link>
            <Link
                href="/blog"
                class="hidden text-sm font-bold text-zinc-400 hover:text-white sm:block"
            >
                Community Blog
            </Link>
            <nav class="ml-auto flex items-center gap-2">
                <Link
                    v-if="user"
                    href="/blog/mine"
                    class="rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold text-zinc-300"
                >
                    My posts
                </Link>
                <a
                    :href="
                        user
                            ? '/blog/create'
                            : '/auth/steam?intended=/blog/create'
                    "
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                >
                    <PenLine class="h-4 w-4" />
                    Write
                </a>
            </nav>
        </div>
    </header>
</template>
