<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ESignTemplate;
use App\Models\ESignTemplateField;

class ESignTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default KYC Form Template
        $template = ESignTemplate::create([
            'name' => 'Default KYC Form Template',
            'pdf_url' => 'storage/templates/kyc_template.pdf' // Placeholder template path
        ]);

        // Define coordinate mappings for this template
        $fields = [
            [
                'field_key' => 'full_name',
                'label' => 'Full Name',
                'type' => 'text',
                'page_num' => 1,
                'x_coordinate' => 150.0,
                'y_coordinate' => 500.0,
                'width' => 200,
                'height' => 20
            ],
            [
                'field_key' => 'pan_number',
                'label' => 'PAN Number',
                'type' => 'text',
                'page_num' => 1,
                'x_coordinate' => 150.0,
                'y_coordinate' => 450.0,
                'width' => 200,
                'height' => 20
            ],
            [
                'field_key' => 'signature',
                'label' => 'Customer Signature',
                'type' => 'signature',
                'page_num' => 1,
                'x_coordinate' => 400.0,
                'y_coordinate' => 150.0,
                'width' => 150,
                'height' => 50
            ]
        ];

        foreach ($fields as $field) {
            $template->fields()->create($field);
        }
    }
}
