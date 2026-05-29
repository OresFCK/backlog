<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,

    items: {
        type: Array,
        default: () => [],
    },
})

const editingItem = ref(null)

const form = useForm({
    name: '',
    description: '',
    type: 'profile_overlay',
    price: 0,
    image: null,
    is_active: true,
})

const typeInfo = {
    profile_overlay: {
        size: '512x512',
        format: 'PNG / WebP',
        notes: 'Transparent background required',
    },

    badge: {
        size: '128x128',
        format: 'PNG / WebP',
        notes: 'Transparent background required',
    },

    profile_banner: {
        size: '1600x400',
        format: 'JPG / WebP',
        notes: 'Panoramic banner',
    },

    theme: {
        size: '1200x700',
        format: 'JPG / WebP',
        notes: 'Preview image only',
    },

    profile_showcase: {
        size: '1200x600',
        format: 'JPG / WebP',
        notes: 'Optional showcase image',
    },

    username_font: {
        size: 'No image',
        format: '-',
        notes: 'Uses CSS/font only',
    },

    user_title: {
        size: 'No image',
        format: '-',
        notes: 'Text-only item',
    },
}

const hasImageUpload = () => {
    return !['username_font', 'user_title'].includes(form.type)
}

const resetForm = () => {
    editingItem.value = null

    form.reset()

    form.type = 'profile_overlay'
    form.price = 0
    form.image = null
    form.is_active = true
}

const submit = () => {
    if (!hasImageUpload()) {
        form.image = null
    }

    if (editingItem.value) {
        form.post(`/admin/shop-items/${editingItem.value.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: resetForm,
            data: {
                _method: 'put',
            },
        })

        return
    }

    form.post('/admin/shop-items', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: resetForm,
    })
}

const editItem = (item) => {
    editingItem.value = item

    form.name = item.name
    form.description = item.description ?? ''
    form.type = item.type
    form.price = item.price
    form.image = null
    form.is_active = item.is_active
}

const deleteItem = (item) => {
    if (!confirm(`Delete "${item.name}"?`)) {
        return
    }

    router.delete(`/admin/shop-items/${item.id}`, {
        preserveScroll: true,
    })
}
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
                        Add, edit and manage shop items.
                    </p>
                </section>

                <section class="grid gap-8 xl:grid-cols-[420px_1fr]">
                    <form
                        class="space-y-5 rounded-3xl border border-zinc-800 bg-zinc-900 p-6"
                        @submit.prevent="submit"
                    >
                        <h2 class="text-xl font-bold text-white">
                            {{ editingItem ? 'Edit item' : 'Add shop item' }}
                        </h2>

                        <input
                            v-model="form.name"
                            placeholder="Item name"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <textarea
                            v-model="form.description"
                            placeholder="Description"
                            rows="4"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <select
                            v-model="form.type"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        >
                            <option value="profile_overlay">
                                Profile overlay
                            </option>

                            <option value="badge">
                                Badge
                            </option>

                            <option value="profile_banner">
                                Profile banner
                            </option>

                            <option value="theme">
                                Theme
                            </option>

                            <option value="user_title">
                                User title
                            </option>

                            <option value="profile_showcase">
                                Profile showcase
                            </option>

                            <option value="username_font">
                                Username font
                            </option>
                        </select>

                        <div
                            class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-4"
                        >
                            <h3 class="mb-3 text-sm font-bold text-blue-300">
                                Asset requirements
                            </h3>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="text-zinc-400">
                                        Recommended size
                                    </span>

                                    <span class="text-right font-medium text-white">
                                        {{ typeInfo[form.type]?.size }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="text-zinc-400">
                                        Format
                                    </span>

                                    <span class="text-right font-medium text-white">
                                        {{ typeInfo[form.type]?.format }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="text-zinc-400">
                                        Notes
                                    </span>

                                    <span class="text-right font-medium text-white">
                                        {{ typeInfo[form.type]?.notes }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <input
                            v-model="form.price"
                            type="number"
                            min="0"
                            placeholder="Price"
                            class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                        />

                        <div v-if="hasImageUpload()">
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none file:mr-4 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-zinc-950 hover:file:bg-zinc-200"
                                @input="form.image = $event.target.files[0]"
                            />
                        </div>

                        <div
                            v-else-if="form.type === 'user_title'"
                            class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-sm text-amber-300"
                        >
                            This item uses only text and does not require an
                            image.
                        </div>

                        <div
                            v-else-if="form.type === 'username_font'"
                            class="rounded-2xl border border-purple-500/20 bg-purple-500/5 p-4 text-sm text-purple-300"
                        >
                            This item should store font information in metadata
                            instead of an image.
                        </div>

                        <label
                            class="flex items-center gap-3 text-sm text-zinc-300"
                        >
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-700 bg-zinc-950"
                            />

                            Active in shop
                        </label>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200"
                                :disabled="form.processing"
                            >
                                {{ editingItem ? 'Save changes' : 'Add item' }}
                            </button>

                            <button
                                v-if="editingItem"
                                type="button"
                                class="rounded-2xl bg-zinc-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-700"
                                @click="resetForm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>

                    <div
                        class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900"
                    >
                        <div class="border-b border-zinc-800 p-6">
                            <h2 class="text-xl font-bold text-white">
                                Shop items
                            </h2>
                        </div>

                        <div class="divide-y divide-zinc-800">
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="flex items-center gap-5 p-5"
                            >
                                <div
                                    class="flex h-20 w-28 items-center justify-center overflow-hidden rounded-2xl bg-zinc-800"
                                >
                                    <img
                                        v-if="item.image_url"
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span
                                        v-else
                                        class="px-3 text-center text-xs font-medium text-zinc-500"
                                    >
                                        No image
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-white">
                                        {{ item.name }}
                                    </h3>

                                    <p class="mt-1 text-sm text-zinc-400">
                                        {{ item.type }} · {{ item.price }} coins
                                    </p>

                                    <p
                                        class="mt-1 truncate text-sm text-zinc-500"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        item.is_active
                                            ? 'bg-emerald-500/10 text-emerald-400'
                                            : 'bg-zinc-800 text-zinc-500'
                                    "
                                >
                                    {{ item.is_active ? 'Active' : 'Hidden' }}
                                </span>

                                <button
                                    type="button"
                                    class="rounded-xl bg-zinc-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-zinc-700"
                                    @click="editItem(item)"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-red-500/10 px-4 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/20"
                                    @click="deleteItem(item)"
                                >
                                    Delete
                                </button>
                            </div>

                            <div
                                v-if="!items.length"
                                class="p-10 text-center text-zinc-500"
                            >
                                No shop items yet.
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>