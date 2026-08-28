<script setup lang="ts">
import { computed } from 'vue';

import RichTextContent from '@/components/ui/RichTextContent.vue';
import { splitImageContent } from '@/lib/blogImages';

const props = defineProps<{ content: string; images: string[] }>();
const blocks = computed(() => splitImageContent(props.content, props.images));
</script>

<template>
    <div class="min-w-0 space-y-6 [overflow-wrap:anywhere]">
        <template v-for="(block, index) in blocks" :key="index">
            <img
                v-if="block.image"
                :src="block.image"
                alt="Post image"
                class="mx-auto max-h-[620px] max-w-full rounded-xl object-contain"
            />
            <RichTextContent
                v-else-if="block.text.trim()"
                :content="block.text"
            />
        </template>
    </div>
</template>
