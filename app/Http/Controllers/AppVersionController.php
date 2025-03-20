<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function checkQuasarAndroidVersion()
    {
        $files = glob(storage_path('app/app-debug-*.apk'));
        if (empty($files)) {
            return response()->json(['error' => 'дистрибутив не найден'],404);
        }

        $latestApk = basename($files[0]);

        preg_match('/app-release-([\d.]+)\.apk', $latestApk, $matches );

        return response()->json([
            'version' => $matches[1] ?? '1.0.0',
            'apk_name' => $latestApk
        ]);
    }
}
