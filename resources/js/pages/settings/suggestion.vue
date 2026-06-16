<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,
})

const fileInput = ref(null)

const allowedImageTypes = [
    'image/jpeg',
    'image/png',
    'image/webp',
]

const maxImageSize = 4 * 1024 * 1024

const form = useForm({
    type: 'suggestion',
    title: '',
    message: '',
    image: null,
})

const handleImageChange = (event) => {
    const file = event.target.files?.[0]

    form.clearErrors('image')
    form.image = null

    if (!file) {
        return
    }

    if (!allowedImageTypes.includes(file.type)) {
        form.setError(
            'image',
            'Only JPG, PNG and WEBP images are allowed.'
        )

        event.target.value = ''

        return
    }

    if (file.size > maxImageSize) {
        form.setError(
            'image',
            'Image cannot be larger than 4 MB.'
        )

        event.target.value = ''

        return
    }

    form.image = file
}

const submit = () => {
    if (form.errors.image) {
        return
    }

    form.post('/settings/submissions', {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            form.reset(
                'title',
                'message',
                'image'
            )

            form.clearErrors()

            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },
    })
}
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">
        <Sidebar />

        <div class="flex flex-1 flex-col">
            <Topbar :user="user" />

            <main class="flex-1 p-8">
                <section class="mx-auto max-w-3xl rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
                    <h1 class="text-3xl font-bold text-white">
                        Send suggestion
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Share an idea, improvement or user admission request.
                    </p>

                    <form
                        class="mt-8 space-y-5"
                        enctype="multipart/form-data"
                        @submit.prevent="submit"
                    >
                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                Type
                            </label>

                            <select
                                v-model="form.type"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                            >
                                <option value="suggestion">
                                    Suggestion
                                </option>

                                <option value="admission">
                                    User admission
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                Title
                            </label>

                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                placeholder="Short title"
                            >

                            <p
                                v-if="form.errors.title"
                                class="mt-2 text-sm text-red-400"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                Message
                            </label>

                            <textarea
                                v-model="form.message"
                                rows="8"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                placeholder="Describe your idea or request"
                            />

                            <p
                                v-if="form.errors.message"
                                class="mt-2 text-sm text-red-400"
                            >
                                {{ form.errors.message }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                Screenshot / image
                            </label>

                            <input
                                ref="fileInput"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-300 file:mr-4 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-zinc-950"
                                @change="handleImageChange"
                            >

                            <p class="mt-2 text-xs text-zinc-500">
                                Allowed formats: JPG, PNG, WEBP. Maximum size: 4 MB.
                            </p>

                            <p
                                v-if="form.errors.image"
                                class="mt-2 text-sm text-red-400"
                            >
                                {{ form.errors.image }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Sending...' : 'Send suggestion' }}
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</template>