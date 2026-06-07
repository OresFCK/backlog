<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    suggestions: {
        type: Array,
        default: () => [],
    },
})

const resolveSubmission = (submission) => {
    router.patch(`/admin/user-submissions/${submission.id}/resolve`)
}

const deleteSubmission = (submission) => {
    if (!confirm('Delete this submission?')) {
        return
    }

    router.delete(`/admin/user-submissions/${submission.id}`)
}
</script>

<template>
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/60 p-8">
        <h2 class="text-2xl font-bold text-white">
            User submissions
        </h2>

        <p class="mt-2 text-zinc-400">
            Bug reports, suggestions and user admissions.
        </p>

        <div
            v-if="suggestions.length"
            class="mt-6 space-y-4"
        >
            <article
                v-for="submission in suggestions"
                :key="submission.id"
                class="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">
                            {{ submission.type }}
                        </p>

                        <h3 class="mt-2 text-lg font-bold text-white">
                            {{ submission.title }}
                        </h3>

                        <p class="mt-1 text-xs text-zinc-500">
                            By {{ submission.user?.name ?? 'Unknown user' }}
                        </p>

                        <p
                            v-if="submission.created_at"
                            class="mt-1 text-xs text-zinc-600"
                        >
                            {{ submission.created_at }}
                        </p>
                    </div>

                    <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-bold text-zinc-300">
                        {{ submission.status }}
                    </span>
                </div>

                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-zinc-300">
                    {{ submission.message }}
                </p>

                <a
                    v-if="submission.image_url"
                    :href="submission.image_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950"
                >
                    <img
                        :src="submission.image_url"
                        :alt="submission.title"
                        class="max-h-96 w-full object-contain"
                    >
                </a>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-400 disabled:opacity-50"
                        :disabled="submission.status === 'resolved'"
                        @click="resolveSubmission(submission)"
                    >
                        Resolve
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-400"
                        @click="deleteSubmission(submission)"
                    >
                        Delete
                    </button>
                </div>
            </article>
        </div>

        <div
            v-else
            class="mt-6 rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500"
        >
            No submissions yet.
        </div>
    </section>
</template>