<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import ShopItemsManager from '@/components/admin/ShopItemsManager.vue'
import ChallengeManager from '@/components/admin/ChallengeManager.vue'
import UserTools from '@/components/admin/UserTools.vue'

defineProps({
    user: Object,

    items: Array,
    shopItems: Array,
    challenges: Array,
})
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
                        Admin panel
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Manage shop items, challenges and user rewards.
                    </p>
                </section>

                <ShopItemsManager
                    :items="items"
                />

                <ChallengeManager
                    :shop-items="shopItems"
                    :challenges="challenges"
                />

                <UserTools />
            </main>
        </div>
    </div>
</template>