import { createI18n } from 'vue-i18n';
import el from '@/lang/el.json';
import en from '@/lang/en.json';

export type AppLocale = 'el' | 'en';

export function createAppI18n(locale: AppLocale) {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'en',
        messages: { el, en },
    });
}
