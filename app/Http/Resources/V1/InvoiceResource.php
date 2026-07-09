<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'member_id' => $this->member_id,
            'invoice_date' => optional($this->invoice_date)?->toDateString(),
            'due_date' => optional($this->due_date)?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'balance' => (float) ($this->total_amount - $this->paid_amount),
            'status' => $this->status,
            'notes' => $this->notes,
            'member' => new MemberResource($this->whenLoaded('member')),
            'items' => $this->whenLoaded('items'),
            'payments' => InvoicePaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
