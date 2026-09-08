<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import weberryLogo from '@/images/brand/weberry-logo.png';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-muted/40 pt-10 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <img :src="weberryLogo" alt="Weberry" class="h-28 w-auto sm:h-32" />
            </Link>
        </div>

        <div
            class="mt-8 w-full overflow-hidden rounded-lg border bg-card px-6 py-5 shadow-sm sm:max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
