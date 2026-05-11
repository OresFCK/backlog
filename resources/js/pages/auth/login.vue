<script setup>
defineProps({
    canResetPassword: Boolean,
    status: String,
})
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-zinc-950 px-6"
    >
        <div
            class="w-full max-w-md rounded-3xl border border-zinc-800 bg-zinc-900/50 p-8"
        >
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold text-white">
                    backlog
                </h1>

                <p class="mt-3 text-zinc-400">
                    Organize your games.
                    Finish your backlog.
                </p>
            </div>

            <a
                href="/auth/steam"
                class="flex w-full items-center justify-center gap-3 rounded-2xl bg-[#171a21] px-5 py-4 text-sm font-semibold text-white transition hover:bg-[#1f2430]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 496 512"
                    class="h-5 w-5 fill-current"
                >
                    <path
                        d="M496 256c0 137-111 248-248 248S0 393 0 256 111 8 248 8s248 111 248 248zM277.3 304.5l-48.8-20.3c-.4-.2-.8-.3-1.2-.4-5.9-4.1-13-6.6-20.7-6.6-20.4 0-36.9 16.5-36.9 36.9 0 17.5 12.2 32.2 28.5 35.9l11.8 4.9c18.2 7.6 39.1-1 46.7-19.2 7.6-18.2-1-39.1-19.4-46.2zm-58.8 43.6c-10.9 0-19.7-8.8-19.7-19.7s8.8-19.7 19.7-19.7 19.7 8.8 19.7 19.7-8.8 19.7-19.7 19.7zm116.4-180.3c-53.5 0-96.9 43.4-96.9 96.9 0 1.9.1 3.8.2 5.7l58.2 24.1c6.4 2.7 12.2 6.5 17 11.2 9.4-6.8 16.2-17 18.5-29 1.5-7.8 1.2-15.7-.9-23.2-2.1-7.5-5.9-14.3-11.1-19.8-5.2-5.5-11.6-9.9-18.8-12.7-7.2-2.8-15-4-22.7-3.5-1.5.1-2.9.2-4.3.5 10.6-15.5 28.4-25.7 48.5-25.7 32.5 0 58.9 26.4 58.9 58.9 0 28.3-20 51.9-46.6 57.5 1.7 4.9 2.9 10.1 3.5 15.4 1.3 11.4-.4 22.8-4.8 33.2 52.3-2.2 94.1-45.3 94.1-98.1-.1-54.2-44-98.1-98.2-98.1z"
                    />
                </svg>

                Continue with Steam
            </a>

            <p
                class="mt-8 text-center text-xs text-zinc-500"
            >
                Steam and the Steam logo are trademarks of
                Valve Corporation.
            </p>
        </div>
    </div>
</template>