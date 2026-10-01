<?php

namespace App\Http\Controllers;

use App\Models\BodyScan;
use App\Support\BodyScanTextParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use thiagoalessio\TesseractOCR\TesseractOCR;

class BodyScanController extends Controller
{
    /** Página: historial + botón de escaneo (se integra en Progreso). */
    public function index(Request $request)
    {
        $scans = $request->user()->bodyScans()
            ->orderByDesc('scanned_at')
            ->limit(50)
            ->get();

        $presented = $scans->map(fn (BodyScan $scan) => $this->present($scan))->values();

        return Inertia::render('body-scans/Index', [
            'scans' => $presented->all(),
            'latest' => $this->withDeltas($presented->first(), $presented->get(1)),
        ]);
    }

    /** Lee una captura con Tesseract y devuelve lo detectado (nunca falla duro). */
    public function ocr(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        try {
            $text = (new TesseractOCR($validated['image']->getRealPath()))
                ->lang('spa', 'eng')
                ->run();
        } catch (\Throwable) {
            $text = '';
        }

        $parser = new BodyScanTextParser;

        return response()->json([
            'detected' => $parser->parse($text),
            'extras' => $parser->parseExtras($text),
            'rawTextLength' => mb_strlen($text),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $scan = $request->user()->bodyScans()->create(
            collect($validated)->except('source')->merge(['source' => $validated['source'] ?? 'manual'])->all(),
        );

        return redirect()->route('body-scans.index')->with('status', 'scan-saved');
    }

    public function destroy(Request $request, BodyScan $scan)
    {
        $this->authorize('delete', $scan);

        $scan->delete();

        return back()->with('status', 'scan-deleted');
    }

    private function rules(): array
    {
        return [
            'scanned_at' => ['required', 'date'],
            'weight_kg' => ['sometimes', 'nullable', 'numeric', 'min:20', 'max:400'],
            'body_fat_pct' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:70'],
            'muscle_mass_kg' => ['sometimes', 'nullable', 'numeric', 'min:5', 'max:200'],
            'water_pct' => ['sometimes', 'nullable', 'numeric', 'min:20', 'max:80'],
            'bone_mass_kg' => ['sometimes', 'nullable', 'numeric', 'min:0.5', 'max:15'],
            'bmi' => ['sometimes', 'nullable', 'numeric', 'min:10', 'max:60'],
            'visceral_fat' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:30'],
            'metabolic_age' => ['sometimes', 'nullable', 'integer', 'min:5', 'max:100'],
            'source' => ['sometimes', Rule::in(['ocr', 'manual'])],
            'extra' => ['sometimes', 'array', 'max:12'],
            'extra.*' => ['numeric', 'min:0', 'max:9999'],
        ];
    }

    /** Última medición con cambio respecto a la anterior por métrica. */
    private function withDeltas(?array $latest, ?array $previous): ?array
    {
        if ($latest === null) {
            return null;
        }
        if ($previous === null) {
            $latest['deltas'] = [];

            return $latest;
        }

        $metricKeys = [
            'weightKg', 'bodyFatPct', 'muscleMassKg', 'waterPct', 'boneMassKg',
            'bmi', 'visceralFat', 'metabolicAge',
        ];
        $deltas = [];
        foreach ($metricKeys as $key) {
            if ($latest[$key] !== null && $previous[$key] !== null) {
                $deltas[$key] = round($latest[$key] - $previous[$key], 2);
            }
        }

        $latest['deltas'] = $deltas;

        return $latest;
    }

    private function present(BodyScan $scan): array
    {
        return [
            'id' => $scan->id,
            'scannedAt' => $scan->scanned_at->toDateString(),
            'weightKg' => $scan->weight_kg !== null ? (float) $scan->weight_kg : null,
            'bodyFatPct' => $scan->body_fat_pct !== null ? (float) $scan->body_fat_pct : null,
            'muscleMassKg' => $scan->muscle_mass_kg !== null ? (float) $scan->muscle_mass_kg : null,
            'waterPct' => $scan->water_pct !== null ? (float) $scan->water_pct : null,
            'boneMassKg' => $scan->bone_mass_kg !== null ? (float) $scan->bone_mass_kg : null,
            'bmi' => $scan->bmi !== null ? (float) $scan->bmi : null,
            'visceralFat' => $scan->visceral_fat,
            'metabolicAge' => $scan->metabolic_age,
            'source' => $scan->source,
            'extra' => $scan->extra ?? [],
        ];
    }
}
