<script setup>
defineProps({
    game: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <aside class="space-y-4">
        <img
            v-if="game.cover_url"
            :src="game.cover_url"
            class="w-full rounded-2xl object-cover"
        />

        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <h3 class="font-bold text-white">
                Information
            </h3>

            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="text-zinc-500">
                        Developer
                    </dt>

                    <dd class="text-zinc-200">
                        {{ game.developers?.join(', ') || 'Unknown' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-zinc-500">
                        Publisher
                    </dt>

                    <dd class="text-zinc-200">
                        {{
                            game.publishers?.join(', ') ||
                            game.publisher ||
                            'Unknown'
                        }}
                    </dd>
                </div>

                <div>
                    <dt class="text-zinc-500">
                        Release date
                    </dt>

                    <dd class="text-zinc-200">
                        {{ game.release_date || 'Unknown' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-zinc-500">
                        Platform
                    </dt>

                    <dd class="text-zinc-200">
                        <span v-if="game.platforms?.windows">
                            Windows
                        </span>

                        <span v-if="game.platforms?.mac">
                            , Mac
                        </span>

                        <span v-if="game.platforms?.linux">
                            , Linux
                        </span>

                        <span v-if="game.is_custom">
                            Custom
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </aside>
</template>