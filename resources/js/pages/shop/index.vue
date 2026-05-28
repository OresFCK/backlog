<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'
import { router } from '@inertiajs/vue3'
import {
    BadgeCheck,
    ShoppingBag,
    Sparkles,
} from 'lucide-vue-next'

defineProps({
    user: Object,

    items: {
        type: Array,
        default: () => [],
    },
})

const buyItem = (item) => {
    router.post(`/shop/${item.id}/buy`)
}

const equipItem = (item) => {
    router.post(`/shop/${item.id}/equip`)
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section>
                    <div
                        class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-800 text-white"
                            >
                                <ShoppingBag class="h-7 w-7" />
                            </div>

                            <div>
                                <h1 class="text-3xl font-bold text-white">
                                    Shop
                                </h1>

                                <p class="mt-1 text-zinc-400">
                                    Buy profile cosmetics, badges and visual upgrades.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="items.length"
                    class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="item in items"
                        :key="item.id"
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700"
                    >
                        <div
                            class="flex h-44 items-center justify-center bg-zinc-800"
                        >
                            <img
                                v-if="item.preview_url"
                                :src="item.preview_url"
                                :alt="item.name"
                                class="h-full w-full object-cover"
                            />

                            <Sparkles
                                v-else
                                class="h-12 w-12 text-zinc-500"
                            />
                        </div>

                        <div class="space-y-5 p-6">
                            <div>
                                <div
                                    class="mb-3 inline-flex rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-400"
                                >
                                    {{ item.type }}
                                </div>

                                <h2 class="text-xl font-bold text-white">
                                    {{ item.name }}
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-zinc-400">
                                    {{ item.description }}
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-zinc-800 pt-5"
                            >
                                <div>
                                    <p class="text-xs text-zinc-500">
                                        Price
                                    </p>

                                    <p class="text-lg font-bold text-white">
                                        {{ item.price }} coins
                                    </p>
                                </div>

                                <button
                                    v-if="!item.owned"
                                    type="button"
                                    class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                    @click="buyItem(item)"
                                >
                                    Buy
                                </button>

                                <button
                                    v-else-if="!item.equipped"
                                    type="button"
                                    class="rounded-2xl bg-zinc-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-700"
                                    @click="equipItem(item)"
                                >
                                    Equip
                                </button>

                                <div
                                    v-else
                                    class="flex items-center gap-2 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-400"
                                >
                                    <BadgeCheck class="h-4 w-4" />
                                    Equipped
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <section
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-800 bg-zinc-900/40 p-12 text-center"
                >
                    <ShoppingBag class="mx-auto h-12 w-12 text-zinc-600" />

                    <h2 class="mt-4 text-xl font-bold text-white">
                        Shop is empty
                    </h2>

                    <p class="mt-2 text-zinc-400">
                        Add shop items in the database to display them here.
                    </p>
                </section>
            </main>
        </div>
    </div>
</template>