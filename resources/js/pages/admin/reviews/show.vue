<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,

    review: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <section class="mx-auto max-w-5xl rounded-3xl border border-zinc-800 bg-zinc-900 p-8">
                    <div class="mb-8 flex items-center gap-4">
                        <img
                            v-if="review.user?.avatar"
                            :src="review.user.avatar"
                            :alt="review.user.name"
                            class="h-14 w-14 rounded-full object-cover"
                        />

                        <div>
                            <p class="font-bold text-white">
                                {{ review.user?.name ?? 'Unknown user' }}
                            </p>

                            <p class="text-sm text-zinc-500">
                                {{ review.created_at }}
                            </p>
                        </div>
                    </div>

                    <p class="text-sm font-bold text-indigo-300">
                        {{ review.game_title }}
                    </p>

                    <h1 class="mt-2 text-3xl font-black text-white">
                        {{ review.title || 'Untitled review' }}
                    </h1>

                    <div class="mt-4 flex gap-3">
                        <span class="rounded-full border border-zinc-700 px-3 py-1 text-sm text-zinc-300">
                            {{ review.rating }}/10
                        </span>

                        <span
                            v-if="review.recommended"
                            class="rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-bold text-emerald-400"
                        >
                            Recommended
                        </span>

                        <span
                            v-if="review.not_recommended"
                            class="rounded-full bg-red-500/10 px-3 py-1 text-sm font-bold text-red-400"
                        >
                            Not recommended
                        </span>
                    </div>

                    <div class="mt-8 rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
                        <p class="whitespace-pre-wrap break-words text-[15px] leading-7 text-zinc-300">
                            {{ review.body }}
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>