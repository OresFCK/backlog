<script setup>
defineProps({
    user: Object,
    games: {
        type: Array,
        default: () => [],
    },
})

const formatPlaytime = (minutes) => {
    if (!minutes) return '0h'

    return `${Math.round(minutes / 60)}h`
}
</script>

<template>
    <div class="min-h-screen bg-zinc-950 px-6 py-10 text-white">
        <div class="mx-auto max-w-6xl">
            <div class="mb-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img
                        v-if="user?.avatar"
                        :src="user.avatar"
                        class="h-14 w-14 rounded-2xl"
                    >

                    <div>
                        <h1 class="text-3xl font-bold">
                            Steam library
                        </h1>

                        <p class="text-zinc-400">
                            Logged in as {{ user?.name }}
                        </p>
                    </div>
                </div>

                <a
                    href="/dashboard"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-semibold text-black hover:bg-zinc-200"
                >
                    Dashboard
                </a>
            </div>

            <div
                v-if="games.length"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="game in games"
                    :key="game.appid"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4"
                >
                    <img
                        v-if="game.img_icon_url"
                        :src="`https://media.steampowered.com/steamcommunity/public/images/apps/${game.appid}/${game.img_icon_url}.jpg`"
                        class="mb-4 h-12 w-12 rounded-xl"
                    >

                    <h2 class="font-semibold">
                        {{ game.name }}
                    </h2>

                    <p class="mt-2 text-sm text-zinc-400">
                        Played: {{ formatPlaytime(game.playtime_forever) }}
                    </p>
                </div>
            </div>

            <div
                v-else
                class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-10 text-center"
            >
                <h2 class="text-2xl font-bold">
                    No games found
                </h2>

                <p class="mt-3 text-zinc-400">
                    Your Steam profile may be private or empty.
                </p>
            </div>
        </div>
    </div>
</template>