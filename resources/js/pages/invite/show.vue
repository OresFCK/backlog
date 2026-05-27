<script setup>
import {
    Link,
} from '@inertiajs/vue3'

defineProps({
    profile: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-black px-6"
    >
        <div
            class="absolute inset-0 bg-gradient-to-br from-zinc-950 via-black to-zinc-900"
        />

        <div
            class="absolute inset-0 opacity-30"
            style="
                background-image:
                    radial-gradient(circle at top, rgba(255,255,255,0.08), transparent 40%);
            "
        />

        <div
            class="relative z-10 w-full max-w-xl rounded-[32px] border border-zinc-800 bg-zinc-950/90 p-10 shadow-2xl backdrop-blur"
        >
            <div class="flex flex-col items-center text-center">
                <img
                    v-if="profile.avatar"
                    :src="profile.avatar"
                    :alt="profile.name"
                    class="h-28 w-28 rounded-full border-4 border-zinc-800 object-cover shadow-xl"
                />

                <div
                    v-else
                    class="h-28 w-28 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500"
                />

                <p
                    class="mt-8 text-sm font-semibold uppercase tracking-[0.3em] text-zinc-500"
                >
                    Curator.gg Invite
                </p>

                <h1
                    class="mt-3 text-5xl font-black tracking-tight text-white"
                >
                    {{ profile.name }}
                </h1>

                <p
                    class="mt-5 max-w-md text-lg leading-relaxed text-zinc-400"
                >
                    invited you to join
                    <span class="font-bold text-white">
                        Curator.gg
                    </span>

                    and connect through reviews,
                    backlog tracking and game recommendations.
                </p>

                <div
                    class="mt-10 flex w-full flex-col gap-4 sm:flex-row"
                >
                    <Link
                        href="/auth/steam"
                        class="flex-1 rounded-2xl bg-white px-6 py-4 text-center text-base font-black text-zinc-950 transition hover:bg-zinc-200"
                    >
                        Join with Steam
                    </Link>

                    <a
                        v-if="profile.profile_url"
                        :href="profile.profile_url"
                        target="_blank"
                        rel="noreferrer"
                        class="flex-1 rounded-2xl border border-zinc-700 px-6 py-4 text-center text-base font-bold text-white transition hover:bg-zinc-900"
                    >
                        View Steam Profile
                    </a>
                </div>

                <div
                    class="mt-10 rounded-2xl border border-zinc-800 bg-zinc-900/80 px-5 py-4"
                >
                    <p
                        class="text-sm text-zinc-500"
                    >
                        Steam ID
                    </p>

                    <p
                        class="mt-1 font-mono text-sm text-zinc-300"
                    >
                        {{ profile.steam_id }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>