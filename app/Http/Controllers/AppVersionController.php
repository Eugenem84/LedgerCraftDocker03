<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppVersionController extends Controller
{
    public function checkQuasarAndroidVersion()
    {
        $files = glob(storage_path('app/public/*'));
        if (empty($files)) {
            return response()->json(['error' => 'дистрибутив не найден'],404);
        }

        $latestApk = basename($files[0]);

        //preg_match('/LedgerCraft-([\d.]+)\-debug.apk', $latestApk, $matches );

        preg_match('/LedgerCraft-([\d.]+)-debug\.apk/', $latestApk, $matches);
        if (empty($matches)) {
            return response()->json([
                'error' => 'Версия не найдена в имени файла',
                'apk_name' => $latestApk
            ], 400);
        }

        return response()->json([
            'version' => $matches[1],
            'apk_name' => $latestApk
        ]);
    }

    public function downloadLatestApk(): BinaryFileResponse
    {
        $directory = storage_path('app/public');
        $files = glob($directory . '/*.apk');
        if (empty($files)) {
            abort(404, 'APK файл не найден');
        }
        usort($files, function ($a, $b) {
           return filemtime($b) - filemtime($a);
        });

        $latestApk = $files[0];
        return response()->download($latestApk, basename($latestApk));
    }
}
