<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    item: {
        type: Object,
        required: true,
    },
})

defineEmits(['open-review', 'open-list'])
</script>

<template>
    <article class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
        <div class="flex items-center gap-3">
            <Link
                v-if="item.user?.steam_id"
                :href="`/u/${item.user.steam_id}`"
            >
                <img
                    v-if="item.user.avatar"
                    :src="item.user.avatar"
                    :alt="item.user.name"
                    class="h-10 w-10 rounded-xl object-cover transition hover:opacity-80"
                />

                <div
                    v-else
                    class="h-10 w-10 rounded-xl bg-zinc-800"
                />
            </Link>

            <div
                v-else
                class="h-10 w-10 rounded-xl bg-zinc-800"
            />

            <div>
                <Link
                    v-if="item.user?.steam_id"
                    :href="`/u/${item.user.steam_id}`"
                    class="font-bold hover:text-indigo-300"
                >
                    {{ item.user.name }}
                </Link>

                <p
                    v-else
                    class="font-bold"
                >
                    {{ item.user.name }}
                </p>

                <p class="text-xs text-zinc-500">
                    {{ item.created_at_human }}
                </p>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-purple-300">
                {{ item.type === 'review' ? 'Review' : 'Custom List' }}
            </p>

            <h3 class="mt-2 text-lg font-black">
                {{ item.title }}
            </h3>

            <p
                v-if="item.game_title"
                class="mt-1 text-sm text-zinc-500"
            >
                {{ item.game_title }}
            </p>

            <p
                v-if="item.body"
                class="mt-3 line-clamp-4 text-sm text-zinc-300"
            >
                {{ item.body }}
            </p>

            <p
                v-if="item.description"
                class="mt-3 line-clamp-4 text-sm text-zinc-300"
            >
                {{ item.description }}
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <button
                    v-if="item.type === 'review'"
                    type="button"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-black transition hover:bg-zinc-200"
                    @click="$emit('open-review', item)"
                >
                    Read more
                </button>

                <button
                    v-else
                    type="button"
                    class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-black transition hover:bg-zinc-200"
                    @click="$emit('open-list', item)"
                >
                    View list
                </button>
            </div>
        </div>
    </article>
</template>