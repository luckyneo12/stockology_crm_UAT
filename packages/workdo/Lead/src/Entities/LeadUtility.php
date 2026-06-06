<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;

class LeadUtility extends Model
{
    use HasFactory;

    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $client_permissions = [
            'crm manage',
            'deal manage',
            'deal show',
            'deal task create',
            'deal task edit',
            'deal task delete',
            'deal task show',
            'deal call create',
            'deal call edit',
            'deal call delete',
            'deal email create',
        ];


        $staff_permissions = [
            'crm manage',
            'lead manage',
            'lead show',
            'deal manage',
            'deal show',
            'deal task show',
        ];

        if ($role_id == Null) {
            // client
            $roles_c = Role::where('name', 'client')->get();
            foreach ($roles_c as $role) {
                foreach ($client_permissions as $permission_c) {
                    $permission = Permission::where('name', $permission_c)->first();
                    if (!$role->hasPermission($permission_c)) {
                        $role->givePermission($permission);
                    }

                }
            }

            // staff
            $roles_s = Role::where('name', 'staff')->get();

            foreach ($roles_s as $role) {
                foreach ($staff_permissions as $permission_s) {
                    $permission = Permission::where('name', $permission_s)->first();
                    if (!$role->hasPermission($permission_s)) {
                        $role->givePermission($permission);
                    }
                }
            }

        } else {
            if ($rolename == 'client') {
                $roles_c = Role::where('name', 'client')->where('id', $role_id)->first();
                foreach ($client_permissions as $permission_c) {
                    $permission = Permission::where('name', $permission_c)->first();
                    if (!$roles_c->hasPermission($permission_c)) {
                        $roles_c->givePermission($permission);
                    }
                }
            } elseif ($rolename == 'staff') {
                $roles_s = Role::where('name', 'staff')->where('id', $role_id)->first();
                foreach ($staff_permissions as $permission_s) {
                    $permission = Permission::where('name', $permission_s)->first();
                    if (!$roles_s->hasPermission($permission_s)) {
                        $roles_s->givePermission($permission);
                    }
                }
            }
        }

    }

    public static function defaultdata($company_id = null, $workspace_id = null)
    {
        $pipelines = [
            'Sales',
        ];

        $lead_stages = [
            "Draft",
            "Sent",
            "Open",
            "Revised",
            "Declined",
            "Accepted",
        ];
        $stages = [
            'Initial Contact',
            'Qualification',
            'Meeting',
            'Proposal',
            'Close',
        ];

        if ($company_id == Null) {
            $companys = User::where('type', 'company')->get();
            foreach ($companys as $company) {
                $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
                foreach ($WorkSpaces as $WorkSpace) {
                    foreach ($pipelines as $pipeline) {
                        $Pipeline = Pipeline::where('name', $pipeline)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();

                        if ($Pipeline == null) {
                            $Pipeline = new Pipeline();
                            $Pipeline->name = $pipeline;
                            $Pipeline->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                            $Pipeline->created_by = !empty($company->id) ? $company->id : 2;
                            $Pipeline->save();
                        }
                    }
                    foreach ($lead_stages as $lead_stage) {
                        $leadstage = LeadStage::where('name', $lead_stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();

                        if ($leadstage == null) {
                            $leadstage = new LeadStage();
                            $leadstage->name = $lead_stage;
                            $leadstage->pipeline_id = $Pipeline->id;
                            $leadstage->order = 0;
                            $leadstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                            $leadstage->created_by = !empty($company->id) ? $company->id : 2;
                            $leadstage->save();
                        }
                    }
                    foreach ($stages as $stage) {
                        $dealstage = DealStage::where('name', $stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();

                        if ($dealstage == null) {
                            $dealstage = new DealStage();
                            $dealstage->name = $stage;
                            $dealstage->pipeline_id = $Pipeline->id;
                            $dealstage->order = 0;
                            $dealstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                            $dealstage->created_by = !empty($company->id) ? $company->id : 2;
                            $dealstage->save();
                        }

                    }
                }
            }
        } elseif ($workspace_id == Null) {
            $company = User::where('type', 'company')->where('id', $company_id)->first();
            $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
            foreach ($WorkSpaces as $WorkSpace) {
                foreach ($pipelines as $pipeline) {
                    $Pipeline = Pipeline::where('name', $pipeline)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();

                    if ($Pipeline == null) {
                        $Pipeline = new Pipeline();
                        $Pipeline->name = $pipeline;
                        $Pipeline->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                        $Pipeline->created_by = !empty($company->id) ? $company->id : 2;
                        $Pipeline->save();
                    }
                }
                foreach ($lead_stages as $lead_stage) {
                    $leadstage = LeadStage::where('name', $lead_stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();
                    if ($leadstage == null) {
                        $leadstage = new LeadStage();
                        $leadstage->name = $lead_stage;
                        $leadstage->pipeline_id = $Pipeline->id;
                        $leadstage->order = 0;
                        $leadstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                        $leadstage->created_by = !empty($company->id) ? $company->id : 2;
                        $leadstage->save();
                    }

                }
                foreach ($stages as $stage) {
                    $dealstage = DealStage::where('name', $stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();
                    if ($dealstage == null) {
                        $dealstage = new DealStage();
                        $dealstage->name = $stage;
                        $dealstage->pipeline_id = $Pipeline->id;
                        $dealstage->order = 0;
                        $dealstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                        $dealstage->created_by = !empty($company->id) ? $company->id : 2;
                        $dealstage->save();
                    }

                }
            }
        } else {
            $company = User::where('type', 'company')->where('id', $company_id)->first();
            $WorkSpace = WorkSpace::where('created_by', $company->id)->where('id', $workspace_id)->first();
            foreach ($pipelines as $pipeline) {
                $Pipeline = Pipeline::where('name', $pipeline)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();
                if ($Pipeline == null) {
                    $Pipeline = new Pipeline();
                    $Pipeline->name = $pipeline;
                    $Pipeline->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                    $Pipeline->created_by = !empty($company->id) ? $company->id : 2;
                    $Pipeline->save();
                }
            }
            foreach ($lead_stages as $lead_stage) {
                $leadstage = LeadStage::where('name', $lead_stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();
                if ($leadstage == null) {
                    $leadstage = new LeadStage();
                    $leadstage->name = $lead_stage;
                    $leadstage->pipeline_id = $Pipeline->id;
                    $leadstage->order = 0;
                    $leadstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                    $leadstage->created_by = !empty($company->id) ? $company->id : 2;
                    $leadstage->save();
                }
            }
            foreach ($stages as $stage) {
                $dealstage = DealStage::where('name', $stage)->where('workspace_id', $WorkSpace->id)->where('created_by', $company->id)->first();
                if ($dealstage == null) {
                    $dealstage = new DealStage();
                    $dealstage->name = $stage;
                    $dealstage->pipeline_id = $Pipeline->id;
                    $dealstage->order = 0;
                    $dealstage->workspace_id = !empty($WorkSpace->id) ? $WorkSpace->id : 0;
                    $dealstage->created_by = !empty($company->id) ? $company->id : 2;
                    $dealstage->save();
                }

            }
        }
    }

    public static function canSeeField($leadId, $fieldName)
    {
        // Fetch rules for this field
        $rules = \Workdo\Lead\Entities\LeadFieldVisibility::where('field_name', $fieldName)
            ->where('workspace_id', getActiveWorkSpace())
            ->get();

        // If no rules, visible by default
        if ($rules->isEmpty()) {
            return true;
        }

        $lead = Lead::find($leadId);
        if (!$lead)
            return false;

        $user = \Illuminate\Support\Facades\Auth::user();

        // If user has specific "show protected" permission, allow
        if ($user->isAbleTo('lead show protected data'))
            return true;

        foreach ($rules as $rule) {
            // Check Stage Restriction
            if ($rule->pipeline_id && $rule->pipeline_id != $lead->pipeline_id)
                continue;
            if ($rule->stage_id && $rule->stage_id != $lead->stage_id)
                continue;

            // If rule matches context and type is 'hide', it is hidden
            if ($rule->encryption_type == 'hide') {
                // Check if user has specific permission to override?
                // For now, strict: if rule says hide, it's hidden unless you are super admin (handled by isAbleTo usually?)
                // Let's assume the 'lead show protected data' check above handles exemptions.
                return false;
            }
        }

        return true;
    }

    public static $visibilityRules = null;

    public static function getFieldDisplay($lead, $fieldName, $originalValue, $stripHtml = false)
    {
        // 1. Check Visibility Rules (Static Cache)
        if (self::$visibilityRules === null) {
            self::$visibilityRules = \Workdo\Lead\Entities\LeadFieldVisibility::where('workspace_id', getActiveWorkSpace())
                ->get()
                ->groupBy('field_name');
        }

        $rules = self::$visibilityRules->get($fieldName) ?? collect();

        $isMasked = false;

        $leadInstance = $lead instanceof \Workdo\Lead\Entities\Lead ? $lead : Lead::find($lead);
        if (!$leadInstance)
            return $originalValue;

        foreach ($rules as $rule) {
            // Context Check
            if ($rule->pipeline_id && $rule->pipeline_id != $leadInstance->pipeline_id)
                continue;

            if ($rule->stage_id) {
                $stageIds = explode(',', $rule->stage_id);
                if (!in_array($leadInstance->stage_id, $stageIds)) {
                    continue;
                }
            }

            if ($rule->encryption_type == 'hide') {
                return ''; // Completely hidden
            }

            if ($rule->encryption_type == 'mask') {
                $isMasked = true;
                $maskingType = $rule->masking_type ?? 'partial'; // Default to partial
                break;
            }
        }

        if ($isMasked && $originalValue) {
            $length = strlen($originalValue);

            // Check masking type
            if ($maskingType == 'full') {
                // Full Mask - all asterisks
                $masked = str_repeat('*', $length);
            } else {
                // Partial Mask - show last 4 characters
                $visibleChars = 4;
                if ($length > $visibleChars) {
                    $masked = str_repeat('*', $length - $visibleChars) . substr($originalValue, -$visibleChars);
                } else {
                    $masked = str_repeat('*', $length);
                }
            }

            if ($stripHtml) {
                return $masked;
            }

            // Return masked value with eye icon for reveal
            $revealUrl = route('lead.reveal.field', ['lead_id' => $leadInstance->id, 'field_name' => $fieldName]);
            $uniqueId = 'field-' . $fieldName . '-' . $leadInstance->id;

            return '<span class="masked-value" id="' . $uniqueId . '">' . $masked . '</span> ' .
                '<a href="#" class="reveal-link" data-url="' . $revealUrl . '" data-target="#' . $uniqueId . '" title="' . __('Reveal') . '">' .
                '<i class="ti ti-eye"></i></a>';
        }

        return $originalValue ?? '-';
    }
}
