<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * List bookings
     * - Admin: sees all bookings
     * - User: sees only their own bookings
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $bookings = $this->bookingService->getAll();
        } else {
            $bookings = $this->bookingService->getAllForUser($user);
        }

        return BookingResource::collection($bookings);
    }

    /**
     * Create a new booking (authenticated users)
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create($request->user(), $request->validated());

        return $this->apiResponse(
            new BookingResource($booking),
            'Booking created successfully',
            201
        );
    }

    /**
     * Show a single booking
     * - Admin: can see any booking
     * - User: can only see their own booking
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        if (!$this->bookingService->canAccess($booking, $request->user())) {
            return $this->apiResponse(null, 'Unauthorized', 403);
        }

        $booking->load(['service', 'user']);

        return $this->apiResponse(new BookingResource($booking));
    }

    /**
     * Update booking status (admin only)
     * Status flow: pending → confirmed → cancelled
     *              pending → cancelled
     */
    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking): JsonResponse
    {
        $result = $this->bookingService->updateStatus($booking, $request->validated()['status']);

        if (!$result['success']) {
            return $this->apiResponse(null, $result['message'], 422);
        }

        return $this->apiResponse(
            new BookingResource($result['booking']),
            $result['message']
        );
    }

    /**
     * Cancel a booking (user can cancel their own pending/confirmed bookings)
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $result = $this->bookingService->cancelByUser($booking, $request->user());

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Unauthorized' ? 403 : 422;
            return $this->apiResponse(null, $result['message'], $statusCode);
        }

        return $this->apiResponse(
            new BookingResource($result['booking']),
            $result['message']
        );
    }

    /**
     * Delete a booking (only owner or admin)
     */
    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        if (!$this->bookingService->canAccess($booking, $request->user())) {
            return $this->apiResponse(null, 'Unauthorized', 403);
        }

        $this->bookingService->delete($booking);

        return $this->apiResponse(null, 'Booking deleted successfully');
    }
}
