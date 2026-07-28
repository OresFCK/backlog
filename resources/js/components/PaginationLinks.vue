<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <nav
        v-if="pagination.last_page > 1"
        class="mt-8 flex flex-wrap items-center justify-center gap-2"
        aria-label="Pagination"
    >
        <Link
            v-if="pagination.prev_page_url"
            :href="pagination.prev_page_url"
            preserve-scroll
            class="inline-flex h-10 items-center gap-1 rounded-xl border border-zinc-700 px-3 text-sm font-bold text-zinc-300 hover:bg-zinc-900"
        >
            <ChevronLeft class="h-4 w-4" /> Previous
        </Link>

        <template v-for="link in pagination.links" :key="link.label">
            <Link
                v-if="
                    link.url &&
                    !link.label.includes('Previous') &&
                    !link.label.includes('Next')
                "
                :href="link.url"
                preserve-scroll
                class="flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-black"
                :class="
                    link.active
                        ? 'border-white bg-white text-zinc-950'
                        : 'border-zinc-700 text-zinc-400 hover:bg-zinc-900'
                "
            >
                <span v-html="link.label" />
            </Link>
            <span
                v-else-if="
                    !link.url &&
                    !link.label.includes('Previous') &&
                    !link.label.includes('Next')
                "
                class="flex h-10 min-w-10 items-center justify-center px-2 text-sm text-zinc-600"
                v-html="link.label"
            />
        </template>

        <Link
            v-if="pagination.next_page_url"
            :href="pagination.next_page_url"
            preserve-scroll
            class="inline-flex h-10 items-center gap-1 rounded-xl border border-zinc-700 px-3 text-sm font-bold text-zinc-300 hover:bg-zinc-900"
        >
            Next <ChevronRight class="h-4 w-4" />
        </Link>
    </nav>
</template>
