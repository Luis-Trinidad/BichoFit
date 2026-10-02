<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VersionController extends Controller
{
    /** Hash de la versión actual: cambia en cada deploy (assets rebuild). */
    public function __invoke(Request $request)
    {
        $manifest = public_path('build/manifest.json');

        return response()->json([
            'version' => is_file($manifest)
                ? md5_file($manifest)
                : 'dev-'.now()->timestamp,
        ]);
    }
}
