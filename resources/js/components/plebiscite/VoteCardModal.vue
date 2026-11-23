<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { Download, X } from 'lucide-vue-next';

type Category = {
    key: string;
    title: string;
    candidates: { key: string; title: string; image?: string | null }[];
    my_vote: string | null;
};

const props = defineProps<{
    open: boolean;
    categories: Category[];
    userName: string;
}>();
const emit = defineEmits<{ close: [] }>();

const canvas = ref<HTMLCanvasElement | null>(null);
const selectedKeys = ref<string[]>([]);
const selectedTheme = ref('cyan');
const rendering = ref(false);
const themes = {
    cyan: { accent: '#67e8f9', secondary: '#fbbf24', glow: '#164e63', label: 'Arcade cyan' },
    gold: { accent: '#fbbf24', secondary: '#fb7185', glow: '#78350f', label: 'Golden ticket' },
    pink: { accent: '#f0abfc', secondary: '#67e8f9', glow: '#701a75', label: 'Pixel pink' },
};
const votedCategories = computed(() => props.categories.filter((category) => category.my_vote));
const selectedVotes = computed(() =>
    votedCategories.value.filter((category) => selectedKeys.value.includes(category.key)),
);

const wrapText = (context: CanvasRenderingContext2D, text: string, maxWidth: number, maxLines: number) => {
    const words = text.split(/\s+/);
    const lines: string[] = [];
    let line = '';
    for (const word of words) {
        const candidate = line ? `${line} ${word}` : word;
        if (context.measureText(candidate).width <= maxWidth) line = candidate;
        else {
            if (line) lines.push(line);
            line = word;
        }
    }
    if (line) lines.push(line);
    if (lines.length > maxLines) {
        lines.length = maxLines;
        lines[maxLines - 1] = `${lines[maxLines - 1].replace(/[.,;:!?]?$/, '')}…`;
    }
    return lines;
};

