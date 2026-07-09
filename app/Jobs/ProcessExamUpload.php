<?php

namespace App\Jobs;

use App\Models\MemberPhoto;
use App\Models\User;
use App\Notifications\InAppAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessExamUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $memberPhotoId,
        public ?int $notifyUserId = null,
    ) {}

    public function handle(): void
    {
        $photo = MemberPhoto::query()->with('member')->find($this->memberPhotoId);

        if (! $photo) {
            return;
        }

        $exists = Storage::disk('public')->exists($photo->path);

        Log::info('ProcessExamUpload: arquivo verificado', [
            'member_photo_id' => $photo->id,
            'path' => $photo->path,
            'exists' => $exists,
            'type' => $photo->type,
        ]);

        if (! $exists) {
            $this->fail(new \RuntimeException('Arquivo de exame nao encontrado no storage.'));

            return;
        }

        $userId = $this->notifyUserId ?? $photo->parent_id;
        $user = $userId ? User::query()->find($userId) : null;

        if ($user) {
            $memberName = $photo->member?->name ?? 'cliente';
            $label = $photo->type === 'exam_document' ? 'exame' : 'foto';

            $user->notify(new InAppAlert(
                title: 'Upload processado',
                body: "O {$label} de {$memberName} foi processado com sucesso.",
                url: $photo->member_id ? url('/members/'.$photo->member_id.'/show?tab=photos') : null,
                icon: 'ri-file-upload-line',
                level: 'success',
            ));
        }
    }
}
