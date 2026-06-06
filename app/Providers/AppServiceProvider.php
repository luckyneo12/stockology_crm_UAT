<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Module;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('module', function ($app) {
            return new Module();
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force the root URL and scheme globally to fix redirect issues
        if (!app()->runningInConsole() && config('app.url')) {

            // Enforce production mode for HTTPS and correct URL generation
            if (strpos(request()->root(), 'localhost') === false) {
                config(['app.env' => 'production']);
            }

            if (strpos(config('app.url'), 'https') === 0) {
                \URL::forceScheme('https');
            }
            \URL::forceRootUrl(config('app.url'));
        }

        // Target Automation Observers for Lead changes
        if (class_exists('\Workdo\Lead\Entities\Lead')) {
            \Workdo\Lead\Entities\Lead::saved(function ($lead) {
                if (class_exists('\App\Models\Target')) {
                    // Update for the current stage
                    $targets = \App\Models\Target::where('workspace', $lead->workspace_id)
                        ->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $lead->pipeline_id)
                        ->where('stage_id', $lead->stage_id)
                        ->get();
                    foreach ($targets as $target) {
                        $target->recalculateAchievedValue();
                    }
                    
                    // Update for the original stage if stage changed
                    $originalStageId = $lead->getOriginal('stage_id');
                    $originalPipelineId = $lead->getOriginal('pipeline_id');
                    if ($originalStageId && $originalStageId != $lead->stage_id) {
                        $oldTargets = \App\Models\Target::where('workspace', $lead->workspace_id)
                            ->where('target_type', 'lead_stage')
                            ->where('pipeline_id', $originalPipelineId ?? $lead->pipeline_id)
                            ->where('stage_id', $originalStageId)
                            ->get();
                        foreach ($oldTargets as $target) {
                            $target->recalculateAchievedValue();
                        }
                    }
                }
            });
            \Workdo\Lead\Entities\Lead::deleted(function ($lead) {
                if (class_exists('\App\Models\Target')) {
                    $targets = \App\Models\Target::where('workspace', $lead->workspace_id)
                        ->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $lead->pipeline_id)
                        ->where('stage_id', $lead->stage_id)
                        ->get();
                    foreach ($targets as $target) {
                        $target->recalculateAchievedValue();
                    }
                }
            });
        }

        if (class_exists('\Workdo\Lead\Entities\LeadCustomFieldValue')) {
            \Workdo\Lead\Entities\LeadCustomFieldValue::saved(function ($customFieldValue) {
                $lead = \Workdo\Lead\Entities\Lead::find($customFieldValue->lead_id);
                if ($lead && class_exists('\App\Models\Target')) {
                    $targets = \App\Models\Target::where('workspace', $lead->workspace_id)
                        ->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $lead->pipeline_id)
                        ->where('stage_id', $lead->stage_id)
                        ->get();
                    foreach ($targets as $target) {
                        $target->recalculateAchievedValue();
                    }
                }
            });
            \Workdo\Lead\Entities\LeadCustomFieldValue::deleted(function ($customFieldValue) {
                $lead = \Workdo\Lead\Entities\Lead::find($customFieldValue->lead_id);
                if ($lead && class_exists('\App\Models\Target')) {
                    $targets = \App\Models\Target::where('workspace', $lead->workspace_id)
                        ->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $lead->pipeline_id)
                        ->where('stage_id', $lead->stage_id)
                        ->get();
                    foreach ($targets as $target) {
                        $target->recalculateAchievedValue();
                    }
                }
            });
        }
    }
}
