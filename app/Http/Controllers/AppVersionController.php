<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function checkQuasarAndroidVersion()
    {
        $files = glob(storage_path('app/public/*.apk'));
        if (empty($files)) {
            return response()->json(['error' => 'дистрибутив не найден'],404);
        }

        $latestApk = basename($files[0]);

        preg_match('/([\d.]+)(?:-[\w]+)?\.apk/', $latestApk, $matches);

        if (empty($matches[1])) {
            return response()->json(['error' => 'не удалось извлечь версию из файла'], 400);
        }

        return response()->json([
            'version' => $matches[1],
            'apk_name' => $latestApk
        ]);
    }
}
