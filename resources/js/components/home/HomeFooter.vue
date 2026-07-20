<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t border-zinc-800 bg-zinc-950">
        <div
            class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.2fr_2fr]"
        >
            <div>
                <Link href="/home" class="text-lg font-black text-white"
                    >Curator.gg</Link
                >
                <p class="mt-3 max-w-sm text-sm leading-6 text-zinc-500">
                    An independent Steam library organizer, community review
                    platform and collection of public tools for players.
                </p>
                <p class="mt-4 text-xs leading-5 text-zinc-600">
                    Curator.gg is not affiliated with or endorsed by Valve
                    Corporation.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                <div>
                    <h2 class="text-sm font-black text-white">Product</h2>
                    <nav class="mt-4 grid gap-3 text-sm text-zinc-500">
                        <a href="/home#features" class="hover:text-white"
                            >Features</a
                        >
                        <Link href="/auth/steam" class="hover:text-white"
                            >Sign in with Steam</Link
                        >
                    </nav>
                </div>
                <div>
                    <h2 class="text-sm font-black text-white">Company</h2>
                    <nav class="mt-4 grid gap-3 text-sm text-zinc-500">
                        <Link href="/about" class="hover:text-white"
                            >About</Link
                        >
                        <Link href="/contact" class="hover:text-white"
                            >Contact</Link
                        >
                        <Link
                            href="/community-guidelines"
                            class="hover:text-white"
                            >Community Guidelines</Link
                        >
                    </nav>
                </div>
                <div>
                    <h2 class="text-sm font-black text-white">Legal</h2>
                    <nav class="mt-4 grid gap-3 text-sm text-zinc-500">
                        <Link href="/terms" class="hover:text-white"
                            >Terms of Service</Link
                        >
                        <Link href="/privacy" class="hover:text-white"
                            >Privacy Policy</Link
                        >
                    </nav>
                </div>
            </div>
        </div>

        <div
            class="border-t border-zinc-800 px-4 py-6 text-center text-xs text-zinc-600"
        >
            © {{ new Date().getFullYear() }} Curator.gg. All rights reserved.
        </div>
    </footer>
</template>
