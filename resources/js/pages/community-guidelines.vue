<script setup>
import { Head } from '@inertiajs/vue3';
import PublicInfoLayout from '@/components/home/PublicInfoLayout.vue';
</script>

<template>
    <Head title="Community Guidelines — Curator.gg" />
    <PublicInfoLayout
        eyebrow="Community Guidelines"
        title="Share strong opinions without attacking people."
        intro="Curator.gg welcomes honest reviews, unusual rankings and disagreement. These rules keep public content useful and safe for the wider gaming community."
    >
        <section>
            <h2>Write original and relevant content</h2>
            <ul>
                <li>
                    Base reviews on your own experience and clearly discuss the
                    game being reviewed.
                </li>
                <li>
                    Do not copy reviews, articles or substantial passages from
                    other people.
                </li>
                <li>
                    Do not publish spam, repetitive promotions or content
                    created only to manipulate rankings.
                </li>
            </ul>
        </section>
        <section>
            <h2>Respect other people</h2>
            <ul>
                <li>
                    Criticism of a game or opinion is allowed; harassment and
                    threats are not.
                </li>
                <li>
                    Do not publish hateful, discriminatory or sexually
                    exploitative content.
                </li>
                <li>
                    Do not expose private personal information or impersonate
                    another person.
                </li>
            </ul>
        </section>
        <section>
            <h2>Respect intellectual property</h2>
            <p>
                Upload only screenshots and material you are allowed to share.
                Do not use Curator.gg to distribute pirated games, cracks,
                unauthorized downloads or instructions intended to bypass
                ownership systems.
            </p>
        </section>
        <section>
            <h2>Moderation and enforcement</h2>
            <p>
                Content may be hidden or removed when it violates these
                guidelines, applicable law or platform policies. Repeated or
                serious violations may result in feature restrictions or account
                suspension.
            </p>
        </section>
        <section>
            <h2>Report a problem</h2>
            <p>
                Use the report action available on public reviews or contact
                <a href="mailto:faurorakontakt@gmail.com"
                    >faurorakontakt@gmail.com</a
                >
                with the affected URL and a concise explanation.
            </p>
        </section>
    </PublicInfoLayout>
</template>
