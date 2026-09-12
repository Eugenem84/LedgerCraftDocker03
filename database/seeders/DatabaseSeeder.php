<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Пресеты специализаций (Фаза 11, задача 11.3) — это **контент**, без которого
     * новый пользователь не получит стартовый каталог. Сид идемпотентен, поэтому
     * его безопасно запускать на любом контуре: `php artisan db:seed --force`
     * (или точечно `--class=SpecializationTemplateSeeder`).
     */
    public function run(): void
    {
        $this->call([
            SpecializationTemplateSeeder::class,
        ]);
    }
}
