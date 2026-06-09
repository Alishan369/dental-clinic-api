<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TreatmentType;
use Illuminate\Support\Str;

class TreatmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Cleaning & Polishing',
                'description' => 'Routine dental hygiene treatment to remove plaque and tartar.',
                'base_cost' => 1500.00
            ],
            [
                'name' => 'Composite Filling',
                'description' => 'Tooth-colored filling material used to repair decay or damage.',
                'base_cost' => 2000.00
            ],
            [
                'name' => 'Root Canal Treatment (RCT)',
                'description' => 'Therapy to save a badly damaged or infected tooth.',
                'base_cost' => 6000.00
            ],
            [
                'name' => 'Dental Crown (Porcelain)',
                'description' => 'Custom cap placed over a tooth to restore shape, size, and strength.',
                'base_cost' => 8000.00
            ],
            [
                'name' => 'Teeth Whitening',
                'description' => 'Professional bleaching treatment to brighten and whiten teeth.',
                'base_cost' => 5000.00
            ],
            [
                'name' => 'Tooth Extraction',
                'description' => 'Removal of a severely damaged, decayed, or wisdom tooth.',
                'base_cost' => 1200.00
            ],
        ];

        foreach ($types as $type) {
            TreatmentType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'id' => Str::uuid(),
                    'description' => $type['description'],
                    'base_cost' => $type['base_cost'],
                ]
            );
        }
    }
}
