<script setup>
import { router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,

    items: {
        type: Array,
        default: () => [],
    },
})

const equipItem = (item) => {
    router.post(`/wardrobe/${item.id}/equip`, {}, {
        preserveScroll: true,
    })
}

const unequipItem = (item) => {
    router.delete(`/wardrobe/${item.id}/equip`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section
                    class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8"
                >
                    <h1 class="text-3xl font-bold text-white">
                        Wardrobe
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Manage and equip your owned cosmetics.
                    </p>
                </section>

                <section
                    v-if="items.length"
                    class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="item in items"
                        :key="item.id"
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                    >
                        <div
                            class="flex h-44 items-center justify-center bg-zinc-800"
                        >
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.name"
                                class="h-full w-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-sm text-zinc-500"
                            >
                                No preview
                            </div>
                        </div>

                        <div class="space-y-4 p-5">
                            <div>
                                <p
                                    class="text-xs uppercase tracking-widest text-zinc-500"
                                >
                                    {{ item.type }}
                                </p>

                                <h2
                                    class="mt-1 text-lg font-bold text-white"
                                >
                                    {{ item.name }}
                                </h2>

                                <p
                                    class="mt-2 line-clamp-2 text-sm text-zinc-400"
                                >
                                    {{ item.description }}
                                </p>
                            </div>

                            <button
                                v-if="!item.is_equipped"
                                type="button"
                                class="w-full rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                @click="equipItem(item)"
                            >
                                Equip
                            </button>

                            <button
                                v-else
                                type="button"
                                class="w-full rounded-2xl bg-emerald-500/10 px-5 py-3 text-sm font-bold text-emerald-400 transition hover:bg-red-500/10 hover:text-red-400"
                                @click="unequipItem(item)"
                            >
                                Unequip
                            </button>
                        </div>
                    </article>
                </section>

                <section
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-900/40 p-12 text-center"
                >
                    <h2 class="text-xl font-bold text-white">
                        No owned items yet
                    </h2>

                    <p class="mt-2 text-zinc-400">
                        Buy something in the shop first.
                    </p>
                </section>
            </main>
        </div>
    </div>
</template>