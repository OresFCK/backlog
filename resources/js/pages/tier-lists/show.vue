<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, Gamepad2, Pencil, Share2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    tierList: {
        type: Object,
        required: true,
    },
    isOwner: Boolean,
    seo: {
        type: Object,
        required: true,
    },
});

const copied = ref(false);

const itemsForTier = (tierId) =>
    props.tierList.items
        .filter((item) => item.tier_id === tierId)
        .sort((a, b) => a.position - b.position);

const share = async () => {
    const data = {
        title: props.tierList.title,
        text: `Check out ${props.tierList.title} on Curator.gg`,
        url: props.tierList.share_url,
    };

    if (navigator.share) {
        try {
            await navigator.share(data);

            return;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(props.tierList.share_url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 1800);
};
</script>

<template>
    <Head :title="seo.title" />

    <div class="min-h-screen bg-zinc-950 text-white">
        <header
            class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950/90 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6"
            >
                <Link href="/home" class="flex items-center gap-2 font-black">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-zinc-950"
                    >
                        <Gamepad2 class="h-5 w-5" />
                    </span>
                    Curator.gg
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold"
                        @click="share"
                    >
                        <Check v-if="copied" class="h-4 w-4 text-emerald-400" />
                        <Share2 v-else class="h-4 w-4" />
                        <span class="hidden sm:inline">{{
                            copied ? 'Copied' : 'Share'
                        }}</span>
                    </button>

                    <Link
                        v-if="isOwner"
                        :href="`/tier-lists/${tierList.slug}/edit`"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                    >
                        <Pencil class="h-4 w-4" /> Edit
                    </Link>
                    <Link
                        v-else
                        href="/tier-list-maker"
                        class="rounded-xl bg-white px-3 py-2 text-sm font-black text-zinc-950"
                    >
                        Make your own
                    </Link>
                </div>
            </div>
        </header>

        <main class="px-3 py-6 sm:px-6 sm:py-10">
            <div class="mx-auto max-w-[1450px]">
                <div class="min-w-0">
                    <header class="mb-7">
                        <p
                            class="text-xs font-black tracking-[0.22em] text-indigo-400 uppercase"
                        >
                            Community tier list
                        </p>
                        <h1
                            class="mt-3 text-3xl font-black tracking-tight sm:text-5xl"
                        >
                            {{ tierList.title }}
                        </h1>
                        <p
                            v-if="tierList.description"
                            class="mt-4 max-w-3xl leading-7 text-zinc-400"
                        >
                            {{ tierList.description }}
                        </p>
                        <Link
                            v-if="tierList.author.profile_url"
                            :href="tierList.author.profile_url"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-zinc-400 hover:text-white"
                        >
                            <img
                                v-if="tierList.author.avatar"
                                :src="tierList.author.avatar"
                                :alt="tierList.author.name"
                                class="h-8 w-8 rounded-lg object-cover"
                            />
                            by {{ tierList.author.name }}
                        </Link>
                    </header>

                    <section
                        class="overflow-hidden rounded-2xl border border-zinc-800 bg-black sm:rounded-3xl"
                    >
                        <div
                            v-for="tier in tierList.tiers"
                            :key="tier.id"
                            class="grid min-h-24 grid-cols-[72px_minmax(0,1fr)] border-b border-zinc-800 last:border-b-0 sm:min-h-32 sm:grid-cols-[112px_minmax(0,1fr)]"
                        >
                            <div
                                class="flex items-center justify-center p-2 text-center text-sm font-black break-words text-zinc-950 sm:text-base"
                                :style="{ backgroundColor: tier.color }"
                            >
                                {{ tier.name }}
                            </div>

                            <div
                                class="flex min-w-0 flex-wrap content-start gap-2 bg-zinc-900/30 p-2 sm:p-3"
                            >
                                <Link
                                    v-for="game in itemsForTier(tier.id)"
                                    :key="game.id"
                                    :href="game.slug ? `/${game.slug}` : '#'"
                                    :title="game.title"
                                    class="h-24 w-17 overflow-hidden rounded-lg border border-zinc-700 bg-zinc-800 transition hover:-translate-y-0.5 hover:border-zinc-500 sm:h-28 sm:w-20"
                                >
                                    <img
                                        v-if="game.cover_url"
                                        :src="game.cover_url"
                                        :alt="game.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center p-2 text-center text-[10px] text-zinc-400"
                                    >
                                        {{ game.title }}
                                    </div>
                                </Link>

                                <p
                                    v-if="!itemsForTier(tier.id).length"
                                    class="self-center px-3 text-sm text-zinc-700"
                                >
                                    Empty tier
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
