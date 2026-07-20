<script setup>
import HomeHeader from '@/components/home/HomeHeader.vue';
import HomeFooter from '@/components/home/HomeFooter.vue';

defineProps({
    eyebrow: { type: String, required: true },
    title: { type: String, required: true },
    intro: { type: String, required: true },
});
</script>

<template>
    <div class="min-h-screen bg-[#0b0b0d] text-white">
        <HomeHeader />
        <main class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-20">
            <p class="text-sm font-black text-indigo-300 uppercase">
                {{ eyebrow }}
            </p>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-6xl">
                {{ title }}
            </h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-400">
                {{ intro }}
            </p>
            <div class="public-copy mt-12 space-y-10 text-zinc-300">
                <slot />
            </div>
        </main>
        <HomeFooter />
    </div>
</template>

<style scoped>
.public-copy :deep(h2) {
    color: white;
    font-size: 1.5rem;
    font-weight: 900;
}
.public-copy :deep(p) {
    margin-top: 0.75rem;
    line-height: 1.8;
    color: rgb(161 161 170);
}
.public-copy :deep(ul) {
    margin-top: 0.75rem;
    list-style: disc;
    padding-left: 1.5rem;
    color: rgb(161 161 170);
}
.public-copy :deep(li + li) {
    margin-top: 0.5rem;
}
.public-copy :deep(a) {
    color: rgb(165 180 252);
    text-decoration: underline;
    text-underline-offset: 3px;
}
</style>