const renderCard = async () => {
    await nextTick();
    if (!canvas.value) return;

    if (!selectedVotes.value.length) {
        canvas.value.width = 1080;
        canvas.value.height = 540;
        const emptyContext = canvas.value.getContext('2d');
        if (emptyContext) {
            emptyContext.fillStyle = '#0b0b0d';
            emptyContext.fillRect(0, 0, canvas.value.width, canvas.value.height);
            emptyContext.fillStyle = '#67e8f9';
            emptyContext.font = '900 30px monospace';
            emptyContext.textAlign = 'center';
            emptyContext.fillText('SELECT A VOTE TO PREVIEW YOUR CARD', 540, 270);
            emptyContext.textAlign = 'left';
        }
        rendering.value = false;
        return;
    }
    rendering.value = true;

    const context = canvas.value.getContext('2d');
    if (!context) {
        rendering.value = false;
        return;
    }

    const width = 1080;
    const columns = 2;
    const rows = Math.ceil(selectedVotes.value.length / columns);
    const rowHeight = selectedVotes.value.length > 14 ? 88 : 96;
    const height = Math.max(760, 360 + rows * rowHeight + 120);
    const palette = themes[selectedTheme.value as keyof typeof themes];
    canvas.value.width = width;
    canvas.value.height = height;

    context.fillStyle = '#0b0b0d';
    context.fillRect(0, 0, width, height);
    context.strokeStyle = `${palette.accent}28`;
    context.lineWidth = 1;
    for (let x = 0; x < width; x += 28) {
        context.beginPath(); context.moveTo(x, 0); context.lineTo(x, height); context.stroke();
    }
    for (let y = 0; y < height; y += 28) {
        context.beginPath(); context.moveTo(0, y); context.lineTo(width, y); context.stroke();
    }

    context.fillStyle = '#171823';
    context.fillRect(34, 34, width - 68, 270);
    context.strokeStyle = palette.accent;
    context.lineWidth = 4;
    context.strokeRect(34, 34, width - 68, 270);

    context.fillStyle = palette.secondary;
    context.font = '900 20px monospace';
    context.letterSpacing = '4px';
    context.fillText('CURATOR.GG  •  COMMUNITY AWARDS  •  2026', 70, 88);
    context.fillStyle = '#ffffff';
    context.font = '900 68px Arial, sans-serif';
    context.letterSpacing = '0px';
    context.fillText('Złote NIKTniepytał', 70, 170);
    context.fillStyle = palette.accent;
    context.font = '700 26px Arial, sans-serif';
    context.fillText(`Głosy gracza: ${props.userName || 'Curator player'}`, 70, 220);
    context.fillStyle = '#a1a1aa';
    context.font = '600 19px monospace';
    context.fillText(`${selectedVotes.value.length} KATEGORII  ·  WYBORY SPOŁECZNOŚCI`, 70, 264);

    const cardGap = 20;
    const cardWidth = (width - 140 - cardGap) / 2;
    const startY = 330;
    const candidateImages = await Promise.all(selectedVotes.value.map((category) => {
        const candidate = category.candidates.find((item) => item.key === category.my_vote);
        if (!candidate?.image) return Promise.resolve(null);
        return new Promise<HTMLImageElement | null>((resolve) => {
            const image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = () => resolve(image);
            image.onerror = () => resolve(null);
            image.src = candidate.image!;
        });
    }));

    selectedVotes.value.forEach((category, index) => {
        const col = index % columns;
        const row = Math.floor(index / columns);
        const x = 50 + col * (cardWidth + cardGap);
        const y = startY + row * rowHeight;
        const candidate = category.candidates.find((item) => item.key === category.my_vote);
        context.fillStyle = '#17171c';
        context.fillRect(x, y, cardWidth, rowHeight - 8);
        context.strokeStyle = '#3f3f46';
        context.lineWidth = 2;
        context.strokeRect(x, y, cardWidth, rowHeight - 8);
        context.fillStyle = palette.accent;
        context.fillRect(x, y, 7, rowHeight - 8);

        const artX = x + 16;
        const artY = y + 12;
        const artWidth = 68;
        const artHeight = rowHeight - 32;
        context.fillStyle = palette.glow;
        context.fillRect(artX, artY, artWidth, artHeight);
        const image = candidateImages[index];
        if (image) {
            const scale = Math.max(artWidth / image.width, artHeight / image.height);
            const drawnWidth = image.width * scale;
            const drawnHeight = image.height * scale;
            context.save();
            context.beginPath();
            context.rect(artX, artY, artWidth, artHeight);
            context.clip();
            context.drawImage(image, artX + (artWidth - drawnWidth) / 2, artY + (artHeight - drawnHeight) / 2, drawnWidth, drawnHeight);
            context.restore();
        } else {
            context.fillStyle = palette.accent;
            context.font = '900 38px monospace';
            context.textAlign = 'center';
            context.fillText(candidate?.title.slice(0, 1) ?? '?', artX + artWidth / 2, artY + artHeight / 2 + 13);
            context.textAlign = 'left';
        }

        context.fillStyle = '#a1a1aa';
        context.font = '700 14px Arial, sans-serif';
        const textX = x + 96;
        const textWidth = cardWidth - 110;
        const categoryLines = wrapText(context, category.title, textWidth, 2);
        categoryLines.forEach((line, lineIndex) => context.fillText(line, textX, y + 26 + lineIndex * 17));

        context.fillStyle = '#ffffff';
        context.font = '900 21px Arial, sans-serif';
        const candidateLines = wrapText(context, candidate?.title ?? '', textWidth, 1);
        candidateLines.forEach((line) => context.fillText(line, textX, y + rowHeight - 17));
    });

    const footerY = startY + rows * rowHeight + 8;
    context.fillStyle = palette.secondary;
    context.fillRect(50, footerY, width - 100, 5);
    context.fillStyle = '#d4d4d8';
    context.font = '700 18px monospace';
    context.fillText('ZŁOTE NIKTniepytał  ·  CURATOR.GG', 50, footerY + 42);
    context.textAlign = 'right';
    context.fillStyle = '#71717a';
    context.fillText('MY PICKS • 2026', width - 50, footerY + 42);
    context.textAlign = 'left';
    rendering.value = false;
};

watch(() => props.open, async (open) => {
    if (!open) return;
    selectedKeys.value = votedCategories.value.map((category) => category.key);
    await renderCard();
});
watch([selectedKeys, selectedTheme], renderCard, { deep: true });

