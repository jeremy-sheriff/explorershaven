<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {BookOpen, Folder, LayoutGrid, UsersIcon, DollarSign, Receipt, CogIcon} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import students from "@/routes/students";
import fees from "@/routes/fees";
import { dashboard } from '@/routes';
import feePayments from "@/routes/fee-payments";
import feeCredits from "@/routes/fee-credits";
import studentProgression from "@/routes/student-progression";

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Students',
        href: students.index(),
        icon: UsersIcon,
    },
    {
        title: 'Fee Structure',
        href: fees.index(),
        icon: DollarSign,
    },
    {
        title: 'Fee Payments',
        href: feePayments.index(),
        icon: Receipt,
    },

    {
        title: 'Fee Credits (Advance)',
        href: feeCredits.index(),
        icon: Receipt,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Progression',
        href: studentProgression.index(),
        icon: CogIcon,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
