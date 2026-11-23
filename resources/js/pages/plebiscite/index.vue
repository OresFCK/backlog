<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Gamepad2, Trophy } from 'lucide-vue-next';
import VoteCardModal from '@/components/plebiscite/VoteCardModal.vue';

type Candidate = { key: string; title: string; image: string; votes: number };
type Category = {
    key: string;
    title: string;
    candidates: Candidate[];
    my_vote: string | null;
    total_votes: number;
};

const props = defineProps<{
    categories: Category[];
    isAuthenticated: boolean;
    signInUrl: string;
    userName: string | null;
}>();

const activeKey = ref(props.categories[0]?.key ?? '');
const imageErrors = ref<Record<string, boolean>>({});
const voteCardOpen = ref(false);
const hasVotes = computed(() => props.categories.some((category) => category.my_vote));
const activeCategory = computed(
    () => props.categories.find((category) => category.key === activeKey.value) ?? props.categories[0],
);
const rankedCandidates = computed(() =>
    [...(activeCategory.value?.candidates ?? [])].sort((a, b) => b.votes - a.votes),
);

const voteFor = (candidate: Candidate) => {
    if (!props.isAuthenticated || !activeCategory.value) return;

    router.post(
        '/plebiscite/vote',
        { category: activeCategory.value.key, candidate: candidate.key },
        { preserveScroll: true },
    );
};

const share = async () => {
    if (navigator.share) {
        await navigator.share({ title: 'Złote NIKTniepytał', url: window.location.href });
        return;
    }
    await navigator.clipboard.writeText(window.location.href);
};
</script>

