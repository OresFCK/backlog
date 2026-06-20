<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,

    lists: {
        type: Array,
        default: () => [],
    },
})

const form = ref({
    title: '',
    description: '',
    visibility: 'private',
})

const editingListId = ref(null)

const editForm = ref({
    title: '',
    description: '',
    visibility: 'private',
})

const createList = () => {
    router.post('/lists', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            form.value = {
                title: '',
                description: '',
                visibility: 'private',
            }
        },
    })
}

const startEditing = (list) => {
    editingListId.value = list.id

    editForm.value = {
        title: list.title,
        description: list.description || '',
        visibility: list.visibility,
    }
}

const cancelEditing = () => {
    editingListId.value = null

    editForm.value = {
        title: '',
        description: '',
        visibility: 'private',
    }
}

const updateList = (list) => {
    router.patch(`/lists/${list.id}`, editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEditing()
        },
    })
}

const deleteList = (list) => {
    if (!confirm(`Usunąć listę "${list.title}"?`)) {
        return
    }

    router.delete(`/lists/${list.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 space-y-8 p-8">
                <section class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <h1 class="text-3xl font-black">
                            Custom Lists
                        </h1>

                        <p class="mt-2 max-w-2xl text-zinc-400">
                            Create your own rankings, collections and favorite game lists.
                        </p>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[380px_1fr]">
                    <form
                        class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                        @submit.prevent="createList"
                    >
                        <h2 class="text-xl font-bold">
                            Create list
                        </h2>

                        <div class="mt-5 space-y-4">
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="Atlus Ranked, Top JRPG..."
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                            />

                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="Optional description..."
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                            />

                            <select
                                v-model="form.visibility"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                            >
                                <option value="public">
                                    Public
                                </option>

                                <option value="friends">
                                    Friends Only
                                </option>

                                <option value="private">
                                    Private
                                </option>
                            </select>

                            <button
                                type="submit"
                                class="w-full rounded-xl bg-white px-4 py-3 text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                            >
                                Create list
                            </button>
                        </div>
                    </form>

                    <div class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-bold">
                                Your lists
                            </h2>

                            <p class="text-sm text-zinc-500">
                                {{ lists.length }} lists
                            </p>
                        </div>

                        <div
                            v-if="lists.length"
                            class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="list in lists"
                                :key="list.id"
                                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5 transition hover:border-zinc-600 hover:bg-zinc-900"
                            >
                                <form
                                    v-if="editingListId === list.id"
                                    class="space-y-4"
                                    @submit.prevent="updateList(list)"
                                >
                                    <input
                                        v-model="editForm.title"
                                        type="text"
                                        required
                                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                                    />

                                    <textarea
                                        v-model="editForm.description"
                                        rows="4"
                                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none placeholder:text-zinc-500 focus:border-zinc-500"
                                    />

                                    <select
                                        v-model="editForm.visibility"
                                        class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                                    >
                                        <option value="public">
                                            Public
                                        </option>

                                        <option value="friends">
                                            Friends Only
                                        </option>

                                        <option value="private">
                                            Private
                                        </option>
                                    </select>

                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="flex-1 rounded-xl bg-white px-4 py-2 text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                                        >
                                            Save
                                        </button>

                                        <button
                                            type="button"
                                            class="flex-1 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                                            @click="cancelEditing"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>

                                <template v-else>
                                    <Link :href="`/lists/${list.id}`">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h3 class="font-black text-white">
                                                    {{ list.title }}
                                                </h3>

                                                <p class="mt-2 line-clamp-2 text-sm text-zinc-400">
                                                    {{ list.description || 'No description yet.' }}
                                                </p>
                                            </div>

                                            <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400">
                                                {{ list.visibility }}
                                            </span>
                                        </div>

                                        <p class="mt-5 text-sm text-zinc-500">
                                            {{ list.items_count }} games
                                        </p>
                                    </Link>

                                    <div class="mt-5 flex gap-2">
                                        <button
                                            type="button"
                                            class="flex-1 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                                            @click="startEditing(list)"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="flex-1 rounded-xl border border-red-900/70 px-4 py-2 text-sm font-bold text-red-400 transition hover:border-red-700 hover:bg-red-950/40"
                                            @click="deleteList(list)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-950 p-10 text-center"
                        >
                            <p class="font-bold">
                                No custom lists yet
                            </p>

                            <p class="mt-2 text-sm text-zinc-500">
                                Create your first ranking or collection.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>