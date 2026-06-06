<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Check, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
})

const selectedStatus = ref('all')
const selectedUser = ref('all')

const reporterOptions = computed(() => {
    const users = new Map()

    props.reports.forEach((report) => {
        if (report.reporter?.id) {
            users.set(report.reporter.id, report.reporter.name || 'Unknown')
        }
    })

    return Array.from(users, ([id, name]) => ({ id, name }))
        .sort((a, b) => a.name.localeCompare(b.name))
})

const filteredReports = computed(() => {
    return props.reports.filter((report) => {
        const matchesStatus =
            selectedStatus.value === 'all' ||
            report.status === selectedStatus.value

        const matchesUser =
            selectedUser.value === 'all' ||
            String(report.reporter?.id) === String(selectedUser.value)

        return matchesStatus && matchesUser
    })
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
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-white">
                    Review Reports
                </h2>

                <p class="mt-1 text-sm text-zinc-400">
                    Review reports submitted by users.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <select
                    v-model="selectedStatus"
                    class="rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                >
                    <option value="all">
                        All statuses
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="resolved">
                        Resolved
                    </option>
                </select>

                <select
                    v-model="selectedUser"
                    class="rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm text-white outline-none focus:border-zinc-500"
                >
                    <option value="all">
                        All reporters
                    </option>

                    <option
                        v-for="user in reporterOptions"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </option>
                </select>
            </div>
        </div>

        <p class="mb-4 text-sm text-zinc-500">
            Showing {{ filteredReports.length }} of {{ reports.length }} reports.
        </p>

        <div
            v-if="filteredReports.length"
            class="space-y-5"
        >
            <article
                v-for="report in filteredReports"
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
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-2 text-sm font-bold text-zinc-300 transition hover:bg-zinc-900 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="report.status === 'resolved'"
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
                No reports found
            </h3>

            <p class="mt-2 text-zinc-500">
                Try changing the selected filters.
            </p>
        </div>
    </section>
</template>