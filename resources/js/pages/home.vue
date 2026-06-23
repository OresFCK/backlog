<script setup>
import HomeHeader from '@/components/home/HomeHeader.vue'
import HeroSection from '@/components/home/HeroSection.vue'
import FeaturesSection from '@/components/home/FeaturesSection.vue'
import ShowcaseSection from '@/components/home/ShowcaseSection.vue'
import SupportSection from '@/components/home/SupportSection.vue'
import HomeFooter from '@/components/home/HomeFooter.vue'
</script>

<template>
    <div class="min-h-screen bg-[#0b0b0d] text-white">
        <HomeHeader />

        <main>
            <HeroSection />
            <FeaturesSection />
            <ShowcaseSection />
            <SupportSection />
        </main>

        <HomeFooter />
    </div>
</template>