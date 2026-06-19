<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadSection extends Model
{
    protected $fillable = [
        'name',
        'order',
        'columns',
        'workspace_id',
        'is_system',
        'layout_type',
        'api_url',
        'api_method',
        'api_trigger_stage_id',
        'api_response_mapping',
        'pipeline_id',
        'visible_stages'
    ];

    protected $casts = [
        'visible_stages' => 'array'
    ];


    public static function ensurePipelineLayout($pipelineId, $workspaceId)
    {
        if (empty($pipelineId)) {
            return;
        }

        // Get the system sections to be deleted
        $systemSections = self::where('workspace_id', $workspaceId)
            ->where('pipeline_id', $pipelineId)
            ->where('is_system', 1)
            ->get();

        foreach ($systemSections as $section) {
            // Move any custom fields in this system section to Unassigned (null section_id)
            LeadCustomField::where('section_id', $section->id)
                ->where(function($q) {
                    $q->whereNull('is_system')->orWhere('is_system', 0);
                })
                ->update(['section_id' => null]);

            // Delete the section
            $section->delete();
        }

        // Delete all system-defined custom fields for this pipeline
        LeadCustomField::where('workspace_id', $workspaceId)
            ->where('pipeline_id', $pipelineId)
            ->where('is_system', 1)
            ->delete();
    }

    public function fields()
    {
        return $this->hasMany(LeadCustomField::class, 'section_id')->orderBy('order');
    }
}