<template>
    <Head title="Złote NIKTniepytał" />
    <div class="plebiscite-page relative min-h-screen overflow-hidden bg-[#0b0b0d] text-white">
        <div class="plebiscite-background-grid" aria-hidden="true" />
        <header class="relative z-10 border-b-2 border-zinc-800 bg-[#111117]/95">
            <div class="mx-auto flex max-w-[1800px] items-center justify-between px-4 py-2 sm:px-6">
                <Link href="/home" class="flex items-center gap-3 font-mono font-black">
                    <span class="flex h-8 w-8 items-center justify-center border-2 border-cyan-300 bg-cyan-300 text-zinc-950 shadow-[2px_2px_0_#fff]"><Gamepad2 class="h-4 w-4" /></span>
                    CURATOR.GG
                </Link>
                <Link href="/home" class="inline-flex items-center gap-2 font-mono text-xs text-zinc-400 hover:text-white"><ArrowLeft class="h-4 w-4" /> Home</Link>
            </div>
        </header>

        <main class="relative z-10 mx-auto max-w-[1800px] px-3 py-3 sm:px-6 sm:py-4">
            <section class="relative overflow-hidden border-2 border-zinc-700 bg-[#171823]/95 px-4 py-3 shadow-[4px_4px_0_#050506] sm:px-6 sm:py-4">
                <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-mono text-[10px] font-black tracking-[.22em] text-amber-300 uppercase">Curator.gg community awards · 2026</p>
                        <h1 class="mt-1 font-mono text-2xl leading-tight font-black sm:text-3xl"><span class="text-cyan-300">ZŁOTE</span> NIKTniepytał</h1>
                        <p class="mt-1 max-w-2xl text-xs leading-5 text-zinc-300 sm:text-sm">Wybierz faworytów. Jeden głos w każdej kategorii — możesz go później zmienić. Wyniki na żywo.</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2 sm:self-center">
                        <button v-if="isAuthenticated && hasVotes" type="button" class="border-2 border-cyan-300 bg-cyan-300 px-3 py-2 font-mono text-xs font-black text-zinc-950 shadow-[2px_2px_0_#fff] hover:translate-x-0.5 hover:translate-y-0.5" @click="voteCardOpen = true">MAKE MY VOTE CARD</button>
                        <button type="button" class="border-2 border-white bg-amber-300 px-3 py-2 font-mono text-xs font-black text-zinc-950 shadow-[2px_2px_0_#fff] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0_#fff]" @click="share">SHARE THE VOTE ↗</button>
                    </div>
                </div>
                <div class="relative mt-2 flex flex-wrap gap-2 font-mono text-[10px] font-bold text-zinc-300">
                    <span class="border border-zinc-600 bg-zinc-950/70 px-2 py-1">{{ categories.length }} CATEGORIES</span>
                    <span class="border border-zinc-600 bg-zinc-950/70 px-2 py-1">6 NOMINEES EACH</span>
                    <span class="border border-zinc-600 bg-zinc-950/70 px-2 py-1">{{ categories.reduce((sum, category) => sum + category.total_votes, 0) }} TOTAL VOTES</span>
                </div>
            </section>

            <div class="mt-3 grid gap-4 lg:grid-cols-[260px_minmax(0,1fr)] xl:grid-cols-[290px_minmax(0,1fr)]">
                <aside class="h-fit border-2 border-zinc-800 bg-[#111117]/95 p-2 lg:sticky lg:top-3">
                    <p class="px-2 py-1.5 font-mono text-[10px] font-black tracking-widest text-zinc-500 uppercase">Pick a category</p>
                    <nav class="plebiscite-scroll grid max-h-[calc(100dvh-330px)] gap-0.5 overflow-y-auto pr-1">
                        <button v-for="(category, index) in categories" :key="category.key" type="button" class="flex items-start gap-2 border px-2 py-2 text-left text-xs transition" :class="activeKey === category.key ? 'border-cyan-300 bg-cyan-300 text-zinc-950 shadow-[2px_2px_0_#fff]' : 'border-transparent text-zinc-300 hover:border-zinc-700 hover:bg-zinc-900'" @click="activeKey = category.key">
                            <span class="font-mono text-xs font-black opacity-60">{{ String(index + 1).padStart(2, '0') }}</span>
                            <span class="font-bold leading-4">{{ category.title }}</span>
                        </button>
                    </nav>
                </aside>

                <section v-if="activeCategory" class="min-w-0">
                    <header class="mb-3 flex items-end justify-between gap-4 border-b-2 border-zinc-800 pb-2">
                        <div>
                            <p class="font-mono text-xs font-bold tracking-widest text-cyan-300 uppercase">Category {{ String(categories.findIndex((category) => category.key === activeCategory.key) + 1).padStart(2, '0') }}</p>
                            <h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ activeCategory.title }}</h2>
                        </div>
                        <p class="shrink-0 font-mono text-xs text-zinc-500">{{ activeCategory.total_votes }} votes</p>
                    </header>

                    <p v-if="!isAuthenticated" class="mb-5 border-2 border-amber-300/40 bg-amber-300/10 p-4 text-sm text-amber-100">
                        Zaloguj się, aby zagłosować. Głosowanie zapisze jeden wybór w tej kategorii.
                        <a :href="signInUrl" class="ml-2 inline-flex items-center gap-1 font-black underline underline-offset-4">Sign in <ArrowLeft class="h-3 w-3 rotate-180" /></a>
                    </p>
                    <p v-else-if="activeCategory.my_vote" class="mb-5 border-2 border-emerald-400/40 bg-emerald-400/10 p-4 text-sm text-emerald-100">Twój głos: <strong>{{ activeCategory.candidates.find((candidate) => candidate.key === activeCategory.my_vote)?.title }}</strong>. Możesz wybrać innego nominowanego, aby zmienić głos.</p>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <article v-for="(candidate, index) in rankedCandidates" :key="candidate.key" class="group overflow-hidden border-2 bg-[#17171c] transition" :class="activeCategory.my_vote === candidate.key ? 'border-cyan-300 shadow-[4px_4px_0_#67e8f9]' : 'border-zinc-800 hover:border-zinc-500'">
                            <div class="relative aspect-video overflow-hidden bg-zinc-900">
                                <img v-if="candidate.image && !imageErrors[candidate.key]" :src="candidate.image" :alt="candidate.title" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" @error="imageErrors[candidate.key] = true" />
                                <div v-else class="plebiscite-game-placeholder flex h-full w-full flex-col items-center justify-center bg-[#171823] font-mono text-cyan-200">
                                    <span class="text-5xl font-black text-cyan-300">{{ candidate.title.slice(0, 1) }}</span>
                                    <span class="mt-2 text-[10px] font-bold tracking-[.25em]">WISHLIST PICK</span>
                                </div>
                                <span class="absolute top-3 left-3 border-2 border-white bg-zinc-950 px-2 py-1 font-mono text-xs font-black">#{{ String(index + 1).padStart(2, '0') }}</span>
                                <span v-if="activeCategory.my_vote === candidate.key" class="absolute top-3 right-3 flex h-8 w-8 items-center justify-center border-2 border-zinc-950 bg-cyan-300 text-zinc-950"><Check class="h-5 w-5" /></span>
                            </div>
                            <div class="p-4">
                                <h3 class="min-h-12 text-base font-black leading-6">{{ candidate.title }}</h3>
                                <div class="mt-3 flex items-center justify-between font-mono text-xs text-zinc-400">
                                    <span>{{ candidate.votes }} {{ candidate.votes === 1 ? 'VOTE' : 'VOTES' }}</span>
                                    <span>{{ activeCategory.total_votes ? Math.round(candidate.votes / activeCategory.total_votes * 100) : 0 }}%</span>
                                </div>
                                <div class="mt-2 h-2 border border-zinc-700 bg-zinc-950"><div class="h-full bg-cyan-300" :style="{ width: `${activeCategory.total_votes ? candidate.votes / activeCategory.total_votes * 100 : 0}%` }" /></div>
                                <button v-if="isAuthenticated" type="button" class="mt-4 w-full border-2 px-3 py-3 font-mono text-xs font-black transition" :class="activeCategory.my_vote === candidate.key ? 'border-cyan-300 bg-cyan-300 text-zinc-950' : 'border-zinc-600 text-white hover:border-white hover:bg-white hover:text-zinc-950'" @click="voteFor(candidate)">
                                    {{ activeCategory.my_vote === candidate.key ? 'YOUR PICK ✓' : 'VOTE FOR THIS' }}
                                </button>
                                <a v-else :href="signInUrl" class="mt-4 block w-full border-2 border-zinc-600 px-3 py-3 text-center font-mono text-xs font-black hover:border-white hover:bg-white hover:text-zinc-950">SIGN IN TO VOTE</a>
                            </div>
                        </article>
                    </div>
                    <div class="mt-8 flex items-center gap-2 font-mono text-xs text-zinc-600"><Trophy class="h-4 w-4 text-amber-300" /> NOMINEES ARE A STARTING POINT — COMMUNITY RESULTS UPDATE AS PEOPLE VOTE.</div>
                </section>
            </div>
        </main>
        <VoteCardModal :open="voteCardOpen" :categories="categories" :user-name="userName ?? ''" @close="voteCardOpen = false" />
    </div>
