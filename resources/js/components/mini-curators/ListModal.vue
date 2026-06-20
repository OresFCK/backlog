<script setup>
import { Package, X } from 'lucide-vue-next'

defineProps({
    list: {
        type: Object,
        required: true,
    },
})

defineEmits(['close'])
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
        @click.self="$emit('close')"
    >
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-zinc-800 bg-zinc-950 shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                        Custom List
                    </p>

                    <h2 class="mt-1 text-2xl font-black">
                        {{ list.title }}
                    </h2>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-zinc-800 p-2 text-zinc-300 transition hover:bg-zinc-700 hover:text-white"
                    @click="$emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="p-6">
                <p
                    v-if="list.description"
                    class="mb-6 whitespace-pre-line text-sm leading-7 text-zinc-300"
                >
                    {{ list.description }}
                </p>

                <div
                    v-if="list.items?.length"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <div
                        v-for="game in list.items"
                        :key="game.id"
                        class="flex gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-4"
                    >
                        <img
                            v-if="game.cover_url"
                            :src="game.cover_url"
                            :alt="game.title"
                            class="h-24 w-16 rounded-xl object-cover"
                        />

                        <div
                            v-else
                            class="flex h-24 w-16 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-zinc-500"
                        >
                            <Package class="h-7 w-7" />
                        </div>

                        <div>
                            <p class="text-xs font-bold text-zinc-500">
                                #{{ game.position ?? '' }}
                            </p>

                            <h3 class="mt-1 font-black">
                                {{ game.title }}
                            </h3>

                            <p
                                v-if="game.note"
                                class="mt-2 line-clamp-3 text-sm text-zinc-400"
                            >
                                {{ game.note }}
                            </p>
                        </div>
                    </div>
                </div>

                <p
                    v-else
                    class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500"
                >
                    This list has no games yet.
                </p>
            </div>
        </div>
    </div>
</template>