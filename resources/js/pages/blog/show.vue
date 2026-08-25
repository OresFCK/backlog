<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Flag, Pencil, Share2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import BlogHeader from '@/components/blog/BlogHeader.vue';
import RichTextContent from '@/components/ui/RichTextContent.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    isOwner: Boolean,
    seo: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const reported = ref(false);
const copied = ref(false);

const vote = (value) => {
    if (!user.value) {
        window.location.assign(`/auth/steam?intended=/blog/${props.post.slug}`);

        return;
    }

    if (props.post.user_vote === value) {
        router.delete(`/blog/${props.post.slug}/vote`, {
            preserveScroll: true,
        });

        return;
    }

    router.post(
        `/blog/${props.post.slug}/vote`,
        { value },
        { preserveScroll: true },
    );
};

const report = () => {
    if (!user.value) {
        window.location.assign(`/auth/steam?intended=/blog/${props.post.slug}`);

        return;
    }

    const reason = window.prompt('Why are you reporting this post?');

    if (!reason) {
        return;
    }

    router.post(
        `/blog/${props.post.slug}/report`,
        { reason },
        {
            preserveScroll: true,
            onSuccess: () => {
                reported.value = true;
            },
        },
    );
};

const share = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.post.title,
                text: props.post.excerpt,
                url: props.post.share_url,
            });

            return;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(props.post.share_url);
    copied.value = true;
    window.setTimeout(() => {
        copied.value = false;
    }, 1600);
};
</script>

<template>
    <Head :title="seo?.title || post.title">
        <meta
            v-if="seo?.description"
            head-key="description"
            name="description"
            :content="seo.description"
        />
        <meta property="og:title" :content="seo?.title || post.title" />
        <meta property="og:description" :content="seo?.description" />
        <meta property="og:url" :content="seo?.url" />
        <meta property="og:image" :content="seo?.image" />
    </Head>

    <div class="min-h-screen bg-zinc-950 text-white">
        <BlogHeader />
        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-14">
            <article>
                <header>
                    <Link
                        href="/blog"
                        class="text-sm font-bold text-indigo-400 hover:text-indigo-300"
                    >
                        Community Blog
                    </Link>
                    <h1
                        class="mt-4 text-4xl leading-tight font-black sm:text-6xl"
                    >
                        {{ post.title }}
                    </h1>
                    <span
                        class="mt-4 inline-flex rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-bold text-indigo-300"
                        >{{ post.category_label }}</span
                    >
                    <p
                        v-if="post.excerpt"
                        class="mt-5 text-lg leading-8 text-zinc-400"
                    >
                        {{ post.excerpt }}
                    </p>
                    <div
                        class="mt-7 flex flex-wrap items-center justify-between gap-4 border-y border-zinc-800 py-4"
                    >
                        <Link
                            :href="post.author.profile_url || '#'"
                            class="flex items-center gap-3"
                        >
                            <img
                                v-if="post.author.avatar"
                                :src="post.author.avatar"
                                :alt="post.author.name"
                                class="h-11 w-11 rounded-xl object-cover"
                            />
                            <span>
                                <strong class="block">{{
                                    post.author.name
                                }}</strong>
                                <small class="text-zinc-500">{{
                                    post.published_at || post.updated_at
                                }}</small>
                            </span>
                        </Link>
                        <Link
                            v-if="isOwner"
                            :href="`/blog/${post.slug}/edit`"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-bold"
                        >
                            <Pencil class="h-4 w-4" /> Edit
                        </Link>
                    </div>
                </header>

                <div
                    v-if="post.images?.length"
                    class="mt-10 gap-4"
                    :class="{
                        'grid grid-cols-1 sm:grid-cols-2':
                            post.image_layout === 'grid' &&
                            post.images.length > 1,
                        'flex snap-x snap-mandatory overflow-x-auto pb-2':
                            post.image_layout === 'carousel',
                        'grid grid-cols-1':
                            post.image_layout === 'full' ||
                            post.images.length === 1,
                    }"
                >
                    <img
                        v-for="image in post.images"
                        :key="image"
                        :src="image"
                        :alt="post.title"
                        class="max-h-[620px] w-full rounded-2xl border border-zinc-800"
                        :class="[
                            post.image_layout === 'carousel'
                                ? 'min-w-[85%] snap-center object-cover sm:min-w-[60%]'
                                : '',
                            post.image_layout === 'full'
                                ? 'object-contain'
                                : 'object-cover',
                        ]"
                    />
                </div>

                <RichTextContent
                    :content="post.body"
                    class="mt-10 text-lg leading-8 text-zinc-200"
                />

                <div
                    v-if="post.youtube_embed_url"
                    class="mt-10 aspect-video overflow-hidden rounded-2xl border border-zinc-800 bg-black"
                >
                    <iframe
                        :src="post.youtube_embed_url"
                        :title="`${post.title} video`"
                        class="h-full w-full"
                        loading="lazy"
                        allow="
                            accelerometer;
                            autoplay;
                            clipboard-write;
                            encrypted-media;
                            gyroscope;
                            picture-in-picture;
                            web-share;
                        "
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                    />
                </div>

                <footer
                    class="mt-12 flex flex-wrap items-center gap-2 border-t border-zinc-800 pt-6"
                >
                    <div
                        class="inline-flex items-center rounded-xl border border-zinc-700"
                    >
                        <button
                            type="button"
                            :disabled="isOwner"
                            class="p-3 hover:text-emerald-400 disabled:opacity-30"
                            :class="
                                post.user_vote === 1
                                    ? 'text-emerald-400'
                                    : 'text-zinc-400'
                            "
                            aria-label="Upvote"
                            @click="vote(1)"
                        >
                            <ArrowUp class="h-4 w-4" />
                        </button>
                        <strong class="min-w-10 text-center">{{
                            post.score
                        }}</strong>
                        <button
                            type="button"
                            :disabled="isOwner"
                            class="p-3 hover:text-red-400 disabled:opacity-30"
                            :class="
                                post.user_vote === -1
                                    ? 'text-red-400'
                                    : 'text-zinc-400'
                            "
                            aria-label="Downvote"
                            @click="vote(-1)"
                        >
                            <ArrowDown class="h-4 w-4" />
                        </button>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold"
                        @click="share"
                    >
                        <Share2 class="h-4 w-4" />
                        {{ copied ? 'Copied' : 'Share' }}
                    </button>
                    <button
                        v-if="!isOwner"
                        type="button"
                        :disabled="reported"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-4 py-3 text-sm font-bold text-zinc-400 disabled:opacity-50"
                        @click="report"
                    >
                        <Flag class="h-4 w-4" />
                        {{ reported ? 'Reported' : 'Report' }}
                    </button>
                </footer>
            </article>
        </main>
    </div>
</template>
