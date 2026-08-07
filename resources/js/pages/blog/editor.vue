<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { ref } from 'vue';

import Sidebar from '@/components/layout/Sidebar.vue';
import Topbar from '@/components/layout/Topbar.vue';
import RichTextEditor from '@/components/ui/RichTextEditor.vue';

const props = defineProps({
    user: Object,
    post: Object,
});

const form = ref({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    youtube_url: props.post?.youtube_url ?? '',
    images: [],
    image_layout: props.post?.image_layout ?? 'grid',
    remove_images: false,
    is_published: props.post?.is_published ?? false,
});
const errors = ref({});
const saving = ref(false);
const imagePreviews = ref([]);
const allowedImageTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);

const selectImages = (event) => {
    const files = Array.from(event.target.files ?? []).slice(0, 10);

    if (files.some((file) => !allowedImageTypes.has(file.type))) {
        errors.value.images = 'Only JPG, PNG and WEBP image files are allowed.';
        form.value.images = [];
        event.target.value = '';
        imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
        imagePreviews.value = [];

        return;
    }

    delete errors.value.images;
    form.value.images = files;
    imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
    imagePreviews.value = files.map((file) => URL.createObjectURL(file));
    form.value.remove_images = files.length > 0;
};

const moveImage = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= form.value.images.length) return;

    [form.value.images[index], form.value.images[target]] = [
        form.value.images[target],
        form.value.images[index],
    ];
    [imagePreviews.value[index], imagePreviews.value[target]] = [
        imagePreviews.value[target],
        imagePreviews.value[index],
    ];
    form.value.images = [...form.value.images];
    imagePreviews.value = [...imagePreviews.value];
};

const save = () => {
    saving.value = true;
    errors.value = {};
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onError: (value) => {
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
                                v-if="
                                    post?.images?.length &&
                                    !form.remove_images &&
                                    !imagePreviews.length
                                "
                                class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3"
                            >
                                <img
                                    v-for="image in post.images"
                                    :key="image"
                                    :src="image"
                                    alt=""
                                    class="aspect-video w-full rounded-xl object-cover"
                                />
                            </div>
                            <div
                                v-if="imagePreviews.length"
                                class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3"
                            >
                                <div
                                    v-for="(image, index) in imagePreviews"
                                    :key="image"
                                    class="overflow-hidden rounded-xl border border-zinc-700"
                                >
                                    <img
                                        :src="image"
                                        alt="Selected upload"
                                        class="aspect-video w-full object-cover"
                                    />
                                    <div
                                        class="grid grid-cols-2 border-t border-zinc-700 text-xs"
                                    >
                                        <button
                                            type="button"
                                            class="p-2 hover:bg-zinc-800"
                                            @click="moveImage(index, -1)"
                                        >
                                            ←</button
                                        ><button
                                            type="button"
                                            class="p-2 hover:bg-zinc-800"
                                            @click="moveImage(index, 1)"
                                        >
                                            →
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input
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
                            <label
                                v-if="post?.images?.length"
                                class="mt-3 flex items-center gap-2 text-sm text-zinc-400"
                            >
                                <input
                                    v-model="form.remove_images"
                                    type="checkbox"
                                />
                                Remove current images
                            </label>
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
                                v-model="form.body"
                                :rows="18"
                                :maxlength="50000"
                                class="mt-2"
                                placeholder="Write your post..."
                            />
                            <div
                                class="mt-1 flex items-center justify-between text-xs text-zinc-500"
                            >
                                <span class="text-red-400">{{
                                    errors.body
                                }}</span>
                                <span>{{ form.body.length }}/50000</span>
                            </div>
                        </label>

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
