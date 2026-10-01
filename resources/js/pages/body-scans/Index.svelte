<script module lang="ts">
    import { index } from '@/routes/body-scans';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Composición corporal',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { cn } from '@/lib/utils';
    import { destroy as destroyScan, ocr as ocrUrl, store as storeScan } from '@/routes/body-scans';

    interface ScanRow {
        id: number;
        scannedAt: string;
        weightKg: number | null;
        bodyFatPct: number | null;
        muscleMassKg: number | null;
        waterPct: number | null;
        boneMassKg: number | null;
        bmi: number | null;
        visceralFat: number | null;
        metabolicAge: number | null;
        source: string;
        extra: Record<string, number>;
    }

    let {
        scans = [],
        latest = null,
    }: {
        scans?: ScanRow[];
        latest?: (ScanRow & { deltas: Record<string, number> }) | null;
    } = $props();

    const FIELDS = [
        { key: 'weight_kg', label: 'Peso', unit: 'kg', step: '0.1' },
        { key: 'body_fat_pct', label: 'Grasa corporal', unit: '%', step: '0.1' },
        { key: 'muscle_mass_kg', label: 'Masa muscular', unit: 'kg', step: '0.1' },
        { key: 'water_pct', label: 'Agua corporal', unit: '%', step: '0.1' },
        { key: 'bone_mass_kg', label: 'Masa ósea', unit: 'kg', step: '0.1' },
        { key: 'bmi', label: 'IMC', unit: '', step: '0.1' },
        { key: 'visceral_fat', label: 'Grasa visceral', unit: '', step: '1' },
        { key: 'metabolic_age', label: 'Edad metabólica', unit: 'años', step: '1' },
    ] as const;

    // --- flujo de escaneo ---
    let fileInput: HTMLInputElement | undefined = $state();
    let previewUrl = $state<string | null>(null);
    let reading = $state(false);
    let form = $state<Record<string, string>>({});
    let confidence = $state<Record<string, number>>({});
    let extraForm = $state<Record<string, string>>({});
    let scannedDate = $state(new Date().toISOString().slice(0, 10));

    async function onFile(event: Event) {
        const input = event.currentTarget as HTMLInputElement;
        const file = input.files?.[0];
        if (!file) return;

        previewUrl = URL.createObjectURL(file);
        reading = true;
        form = {};
        confidence = {};

        try {
            const body = new FormData();
            body.append('image', file);
            const response = await fetch(ocrUrl().url, {
                method: 'POST',
                body,
                headers: {
                    'X-XSRF-TOKEN': decodeURIComponent(
                        document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
                    ),
                    Accept: 'application/json',
                },
            });
            const data = await response.json();

            if (!response.ok) {
                toast.error('No se pudo leer la imagen. Llena los datos a mano.');
                return;
            }

            for (const [field, info] of Object.entries<any>(data.detected ?? {})) {
                form[field] = String(info.value);
                confidence[field] = info.confidence;
            }
            for (const [label, value] of Object.entries<any>(data.extras ?? {})) {
                extraForm[label] = String(value);
            }
            const count = Object.keys(data.detected ?? {}).length + Object.keys(data.extras ?? {}).length;
            if (count > 0) {
                toast.success(`Detecté ${count} dato${count === 1 ? '' : 's'} — revisa y confirma.`);
            } else {
                toast.error('No reconocí valores en la captura. Llena a mano.');
            }
        } catch {
            toast.error('Error leyendo la imagen. Llena los datos a mano.');
        } finally {
            reading = false;
        }
    }

    function save() {
        const payload: Record<string, unknown> = { scanned_at: scannedDate, source: 'ocr' };
        for (const field of FIELDS) {
            if (form[field.key] !== '' && form[field.key] !== undefined) {
                payload[field.key] = form[field.key];
            }
        }
        const extraEntries = Object.entries(extraForm).filter(([, v]) => v !== '' && v !== undefined);
        if (extraEntries.length > 0) {
            payload.extra = Object.fromEntries(extraEntries);
        }

        router.post(storeScan().url, payload, {
            onSuccess: () => {
                previewUrl = null;
                form = {};
                confidence = {};
                extraForm = {};
                fileInput && (fileInput.value = '');
            },
        });
    }

    function remove(scan: ScanRow) {
        if (confirm('¿Borrar esta medición?')) {
            router.delete(destroyScan({ scan: scan.id }).url, { preserveScroll: true });
        }
    }

    function scanValues(scan: ScanRow): Record<string, number | null> {
        return {
            weight_kg: scan.weightKg,
            body_fat_pct: scan.bodyFatPct,
            muscle_mass_kg: scan.muscleMassKg,
            water_pct: scan.waterPct,
            bone_mass_kg: scan.boneMassKg,
            bmi: scan.bmi,
            visceral_fat: scan.visceralFat,
            metabolic_age: scan.metabolicAge,
        };
    }

    function formatDate(iso: string): string {
        return new Intl.DateTimeFormat('es', { day: 'numeric', month: 'short', year: 'numeric' }).format(
            new Date(iso + 'T12:00:00'),
        );
    }
</script>

<AppHead title="Composición corporal" />

