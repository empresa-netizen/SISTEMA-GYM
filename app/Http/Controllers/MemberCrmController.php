<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessExamUpload;
use App\Jobs\SendClientNotificationEmail;
use App\Jobs\SendPaymentReminderEmail;
use App\Models\CardioPlan;
use App\Models\DietPrescription;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MemberAnamnesis;
use App\Models\MemberLogbook;
use App\Models\MemberNote;
use App\Models\MemberPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberCrmController extends Controller
{
    public function storeAnamnesis(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $data = $request->validate([
            'goals' => 'nullable|string',
            'injuries' => 'nullable|string',
            'medications' => 'nullable|string',
            'lifestyle' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed',
        ]);

        MemberAnamnesis::updateOrCreate(
            ['parent_id' => parentId(), 'member_id' => $member->id],
            array_merge($data, ['status' => $data['status'] ?? 'completed'])
        );

        return back()->with('success', 'Anamnese salva.');
    }

    public function storePhoto(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'photo' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx',
            'type' => 'required|in:front,back,side,progress,document,exam_document',
            'caption' => 'nullable|string|max:255',
        ]);

        $folder = $validated['type'] === 'exam_document' ? 'member-exams' : 'member-photos';
        $path = $request->file('photo')->store($folder, 'public');

        $photo = MemberPhoto::create([
            'parent_id' => parentId(),
            'member_id' => $member->id,
            'path' => $path,
            'type' => $validated['type'],
            'caption' => $validated['caption'] ?? null,
        ]);

        // Processamento pesado (verificacao/notificacao) em fila
        ProcessExamUpload::dispatch($photo->id, auth()->id());

        $message = $validated['type'] === 'exam_document' ? 'Exame enviado.' : 'Foto enviada.';

        return back()->with('success', $message);
    }

    public function storeDietPrescription(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'diet_menu_id' => 'nullable|exists:diet_menus,id',
            'notes' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        DietPrescription::create([
            'parent_id' => parentId(),
            'member_id' => $member->id,
            'title' => $validated['title'],
            'diet_menu_id' => $validated['diet_menu_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'status' => 'scheduled',
            'delivery_status' => 'PENDING',
        ]);

        return back()->with('success', 'Dieta prescrita.');
    }

    public function sendDietPrescription(DietPrescription $prescription): RedirectResponse
    {
        abort_unless($prescription->parent_id === parentId(), 403);

        $prescription->update([
            'status' => 'sent',
            'delivery_status' => 'DELIVERED',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Dieta enviada ao aluno.');
    }

    public function storeLogbook(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'type' => 'required|in:TRAINING,DIET,WEIGHT',
            'title' => 'required|string|max:255',
            'logged_at' => 'required|date',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        MemberLogbook::create(array_merge($validated, [
            'parent_id' => parentId(),
            'member_id' => $member->id,
        ]));

        return back()->with('success', 'Registro adicionado ao diário.');
    }

    public function storeCardioPlan(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'modality' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'intensity' => 'nullable|string|max:50',
            'weekly_frequency' => 'nullable|integer|min:1|max:14',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,active,completed,cancelled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        CardioPlan::create([
            'parent_id' => parentId(),
            'member_id' => $member->id,
            'title' => $validated['title'],
            'modality' => $validated['modality'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'intensity' => $validated['intensity'] ?? null,
            'weekly_frequency' => $validated['weekly_frequency'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'starts_at' => $validated['starts_at'] ?? now()->toDateString(),
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return back()->with('success', 'Plano de cardio criado.');
    }

    public function storeNote(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'body' => 'required|string',
            'noted_at' => 'nullable|date',
        ]);

        MemberNote::create([
            'parent_id' => parentId(),
            'member_id' => $member->id,
            'author_id' => auth()->id(),
            'body' => $validated['body'],
            'noted_at' => $validated['noted_at'] ?? now(),
        ]);

        return back()->with('success', 'Nota adicionada.');
    }

    public function assumeClient(Request $request, Member $member): JsonResponse|RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $member->update([
            'coach_user_id' => auth()->id(),
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Cliente assumido com sucesso.',
                'coach_user_id' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Você assumiu este cliente.');
    }

    public function notifyClient(Request $request, Member $member): JsonResponse|RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'channel' => 'nullable|in:app,email,whatsapp',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $channel = $validated['channel'] ?? 'email';
        $subject = $validated['subject'] ?: 'Mensagem do seu coach';
        $message = $validated['message'] ?: 'Voce tem uma nova mensagem do seu coach.';
        $isPaymentReminder = str_contains(mb_strtolower($subject.' '.$message), 'pagament');

        if ($channel === 'email') {
            $openInvoice = Invoice::query()
                ->where('member_id', $member->id)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->latest('due_date')
                ->first();

            if ($isPaymentReminder && $openInvoice) {
                SendPaymentReminderEmail::dispatch($openInvoice->id, $subject, $message);
            } else {
                SendClientNotificationEmail::dispatch($member->id, $subject, $message);
            }
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Notificação enfileirada.',
                'data' => $validated,
            ]);
        }

        return back()->with('success', 'Notificação enfileirada para envio.');
    }

    public function comparePhotos(Request $request, Member $member): JsonResponse|RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $validated = $request->validate([
            'photo_a_id' => 'required|exists:member_photos,id',
            'photo_b_id' => 'required|exists:member_photos,id|different:photo_a_id',
        ]);

        $photos = MemberPhoto::where('member_id', $member->id)
            ->whereIn('id', [$validated['photo_a_id'], $validated['photo_b_id']])
            ->get()
            ->keyBy('id');

        abort_unless($photos->count() === 2, 422, 'Fotos inválidas para comparação.');

        $payload = [
            'status' => true,
            'message' => 'Comparação pronta.',
            'photos' => [
                'a' => [
                    'id' => $photos[$validated['photo_a_id']]->id,
                    'url' => $photos[$validated['photo_a_id']]->url,
                    'caption' => $photos[$validated['photo_a_id']]->caption,
                    'type' => $photos[$validated['photo_a_id']]->type,
                    'created_at' => $photos[$validated['photo_a_id']]->created_at?->toIso8601String(),
                ],
                'b' => [
                    'id' => $photos[$validated['photo_b_id']]->id,
                    'url' => $photos[$validated['photo_b_id']]->url,
                    'caption' => $photos[$validated['photo_b_id']]->caption,
                    'type' => $photos[$validated['photo_b_id']]->type,
                    'created_at' => $photos[$validated['photo_b_id']]->created_at?->toIso8601String(),
                ],
            ],
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', 'Comparação gerada.')->with('compare_photos', $payload['photos']);
    }
}
