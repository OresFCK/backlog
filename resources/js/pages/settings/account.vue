<script setup>
import { useForm } from '@inertiajs/vue3'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,
})

const form = useForm({
    display_name: props.user?.display_name ?? '',
})

const submit = () => {
    form.patch('/settings/account', {
        preserveScroll: true,
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
                        Account settings
                    </h1>

                    <p class="mt-2 text-zinc-400">
                        Set your app nickname. If empty, your Steam name will be used.
                    </p>

                    <form
                        class="mt-8 space-y-6"
                        @submit.prevent="submit"
                    >
                        <div>
                            <label class="text-sm font-bold text-zinc-300">
                                App nickname
                            </label>

                            <input
                                v-model="form.display_name"
                                type="text"
                                maxlength="32"
                                class="mt-2 w-full rounded-2xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-white outline-none focus:border-zinc-600"
                                placeholder="e.g. Dagne"
                            >

                            <p class="mt-2 text-xs text-zinc-500">
                                Steam name: {{ user.steam_persona_name ?? user.name }}
                            </p>

                            <p
                                v-if="form.errors.display_name"
                                class="mt-2 text-sm text-red-400"
                            >
                                {{ form.errors.display_name }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save nickname' }}
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</template>