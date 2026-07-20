<script setup>
const questions = [
    {
        question: 'Is Curator.gg free?',
        answer: 'Yes. You can connect Steam, organize your library and use the core community features for free.',
    },
    {
        question: 'Does Curator.gg receive my Steam password?',
        answer: 'No. Authentication is handled by Steam OpenID on Steam’s own domain. Curator.gg receives your Steam identifier and can import public profile and library information permitted by your privacy settings.',
    },
    {
        question: 'What happens if my Steam library is private?',
        answer: 'You can still sign in, but library synchronization may be limited until game details are public in your Steam privacy settings.',
    },
    {
        question: 'Can I control what other people see?',
        answer: 'Yes. Your library organization remains part of your account, while public profiles, reviews, lists and tier lists expose only the information designed for sharing.',
    },
    {
        question: 'Where do game descriptions and images come from?',
        answer: 'Curator.gg combines catalog information from Steam and IGDB with original organization, statistics, rankings and user-written community content.',
    },
    {
        question: 'Can I report an inappropriate review or public submission?',
        answer: 'Yes. Public reviews include reporting tools, and community content is subject to the Community Guidelines and moderation.',
    },
    {
        question: 'Can I delete my account and data?',
        answer: 'Account deletion is available from account settings. You can also contact the administrator for privacy and data protection requests.',
    },
    {
        question: 'Is Curator.gg affiliated with Valve?',
        answer: 'No. Curator.gg is an independent project and is not affiliated with or endorsed by Valve Corporation. Steam names and assets belong to their respective owners.',
    },
];
</script>

<template>
    <section id="faq" class="mx-auto max-w-5xl px-4 py-20 sm:px-6 sm:py-24">
        <div class="text-center">
            <p class="text-sm font-black text-indigo-300 uppercase">
                Frequently asked questions
            </p>
            <h2 class="mt-3 text-3xl font-black sm:text-5xl">
                Know what you are connecting.
            </h2>
        </div>

        <div class="mt-12 grid gap-3">
            <details
                v-for="item in questions"
                :key="item.question"
                class="group rounded-2xl border border-zinc-800 bg-zinc-900 p-5 open:border-zinc-700"
            >
                <summary
                    class="cursor-pointer list-none pr-8 font-black text-white"
                >
                    {{ item.question }}
                </summary>
                <p class="mt-4 max-w-3xl text-sm leading-7 text-zinc-400">
                    {{ item.answer }}
                </p>
            </details>
        </div>
    </section>
</template>
