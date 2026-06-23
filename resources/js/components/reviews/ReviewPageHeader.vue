<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    auth: {
        type: Object,
        default: () => ({
            user: null,
        }),
    },
})

defineEmits(['write-review'])
</script>

<template>
    <header class="border-b border-zinc-800 bg-zinc-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <Link
                href="/home"
                class="text-xl font-black tracking-tight"
                aria-label="Curator.gg home"
            >
                Curator.gg
            </Link>

            <div class="flex items-center gap-3">
                <Link
                    v-if="!auth.user"
                    href="/login"
                    class="rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 hover:bg-zinc-900 hover:text-white"
                >
                    Login
                </Link>

                <button
                    v-if="auth.user"
                    type="button"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 hover:bg-zinc-200"
                    @click="$emit('write-review')"
                >
                    Write review
                </button>
            </div>
        </div>
    </header>
</template>