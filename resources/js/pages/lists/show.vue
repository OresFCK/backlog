<script setup>
import { ref, watch } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

import ListHeader from '@/components/lists/ListHeader.vue'
import AddGameForm from '@/components/lists/AddGameForm.vue'
import RankingList from '@/components/lists/RankingList.vue'

import { router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    list: Object,
    games: Array,
})

const localItems = ref([])
const draggedIndex = ref(null)

const syncItems = () => {
    localItems.value = [...(props.list.items ?? [])].sort(
        (a, b) => a.position - b.position
    )
}

syncItems()

watch(
    () => props.list.items,
    syncItems,
    { deep: true }
)

const removeItem = (item) => {
    router.delete(`/lists/${props.list.id}/items/${item.id}`, {
        preserveScroll: true,
    })
}

const persistOrder = () => {
    router.patch(
        `/lists/${props.list.id}/items/reorder`,
        {
            items: localItems.value.map((item, index) => ({
                id: item.id,
                position: index + 1,
            })),
        },
        {
            preserveScroll: true,
        }
    )
}

const reorderItems = (fromIndex, toIndex) => {
    if (
        fromIndex === null ||
        fromIndex === toIndex ||
        toIndex < 0 ||
        toIndex >= localItems.value.length
    ) {
        return
    }

    const items = [...localItems.value]
    const [moved] = items.splice(fromIndex, 1)

    items.splice(toIndex, 0, moved)

    localItems.value = items.map((item, index) => ({
        ...item,
        position: index + 1,
    }))

    persistOrder()
}

const moveItem = (index, direction) => {
    reorderItems(index, index + direction)
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <ListHeader
                    :list="list"
                    :count="localItems.length"
                />

                <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
                    <AddGameForm
                        :list-id="list.id"
                        :games="games"
                        :items="localItems"
                    />

                    <RankingList
                        :items="localItems"
                        :dragged-index="draggedIndex"
                        @remove="removeItem"
                        @move="moveItem"
                        @drag-start="draggedIndex = $event"
                        @drop="
                            reorderItems(
                                draggedIndex,
                                $event
                            );
                            draggedIndex = null
                        "
                        @drag-end="draggedIndex = null"
                    />
                </section>
            </main>
        </div>
    </div>
</template>