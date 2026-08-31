import { format, parseISO } from 'date-fns';
import { el, enUS } from 'date-fns/locale';

const dateLocales = { el, en: enUS } as const;

/**
 * Eloquent date casts serialize as full ISO8601 timestamps even for a
 * DATE column, so every date shown in the UI goes through here rather
 * than being printed raw.
 */
export function formatDate(value: string | null | undefined, locale: string = 'el'): string {
    if (!value) return '—';

    const resolved = locale in dateLocales ? (locale as keyof typeof dateLocales) : 'el';

    return format(parseISO(value), 'dd/MM/yyyy', { locale: dateLocales[resolved] });
}
