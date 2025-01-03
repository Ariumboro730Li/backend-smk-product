<?php

namespace Database\Seeders;

use App\Models\SmkElement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmkElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elements = [
            [
                'title' => 'Element 1',
                'element_properties' => json_encode([
                    'property_1' => 'value_1',
                    'property_2' => 'value_2',
                    'property_3' => 'value_3',
                ]),
                'is_active' => 1,
            ],
            [
                'title' => 'Element 2',
                'element_properties' => json_encode([
                    'property_1' => 'value_1',
                    'property_2' => 'value_2',
                ]),
                'is_active' => 1,
            ],
            [
                'title' => 'Element 3',
                'element_properties' => json_encode([
                    'property_1' => 'value_1',
                    'property_2' => 'value_2',
                    'property_3' => 'value_3',
                    'property_4' => 'value_4',
                ]),
                'is_active' => 0,
            ],
        ];

        foreach ($elements as $element) {
            SmkElement::create($element);
        }
    }
}
