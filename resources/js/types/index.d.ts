import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: string[];
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Post {
    id: number;
    title: string;
    body: string;
    created_at: string;
    user: User; // A post has one user
    replies: Reply[]; // A post has many replies
    replies_count?: number; // For the forum index page
    can_update: boolean; 
    can_delete: boolean;
}

// This defines the structure for a single Reply
export interface Reply {
    id: number;
    body: string;
    created_at: string;
    user: User; // A reply has one user
    post_id: number;
    parent_id: number | null;
    children: Reply[]; // A reply can have many children (other replies)
    can_update: boolean; //
    can_delete: boolean;
}
