<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamSupportSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner || SupportTicket::where('parent_id', $owner->id)->exists()) {
            return;
        }

        SupportTicket::create([
            'parent_id' => $owner->id,
            'created_by' => $owner->id,
            'subject' => 'Dúvida sobre prescrição de treino',
            'description' => 'Como vincular exercícios da biblioteca Vimeo ao treino do cliente?',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $this->command->info('✅ Ticket de suporte de exemplo criado');
    }
}
