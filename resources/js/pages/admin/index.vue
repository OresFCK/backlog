<script setup>
import { ref } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import ShopItemsManager from '@/components/admin/ShopItemsManager.vue'
import ChallengeManager from '@/components/admin/ChallengeManager.vue'
import UserTools from '@/components/admin/UserTools.vue'
import ReviewReportsManager from '@/components/admin/ReviewReportsManager.vue'

defineProps({
    user: Object,

    items: Array,
    shopItems: Array,
    challenges: Array,

    submissions: {
        type: Array,
        default: () => [],
    },

    reviewReports: {
        type: Array,
        default: () => [],
    },
})

const activeTab = ref('shop')
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
                    <h1 class="text-3xl font-bold text-white">
                        Admin panel
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Manage shop items, challenges, user rewards and reports.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="
                                activeTab === 'shop'
                                    ? 'bg-white text-zinc-950'
                                    : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                            "
                            @click="activeTab = 'shop'"
                        >
                            Shop Items
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="
                                activeTab === 'challenges'
                                    ? 'bg-white text-zinc-950'
                                    : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                            "
                            @click="activeTab = 'challenges'"
                        >
                            Challenges
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="
                                activeTab === 'users'
                                    ? 'bg-white text-zinc-950'
                                    : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                            "
                            @click="activeTab = 'users'"
                        >
                            Users
                        </button>

                        <button
                            type="button"
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition"
                            :class="
                                activeTab === 'reports'
                                    ? 'bg-white text-zinc-950'
                                    : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                            "
                            @click="activeTab = 'reports'"
                        >
                            Reports
                        </button>
                    </div>
                </section>

                <ShopItemsManager
                    v-if="activeTab === 'shop'"
                    :items="items"
                />

                <ChallengeManager
                    v-if="activeTab === 'challenges'"
                    :shop-items="shopItems"
                    :challenges="challenges"
                    :submissions="submissions"
                />

                <UserTools
                    v-if="activeTab === 'users'"
                />

                <ReviewReportsManager
                    v-if="activeTab === 'reports'"
                    :reports="reviewReports"
                />
            </main>
        </div>
    </div>
</template>