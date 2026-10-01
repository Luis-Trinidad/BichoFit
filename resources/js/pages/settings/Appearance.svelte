<script module lang="ts">
    import { edit as editAppearance } from '@/routes/appearance';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Configuración de apariencia',
                href: editAppearance(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppearanceTabs from '@/components/AppearanceTabs.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
</script>

<AppHead title="Configuración de apariencia" />

<h1 class="sr-only">Configuración de apariencia</h1>

<div class="space-y-6">
    <Heading
        variant="small"
        title="Configuración de apariencia"
        description="Update the appearance settings for your account"
    />
    <AppearanceTabs />
</div>
