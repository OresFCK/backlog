<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'

const props = defineProps({
    user: Object,
})

const page = usePage()

const successMessage = computed(() => page.props.flash?.success)

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

                    <div
                        v-if="successMessage"
                        class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300"
                    >
                        {{ successMessage }}
                    </div>

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
                                :class="[
                                    'mt-2 w-full rounded-2xl border bg-zinc-950 px-4 py-3 text-white outline-none',
                                    form.errors.display_name
                                        ? 'border-red-500 focus:border-red-400'
                                        : 'border-zinc-800 focus:border-zinc-600',
                                ]"
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

                    <section class="mt-10 border-t border-zinc-800 pt-8">
                        <h2 class="text-lg font-semibold text-white">
                            Legal
                        </h2>

                        <p class="mt-2 text-sm text-zinc-400">
                            Review the legal documents governing your use of Curator.gg.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a
                                href="/terms"
                                class="rounded-xl border border-zinc-700 px-4 py-2 text-sm text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                            >
                                Terms of Service
                            </a>

                            <a
                                href="/privacy"
                                class="rounded-xl border border-zinc-700 px-4 py-2 text-sm text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                            >
                                Privacy Policy
                            </a>
                        </div>
                    </section>
                </section>
            </main>
        </div>
    </div>
</template>
