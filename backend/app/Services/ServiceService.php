<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function getAll(): Collection
    {
        return Service::orderBy('title')->get();
    }

    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function create(array $data): Service
    {
        return Service::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
        ]);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
        return $service->delete();
    }
}
