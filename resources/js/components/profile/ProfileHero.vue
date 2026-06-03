<script setup>
defineProps({
    user: Object,
    equippedByType: Object,
    usernameFontStyle: Object,
})
</script>

<template>
    <div class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
        <img
            v-if="equippedByType.profile_banner?.image_url"
            :src="equippedByType.profile_banner.image_url"
            :alt="equippedByType.profile_banner.name"
            class="absolute inset-0 h-full w-full object-cover"
        />

        <div
            v-else
            class="absolute inset-0 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900"
        />

        <div class="pointer-events-none absolute inset-0 bg-black/45" />

        <div class="relative z-10 flex flex-col gap-6 p-8 pt-44 lg:flex-row lg:items-end">
            <div class="relative h-32 w-32 shrink-0">
                <img
                    v-if="user?.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-32 w-32 rounded-3xl border-4 border-zinc-900 object-cover shadow-2xl"
                />

                <div
                    v-else
                    class="h-32 w-32 rounded-3xl border-4 border-zinc-900 bg-gradient-to-br from-indigo-500 to-purple-500"
                />

                <img
                    v-if="equippedByType.profile_overlay?.image_url"
                    :src="equippedByType.profile_overlay.image_url"
                    :alt="equippedByType.profile_overlay.name"
                    class="pointer-events-none absolute inset-0 h-full w-full rounded-3xl object-cover"
                />
            </div>

            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1
                        class="text-5xl font-bold tracking-tight"
                        :style="usernameFontStyle"
                    >
                        {{ user?.name }}
                    </h1>

                    <img
                        v-if="equippedByType.badge?.image_url"
                        :src="equippedByType.badge.image_url"
                        :alt="equippedByType.badge.name"
                        class="h-9 w-9 rounded-lg object-cover"
                    />
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-if="equippedByType.user_title"
                        class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-sm font-medium text-white"
                    >
                        {{ equippedByType.user_title.name }}
                    </span>
                </div>

                <p class="mt-3 text-zinc-300">
                    Steam ID: {{ user?.steam_id }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a
                        :href="`https://steamcommunity.com/profiles/${user?.steam_id}`"
                        target="_blank"
                        class="inline-flex rounded-xl border border-zinc-700 bg-zinc-900/80 px-5 py-3 text-sm font-medium text-white backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-800"
                    >
                        Open Steam Profile
                    </a>

                    <a
                        href="/wardrobe"
                        class="inline-flex rounded-xl border border-zinc-700 bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                    >
                        Open Wardrobe
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>