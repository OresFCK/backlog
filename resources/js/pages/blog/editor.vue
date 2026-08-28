<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

import BlogPostContent from '@/components/blog/BlogPostContent.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';
import RichTextEditor from '@/components/ui/RichTextEditor.vue';
import { remapImageMarkers } from '@/lib/blogImages';

const props = defineProps({
    user: Object,
    post: Object,
    categories: { type: Object, default: () => ({}) },
});

const form = ref({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    category: props.post?.category ?? 'other',
    body: props.post?.body ?? '',
    youtube_url: props.post?.youtube_url ?? '',
    images: [] as File[],
    retained_images: (props.post?.images ?? []).map(
        (_image: string, index: number) => index,
    ) as number[],
    image_layout: props.post?.image_layout ?? 'grid',
    remove_images: false,
    is_published: props.post?.is_published ?? false,
});
const errors = ref<Record<string, string>>({});
const saving = ref(false);
const imagePreviews = ref<string[]>([]);
const imageInput = ref<HTMLInputElement | null>(null);
const contentEditor = ref<InstanceType<typeof RichTextEditor> | null>(null);
const allowedImageTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
const displayedImages = computed<string[]>(() =>
    form.value.remove_images
        ? imagePreviews.value
        : form.value.retained_images.map((index) => props.post?.images[index]),
);
const clearPreviews = () => {
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = [];
};
onBeforeUnmount(clearPreviews);
const galleryClasses = computed(() => ({
    'grid grid-cols-1 sm:grid-cols-2': form.value.image_layout === 'grid',
    'flex snap-x snap-mandatory overflow-x-auto pb-2':
        form.value.image_layout === 'carousel',
    'grid grid-cols-1': form.value.image_layout === 'full',
}));

const selectImages = (event: Event) => {
    const element = event.target as HTMLInputElement;
    const files = Array.from(element.files ?? []);
    element.value = '';

    if (!files.length) {
        return;
    }

    if (
        files.length > 10 ||
        files.some(
            (file) =>
                !allowedImageTypes.has(file.type) ||
                file.size > 5 * 1024 * 1024,
        )
    ) {
        errors.value.images =
            'Choose up to 10 JPG, PNG or WEBP images, maximum 5 MB each.';

        return;
    }

    delete errors.value.images;
    // Replacing the gallery must not point old inline markers at different photos.
    form.value.body = remapImageMarkers(form.value.body, []);
    clearPreviews();
    form.value.images = files;
    imagePreviews.value = files.map((file) => URL.createObjectURL(file));
    form.value.remove_images = true;
};

const removeImage = (index: number) => {
    const order = displayedImages.value
        .map((_image, oldIndex) => oldIndex)
        .filter((oldIndex) => oldIndex !== index);
    form.value.body = remapImageMarkers(form.value.body, order);

    if (form.value.remove_images) {
        URL.revokeObjectURL(imagePreviews.value[index]);
        imagePreviews.value.splice(index, 1);
        form.value.images.splice(index, 1);
    } else {
        form.value.retained_images.splice(index, 1);

        if (!form.value.retained_images.length) {
            form.value.remove_images = true;
        }
    }
};

const moveImage = (index: number, direction: number) => {
    const target = index + direction;

    if (target < 0 || target >= displayedImages.value.length) {
        return;
    }

    const order = displayedImages.value.map((_image, oldIndex) => oldIndex);
    [order[index], order[target]] = [order[target], order[index]];
    form.value.body = remapImageMarkers(form.value.body, order);

    if (form.value.remove_images) {
        form.value.images = order.map(
            (oldIndex) => form.value.images[oldIndex],
        );
        imagePreviews.value = order.map(
            (oldIndex) => imagePreviews.value[oldIndex],
        );
    } else {
        form.value.retained_images = order.map(
            (oldIndex) => form.value.retained_images[oldIndex],
        );
    }
};

const save = () => {
    saving.value = true;
    errors.value = {};
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onError: (value: Record<string, string>) => {
            errors.value = value;
        },
        onFinish: () => {
            saving.value = false;
        },
    };

    if (props.post) {
        router.post(
            `/blog/${props.post.slug}`,
            { ...form.value, _method: 'patch' },
            options,
        );

        return;
    }

    router.post('/blog', form.value, options);
};
</script>

