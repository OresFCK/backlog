<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <div
                    class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                >
                    <div
                        class="relative min-h-[360px] bg-cover bg-center"
                        :style="{
                            backgroundImage: game.header_image
                                ? `linear-gradient(to right, rgba(9,9,11,.95), rgba(9,9,11,.55)), url(${game.header_image})`
                                : null,
                        }"
                    >
                        <div class="p-10">
                            <div class="mb-4 flex flex-wrap gap-2">
                                <span
                                    v-for="genre in game.genres"
                                    :key="genre"
                                    class="rounded-full border border-zinc-700 bg-zinc-950/70 px-3 py-1 text-xs font-semibold text-zinc-300"
                                >
                                    {{ genre }}
                                </span>
                            </div>

                            <h1 class="max-w-3xl text-5xl font-black text-white">
                                {{ game.title }}
                            </h1>

                            <p class="mt-4 max-w-2xl text-zinc-300">
                                {{ game.description || 'No description available.' }}
                            </p>

                            <div class="mt-8 flex gap-4">
                                <a
                                    v-if="game.steam_url"
                                    :href="game.steam_url"
                                    target="_blank"
                                    class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-black hover:bg-zinc-200"
                                >
                                    Open Steam page
                                </a>

                                <button
                                    class="rounded-xl border border-zinc-700 bg-zinc-950 px-6 py-3 text-sm font-bold text-white hover:bg-zinc-800"
                                >
                                    Mark as playing
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-8 p-10 lg:grid-cols-[1fr_360px]">
                        <section class="space-y-8">
                            <div>
                                <h2 class="text-2xl font-bold text-white">
                                    About
                                </h2>

                                <p class="mt-3 whitespace-pre-line text-zinc-400">
                                    {{ game.about || game.description || 'No details available.' }}
                                </p>
                            </div>

                            <div v-if="game.screenshots.length">
                                <h2 class="mb-4 text-2xl font-bold text-white">
                                    Gallery
                                </h2>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <img
                                        v-for="screenshot in game.screenshots"
                                        :key="screenshot"
                                        :src="screenshot"
                                        class="h-52 w-full rounded-2xl object-cover"
                                    />
                                </div>
                            </div>
                        </section>

                        <aside class="space-y-4">
                            <img
                                v-if="game.cover_url"
                                :src="game.cover_url"
                                class="w-full rounded-2xl object-cover"
                            />

                            <div
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
                            >
                                <h3 class="font-bold text-white">
                                    Information
                                </h3>

                                <dl class="mt-4 space-y-4 text-sm">
                                    <div>
                                        <dt class="text-zinc-500">
                                            Developer
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{ game.developers?.join(', ') || 'Unknown' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Publisher
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{ game.publishers?.join(', ') || game.publisher || 'Unknown' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Release date
                                        </dt>

                                        <dd class="text-zinc-200">
                                            {{ game.release_date || 'Unknown' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-500">
                                            Platform
                                        </dt>

                                        <dd class="text-zinc-200">
                                            <span v-if="game.platforms?.windows">Windows</span>
                                            <span v-if="game.platforms?.mac">, Mac</span>
                                            <span v-if="game.platforms?.linux">, Linux</span>
                                            <span v-if="game.is_custom">Custom</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </aside>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>