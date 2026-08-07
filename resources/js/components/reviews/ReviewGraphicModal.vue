<!-- eslint-disable vue/block-lang -->
<script setup>
import { Download, Image, Share2, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    review: { type: Object, required: true },
    imageUrl: { type: String, default: null },
});

defineEmits(['close']);
const canvas = ref(null);
const excerpt = ref('');
const theme = ref('gold');
const rendering = ref(false);
const imageWarning = ref(false);

const themes = {
    gold: { accent: '#f5c451', glow: '#6d4b16', label: 'Gold' },
    violet: { accent: '#a78bfa', glow: '#4c1d95', label: 'Violet' },
    mint: { accent: '#6ee7b7', glow: '#064e3b', label: 'Mint' },
};

const defaultExcerpt = computed(() => {
    const body = String(props.review.body ?? '')
        .replace(/\s+/g, ' ')
        .trim();

    return body.length > 230 ? `${body.slice(0, 227).trim()}…` : body;
});

const loadImage = (url) =>
    new Promise((resolve, reject) => {
        if (!url) {
            reject(new Error('No image'));

            return;
        }

        const image = new window.Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = url;
    });

const drawCoverImage = (context, image, width, height) => {
    const scale = Math.max(width / image.width, height / image.height);
    const drawnWidth = image.width * scale;
    const drawnHeight = image.height * scale;

    context.drawImage(
        image,
        (width - drawnWidth) / 2,
        (height - drawnHeight) / 2,
        drawnWidth,
        drawnHeight,
    );
};

const wrapText = (context, text, maxWidth, maxLines) => {
    const words = String(text).split(/\s+/).filter(Boolean);
    const lines = [];
    let line = '';

    words.forEach((word) => {
        const candidate = line ? `${line} ${word}` : word;

        if (context.measureText(candidate).width <= maxWidth) {
            line = candidate;
        } else if (line && lines.length < maxLines) {
            lines.push(line);
            line = word;
        }
    });

    if (line && lines.length < maxLines) {
        lines.push(line);
    }

    if (
        lines.length === maxLines &&
        words.join(' ').length > lines.join(' ').length
    ) {
        lines[maxLines - 1] =
            `${lines[maxLines - 1].replace(/[.,;:!?]?$/, '')}…`;
    }

    return lines;
};

const drawHexagon = (context, centerX, centerY, radius, accent) => {
    context.beginPath();

    for (let index = 0; index < 6; index += 1) {
        const angle = (Math.PI / 3) * index - Math.PI / 2;
        const x = centerX + radius * Math.cos(angle);
        const y = centerY + radius * Math.sin(angle);

        if (index === 0) {
            context.moveTo(x, y);
        } else {
            context.lineTo(x, y);
        }
    }

    context.closePath();
    context.strokeStyle = accent;
    context.lineWidth = 5;
    context.stroke();
};

const renderGraphic = async () => {
    if (!canvas.value) {
        return;
    }

    rendering.value = true;
    imageWarning.value = false;
    const context = canvas.value.getContext('2d');
    const width = 1080;
    const height = 1350;
    const palette = themes[theme.value];
    canvas.value.width = width;
    canvas.value.height = height;

    const fallback = context.createLinearGradient(0, 0, width, height);
    fallback.addColorStop(0, '#18181b');
    fallback.addColorStop(0.5, palette.glow);
    fallback.addColorStop(1, '#09090b');
    context.fillStyle = fallback;
    context.fillRect(0, 0, width, height);

    try {
        const image = await loadImage(
            props.imageUrl || props.review.screenshot_url,
        );
        drawCoverImage(context, image, width, 930);
    } catch {
        imageWarning.value = Boolean(
            props.imageUrl || props.review.screenshot_url,
        );
    }

    const shade = context.createLinearGradient(0, 400, 0, 1120);
    shade.addColorStop(0, 'rgba(9, 9, 11, 0)');
    shade.addColorStop(0.48, 'rgba(9, 9, 11, .72)');
    shade.addColorStop(0.72, 'rgba(9, 9, 11, .97)');
    shade.addColorStop(1, '#09090b');
    context.fillStyle = shade;
    context.fillRect(0, 300, width, 1050);

    context.fillStyle = palette.accent;
    context.fillRect(70, 790, 90, 7);
    context.font = '800 30px Arial, sans-serif';
    context.letterSpacing = '5px';
    context.fillText('CURATOR.GG REVIEW', 70, 845);

    context.fillStyle = '#ffffff';
    context.font = '900 72px Arial, sans-serif';
    wrapText(context, props.review.game_title, 730, 2).forEach(
        (line, index) => {
            context.fillText(line, 70, 930 + index * 76);
        },
    );

    const rating = props.review.rating ?? '—';
    drawHexagon(context, 895, 900, 105, palette.accent);
    context.fillStyle = '#ffffff';
    context.font = '900 74px Arial, sans-serif';
    context.textAlign = 'center';
    context.fillText(String(rating), 895, 920);
    context.fillStyle = palette.accent;
    context.font = '800 20px Arial, sans-serif';
    context.fillText('OUT OF 10', 895, 965);
    context.textAlign = 'left';

    context.fillStyle = '#f4f4f5';
    context.font = '500 35px Arial, sans-serif';
    wrapText(context, excerpt.value, 930, 5).forEach((line, index) => {
        context.fillText(line, 70, 1090 + index * 46);
    });

    context.fillStyle = '#a1a1aa';
    context.font = '600 23px Arial, sans-serif';
    context.fillText(
        `Review by ${props.review.user?.name || 'Curator.gg player'}`,
        70,
        1300,
    );
    rendering.value = false;
};

