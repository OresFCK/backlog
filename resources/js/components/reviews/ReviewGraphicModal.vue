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
const theme = ref('aurora');
const rendering = ref(false);
const imageWarning = ref(false);

const themes = {
    aurora: {
        accent: '#67e8f9',
        secondary: '#6366f1',
        glow: '#164e63',
        label: 'Aurora',
    },
    sunset: {
        accent: '#fb7185',
        secondary: '#f97316',
        glow: '#7c2d12',
        label: 'Sunset',
    },
    mint: {
        accent: '#6ee7b7',
        secondary: '#14b8a6',
        glow: '#064e3b',
        label: 'Mint',
    },
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

const roundedRectPath = (context, x, y, width, height, radius) => {
    const corner = Math.min(radius, width / 2, height / 2);
    context.beginPath();
    context.moveTo(x + corner, y);
    context.arcTo(x + width, y, x + width, y + height, corner);
    context.arcTo(x + width, y + height, x, y + height, corner);
    context.arcTo(x, y + height, x, y, corner);
    context.arcTo(x, y, x + width, y, corner);
    context.closePath();
};

const drawCoverImage = (context, image, x, y, width, height) => {
    const scale = Math.max(width / image.width, height / image.height);
    const drawnWidth = image.width * scale;
    const drawnHeight = image.height * scale;

    context.drawImage(
        image,
        x + (width - drawnWidth) / 2,
        y + (height - drawnHeight) / 2,
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

const drawHexagon = () => {};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const _renderLegacyGraphic = async () => {
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

    context.fillStyle = '#09090f';
    context.fillRect(0, 0, width, height);
    const ambient = context.createRadialGradient(900, 70, 0, 900, 70, 620);
    ambient.addColorStop(0, palette.glow);
    ambient.addColorStop(0.5, `${palette.secondary}33`);
    ambient.addColorStop(1, 'rgba(9, 9, 15, 0)');
    context.fillStyle = ambient;
    context.fillRect(0, 0, width, 760);

    context.fillStyle = '#ffffff';
    context.beginPath();
    context.arc(72, 72, 11, 0, Math.PI * 2);
    context.fill();
    context.font = '800 25px Arial, sans-serif';
    context.fillText('curator.gg', 96, 81);
    context.fillStyle = '#a1a1aa';
    context.font = '600 20px Arial, sans-serif';
    context.textAlign = 'right';
    context.fillText('a player perspective', 1008, 79);
    context.textAlign = 'left';

    const imageX = 54;
    const imageY = 126;
    const imageWidth = 972;
    const imageHeight = 620;
    context.save();
    roundedRectPath(context, imageX, imageY, imageWidth, imageHeight, 46);
    context.clip();

    try {
        const image = await loadImage(
            props.imageUrl || props.review.screenshot_url,
        );
        drawCoverImage(context, image, imageX, imageY, imageWidth, imageHeight);
    } catch {
        imageWarning.value = Boolean(
            props.imageUrl || props.review.screenshot_url,
        );
        context.fillStyle = palette.glow;
        context.fillRect(imageX, imageY, imageWidth, imageHeight);
    }

    const imageShade = context.createLinearGradient(0, 380, 0, 746);
    imageShade.addColorStop(0, 'rgba(9, 9, 15, 0)');
    imageShade.addColorStop(1, 'rgba(9, 9, 15, .74)');
    context.fillStyle = imageShade;
    context.fillRect(imageX, imageY, imageWidth, imageHeight);
    context.restore();
    roundedRectPath(context, imageX, imageY, imageWidth, imageHeight, 46);
    context.strokeStyle = 'rgba(255,255,255,.16)';
    context.lineWidth = 2;
    context.stroke();

    const ribbon = context.createLinearGradient(84, 0, 374, 0);
    ribbon.addColorStop(0, palette.accent);
    ribbon.addColorStop(1, palette.secondary);
    roundedRectPath(context, 84, 665, 290, 48, 24);
    context.fillStyle = ribbon;
    context.fill();
    context.fillStyle = '#09090f';
    context.font = '800 19px Arial, sans-serif';
    context.fillText(
        props.review.recommended ? 'Worth your time' : 'Player reviewed',
        112,
        696,
    );

    const rating = props.review.rating ?? '—';
    context.beginPath();
    context.arc(890, 744, 96, 0, Math.PI * 2);
    context.fillStyle = '#f4f4f5';
    context.fill();
    context.beginPath();
    context.arc(890, 744, 83, 0, Math.PI * 2);
    context.fillStyle = '#111118';
    context.fill();
    context.textAlign = 'center';
    context.fillStyle = '#ffffff';
    context.font = '900 62px Arial, sans-serif';
    context.fillText(String(rating), 890, 756);
    context.fillStyle = palette.accent;
    context.font = '800 17px Arial, sans-serif';
    context.fillText('/ 10', 890, 791);
    context.textAlign = 'left';

    context.fillStyle = '#ffffff';
    context.font = '900 68px Arial, sans-serif';
    wrapText(context, props.review.game_title, 760, 2).forEach(
        (line, index) => {
            context.fillText(line, 64, 850 + index * 72);
        },
    );

    roundedRectPath(context, 54, 986, 972, 270, 38);
    context.fillStyle = 'rgba(255,255,255,.065)';
    context.fill();
    context.fillStyle = palette.accent;
    roundedRectPath(context, 84, 1020, 8, 170, 4);
    context.fill();
    context.fillStyle = '#e4e4e7';
    context.font = '500 31px Arial, sans-serif';
    wrapText(context, excerpt.value, 850, 4).forEach((line, index) => {
        context.fillText(line, 122, 1062 + index * 42);
    });

    context.fillStyle = '#a1a1aa';
    context.font = '600 22px Arial, sans-serif';
    context.fillText(
        `@ ${props.review.user?.name || 'Curator.gg player'}`,
        64,
        1310,
    );
    context.textAlign = 'right';
    context.fillText('Share what you play.', 1016, 1310);
    context.textAlign = 'left';
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
