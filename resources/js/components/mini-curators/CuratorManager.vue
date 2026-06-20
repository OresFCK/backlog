<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    curators: {
        type: Array,
        default: () => [],
    },
})

const searchQuery = ref('')

const filteredCurators = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) {
        return props.curators
    }

    return props.curators.filter((curator) =>
        String(curator.name ?? '').toLowerCase().includes(query)
        || String(curator.steam_id ?? '').toLowerCase().includes(query)
    )
})

function follow(curator) {
    router.post(`/mini-curators/${curator.id}/follow`, {}, {
        preserveScroll: true,
    })
}

function unfollow(curator) {
    router.delete(`/mini-curators/${curator.id}/follow`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold">
                    Follow Mini Curators
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    {{ filteredCurators.length }} available
                </p>
            </div>

            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search Mini Curators..."
                class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500 md:w-80"
            />
        </div>

        <div
            v-if="filteredCurators.length"
            class="mt-5 grid max-h-[360px] gap-3 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3"
        >
            <div
                v-for="curator in filteredCurators"
                :key="curator.id"
                class="flex items-center gap-3 rounded-2xl border border-zinc-800 bg-zinc-950 p-3"
            >
                <Link
                    :href="`/u/${curator.steam_id}`"
                    class="flex min-w-0 flex-1 items-center gap-3"
                >
                    <img
                        v-if="curator.avatar"
                        :src="curator.avatar"
                        :alt="curator.name"
                        class="h-11 w-11 rounded-xl object-cover transition hover:opacity-80"
                    />

                    <div
                        v-else
                        class="h-11 w-11 rounded-xl bg-zinc-800"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold hover:text-indigo-300">
                            {{ curator.name }}
                        </p>

                        <p class="truncate text-xs text-zinc-500">
                            {{ curator.steam_id }}
                        </p>
                    </div>
                </Link>

                <button
                    v-if="curator.is_following"
                    type="button"
                    class="rounded-xl border border-red-900/60 px-3 py-2 text-xs font-bold text-red-400 transition hover:bg-red-950/40"
                    @click="unfollow(curator)"
                >
                    Unfollow
                </button>

                <button
                    v-else
                    type="button"
                    class="rounded-xl border border-purple-700 bg-purple-950/50 px-3 py-2 text-xs font-bold text-purple-200 transition hover:bg-purple-900/60"
                    @click="follow(curator)"
                >
                    Follow
                </button>
            </div>
        </div>
    </section>
</template>