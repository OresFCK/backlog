<!-- eslint-disable vue/block-lang -->
<script setup>
import { Bold, Heading2, Heading3, List, Quote } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    maxlength: { type: Number, default: 50000 },
    placeholder: { type: String, default: 'Start writing…' },
    rows: { type: Number, default: 12 },
});
const emit = defineEmits(['update:modelValue']);
const input = ref(null);

const insertImage = (number) => {
    insert('', '\n', `[[image:${number}]]`);
};
defineExpose({ insertImage });

const insert = (prefix, suffix = '', placeholder = 'Text') => {
    const element = input.value;
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const selected = placeholder.startsWith('[[image:') ? placeholder : props.modelValue.slice(start, end) || placeholder;
    const before = props.modelValue.slice(0, start);
    const lineStart = before.length === 0 || before.endsWith('\n') ? '' : '\n';
    const value = `${before}${lineStart}${prefix}${selected}${suffix}${props.modelValue.slice(end)}`;
    emit('update:modelValue', value.slice(0, props.maxlength));

    requestAnimationFrame(() => {
        element.focus();
        const cursor = start + lineStart.length + prefix.length + selected.length + suffix.length;
        element.setSelectionRange(cursor, cursor);
    });
};

const tools = [
    { label: 'Heading 2', icon: Heading2, action: () => insert('## ', '', 'Heading') },
    { label: 'Heading 3', icon: Heading3, action: () => insert('### ', '', 'Heading') },
    { label: 'Bold', icon: Bold, action: () => insert('**', '**', 'bold text') },
    { label: 'Quote', icon: Quote, action: () => insert('> ', '', 'Quote') },
    { label: 'List', icon: List, action: () => insert('- ', '', 'List item') },
];
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-zinc-700 bg-zinc-950 focus-within:border-indigo-400">
        <div class="flex flex-wrap gap-1 border-b border-zinc-800 bg-zinc-900 p-2">
            <button v-for="tool in tools" :key="tool.label" type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white" :title="tool.label" @click="tool.action">
                <component :is="tool.icon" class="h-4 w-4" />
            </button>
        </div>
        <textarea ref="input" :value="modelValue" :rows="rows" :maxlength="maxlength" :placeholder="placeholder" class="w-full resize-y bg-transparent p-4 leading-7 text-zinc-200 outline-none placeholder:text-zinc-600" @input="$emit('update:modelValue', $event.target.value)" />
        <div class="border-t border-zinc-900 px-3 py-2 text-right text-xs text-zinc-600">{{ modelValue.length }}/{{ maxlength }}</div>
    </div>
</template>
