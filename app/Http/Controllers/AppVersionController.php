<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
