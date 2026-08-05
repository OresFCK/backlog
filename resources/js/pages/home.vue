<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

import CommunityTierListsSection from '@/components/home/CommunityTierListsSection.vue';
import FaqSection from '@/components/home/FaqSection.vue';
import FeaturesSection from '@/components/home/FeaturesSection.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import HomeFooter from '@/components/home/HomeFooter.vue';
import HomeHeader from '@/components/home/HomeHeader.vue';
import HowItWorksSection from '@/components/home/HowItWorksSection.vue';
import ShowcaseSection from '@/components/home/ShowcaseSection.vue';
import SupportSection from '@/components/home/SupportSection.vue';
import { track } from '@/lib/analytics';

defineProps({
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    tierLists: {
        type: Array,
        default: () => [],
    },
});

onMounted(() => track('homepage_viewed', { funnel: 'next_three_games' }));
</script>

<template>
    <div class="min-h-screen bg-[#0b0b0d] text-white">
        <Head
            title="Curator.gg — Organize your Steam library and discover what to play"
        />
        <HomeHeader :auth="auth" />

        <main>
            <HeroSection :auth="auth" />
            <FeaturesSection />
            <HowItWorksSection />
            <ShowcaseSection />
            <CommunityTierListsSection :tier-lists="tierLists" />
            <FaqSection />
            <SupportSection :auth="auth" />
        </main>

        <HomeFooter :auth="auth" />
    </div>
</template>
