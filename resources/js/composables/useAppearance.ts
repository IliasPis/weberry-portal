import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark';

const STORAGE_KEY = 'weberry-appearance';

const isDark = ref(false);

function applyAppearance(appearance: Appearance) {
    document.documentElement.classList.toggle('dark', appearance === 'dark');
    isDark.value = appearance === 'dark';
}

/**
 * The initial class is already applied synchronously by an inline script in
 * app.blade.php (before first paint, to avoid a flash of the wrong theme).
 * This composable just mirrors that state into a reactive ref and handles
 * toggling it afterwards.
 */
export function useAppearance() {
    onMounted(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });

    function toggle() {
        const next: Appearance = isDark.value ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyAppearance(next);
    }

    return { isDark, toggle };
}
