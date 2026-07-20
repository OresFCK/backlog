<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Gamepad2 } from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        required: true,
    },

    items: {
        type: Array,
        default: () => [],
    },
});

const visibleItems = computed(() => props.items.slice(0, 10));
const brokenImages = ref(new Set());

const markImageAsBroken = (gameId) => {
    brokenImages.value = new Set([...brokenImages.value, gameId]);
};
</script>

<template>
    <section>
        <div class="mb-6">
            <h2 class="text-3xl font-black text-white">
                {{ title }}
            </h2>

            <p class="mt-2 text-zinc-400">
                {{ subtitle }}
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="item in visibleItems"
                :key="item.game.id"
                :href="item.game.public_url"
                class="group overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 transition duration-200 hover:-translate-y-1 hover:border-zinc-600 hover:shadow-xl hover:shadow-black/30 focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:outline-none"
            >
                <img
                    v-if="
                        item.game.header_image_url &&
                        !brokenImages.has(item.game.id)
                    "
                    :src="item.game.header_image_url"
                    :alt="item.game.title"
                    class="h-44 w-full object-cover"
                    loading="lazy"
                    @error="markImageAsBroken(item.game.id)"
                />

                <div
                    v-else
                    class="flex h-44 w-full items-center justify-center bg-gradient-to-br from-zinc-800 via-zinc-900 to-indigo-950/50"
                >
                    <Gamepad2
                        class="h-12 w-12 text-zinc-600 transition group-hover:text-zinc-500"
                    />
                </div>

                <div class="p-5">
                    <h3 class="text-xl font-black text-white">
                        {{ item.game.title }}
                    </h3>

                    <p class="mt-3 text-sm text-zinc-400">
                        {{ item.reason }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300"
                        >
                            Score {{ item.score }}
                        </span>

                        <span
                            class="rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-bold text-yellow-300"
                        >
                            ★ {{ item.average_rating }}
                        </span>
                    </div>
                </div>
            </Link>
        </div>
    </section>
</template>
