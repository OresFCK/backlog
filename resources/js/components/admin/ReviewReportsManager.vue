<script setup>
import { router } from '@inertiajs/vue3'
import { Check, Trash2 } from 'lucide-vue-next'

defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
})

const resolveReport = (report) => {
    router.patch(
        `/admin/review-reports/${report.id}/resolve`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const deleteReview = (report) => {
    if (!window.confirm('Delete this review permanently?')) {
        return
    }

    router.delete(
        `/admin/review-reports/${report.id}/review`,
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-black text-white">
                Review Reports
            </h2>

            <p class="mt-1 text-sm text-zinc-400">
                Review reports submitted by users.
            </p>
        </div>

        <div
            v-if="reports.length"
            class="space-y-5"
        >
            <article
                v-for="report in reports"
                :key="report.id"
                class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-xl px-3 py-1 text-xs font-bold"
                                :class="
                                    report.status === 'resolved'
                                        ? 'bg-emerald-500/10 text-emerald-300'
                                        : 'bg-red-500/10 text-red-300'
                                "
                            >
                                {{ report.status }}
                            </span>

                            <span class="text-sm text-zinc-500">
                                {{ report.created_at }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm font-bold text-indigo-300">
                            {{ report.review?.game_title || 'Unknown game' }}
                        </p>

                        <h3 class="mt-1 text-xl font-black text-white">
                            {{ report.review?.title || 'Untitled review' }}
                        </h3>

                        <p class="mt-3 whitespace-pre-line text-sm text-zinc-300">
                            {{ report.review?.body }}
                        </p>

                        <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                            <div>
                                <p class="font-bold text-zinc-500">
                                    Author
                                </p>

                                <p class="text-zinc-300">
                                    {{ report.review?.user?.name || 'Unknown' }}
                                </p>
                            </div>

                            <div>
                                <p class="font-bold text-zinc-500">
                                    Reporter
                                </p>

                                <p class="text-zinc-300">
                                    {{ report.reporter?.name || 'Unknown' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="report.reason"
                            class="mt-4 rounded-xl border border-zinc-800 bg-zinc-900 p-4"
                        >
                            <p class="text-xs font-bold uppercase tracking-widest text-zinc-500">
                                Reason
                            </p>

                            <p class="mt-2 text-sm text-zinc-300">
                                {{ report.reason }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white"
                            @click="resolveReport(report)"
                        >
                            <Check class="h-4 w-4" />
                            Resolve
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 px-4 py-2 text-sm font-bold text-red-300 transition hover:bg-red-500/20"
                            @click="deleteReview(report)"
                        >
                            <Trash2 class="h-4 w-4" />
                            Delete Review
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-zinc-800 p-10 text-center"
        >
            <h3 class="text-xl font-bold text-white">
                No reports yet
            </h3>

            <p class="mt-2 text-zinc-500">
                Reported reviews will appear here.
            </p>
        </div>
    </section>
</template>