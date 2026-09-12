<?php

namespace App\Http\Controllers;

use App\Models\SpecializationTemplate;

/**
 * Выдача пресетов специализаций (Фаза 10, задача 10.7, решение D5).
 *
 * Клиент забирает контент и кладёт его в read-only кэш (`meta`), поэтому
 * поправленный пресет приезжает без релиза приложения, а офлайн используется
 * кэш (фолбэк — клиентские JSON из задачи 10.4).
 */
class SpecializationTemplateController extends Controller
{
    public function index()
    {
        $templates = SpecializationTemplate::query()
            ->orderBy('preset_key')
            ->get()
            ->map(fn (SpecializationTemplate $template) => [
                'preset_key' => $template->preset_key,
                'version'    => (int) $template->version,
                'content'    => $template->content ?? [],
                'updated_at' => optional($template->updated_at)->toJSON(),
            ]);

        return response()->json(['templates' => $templates]);
    }
}
