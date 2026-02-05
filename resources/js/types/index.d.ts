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
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Category {
    id: number;
    name: string;
    slug: string;
}

export interface Product {
    id: number;
    name: string;
    description?: string;
    price: number | string;
    stock: number;
    user_id?: number;
    category_id?: number;
    category_ids?: number[];
    category?: Category;
    categories?: Category[];
    image?: string;
    gallery?: Array<{ image: string; alt?: string }>;
    created_at?: string;
    updated_at?: string;
}

export interface CategoryStat {
    id: number;
    name: string;
    count: number;
    percentage: number;
}

export interface ProductWithCapital {
    id: number;
    name: string;
    price: number;
    stock: number;
    capital: number;
    image?: string;
    categories?: Category[];
}

export interface DashboardStats {
    totalProducts: number;
    totalCategories: number;
    totalCapital: number;
    averagePrice: number;
    highestPriceProduct: ProductWithCapital | null;
    highestCapitalProduct: ProductWithCapital | null;
    lowStockProducts: ProductWithCapital[];
    outOfStockProducts: ProductWithCapital[];
    categoryDistribution: CategoryStat[];
    topProducts: ProductWithCapital[];
}

