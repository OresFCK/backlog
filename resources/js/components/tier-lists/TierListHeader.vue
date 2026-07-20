<script setup>
import { Link } from '@inertiajs/vue3';
import { Gamepad2 } from 'lucide-vue-next';

defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});
</script>

<template>
    <header class="border-b border-zinc-800 bg-zinc-950/90 backdrop-blur-xl">
        <div
            class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-8"
        >
            <Link href="/home" class="flex items-center gap-3 font-black">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-zinc-950"
                >
                    <Gamepad2 class="h-5 w-5" />
                </span>
                Curator.gg
            </Link>

            <nav class="flex items-center gap-2">
                <Link
                    v-if="auth.user"
                    href="/my-tier-lists"
                    class="hidden rounded-xl px-4 py-2 text-sm font-bold text-zinc-300 hover:bg-zinc-900 hover:text-white sm:block"
                >
                    My tier lists
                </Link>
                <Link
                    :href="auth.user ? '/dashboard' : '/auth/steam'"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950"
                >
                    {{ auth.user ? 'Dashboard' : 'Join with Steam' }}
                </Link>
            </nav>
        </div>
    </header>
</template>
