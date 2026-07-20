<script setup>
import { Head } from '@inertiajs/vue3';
import PublicInfoLayout from '@/components/home/PublicInfoLayout.vue';
</script>

<template>
    <Head title="Contact Curator.gg" />
    <PublicInfoLayout
        eyebrow="Contact"
        title="Questions, reports and feedback."
        intro="Use the contact address below for product questions, privacy requests, moderation reports, copyright matters and business inquiries."
    >
        <section class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <h2>Email</h2>
            <p>
                <a href="mailto:faurorakontakt@gmail.com"
                    >faurorakontakt@gmail.com</a
                >
            </p>
            <p>
                Please include the relevant public URL when reporting a review,
                tier list, profile or possible copyright infringement.
            </p>
        </section>
        <section>
            <h2>Privacy and account requests</h2>
            <p>
                For access, correction or deletion requests, write from an
                address where you can receive a verification response and
                include your Steam profile identifier when applicable. Never
                send a Steam password or authentication code.
            </p>
        </section>
        <section>
            <h2>Content and moderation</h2>
            <p>
                Public reviews can be reported directly inside the platform. For
                other content, provide the page address, a short explanation and
                any supporting context. Reports are reviewed against the
                Community Guidelines.
            </p>
        </section>
        <section>
            <h2>Copyright matters</h2>
            <p>
                Rights holders may request review or removal of material by
                identifying the protected work, the affected URL and their
                authority to act. Good-faith notices will be investigated
                promptly.
            </p>
        </section>
    </PublicInfoLayout>
</template>