const graphicBlob = () =>
    new Promise((resolve) => canvas.value.toBlob(resolve, 'image/png', 0.95));

const filename = computed(
    () =>
        `${String(props.review.game_title || 'game-review')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '')}-review.png`,
);

const download = async () => {
    await renderGraphic();
    const blob = await graphicBlob();

    if (!blob) {
        return;
    }

    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename.value;
    link.click();
    URL.revokeObjectURL(url);
};

const share = async () => {
    await renderGraphic();
    const blob = await graphicBlob();

    if (!blob) {
        return;
    }

    const file = new File([blob], filename.value, { type: 'image/png' });

    if (navigator.canShare?.({ files: [file] })) {
        await navigator.share({
            title: `${props.review.game_title} review`,
            text: `My ${props.review.game_title} review on Curator.gg`,
            files: [file],
        });

        return;
    }

    await download();
};

watch(
    () => props.open,
    async (open) => {
        if (!open) {
            return;
        }

        excerpt.value = defaultExcerpt.value;
        await nextTick();
        await renderGraphic();
    },
);

watch([theme, excerpt], () => {
    if (props.open) {
        renderGraphic();
    }
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[100] overflow-y-auto bg-black/80 p-4 backdrop-blur-sm"
            @click.self="$emit('close')"
        >
            <div
                class="mx-auto my-4 max-w-5xl overflow-hidden rounded-3xl border border-zinc-700 bg-zinc-950 text-white shadow-2xl"
            >
                <header
                    class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 sm:px-7"
                >
                    <div>
                        <h2 class="text-xl font-black">
                            Create review graphic
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            Ready for Instagram, X or Stories.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white"
                        aria-label="Close"
                        @click="$emit('close')"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </header>

                <div
                    class="grid gap-7 p-5 sm:p-7 lg:grid-cols-[minmax(0,1fr)_340px]"
                >
                    <div
                        class="flex items-start justify-center rounded-2xl bg-zinc-900 p-3 sm:p-6"
                    >
                        <canvas
                            ref="canvas"
                            class="h-auto max-h-[68vh] w-auto max-w-full rounded-xl shadow-2xl"
                            aria-label="Review graphic preview"
                        />
                    </div>

                    <div>
                        <label
                            class="text-sm font-bold text-zinc-300"
                            for="graphic-excerpt"
                            >Review excerpt</label
                        >
                        <textarea
                            id="graphic-excerpt"
                            v-model="excerpt"
                            maxlength="280"
                            rows="7"
                            class="mt-2 w-full resize-none rounded-2xl border border-zinc-700 bg-zinc-900 p-4 text-sm leading-6 outline-none focus:border-indigo-400"
                        />
                        <p class="mt-2 text-right text-xs text-zinc-600">
                            {{ excerpt.length }}/280
                        </p>

                        <p class="mt-6 text-sm font-bold text-zinc-300">
                            Accent
                        </p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <button
                                v-for="(palette, id) in themes"
                                :key="id"
                                type="button"
                                class="rounded-xl border px-3 py-3 text-xs font-bold transition"
                                :class="
                                    theme === id
                                        ? 'border-white bg-zinc-800'
                                        : 'border-zinc-800 bg-zinc-900 text-zinc-400'
                                "
                                @click="theme = id"
                            >
                                <span
                                    class="mx-auto mb-2 block h-4 w-4 rounded-full"
                                    :style="{ backgroundColor: palette.accent }"
                                />{{ palette.label }}
                            </button>
                        </div>

                        <p
                            v-if="imageWarning"
                            class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-xs leading-5 text-amber-200"
                        >
                            The game image cannot be embedded by this source, so
                            the graphic uses a branded gradient.
                        </p>

                        <div class="mt-7 grid gap-3">
                            <button
                                type="button"
                                :disabled="rendering"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 font-black text-zinc-950 disabled:opacity-50"
                                @click="download"
                            >
                                <Download class="h-5 w-5" /> Download PNG
                            </button>
                            <button
                                type="button"
                                :disabled="rendering"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 font-bold hover:bg-zinc-900 disabled:opacity-50"
                                @click="share"
                            >
                                <Share2 class="h-5 w-5" /> Share graphic
                            </button>
                        </div>
                        <p
                            class="mt-4 flex items-center gap-2 text-xs text-zinc-600"
                        >
                            <Image class="h-4 w-4" /> Export size: 1080 × 1350
                            px
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
