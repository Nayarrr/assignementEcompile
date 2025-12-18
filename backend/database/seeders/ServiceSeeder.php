<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Basic House Cleaning',
                'description' => 'Standard cleaning service including dusting, vacuuming, mopping, and bathroom cleaning.',
                'price' => 75.00,
            ],
            [
                'title' => 'Deep Cleaning',
                'description' => 'Thorough cleaning including all basic services plus inside appliances, baseboards, and detailed scrubbing.',
                'price' => 150.00,
            ],
            [
                'title' => 'Move-In/Move-Out Cleaning',
                'description' => 'Complete cleaning for empty homes, including all surfaces, cabinets, and appliances.',
                'price' => 200.00,
            ],
            [
                'title' => 'Office Cleaning',
                'description' => 'Professional cleaning for office spaces including desks, common areas, and restrooms.',
                'price' => 100.00,
            ],
            [
                'title' => 'Window Cleaning',
                'description' => 'Interior and exterior window cleaning for residential or commercial properties.',
                'price' => 50.00,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
