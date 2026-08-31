<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ChevronDown, LogOut, User as UserIcon } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

const { t } = useI18n();
const page = usePage();

const initials = () =>
    page.props.auth.user.name
        .split(' ')
        .map((part: string) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="gap-2 px-2">
                <Avatar class="h-7 w-7">
                    <AvatarFallback class="text-xs">{{ initials() }}</AvatarFallback>
                </Avatar>
                <span class="hidden text-sm font-medium sm:inline">{{ page.props.auth.user.name }}</span>
                <ChevronDown class="h-4 w-4 opacity-60" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">{{ page.props.auth.user.name }}</p>
                    <p class="text-xs leading-none text-muted-foreground">{{ page.props.auth.user.email }}</p>
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link :href="route('profile.edit')" class="flex w-full items-center gap-2">
                    <UserIcon class="h-4 w-4" />
                    {{ t('nav.client.account') }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-2">
                    <LogOut class="h-4 w-4" />
                    {{ t('common.logout') }}
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
