<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, Copy } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    equippedByType: Object,
    usernameFontStyle: Object,
});

const copied = ref(false);

const copyPublicProfileLink = async () => {
    const url = `${window.location.origin}/u/${props.user?.steam_id}`;

    await navigator.clipboard.writeText(url);

    copied.value = true;

    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const toggleCurator = () => {
    router.patch(
        '/profile/curator',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({
                    only: ['user'],
                    preserveScroll: true,
                });
            },
        },
    );
};
</script>

<template>
    <div
        class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 sm:rounded-3xl"
    >
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

        <div
            class="relative z-10 flex flex-col items-center gap-4 p-4 pt-28 text-center sm:gap-6 sm:p-8 sm:pt-44 lg:flex-row lg:items-end lg:text-left"
        >
            <div class="relative h-24 w-24 shrink-0 sm:h-32 sm:w-32">
                <img
                    v-if="user?.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-24 w-24 rounded-2xl border-4 border-zinc-900 object-cover shadow-2xl sm:h-32 sm:w-32 sm:rounded-3xl"
                />

                <div
                    v-else
                    class="h-24 w-24 rounded-2xl border-4 border-zinc-900 bg-gradient-to-br from-indigo-500 to-purple-500 sm:h-32 sm:w-32 sm:rounded-3xl"
                />

                <img
                    v-if="equippedByType.profile_overlay?.image_url"
                    :src="equippedByType.profile_overlay.image_url"
                    :alt="equippedByType.profile_overlay.name"
                    class="pointer-events-none absolute inset-0 h-full w-full rounded-2xl object-cover sm:rounded-3xl"
                />
            </div>

            <div class="flex-1">
                <div
                    class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 lg:justify-start"
                >
                    <h1
                        class="max-w-full text-3xl font-bold tracking-tight break-words sm:text-5xl"
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

                <div
                    class="mt-2 flex flex-wrap justify-center gap-2 sm:mt-3 lg:justify-start"
                >
                    <span
                        v-if="equippedByType.user_title"
                        class="rounded-full border border-zinc-700 bg-zinc-900/80 px-3 py-1 text-sm font-medium text-white"
                    >
                        {{ equippedByType.user_title.name }}
                    </span>
                </div>

                <p
                    class="mt-2 text-sm break-all text-zinc-300 sm:mt-3 sm:text-base"
                >
                    Steam ID: {{ user?.steam_id }}
                </p>

                <div
                    class="mt-4 grid w-full grid-cols-2 gap-2 sm:mt-6 sm:flex sm:flex-wrap sm:gap-3"
                >
                    <a
                        :href="`https://steamcommunity.com/profiles/${user?.steam_id}`"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900/80 px-3 py-2.5 text-center text-xs font-medium text-white backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-800 sm:px-5 sm:py-3 sm:text-sm"
                    >
                        Open Steam Profile
                    </a>

                    <a
                        href="/wardrobe"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-700 bg-white px-3 py-2.5 text-center text-xs font-bold text-zinc-950 transition hover:bg-zinc-200 sm:px-5 sm:py-3 sm:text-sm"
                    >
                        Open Wardrobe
                    </a>

                    <button
                        type="button"
                        :class="[
                            'col-span-2 inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-xs font-bold backdrop-blur transition sm:col-span-1 sm:px-5 sm:py-3 sm:text-sm',
                            user?.is_curator
                                ? 'border border-red-700 bg-red-950/60 text-red-200 hover:bg-red-900/70'
                                : 'border border-purple-700 bg-purple-950/60 text-purple-200 hover:bg-purple-900/70',
                        ]"
                        @click="toggleCurator"
                    >
                        {{
                            user?.is_curator
                                ? 'Disable Mini Curator'
                                : 'Enable Mini Curator'
                        }}
                    </button>

                    <button
                        type="button"
                        class="col-span-2 inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-900/80 px-3 py-2.5 text-xs font-medium text-white backdrop-blur transition hover:border-zinc-500 hover:bg-zinc-800 sm:col-span-1 sm:px-5 sm:py-3 sm:text-sm"
                        @click="copyPublicProfileLink"
                    >
                        <Check v-if="copied" class="h-4 w-4 text-emerald-300" />

                        <Copy v-else class="h-4 w-4" />

                        {{ copied ? 'Copied!' : 'Copy Public Link' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
