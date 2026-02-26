<?php

namespace Workdo\Lead\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\Lead\Entities\LeadSection;
use Workdo\Lead\Entities\LeadCustomField;
use Illuminate\Support\Facades\DB;

class LeadLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Default "General" Section if it doesn't exist
        // Note: In a real multi-tenant app, we might need to loop through workspaces.
        // For now, let's assume active workspace or just create a template one with workspace_id = 0 or 1.
        // Current user seems to be in workspace 1 (from previous logs). Let's target workspace 1.
        $workspaceId = 1; 

        $generalSection = LeadSection::where('workspace_id', $workspaceId)->where('name', 'General')->first();
        if (!$generalSection) {
            $generalSection = LeadSection::create([
                'name' => 'General',
                'order' => 0,
                'columns' => 3,
                'workspace_id' => $workspaceId,
                'is_system' => 1
            ]);
        }

        // 2. Define standard System Fields to be "managed" by the builder
        $systemFields = [
            [
                'name' => 'Email',
                'type' => 'text', // It's a display type really, but 'text' is fine for generic
                'system_field_id' => 'email',
                'width' => 1,
                'is_required' => 0,
            ],
            [
                'name' => 'Phone',
                'type' => 'text',
                'system_field_id' => 'phone',
                'width' => 1,
                'is_required' => 0,
            ],
            [
                'name' => 'Pipeline',
                'type' => 'text',
                'system_field_id' => 'pipeline',
                'width' => 1,
                'is_required' => 1,
            ],
            [
                'name' => 'Stage',
                'type' => 'text',
                'system_field_id' => 'stage',
                'width' => 1,
                'is_required' => 1,
            ],
            [
                'name' => 'Created',
                'type' => 'date',
                'system_field_id' => 'created_at',
                'width' => 1,
                'is_required' => 0,
            ],
             [
                'name' => 'Percentage', // From the UI 55%
                'type' => 'number', 
                'system_field_id' => 'percentage', 
                 'width' => 1,
                 'is_required' => 0,
            ],
             [
                'name' => 'PAN Number',
                'type' => 'text',
                'system_field_id' => 'pan_number',
                'width' => 1,
                 'is_required' => 0,
            ],
             [
                'name' => 'Aadhar Number',
                'type' => 'text',
                'system_field_id' => 'aadhar_number',
                'width' => 1,
                 'is_required' => 0,
            ],
        ];

        foreach ($systemFields as $index => $fieldData) {
            $field = LeadCustomField::where('workspace_id', $workspaceId)
                                    ->where('system_field_id', $fieldData['system_field_id'])
                                    ->first();
            
            if (!$field) {
                LeadCustomField::create([
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'],
                    'system_field_id' => $fieldData['system_field_id'],
                    'section_id' => $generalSection->id,
                    'order' => $index,
                    'width' => $fieldData['width'],
                    'is_required' => $fieldData['is_required'],
                    'is_system' => 1,
                    'workspace_id' => $workspaceId,
                    'created_by' => 1 // Admin
                ]);
            }
        }
        
        // 3. Migrate any existing "orphaned" custom fields to the General section
        LeadCustomField::where('workspace_id', $workspaceId)
            ->whereNull('section_id')
            ->update(['section_id' => $generalSection->id]);
    }
}
