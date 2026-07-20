<script setup>
import HomeHeader from '@/components/home/HomeHeader.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import FeaturesSection from '@/components/home/FeaturesSection.vue';
import HowItWorksSection from '@/components/home/HowItWorksSection.vue';
import ShowcaseSection from '@/components/home/ShowcaseSection.vue';
import FaqSection from '@/components/home/FaqSection.vue';
import SupportSection from '@/components/home/SupportSection.vue';
import HomeFooter from '@/components/home/HomeFooter.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#0b0b0d] text-white">
        <Head
            title="Curator.gg — Organize your Steam library and discover what to play"
        />
        <HomeHeader />

        <main>
            <HeroSection />
            <FeaturesSection />
            <HowItWorksSection />
            <ShowcaseSection />
            <FaqSection />
            <SupportSection />
        </main>

        <HomeFooter />
    </div>
</template>
