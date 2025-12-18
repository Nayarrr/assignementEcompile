<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceService $serviceService
    ) {}

    /**
     * List all services (public endpoint)
     */
    public function index(): AnonymousResourceCollection
    {
        $services = $this->serviceService->getAll();

        return ServiceResource::collection($services);
    }

    /**
     * Show a single service (public endpoint)
     */
    public function show(Service $service): JsonResponse
    {
        return $this->apiResponse(new ServiceResource($service));
    }

    /**
     * Create a new service (admin only)
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->create($request->validated());

        return $this->apiResponse(
            new ServiceResource($service),
            'Service created successfully',
            201
        );
    }

    /**
     * Update a service (admin only)
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $service = $this->serviceService->update($service, $request->validated());

        return $this->apiResponse(
            new ServiceResource($service),
            'Service updated successfully'
        );
    }

    /**
     * Delete a service (admin only)
     */
    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->delete($service);

        return $this->apiResponse(null, 'Service deleted successfully', 204);
    }
}
