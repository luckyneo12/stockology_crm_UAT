<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class PipelineStageAutomation extends Model
{
    protected $fillable = [
        'pipeline_id',
        'stage_id',
        'entity_type',
        'target_department_id',
        'target_user_id',
        'is_auto_task',
        'auto_task_name',
        'auto_task_priority',
        'auto_task_duration',
        'is_auto_reminder',
        'auto_reminder_title',
        'auto_reminder_duration',
        'created_by',
        'workspace_id',
    ];
    public static function run($entity, $stageId)
    {
        $entityType = ($entity instanceof \Workdo\Lead\Entities\Lead) ? 'lead' : 'deal';
        $automation = self::where('stage_id', $stageId)->where('entity_type', $entityType)->first();

        if ($automation) {
            // 1. Department Transfer
            if ($automation->target_department_id) {
                if (module_is_active('Hrm')) {
                    $dept = \Workdo\Hrm\Entities\Department::find($automation->target_department_id);
                    if ($dept && $dept->manager) {
                        $managerUserId = $dept->manager->user_id;
                        if ($entityType == 'lead') {
                            $entity->user_id = $managerUserId;
                            $entity->save();
                            \Workdo\Lead\Entities\UserLead::firstOrCreate([
                                'user_id' => $managerUserId,
                                'lead_id' => $entity->id,
                            ]);
                        } else {
                            \Workdo\Lead\Entities\UserDeal::firstOrCreate([
                                'user_id' => $managerUserId,
                                'deal_id' => $entity->id,
                            ]);
                        }
                    }
                }
            }

            // 2. Auto Task
            if ($automation->is_auto_task && !empty($automation->auto_task_name)) {
                $taskClass = ($entityType == 'lead') ? \Workdo\Lead\Entities\LeadTask::class : \Workdo\Lead\Entities\DealTask::class;
                $taskData = [
                    'name' => $automation->auto_task_name,
                    'priority' => !empty($automation->auto_task_priority) ? $automation->auto_task_priority : 'medium',
                    'date' => date('Y-m-d', strtotime('+' . ($automation->auto_task_duration ?? 0) . ' days')),
                    'status' => 'pending',
                    'created_by' => $automation->created_by,
                    'workspace_id' => $automation->workspace_id,
                    'user_id' => ($entityType == 'lead') ? $entity->user_id : \Auth::user()->id,
                ];
                if ($entityType == 'lead') {
                    $taskData['lead_id'] = $entity->id;
                } else {
                    $taskData['deal_id'] = $entity->id;
                }
                $taskClass::create($taskData);
            }

            // 3. Auto Reminder
            if ($automation->is_auto_reminder && !empty($automation->auto_reminder_title)) {
                \Workdo\Lead\Entities\Reminder::create([
                    'title' => $automation->auto_reminder_title,
                    'remind_at' => date('Y-m-d H:i:s', strtotime('+' . ($automation->auto_reminder_duration ?? 0) . ' days 09:00:00')), // Default to 9 AM
                    'user_id' => ($entityType == 'lead') ? $entity->user_id : \Auth::user()->id,
                    'remindable_id' => $entity->id,
                    'remindable_type' => get_class($entity),
                    'workspace_id' => $automation->workspace_id,
                    'created_by' => $automation->created_by,
                ]);
            }
        }
    }
}
