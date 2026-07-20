<script setup>
import { computed, ref } from 'vue';
import { Gamepad2, GripVertical, X } from 'lucide-vue-next';

const props = defineProps({
    tiers: { type: Array, required: true },
    items: { type: Array, required: true },
    editable: { type: Boolean, default: false },
});

const emit = defineEmits(['move', 'remove-item']);
const draggedGameId = ref(null);
const brokenImages = ref(new Set());

const itemsByTier = computed(() => {
    const groups = new Map(props.tiers.map((tier) => [tier.id, []]));
    groups.set(null, []);

    [...props.items]
        .sort((a, b) => a.position - b.position)
        .forEach((item) => {
            const key = groups.has(item.tier_id) ? item.tier_id : null;
            groups.get(key).push(item);
        });

    return groups;
});

const dropOn = (tierId) => {
    if (draggedGameId.value !== null) {
        emit('move', draggedGameId.value, tierId);
    }
    draggedGameId.value = null;
};

const markBroken = (gameId) => {
    brokenImages.value = new Set([...brokenImages.value, gameId]);
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950">
        <div
            v-for="tier in tiers"
            :key="tier.id"
            class="grid min-h-28 grid-cols-[78px_minmax(0,1fr)] border-b border-zinc-800 sm:grid-cols-[130px_minmax(0,1fr)]"
            @dragover.prevent
            @drop="dropOn(tier.id)"
        >
            <div
                class="flex items-center justify-center p-2 text-center text-sm font-black break-words text-zinc-950 sm:text-base"
                :style="{ backgroundColor: tier.color }"
            >
                {{ tier.name }}
            </div>

            <div
                class="flex min-w-0 flex-wrap content-start gap-2 p-2 sm:gap-3 sm:p-3"
            >
                <article
                    v-for="item in itemsByTier.get(tier.id)"
                    :key="item.game_id"
                    class="group relative w-[82px] overflow-hidden rounded-xl border border-zinc-700 bg-zinc-900 sm:w-28"
                    :draggable="editable"
                    @dragstart="draggedGameId = item.game_id"
                >
                    <img
                        v-if="item.image_url && !brokenImages.has(item.game_id)"
                        :src="item.image_url"
                        :alt="item.title"
                        class="aspect-square w-full object-cover"
                        @error="markBroken(item.game_id)"
                    />
                    <div
                        v-else
                        class="flex aspect-square items-center justify-center bg-zinc-800"
                    >
                        <Gamepad2 class="h-7 w-7 text-zinc-600" />
                    </div>
                    <p
                        class="line-clamp-2 p-2 text-[11px] leading-4 font-bold text-white"
                    >
                        {{ item.title }}
                    </p>
                    <select
                        v-if="editable"
                        :value="item.tier_id ?? ''"
                        class="m-1.5 w-[calc(100%-0.75rem)] rounded-lg border border-zinc-700 bg-zinc-950 p-1 text-[10px] text-zinc-300"
                        @change="
                            emit(
                                'move',
                                item.game_id,
                                $event.target.value || null,
                            )
                        "
                    >
                        <option value="">Unranked</option>
                        <option
                            v-for="option in tiers"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.name }}
                        </option>
                    </select>
                </article>
            </div>
        </div>
    </div>

    <section
        v-if="editable || itemsByTier.get(null)?.length"
        class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-900 p-4"
        @dragover.prevent
        @drop="dropOn(null)"
    >
        <div
            class="mb-3 flex items-center gap-2 text-sm font-black text-zinc-300"
        >
            <GripVertical class="h-4 w-4" />
            Unranked games
        </div>

        <div class="flex min-h-28 flex-wrap gap-3">
            <article
                v-for="item in itemsByTier.get(null)"
                :key="item.game_id"
                class="group relative w-24 overflow-hidden rounded-xl border border-zinc-700 bg-zinc-950 sm:w-28"
                :draggable="editable"
                @dragstart="draggedGameId = item.game_id"
            >
                <button
                    v-if="editable"
                    type="button"
                    class="absolute top-1 right-1 z-10 rounded-lg bg-black/80 p-1 text-white"
                    @click="emit('remove-item', item.game_id)"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
                <img
                    v-if="item.image_url && !brokenImages.has(item.game_id)"
                    :src="item.image_url"
                    :alt="item.title"
                    class="aspect-square w-full object-cover"
                    @error="markBroken(item.game_id)"
                />
                <div
                    v-else
                    class="flex aspect-square items-center justify-center bg-zinc-800"
                >
                    <Gamepad2 class="h-7 w-7 text-zinc-600" />
                </div>
                <p
                    class="line-clamp-2 p-2 text-[11px] leading-4 font-bold text-white"
                >
                    {{ item.title }}
                </p>
                <select
                    v-if="editable"
                    value=""
                    class="m-1.5 w-[calc(100%-0.75rem)] rounded-lg border border-zinc-700 bg-zinc-900 p-1 text-[10px] text-zinc-300"
                    @change="
                        emit('move', item.game_id, $event.target.value || null)
                    "
                >
                    <option value="">Move to...</option>
                    <option
                        v-for="option in tiers"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.name }}
                    </option>
                </select>
            </article>
        </div>
    </section>
</template>
