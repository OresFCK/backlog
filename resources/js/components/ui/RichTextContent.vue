<!-- eslint-disable vue/block-lang -->
<script setup>
import { computed } from 'vue';

const props = defineProps({ content: { type: String, default: '' } });
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
    <div class="space-y-4">
        <template v-for="(block, index) in blocks" :key="index">
            <component :is="block.type === 'h2' ? 'h2' : block.type === 'h3' ? 'h3' : block.type === 'quote' ? 'blockquote' : block.type === 'list' ? 'div' : 'p'" :class="{ 'pt-4 text-3xl font-black text-white': block.type === 'h2', 'pt-3 text-2xl font-black text-white': block.type === 'h3', 'border-l-4 border-indigo-400 pl-5 italic text-zinc-300': block.type === 'quote', 'flex gap-3': block.type === 'list', 'whitespace-pre-wrap': block.type === 'p' }">
                <span v-if="block.type === 'list'" class="text-indigo-400">•</span>
                <span><template v-for="(segment, part) in segments(block.text)" :key="part"><strong v-if="segment.bold">{{ segment.text }}</strong><template v-else>{{ segment.text }}</template></template></span>
            </component>
        </template>
    </div>
</template>
