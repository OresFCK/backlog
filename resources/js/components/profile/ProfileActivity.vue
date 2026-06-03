<script setup>
import { normalizeStatus, ratingStars, statusColor } from '@/lib/profile'

defineProps({
    activity: Array,
})
</script>

<template>
    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">
                    Recent Activity
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    {{ activity.length }} recent updates
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <div
                v-for="item in activity"
                :key="item.id"
                class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-4"
            >
                <img
                    v-if="item.cover_url"
                    :src="item.cover_url"
                    class="h-20 w-14 rounded-lg object-cover"
                />

                <div class="min-w-0 flex-1">
                    <h3 class="truncate font-semibold">
                        {{ item.title }}
                    </h3>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <span
                            class="rounded-lg px-2 py-1 text-xs text-white"
                            :style="{ backgroundColor: statusColor(item) }"
                        >
                            {{ normalizeStatus(item.status) }}
                        </span>

                        <span
                            v-if="item.rating"
                            class="rounded-lg bg-yellow-500/10 px-2 py-1 text-xs text-yellow-400"
                        >
                            {{ ratingStars(item.rating) }}
                        </span>

                        <span
                            v-if="item.recommended"
                            class="rounded-lg bg-green-500/10 px-2 py-1 text-xs text-green-400"
                        >
                            Recommended
                        </span>
                    </div>

                    <p
                        v-if="item.note"
                        class="mt-3 line-clamp-2 text-sm text-zinc-400"
                    >
                        {{ item.note }}
                    </p>

                    <p class="mt-3 text-xs text-zinc-600">
                        {{ item.updated_at }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>