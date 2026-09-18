<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\DocumentTypeField;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documentType = DocumentType::updateOrCreate(
            [
                'name' => 'Document Fee Request Form',
            ],
            [
                'description' => 'Standard form for requesting official documents from the USSC',
                'is_active' => true,
            ]
        );

        $fields = [
            [
                'field_name' => 'student_id',
                'field_label' => 'Student ID Number',
                'field_type' => 'text',
                'is_required' => true,
                'validation_rules' => 'max:255',
                'display_order' => 1,
            ],
            [
                'field_name' => 'department',
                'field_label' => 'College / Department',
                'field_type' => 'text',
                'is_required' => true,
                'validation_rules' => 'max:255',
                'display_order' => 2,
            ],
            [
                'field_name' => 'year_section',
                'field_label' => 'Year & Section',
                'field_type' => 'text',
                'is_required' => true,
                'validation_rules' => 'max:255',
                'display_order' => 3,
            ],
            [
                'field_name' => 'purpose',
                'field_label' => 'Purpose of Request',
                'field_type' => 'textarea',
                'is_required' => true,
                'validation_rules' => 'max:500',
                'display_order' => 4,
            ],
        ];

        foreach ($fields as $field) {
            DocumentTypeField::updateOrCreate(
                [
                    'document_type_id' => $documentType->id,
                    'field_name' => $field['field_name'],
                ],
                $field
            );
        }
    }
}