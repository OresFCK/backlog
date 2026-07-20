<script setup>
import { Head } from '@inertiajs/vue3';
import PublicInfoLayout from '@/components/home/PublicInfoLayout.vue';
</script>

<template>
    <Head title="About Curator.gg" />
    <PublicInfoLayout
        eyebrow="About the project"
        title="A better home for your gaming history."
        intro="Curator.gg is an independent platform for organizing game libraries, publishing player perspectives and discovering what deserves your time next."
    >
        <section>
            <h2>Why Curator.gg exists</h2>
            <p>
                Digital libraries grow faster than the time available to play
                them. Storefronts are excellent at helping people buy games, but
                they are less useful when a player wants to understand what they
                already own. Curator.gg was created to make a large library feel
                personal, manageable and worth revisiting.
            </p>
        </section>
        <section>
            <h2>What the platform provides</h2>
            <p>
                Players can organize a backlog, track active and completed
                games, record ratings and notes, publish reviews, create custom
                collections and present selected games on a public profile.
                Community rankings and recommendations add human context to the
                catalog.
            </p>
        </section>
        <section>
            <h2>Public tools for the wider community</h2>
            <p>
                Not every feature requires an account. Public game pages and the
                Tier List Maker are designed to be useful immediately. Tier
                lists can be shared as completed rankings or as blank templates
                for other players to fill in.
            </p>
        </section>
        <section>
            <h2>Independent development</h2>
            <p>
                Curator.gg is operated as an independent project in Poland. It
                is not affiliated with Valve Corporation. Steam, game titles,
                trademarks and third-party imagery remain the property of their
                respective owners.
            </p>
        </section>
        <section>
            <h2>Data and trust</h2>
            <p>
                Steam authentication takes place through Steam OpenID.
                Curator.gg does not receive Steam passwords. Details about
                stored information, retention and user rights are available in
                the <a href="/privacy">Privacy Policy</a>.
            </p>
        </section>
    </PublicInfoLayout>
</template>