<div class="flex flex-col gap-4 p-4">
    <header>
        <h1 class="text-2xl font-bold">Composición corporal</h1>
        <p class="text-sm text-muted-foreground">
            Manda la captura de tu báscula y la leo por ti. Siempre confirmas antes de guardar.
        </p>
    </header>

    {#if latest}
        {@const deltaLabels: Record<string, string> = {
            weightKg: 'Peso', bodyFatPct: 'Grasa', muscleMassKg: 'Músculo', waterPct: 'Agua',
        }}
        <Card class="border-primary/40">
            <CardContent class="flex flex-col gap-3 py-4">
                <p class="text-sm font-semibold capitalize">{formatDate(latest.scannedAt)} · última medición</p>
                <div class="grid grid-cols-2 gap-3">
                    {#each FIELDS.slice(0, 4) as field (field.key)}
                        {@const key = field.key === 'weight_kg' ? 'weightKg' : field.key === 'body_fat_pct' ? 'bodyFatPct' : field.key === 'muscle_mass_kg' ? 'muscleMassKg' : 'waterPct'}
                        {@const value = latest[key as 'weightKg']}
                        {@const delta = latest.deltas?.[key]}
                        <div class="flex flex-col">
                            <span class="text-xs text-muted-foreground">{deltaLabels[key]}</span>
                            <span class="text-xl font-bold tabular-nums">
                                {value !== null ? value : '—'}
                                <span class="text-xs font-normal text-muted-foreground">{field.unit}</span>
                            </span>
                            {#if delta !== undefined && delta !== 0}
                                <span class="text-xs font-medium tabular-nums {['bodyFatPct', 'visceralFat'].includes(key) ? (delta < 0 ? 'text-emerald-600' : 'text-red-500') : (delta < 0 ? 'text-red-500' : 'text-emerald-600')}">
                                    {delta > 0 ? '▲' : '▼'} {Math.abs(delta)}
                                </span>
                            {/if}
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>
    {/if}

    <input
        type="file"
        accept="image/*"
        capture="environment"
        class="hidden"
        bind:this={fileInput}
        onchange={onFile}
    />
    <Button size="lg" class="h-14 text-base font-semibold" onclick={() => fileInput?.click()}>
        Escanear captura de la báscula
    </Button>

    {#if previewUrl}
        <Card>
            <CardContent class="flex flex-col gap-4 py-4">
                <div class="flex justify-center">
                    <img src={previewUrl} alt="Captura de la báscula" class="max-h-56 rounded-lg border object-contain" />
                </div>

                {#if reading}
                    <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                        <Spinner class="size-5" /> Leyendo la captura…
                    </div>
                {:else}
                    <div class="grid grid-cols-2 gap-3">
                        {#each FIELDS as field (field.key)}
                            {@const low = (confidence[field.key] ?? 0) < 0.7}
                            <div class="flex flex-col gap-1">
                                <Label for={`scan-${field.key}`} class="text-xs">
                                    {field.label}{field.unit ? ` (${field.unit})` : ''}
                                    {#if form[field.key] !== undefined && form[field.key] !== ''}
                                        {#if low}
                                            <span class="text-amber-500">· revisa</span>
                                        {:else}
                                            <span class="text-emerald-500">· detectado</span>
                                        {/if}
                                    {/if}
                                </Label>
                                <Input
                                    id={`scan-${field.key}`}
                                    type="number"
                                    inputmode="decimal"
                                    step={field.step}
                                    class={cn('h-10 tabular-nums', low && form[field.key] !== '' ? 'border-amber-500' : '')}
                                    placeholder="—"
                                    bind:value={form[field.key]}
                                />
                            </div>
                        {/each}
                        <div class="flex flex-col gap-1">
                            <Label for="scan-date" class="text-xs">Fecha</Label>
                            <Input id="scan-date" type="date" class="h-10" bind:value={scannedDate} />
                        </div>

                        {#if Object.keys(extraForm).length > 0}
                            <div class="col-span-2 flex flex-col gap-2">
                                <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Más métricas detectadas
                                </p>
                                <div class="grid grid-cols-2 gap-2">
                                    {#each Object.entries(extraForm) as [label] (label)}
                                        <div class="flex flex-col gap-1">
                                            <Label class="text-xs capitalize">{label.replace(/_/g, ' ')}</Label>
                                            <Input
                                                type="number"
                                                inputmode="decimal"
                                                class="h-10 tabular-nums"
                                                bind:value={extraForm[label]}
                                            />
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>

                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            class="flex-1"
                            onclick={() => {
                                previewUrl = null;
                                form = {};
                                confidence = {};
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button class="flex-[2]" onclick={save}>Guardar medición</Button>
                    </div>
                {/if}
            </CardContent>
        </Card>
    {/if}

    <section class="flex flex-col gap-2">
        <h2 class="px-1 font-semibold">Historial</h2>
        {#each scans as scan (scan.id)}
            <Card>
                <CardContent class="flex items-center justify-between gap-3 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold capitalize">{formatDate(scan.scannedAt)}</p>
                        <p class="truncate text-xs text-muted-foreground">
                            {#each FIELDS as field (field.key)}
                                {@const value = scanValues(scan)[field.key]}
                                {#if value !== null}
                                    {field.label}: {value}{field.unit === '%' ? '%' : field.unit === 'kg' ? ' kg' : field.unit === 'años' ? ' a' : ''} ·
                                {/if}
                            {/each}
                            {#each Object.entries(scan.extra ?? {}) as [label, value] (label)}
                                {label.replace(/_/g, ' ')}: {value} ·
                            {/each}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        {#if scan.source === 'ocr'}
                            <Badge variant="outline" class="text-[10px]">OCR</Badge>
                        {/if}
                        <Button
                            size="sm"
                            variant="ghost"
                            class="h-7 px-2 text-muted-foreground"
                            aria-label="Borrar medición"
                            onclick={() => remove(scan)}
                        >
                            ✕
                        </Button>
                    </div>
                </CardContent>
            </Card>
        {:else}
            <Card>
                <CardContent class="py-6 text-center text-sm text-muted-foreground">
                    Sin mediciones todavía. Escanea la primera captura de tu báscula.
                </CardContent>
            </Card>
        {/each}
    </section>
</div>
