<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

defineProps({
    user: Object,
})

const fileInput = ref(null)

const form = useForm({
    type: 'bug',
    title: '',
    message: '',
    image: null,
})

const submit = () => {
    form.post('/settings/submissions', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset('title', 'message', 'image')

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
                        Report bug
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Tell us what broke or what behaves incorrectly.
                    </p>

                    <form
                        class="mt-8 space-y-5"
                        enctype="multipart/form-data"
                        @submit.prevent="submit"
                    >
                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                Title
                            </label>

                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                placeholder="Short bug summary"
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
                                Description
                            </label>

                            <textarea
                                v-model="form.message"
                                rows="8"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                placeholder="What happened? How can we reproduce it?"
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
                                accept="image/*"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-300 file:mr-4 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-zinc-950"
                                @input="form.image = $event.target.files[0]"
                            >

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
                            {{ form.processing ? 'Sending...' : 'Send bug report' }}
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</template>