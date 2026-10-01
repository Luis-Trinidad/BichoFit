<script lang="ts">
    import type { Snippet } from 'svelte';
    import BottomNavLayout from '@/layouts/app/BottomNavLayout.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<BottomNavLayout {breadcrumbs}>
    {@render children?.()}
</BottomNavLayout>
