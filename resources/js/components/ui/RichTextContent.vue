<!-- eslint-disable vue/block-lang -->
<script setup>
import { computed } from 'vue';

const props = defineProps({ content: { type: String, default: '' } });
const containsHtml = computed(() => /<\/?[a-z][\s\S]*>/i.test(props.content));
const allowedTags = new Set(['P', 'BR', 'H2', 'H3', 'H4', 'STRONG', 'B', 'EM', 'I', 'U', 'S', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'A', 'HR', 'PRE', 'CODE']);
const sanitizedHtml = computed(() => {
    if (!containsHtml.value || typeof DOMParser === 'undefined') return '';

    const document = new DOMParser().parseFromString(props.content, 'text/html');

    document.body.querySelectorAll('*').forEach((element) => {
        if (!allowedTags.has(element.tagName)) {
            element.replaceWith(...element.childNodes);
            return;
        }

        const href = element.tagName === 'A' ? element.getAttribute('href') ?? '' : '';
        Array.from(element.attributes).forEach((attribute) => element.removeAttribute(attribute.name));

        if (element.tagName === 'A') {
            if (/^(https?:|mailto:)/i.test(href)) {
                element.setAttribute('href', href);
                element.setAttribute('rel', 'noopener noreferrer nofollow');
                element.setAttribute('target', '_blank');
            }
        }
    });

    return document.body.innerHTML;
});
const segments = (text) =>
    String(text).split(/(\*\*[^*]+\*\*)/g).filter(Boolean).map((part) => ({
        bold: part.startsWith('**') && part.endsWith('**'),
        text: part.startsWith('**') && part.endsWith('**') ? part.slice(2, -2) : part,
    }));
const blocks = computed(() =>
    props.content.split(/\n+/).filter(Boolean).map((line) => {
        if (line.startsWith('### ')) return { type: 'h3', text: line.slice(4) };
        if (line.startsWith('## ')) return { type: 'h2', text: line.slice(3) };
        if (line.startsWith('> ')) return { type: 'quote', text: line.slice(2) };
        if (line.startsWith('- ')) return { type: 'list', text: line.slice(2) };
        return { type: 'p', text: line };
    }),
);
</script>

<template>
    <div v-if="containsHtml" class="blog-html min-w-0 max-w-full [overflow-wrap:anywhere]" v-html="sanitizedHtml" />
    <div v-else class="min-w-0 max-w-full space-y-4 [overflow-wrap:anywhere]">
        <template v-for="(block, index) in blocks" :key="index">
            <component :is="block.type === 'h2' ? 'h2' : block.type === 'h3' ? 'h3' : block.type === 'quote' ? 'blockquote' : block.type === 'list' ? 'div' : 'p'" :class="{ 'pt-4 text-3xl font-black text-white': block.type === 'h2', 'pt-3 text-2xl font-black text-white': block.type === 'h3', 'border-l-4 border-indigo-400 pl-5 italic text-zinc-300': block.type === 'quote', 'flex gap-3': block.type === 'list', 'whitespace-pre-wrap': block.type === 'p' }">
                <span v-if="block.type === 'list'" class="text-indigo-400">•</span>
                <span><template v-for="(segment, part) in segments(block.text)" :key="part"><strong v-if="segment.bold">{{ segment.text }}</strong><template v-else>{{ segment.text }}</template></template></span>
            </component>
        </template>
    </div>
</template>

<style scoped>
.blog-html :deep(p), .blog-html :deep(ul), .blog-html :deep(ol), .blog-html :deep(blockquote), .blog-html :deep(pre) { margin-top: 1rem; }
.blog-html :deep(h2) { margin-top: 2rem; font-size: 1.875rem; font-weight: 900; color: white; }
.blog-html :deep(h3) { margin-top: 1.75rem; font-size: 1.5rem; font-weight: 900; color: white; }
.blog-html :deep(h4) { margin-top: 1.5rem; font-size: 1.25rem; font-weight: 800; color: white; }
.blog-html :deep(ul) { list-style: disc; padding-left: 1.5rem; }
.blog-html :deep(ol) { list-style: decimal; padding-left: 1.5rem; }
.blog-html :deep(blockquote) { border-left: 4px solid #818cf8; padding-left: 1.25rem; color: #d4d4d8; font-style: italic; }
.blog-html :deep(a) { color: #a5b4fc; text-decoration: underline; }
.blog-html :deep(pre) { overflow-x: auto; border-radius: .75rem; background: #18181b; padding: 1rem; }
</style>
