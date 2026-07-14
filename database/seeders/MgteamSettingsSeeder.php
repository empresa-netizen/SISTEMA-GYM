<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();

        $settings = [
            ['name' => 'app_name', 'value' => 'MGTEAM FITNESS & HEALTH', 'type' => 'app'],
            ['name' => 'app_currency', 'value' => 'BRL', 'type' => 'app'],
            ['name' => 'app_language', 'value' => 'pt_BR', 'type' => 'app'],
            ['name' => 'app_timezone', 'value' => 'America/Sao_Paulo', 'type' => 'app'],
        ];

        foreach ($settings as $setting) {
            Setting::create(array_merge($setting, ['parent_id' => $owner->id]));
        }

        $this->command->info('✅ Configurações MGTEAM Platform aplicadas');
    }
}
