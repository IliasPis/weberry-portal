<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Search } from '@lucide/vue';
import { Input } from '@/Components/ui/input';

interface SearchResult {
    type: string;
    title: string;
    subtitle: string | null;
    url: string;
}

const { t } = useI18n();

const query = ref('');
const results = ref<SearchResult[]>([]);
const open = ref(false);
const container = ref<HTMLElement | null>(null);
let debounceTimer: ReturnType<typeof setTimeout>;

watch(query, (value) => {
    clearTimeout(debounceTimer);
    if (value.trim().length < 2) {
        results.value = [];
        open.value = false;
        return;
    }
    debounceTimer = setTimeout(async () => {
        const response = await fetch(`${route('admin.search')}?q=${encodeURIComponent(value)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        results.value = data.results;
        open.value = true;
    }, 300);
});

function goTo(result: SearchResult) {
    open.value = false;
    query.value = '';
    router.visit(result.url);
}

function onClickOutside(event: MouseEvent) {
    if (container.value && !container.value.contains(event.target as Node)) {
        open.value = false;
    }
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', onClickOutside);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="container" class="relative w-64">
        <Search class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <Input
            v-model="query"
            :placeholder="t('search.placeholder')"
            class="pl-8"
            @focus="() => results.length > 0 && (open = true)"
        />

        <div
            v-if="open"
            class="absolute top-full z-50 mt-1 w-80 rounded-md border bg-popover text-popover-foreground shadow-md"
        >
            <p v-if="results.length === 0" class="px-2 py-3 text-center text-sm text-muted-foreground">
                {{ t('search.noResults') }}
            </p>
            <button
                v-for="(result, index) in results"
                :key="index"
                type="button"
                class="flex w-full flex-col gap-0.5 border-b px-2 py-2 text-left text-sm last:border-b-0 hover:bg-accent"
                @click="goTo(result)"
            >
                <span class="flex items-center gap-2">
                    <span class="rounded bg-muted px-1.5 py-0.5 text-[10px] uppercase text-muted-foreground">
                        {{ t(`search.type.${result.type}`) }}
                    </span>
                    <span class="font-medium">{{ result.title }}</span>
                </span>
                <span v-if="result.subtitle" class="text-xs text-muted-foreground">{{ result.subtitle }}</span>
            </button>
        </div>
    </div>
</template>
