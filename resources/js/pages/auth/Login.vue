<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Gamepad2 } from 'lucide-vue-next';

const page = usePage();
const intended = computed(() => {
    const query = new URLSearchParams(page.url.split('?')[1] ?? '');
    const value = query.get('intended') ?? '/dashboard';
    return value.startsWith('/') && !value.startsWith('//') ? value : '/dashboard';
});
const socialUrl = (provider: string) =>
    `/auth/${provider}?intended=${encodeURIComponent(intended.value)}`;
const steamUrl = computed(
    () => `/auth/steam?intended=${encodeURIComponent(intended.value)}`,
);
const error = computed(() => (page.props.errors as Record<string, string>)?.social);
</script>

<template>
    <Head title="Sign in to Curator.gg" />
    <main class="flex min-h-screen items-center justify-center bg-[#0b0b0d] px-4 py-10 text-white">
        <section class="w-full max-w-md border-2 border-zinc-700 bg-[#14141b] p-6 shadow-[6px_6px_0_#050506] sm:p-9">
            <Link href="/home" class="inline-flex items-center gap-3 font-mono text-sm font-black">
                <span class="flex h-9 w-9 items-center justify-center border-2 border-cyan-300 bg-cyan-300 text-zinc-950"><Gamepad2 class="h-5 w-5" /></span>
                CURATOR.GG
            </Link>
            <p class="mt-8 font-mono text-xs font-black tracking-[.2em] text-amber-300 uppercase">Welcome back, player</p>
            <h1 class="mt-2 text-3xl font-black">Sign in to continue</h1>
            <p class="mt-3 text-sm leading-6 text-zinc-400">Use your gaming or social account to save your votes and create a shareable card.</p>

            <p v-if="error" class="mt-5 border border-red-400/50 bg-red-400/10 p-3 text-sm text-red-200">{{ error }}</p>

            <div class="mt-7 grid gap-3">
                <a :href="steamUrl" class="flex items-center justify-center gap-3 border-2 border-white bg-white px-4 py-3 font-bold text-zinc-950 transition hover:bg-zinc-200">
                    <span class="text-lg">◉</span> Continue with Steam
                </a>
                <a :href="socialUrl('google')" class="flex items-center justify-center gap-3 border-2 border-zinc-700 bg-zinc-950 px-4 py-3 font-bold text-white transition hover:border-zinc-400 hover:bg-zinc-900">
                    <span class="font-black text-sky-300">G</span> Continue with Google
                </a>
                <a :href="socialUrl('discord')" class="flex items-center justify-center gap-3 border-2 border-[#5865f2] bg-[#5865f2] px-4 py-3 font-bold text-white transition hover:bg-[#4752c4]">
                    <span class="font-black">◉</span> Continue with Discord
                </a>
                <a :href="socialUrl('x')" class="flex items-center justify-center gap-3 border-2 border-zinc-700 bg-black px-4 py-3 font-bold text-white transition hover:border-zinc-400">
                    <span class="font-black">𝕏</span> Continue with X (Twitter)
                </a>
            </div>

            <Link href="/home" class="mt-7 inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-white"><ArrowLeft class="h-4 w-4" /> Back to Curator</Link>
        </section>
    </main>
</template>