<template>
    <Head :title="post ? `Edit ${post.title}` : 'Write a blog post'" />
    <div class="flex min-h-screen bg-zinc-950 text-white">
        <Sidebar />
        <div class="flex min-w-0 flex-1 flex-col">
            <Topbar :user="user" />
            <main class="flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <form
                    class="mx-auto max-w-4xl space-y-6"
                    @submit.prevent="save"
                >
                    <header>
                        <p
                            class="text-xs font-black tracking-[0.2em] text-indigo-400 uppercase"
                        >
                            Community blog
                        </p>
                        <h1 class="mt-2 text-3xl font-black sm:text-5xl">
                            {{ post ? 'Edit post' : 'Write a post' }}
                        </h1>
                    </header>

                    <section
                        class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900 p-5 sm:rounded-3xl sm:p-8"
                    >
                        <label class="block">
                            <span class="text-sm font-bold">Title</span>
                            <input
                                v-model="form.title"
                                type="text"
                                maxlength="160"
                                required
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-lg font-bold outline-none focus:border-indigo-400"
                            />
                            <span
                                v-if="errors.title"
                                class="mt-1 block text-sm text-red-400"
                                >{{ errors.title }}</span
                            >
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold">Category</span>
                            <select
                                v-model="form.category"
                                required
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 outline-none focus:border-indigo-400"
                            >
                                <option
                                    v-for="(label, value) in categories"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <span
                                v-if="errors.category"
                                class="mt-1 block text-sm text-red-400"
                                >{{ errors.category }}</span
                            >
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold">Short summary</span>
                            <textarea
                                v-model="form.excerpt"
                                rows="3"
                                maxlength="320"
                                class="mt-2 w-full resize-y rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 outline-none focus:border-indigo-400"
                                placeholder="Optional summary shown on the blog index"
                            />
                            <span
                                v-if="errors.excerpt"
                                class="mt-1 block text-sm text-red-400"
                                >{{ errors.excerpt }}</span
                            >
                        </label>

                        <div>
                            <span class="text-sm font-bold">Images</span>
                            <div
                                v-if="displayedImages.length"
                                class="mt-3 gap-3"
                                :class="galleryClasses"
                            >
                                <div
                                    v-for="(image, index) in displayedImages"
                                    :key="image"
                                    class="overflow-hidden rounded-xl border border-zinc-700 bg-zinc-950"
                                    :class="
                                        form.image_layout === 'carousel'
                                            ? 'min-w-[82%] snap-center sm:min-w-[55%]'
                                            : ''
                                    "
                                >
                                    <img
                                        :src="image"
                                        alt="Gallery preview"
                                        class="w-full"
                                        :class="
                                            form.image_layout === 'full'
                                                ? 'max-h-[620px] object-contain'
                                                : 'aspect-video object-cover'
                                        "
                                    />
                                    <div
                                        class="flex flex-wrap border-t border-zinc-700 text-xs"
                                    >
                                        <button
                                            type="button"
                                            class="p-2 hover:bg-zinc-800"
                                            :disabled="index === 0"
                                            aria-label="Move image earlier"
                                            @click="moveImage(index, -1)"
                                        >
                                            ←</button
                                        ><button
                                            type="button"
                                            class="p-2 hover:bg-zinc-800"
                                            :disabled="
                                                index ===
                                                displayedImages.length - 1
                                            "
                                            aria-label="Move image later"
                                            @click="moveImage(index, 1)"
                                        >
                                            →
                                        </button>
                                        <button
                                            type="button"
                                            class="p-2 hover:bg-zinc-800"
                                            @mousedown.prevent
                                            @click="
                                                contentEditor?.insertImage(
                                                    index + 1,
                                                )
                                            "
                                        >
                                            Insert in text
                                        </button>
                                        <button
                                            type="button"
                                            class="p-2 text-red-400 hover:bg-zinc-800"
                                            @click="removeImage(index)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                class="mt-3 block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm"
                                @change="selectImages"
                            />
                            <p class="mt-2 text-xs text-zinc-500">
                                Up to 10 JPG, PNG or WEBP images, maximum 5 MB
                                each. New files replace the current gallery.
                            </p>
                            <span
                                v-if="errors.images"
                                class="mt-1 block text-sm text-red-400"
                            >
                                {{ errors.images }}
                            </span>
                        </div>

                        <label class="block">
                            <span class="text-sm font-bold"
                                >Gallery layout</span
                            >
                            <select
                                v-model="form.image_layout"
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 outline-none focus:border-indigo-400"
                            >
                                <option value="grid">Grid</option>
                                <option value="carousel">Carousel</option>
                                <option value="full">Full width</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold">YouTube video</span>
                            <input
                                v-model="form.youtube_url"
                                type="url"
                                maxlength="500"
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 outline-none focus:border-indigo-400"
                                placeholder="https://www.youtube.com/watch?v=..."
                            />
                            <p class="mt-2 text-xs text-zinc-500">
                                YouTube, youtu.be and Shorts links are
                                supported.
                            </p>
                            <span
                                v-if="errors.youtube_url"
                                class="mt-1 block text-sm text-red-400"
                            >
                                {{ errors.youtube_url }}
                            </span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold">Content</span>
                            <RichTextEditor
                                ref="contentEditor"
                                v-model="form.body"
                                :rows="18"
                                :maxlength="50000"
                                class="mt-2"
                                placeholder="Write your post..."
                            />
                            <p class="mt-2 text-xs text-zinc-500">
                                Place the cursor in the content, then click
                                “Insert in text” below an image. The [[image:1]]
                                marker displays that image at this position.
                                Removing a marker returns the image to the
                                gallery.
                            </p>
                            <div
                                class="mt-1 flex items-center justify-between text-xs text-zinc-500"
                            >
                                <span class="text-red-400">{{
                                    errors.body
                                }}</span>
                                <span>{{ form.body.length }}/50000</span>
                            </div>
                        </label>

                        <section
                            v-if="form.body"
                            class="min-w-0 rounded-xl border border-zinc-700 p-4"
                        >
                            <h2 class="mb-4 text-sm font-bold text-zinc-400">
                                Content preview
                            </h2>
                            <BlogPostContent
                                :content="form.body"
                                :images="displayedImages"
                            />
                        </section>

                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-700 px-4 py-3"
                        >
                            <input
                                v-model="form.is_published"
                                type="checkbox"
                                class="rounded"
                            />
                            <span>
                                <strong class="block text-sm">Publish</strong>
                                <small class="text-zinc-500">
                                    Uncheck to keep this post as a private
                                    draft.
                                </small>
                            </span>
                        </label>

                        <button
                            type="submit"
                            :disabled="saving"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-zinc-950 disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />
                            {{ saving ? 'Saving...' : 'Save post' }}
                        </button>
                    </section>
                </form>
            </main>
        </div>
    </div>
</template>
