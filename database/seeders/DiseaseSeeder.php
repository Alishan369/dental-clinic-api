<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Disease;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = [
            'Cavities',
            'Root Canal',
            'Gum Infection',
            'Tooth Extraction',
            'Braces Treatment',
            'Teeth Whitening',
            'Wisdom Tooth Pain',
            'Tooth Sensitivity',
            'Broken Tooth',
            'Dental Abscess'
        ];

        foreach ($diseases as $name) {
            Disease::updateOrCreate(
                ['name' => $name],
                [
                    'id' => Str::uuid(),
                    'is_active' => true
                ]
            );
        }
    }
}
