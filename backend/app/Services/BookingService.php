<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService
{
    /**
     * Get all bookings for a specific user
     */
    public function getAllForUser(User $user): Collection
    {
        return $user->bookings()
            ->with(['service', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();
    }

    /**
     * Get all bookings (admin only)
     */
    public function getAll(): Collection
    {
        return Booking::with(['service', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();
    }

    /**
     * Get paginated bookings (admin only)
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Booking::with(['service', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a booking by ID
     */
    public function findById(int $id): ?Booking
    {
        return Booking::with(['service', 'user'])->find($id);
    }

    /**
     * Create a new booking
     */
    public function create(User $user, array $data): Booking
    {
        return $user->bookings()->create([
            'service_id' => $data['service_id'],
            'customer_name' => $data['customer_name'],
            'address' => $data['address'],
            'date' => $data['date'],
            'time' => $data['time'],
            'status' => Booking::STATUS_PENDING,
        ])->load(['service', 'user']);
    }

    /**
     * Update booking status (admin only)
     * Returns array with success status and message
     */
    public function updateStatus(Booking $booking, string $newStatus): array
    {
        // Validate status value
        if (!in_array($newStatus, Booking::getStatuses())) {
            return [
                'success' => false,
                'message' => 'Invalid status value. Allowed: ' . implode(', ', Booking::getStatuses()),
            ];
        }

        // Check if transition is valid
        if (!$booking->canTransitionTo($newStatus)) {
            $allowedTransitions = Booking::STATUS_TRANSITIONS[$booking->status] ?? [];
            $allowedStr = empty($allowedTransitions) ? 'none' : implode(', ', $allowedTransitions);
            
            return [
                'success' => false,
                'message' => "Cannot transition from '{$booking->status}' to '{$newStatus}'. Allowed transitions: {$allowedStr}",
            ];
        }

        $booking->status = $newStatus;
        $booking->save();
        $booking->load(['service', 'user']);

        return [
            'success' => true,
            'message' => 'Booking status updated successfully',
            'booking' => $booking,
        ];
    }

    /**
     * Cancel a booking (user can cancel their own pending bookings)
     */
    public function cancelByUser(Booking $booking, User $user): array
    {
        if ($booking->user_id !== $user->id) {
            return [
                'success' => false,
                'message' => 'Unauthorized',
            ];
        }

        if (!$booking->canTransitionTo(Booking::STATUS_CANCELLED)) {
            return [
                'success' => false,
                'message' => 'This booking cannot be cancelled',
            ];
        }

        $booking->status = Booking::STATUS_CANCELLED;
        $booking->save();
        $booking->load(['service', 'user']);

        return [
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ];
    }

    /**
     * Delete a booking
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Check if booking belongs to user
     */
    public function belongsToUser(Booking $booking, User $user): bool
    {
        return $booking->user_id === $user->id;
    }

    /**
     * Check if user can access booking (owner or admin)
     */
    public function canAccess(Booking $booking, User $user): bool
    {
        return $booking->user_id === $user->id || $user->isAdmin();
    }
}