</template>

<style scoped>
.plebiscite-background-grid {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0.32;
    background-image:
        linear-gradient(rgba(148, 163, 184, 0.24) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148, 163, 184, 0.24) 1px, transparent 1px);
    background-size: 22px 22px;
}

.plebiscite-game-placeholder {
    background-image:
        linear-gradient(rgba(103, 232, 249, 0.12) 1px, transparent 1px),
        linear-gradient(90deg, rgba(103, 232, 249, 0.12) 1px, transparent 1px),
        linear-gradient(135deg, #1f2937, #111117 65%);
    background-size: 10px 10px, 10px 10px, auto;
    image-rendering: pixelated;
}

.plebiscite-scroll {
    scrollbar-color: #67e8f9 #18181b;
    scrollbar-width: thin;
}

.plebiscite-scroll::-webkit-scrollbar {
    width: 10px;
}

.plebiscite-scroll::-webkit-scrollbar-track {
    background: #18181b;
    border-left: 1px solid #3f3f46;
}

.plebiscite-scroll::-webkit-scrollbar-thumb {
    background: #67e8f9;
    border: 2px solid #18181b;
}

.plebiscite-scroll::-webkit-scrollbar-thumb:hover {
    background: #a5f3fc;
}

@media (max-height: 700px) and (min-width: 1024px) {
    .plebiscite-scroll {
        max-height: calc(100dvh - 270px);
    }
}
</style>
