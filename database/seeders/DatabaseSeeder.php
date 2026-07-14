<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            MgteamUserSeeder::class,
            MgteamTenantSeeder::class,
            MgteamSettingsSeeder::class,
            MgteamPlanSeeder::class,
            MgteamMemberSeeder::class,
            MgteamWorkoutSeeder::class,
            MgteamExerciseSeeder::class,
            MgteamPaymentSeeder::class,
            MgteamLibrarySeeder::class,
            MgteamEventSeeder::class,
            MgteamModulesSeeder::class,
            MgteamCrmSeeder::class,
            MgteamSupportSeeder::class,
            MgteamNotificationSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Base MGTEAM FITNESS & HEALTH criada!');
        $this->command->info('   👤 1 profissional + 2 clientes (espelhados no app)');
        $this->command->info('   🏋️ Treinos com exercícios do catálogo Vimeo local');
        $this->command->info('   📧 Login unificado: coach@mgteam.app / password');
        $this->command->info('   📱 Depois do seed: ./scripts/unify-demo-auth.sh');
    }
}
