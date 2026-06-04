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

const igdbDump = ref(null)
const igdbLoading = ref(false)

const fetchIgdbDump = async () => {
    igdbLoading.value = true

    try {
        const response = await fetch('/admin/igdb/dumps/games', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content'),
            },
        })

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`)
        }

        igdbDump.value = await response.json()

        console.log('IGDB dump', igdbDump.value)
    } catch (error) {
        console.error(error)
        alert('Błąd pobierania dumpa IGDB')
    } finally {
        igdbLoading.value = false
    }
}
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

                        <button
                            type="button"
                            class="rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-400"
                            @click="fetchIgdbDump"
                        >
                            {{
                                igdbLoading
                                    ? 'Pobieranie...'
                                    : 'Pobierz strukturę IGDB games CSV'
                            }}
                        </button>
                    </div>

                    <div
                        v-if="igdbDump"
                        class="mt-6 overflow-hidden rounded-2xl border border-zinc-800 bg-black"
                    >
                        <div class="border-b border-zinc-800 px-4 py-3">
                            <h3 class="font-bold text-white">
                                IGDB Games Dump
                            </h3>
                        </div>

                        <pre
                            class="max-h-[500px] overflow-auto p-4 text-xs text-green-400"
                        >{{ JSON.stringify(igdbDump, null, 2) }}</pre>
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