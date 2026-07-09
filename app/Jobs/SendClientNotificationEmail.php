<?php

namespace App\Jobs;

use App\Mail\Common;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendClientNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $memberId,
        public string $subject,
        public string $message,
    ) {}

    public function handle(): void
    {
        $member = Member::query()->find($this->memberId);

        if (! $member?->email) {
            Log::warning('SendClientNotificationEmail: membro sem e-mail', [
                'member_id' => $this->memberId,
            ]);

            return;
        }

        Mail::to($member->email)->send(new Common($this->subject, $this->message));
    }
}
