export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    notification_preferences?: Record<string, boolean> | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        is_admin_side: boolean;
    };
    locale: string;
    unread_notifications_count: number;
};
