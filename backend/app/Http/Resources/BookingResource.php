<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'service_id' => $this->service_id,
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->time,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'can_cancel' => $this->canTransitionTo(Booking::STATUS_CANCELLED),
            'can_confirm' => $this->canTransitionTo(Booking::STATUS_CONFIRMED),
            'allowed_transitions' => Booking::STATUS_TRANSITIONS[$this->status] ?? [],
            'service' => new ServiceResource($this->whenLoaded('service')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