const download = async () => {
    await renderCard();
    if (!canvas.value) return;
    const link = document.createElement('a');
    link.download = 'złote-nikt-niepytał.png';
    link.href = canvas.value.toDataURL('image/png');
    link.click();
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-3 backdrop-blur-sm sm:p-6" @click.self="emit('close')">
        <section role="dialog" aria-modal="true" aria-labelledby="vote-card-title" class="flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden border-2 border-zinc-700 bg-[#111117] shadow-[7px_7px_0_#050506]">
            <header class="flex items-center justify-between border-b border-zinc-800 px-4 py-3 sm:px-6">
                <div><p class="font-mono text-[10px] font-black tracking-widest text-cyan-300 uppercase">Custom download</p><h2 id="vote-card-title" class="mt-1 text-lg font-black">Create your vote card</h2></div>
                <button type="button" class="border border-zinc-700 p-2 text-zinc-400 hover:text-white" aria-label="Close" @click="emit('close')"><X class="h-5 w-5" /></button>
            </header>
            <div class="grid min-h-0 gap-4 overflow-y-auto p-4 lg:grid-cols-[300px_minmax(0,1fr)] lg:p-6">
                <aside class="space-y-5">
                    <div>
                        <h3 class="mb-2 font-mono text-xs font-black tracking-widest text-zinc-400 uppercase">Color theme</h3>
                        <div class="grid gap-2">
                            <button v-for="(palette, key) in themes" :key="key" type="button" class="flex items-center gap-3 border px-3 py-2 text-left text-sm font-bold" :class="selectedTheme === key ? 'border-cyan-300 bg-zinc-900 text-white' : 'border-zinc-800 text-zinc-400 hover:border-zinc-600'" @click="selectedTheme = key">
                                <span class="h-5 w-5 border border-white/40" :style="{ backgroundColor: palette.accent }" />{{ palette.label }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-2"><h3 class="font-mono text-xs font-black tracking-widest text-zinc-400 uppercase">Include votes</h3><button type="button" class="text-[10px] font-bold text-cyan-300 hover:text-white" @click="selectedKeys = selectedKeys.length === votedCategories.length ? [] : votedCategories.map((category) => category.key)">{{ selectedKeys.length === votedCategories.length ? 'Clear all' : 'Select all' }}</button></div>
                        <p v-if="!votedCategories.length" class="border border-zinc-800 p-3 text-xs leading-5 text-zinc-500">Oddaj głos w przynajmniej jednej kategorii, a potem wróć tutaj, by zrobić kartę.</p>
                        <div v-else class="plebiscite-scroll max-h-64 space-y-1 overflow-y-auto pr-1">
                            <label v-for="category in votedCategories" :key="category.key" class="flex cursor-pointer items-start gap-2 border border-zinc-800 p-2 text-xs leading-4 text-zinc-300 hover:bg-zinc-900">
                                <input v-model="selectedKeys" type="checkbox" :value="category.key" class="mt-0.5 accent-cyan-300" />
                                <span>{{ category.title }}<strong class="mt-1 block text-white">{{ category.candidates.find((candidate) => candidate.key === category.my_vote)?.title }}</strong></span>
                            </label>
                        </div>
                    </div>
                    <button type="button" class="flex w-full items-center justify-center gap-2 border-2 border-white bg-cyan-300 px-4 py-3 font-mono text-sm font-black text-zinc-950 shadow-[3px_3px_0_#fff] transition hover:translate-x-0.5 hover:translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-40" :disabled="!selectedKeys.length || rendering" @click="download"><Download class="h-4 w-4" />{{ rendering ? 'Preparing…' : 'Download PNG' }}</button>
                </aside>
                <div class="flex min-h-64 items-start justify-center overflow-auto border border-zinc-800 bg-black/40 p-2 sm:p-4">
                    <canvas ref="canvas" class="h-auto max-h-[68vh] w-auto max-w-full border border-zinc-700 object-contain" />
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.plebiscite-scroll {
    scrollbar-color: #67e8f9 #18181b;
    scrollbar-width: thin;
}

.plebiscite-scroll::-webkit-scrollbar { width: 9px; }
.plebiscite-scroll::-webkit-scrollbar-track { background: #18181b; }
.plebiscite-scroll::-webkit-scrollbar-thumb { background: #67e8f9; border: 2px solid #18181b; }
</style>
