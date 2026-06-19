<?php

namespace Workdo\Lead\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadUtility;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\LeadStage;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Workdo\Hrm\Entities\Employee;
use Illuminate\Support\Facades\DB;

class LeadDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $user = \Auth::user();
        $hasBulkPermission = $user->isAbleTo('lead edit') || $user->isAbleTo('lead delete');
        
        $rowColumn = ['batch', 'name', 'subject', 'email', 'phone', 'stage_id', 'tasks', 'reminders', 'created_at', 'updated_at', 'user_id', 'team'];
        $isExport = in_array(request('action'), ['excel', 'csv']);

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('batch', function (Lead $lead) use ($hasBulkPermission) {
                if (!$hasBulkPermission) return '';
                return '<div class="form-check custom-checkbox">
                            <input type="checkbox" class="form-check-input lead-checkbox" id="lead_checkbox_' . $lead->id . '" value="' . $lead->id . '">
                            <label class="form-check-label" for="lead_checkbox_' . $lead->id . '"></label>
                        </div>';
            })
            ->editColumn('name', function (Lead $lead) use ($isExport) {
                $name = LeadUtility::getFieldDisplay($lead, 'name', $lead->name, $isExport);
                if ($isExport) {
                    return $name;
                }
                $user = \Auth::user();
                if ($user->isAbleTo('lead show') && $lead->is_active) {
                    if (strpos($name, 'reveal-link') === false) {
                        return '<a href="' . route('leads.show', $lead->id) . '" class="lead-name-link">' . e($name) . '</a>';
                    }
                }
                return $name;
            })
            ->editColumn('subject', function (Lead $lead) use ($isExport) {
                return LeadUtility::getFieldDisplay($lead, 'subject', $lead->subject, $isExport);
            })
            ->editColumn('email', function (Lead $lead) use ($isExport) {
                return LeadUtility::getFieldDisplay($lead, 'email', $lead->email, $isExport);
            })
            ->editColumn('phone', function (Lead $lead) use ($isExport) {
                $phone = LeadUtility::getFieldDisplay($lead, 'phone', $lead->phone, $isExport);
                if ($isExport) {
                    return $phone;
                }
                if (!empty($lead->phone)) {
                    return $phone . ' <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="' . $lead->phone . '" data-bs-toggle="tooltip" title="' . __('Call') . '"><i class="ti ti-phone-call"></i></a>';
                }
                return $phone;
            })
            ->editColumn('stage_id', function (Lead $lead) use ($isExport) {
                if ($isExport) {
                    return $lead->stage ? $lead->stage->name : '-';
                }
                
                static $stagesCache = null;
                if ($stagesCache === null) {
                    $stagesCache = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $lead->pipeline_id)->orderBy('order')->get();
                }
                
                $totalStages = $stagesCache->count();
                $currentIdx = 0;
                foreach($stagesCache as $idx => $s) {
                    if ($s->id == $lead->stage_id) {
                        $currentIdx = $idx + 1;
                        break;
                    }
                }
                $pct = $totalStages > 0 ? round(($currentIdx / $totalStages) * 100) : 0;
                
                $stageColors = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
                $color = $stageColors[($currentIdx > 0 ? $currentIdx - 1 : 0) % count($stageColors)];
                
                $stageName = $lead->stage ? $lead->stage->name : '-';
                return '<div class="d-flex align-items-center flex-column align-items-start" style="min-width: 120px;">
                            <div class="mb-1 text-xs fw-bold text-dark text-truncate" style="max-width: 150px;" title="' . $stageName . '">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background-color: ' . $color . ';"></span>' . $stageName . '
                            </div>
                            <div class="progress w-100 bg-light" style="height: 5px; border-radius: 3px;">
                                <div class="progress-bar" role="progressbar" style="width: ' . $pct . '%; background-color: ' . $color . ';"></div>
                            </div>
                        </div>';
            })
            ->filterColumn('stage_id', function ($query, $keyword) {
                $query->whereHas(
                    'stage',
                    function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    }
                );
            })
            ->editColumn('tasks', function (Lead $lead) {
                $totalTasksCount = $lead->tasks_count ?? 0;
                $completeTasksCount = $lead->complete_tasks_count ?? 0;
                return $totalTasksCount . '/' . $completeTasksCount;
            })
            ->addColumn('reminders', function (Lead $lead) use ($isExport) {
                $todayRemindersCount = $lead->today_reminders_count ?? 0;
                $totalRemindersCount = $lead->reminders_count ?? 0;

                if ($isExport) {
                    return $todayRemindersCount . '/' . $totalRemindersCount;
                }

                $html = '<div class="d-flex align-items-center" data-bs-toggle="tooltip" title="' . __('Reminders (Today/Total)') . '">';
                $html .= '<i class="ti ti-bell me-1 ' . ($todayRemindersCount > 0 ? 'text-danger' : 'text-primary') . '"></i>';
                $html .= '<span>' . $todayRemindersCount . '/' . $totalRemindersCount . '</span>';
                $html .= '</div>';

                return $html;
            })
            ->editColumn('created_at', function (Lead $lead) use ($isExport) {
                if ($isExport) {
                    return company_date_formate($lead->created_at);
                }
                return '<span class="fw-bold" title="' . __('Created Date') . '"><i class="ti ti-calendar-plus me-1 text-primary"></i>' . company_date_formate($lead->created_at) . '</span>';
            })
            ->editColumn('updated_at', function (Lead $lead) use ($isExport) {
                if ($isExport) {
                    return company_date_formate($lead->updated_at);
                }
                return '<span class="fw-bold" title="' . __('Modified Date') . '"><i class="ti ti-calendar-event me-1 text-primary"></i>' . company_date_formate($lead->updated_at) . '</span>';
            })
            ->editColumn('follow_up_date', function (Lead $lead) {
                return $lead->follow_up_date ? company_date_formate($lead->follow_up_date) : '-';
            })
            ->editColumn('user_id', function (Lead $lead) use ($isExport) {
                $user = $lead->owner ?? $lead->users->first();
                if ($isExport) {
                    return $user ? $user->name : '-';
                }
                $html = '-';
                if ($user) {
                    $html = '<span class="badge bg-primary p-2 px-3 rounded text-white" style="font-size: 0.8rem; font-weight: 500;">
                            <i class="ti ti-user me-1"></i>' . $user->name . '
                         </span>';
                }
                return $html;
            })
            ->editColumn('created_by', function (Lead $lead) {
                return $lead->createdBy ? $lead->createdBy->name : '-';
            })
            ->editColumn('updated_by', function (Lead $lead) {
                return $lead->updatedBy ? $lead->updatedBy->name : '-';
            })
            ->addColumn('team', function (Lead $lead) {
                if (module_is_active('Hrm') && $lead->user_id) {
                    if ($lead->employee && $lead->employee->department) {
                        return $lead->employee->department->name;
                    }
                }
                return '-';
            });
        try {
            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
            foreach ($customFields as $field) {
                $dataTable->addColumn('custom_' . $field->id, function (Lead $lead) use ($field, $isExport) {
                    try {
                        $valObj = $lead->customFieldValues->firstWhere('field_id', $field->id);
                        $val = $valObj ? $valObj->value : '-';
                        if ($isExport) {
                            return $val;
                        }
                        return '<span>' . e($val) . '</span>';
                    } catch (\Exception $ex) {
                        return '-';
                    }
                });
                $rowColumn[] = 'custom_' . $field->id;
            }
        } catch (\Exception $e) {
            \Log::error('LeadDataTable dataTable customFields error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        if (
            !\Auth::user()->hasRole('client') &&
            (\Laratrust::hasPermission('lead show') ||
                \Laratrust::hasPermission('product&lead edit') ||
                \Laratrust::hasPermission('lead delete'))
        ) {
            $dataTable->addColumn('action', function (Lead $lead) {
                return view('lead::leads.lead_action', compact('lead'));
            });
            $rowColumn[] = 'action';
        }

        if ($isExport) {
            return $dataTable;
        }

        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Lead $model)
    {
        $request = request();
        
        // Prevent DataTables from fetching infinite rows (which causes OOM on 173k records)
        // The user's browser had permanently saved length=-1 (Show All) in DataTables state
        if ($request->has('length') && $request->length == -1 && !in_array($request->action, ['excel', 'csv'])) {
            $request->merge(['length' => 500]); // Hard cap to prevent timeout with withCount subqueries
        }
        $user = \Auth::user();
        $pipeline_id = $request->default_pipeline_id ?? $user->default_pipeline;
        if (!$pipeline_id) {
            $firstPipeline = Pipeline::where('workspace_id', getActiveWorkSpace())->first();
            $pipeline_id = $firstPipeline ? $firstPipeline->id : 0;
        }

        $isExport = in_array($request->action, ['excel', 'csv', 'print']);
        
        $accessibleUserIds = $user->getAccessibleUserIds();

        if ($request->action == 'get_ids') {
            $query = $model->where('leads.pipeline_id', '=', $pipeline_id)
                ->where('leads.workspace_id', '=', getActiveWorkSpace());
        } elseif ($isExport) {
            ini_set('memory_limit', '4096M');
            set_time_limit(0);
            $query = $model->where('leads.pipeline_id', '=', $pipeline_id)
                ->where('leads.workspace_id', '=', getActiveWorkSpace())
                ->with(['users', 'stage', 'employee.department', 'owner', 'createdBy', 'updatedBy', 'customFieldValues'])
                ->withCount([
                    'tasks',
                    'complete_tasks',
                    'reminders' => function ($q) use ($accessibleUserIds) {
                        $q->whereIn('user_id', $accessibleUserIds);
                    },
                    'reminders as today_reminders_count' => function ($q) use ($accessibleUserIds) {
                        $q->whereIn('user_id', $accessibleUserIds)
                          ->whereDate('remind_at', date('Y-m-d'));
                    }
                ]);
        } else {
            ini_set('memory_limit', '1024M'); // Give enough headroom for large table renders
            $query = $model->where('leads.pipeline_id', '=', $pipeline_id)
                ->where('leads.workspace_id', '=', getActiveWorkSpace())
                ->with(['users', 'stage', 'employee.department', 'owner', 'createdBy', 'updatedBy', 'customFieldValues'])
                ->withCount([
                    'tasks',
                    'complete_tasks',
                    'reminders' => function ($q) use ($accessibleUserIds) {
                        $q->whereIn('user_id', $accessibleUserIds);
                    },
                    'reminders as today_reminders_count' => function ($q) use ($accessibleUserIds) {
                        $q->whereIn('user_id', $accessibleUserIds)
                          ->whereDate('remind_at', date('Y-m-d'));
                    }
                ]);
        }

        if ($request->has('export_selected_ids') && !empty($request->export_selected_ids) && in_array($request->action, ['excel', 'csv', 'print'])) {
            $ids = explode(',', $request->export_selected_ids);
            $query->whereIn('leads.id', $ids);
        }


        // Apply visibility restrictions based on department/user access
        // Visibility settings ALWAYS apply (no permission override)
        if ($user->type != 'company' && $user->visibility_level != 'all') {
            $accessibleUserIds = $user->getAccessibleUserIds();
            $query->where(function ($q) use ($accessibleUserIds) {
                $q->whereIn('leads.user_id', $accessibleUserIds)
                    ->orWhereExists(function ($subQ) use ($accessibleUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $accessibleUserIds);
                    });
            });
        }

        // Apply Stage-based visibility (Restrict leads from hidden/restricted stages)
        // If user is not 'company', they only see leads from stages where they have 'can_view' permission
        // Additionally, if they don't have 'can_edit' permission, hide those leads by default unless they filter by those stages
        if ($user->type != 'company') {
            $hiddenStageIds = [];
            $allStagesInPipeline = LeadStage::where('pipeline_id', $pipeline_id)->where('workspace_id', getActiveWorkSpace())->get();
            
            $filteredStages = [];
            if ($request->has('stage_id') && !empty($request->stage_id)) {
                $filteredStages = (array) $request->stage_id;
            }

            foreach ($allStagesInPipeline as $s) {
                // If can_view is false, always hide the stage (cannot see it at all)
                if (!$s->permissions($user)->can_view) {
                    $hiddenStageIds[] = $s->id;
                    continue;
                }
                
                // If can_edit is false:
                // Hide it by default, UNLESS the user has explicitly selected it in the filter dropdown.
                if (!$s->permissions($user)->can_edit) {
                    if (!in_array($s->id, $filteredStages)) {
                        $hiddenStageIds[] = $s->id;
                    }
                }
            }
            if (!empty($hiddenStageIds)) {
                $query->whereNotIn('leads.stage_id', $hiddenStageIds);
            }
        }

        // Apply Custom Filters
        if ($request->has('responsible_person') && !empty($request->responsible_person)) {
            $respIds = (array) $request->responsible_person;
            $query->where(function ($q) use ($respIds) {
                $q->whereIn('leads.user_id', $respIds)
                    ->orWhereExists(function ($subQ) use ($respIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $respIds);
                    });
            });
        }

        if ($request->has('stage_id') && !empty($request->stage_id)) {
            $query->whereIn('leads.stage_id', (array) $request->stage_id);
        }

        if ($request->has('source_id') && !empty($request->source_id)) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->source_id as $source) {
                    $q->orWhereRaw('FIND_IN_SET(?, leads.sources)', [$source]);
                }
            });
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('leads.created_at', '>=', $request->start_date . ' 00:00:00');
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('leads.created_at', '<=', $request->end_date . ' 23:59:59');
        }

        if ($request->has('modified_start_date') && !empty($request->modified_start_date)) {
            $query->where('leads.updated_at', '>=', $request->modified_start_date . ' 00:00:00');
        }

        if ($request->has('modified_end_date') && !empty($request->modified_end_date)) {
            $query->where('leads.updated_at', '<=', $request->modified_end_date . ' 23:59:59');
        }

        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->whereIn('leads.created_by', (array) $request->created_by);
        }

        if ($request->has('modified_by') && !empty($request->modified_by)) {
            $query->whereIn('leads.updated_by', (array) $request->modified_by);
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $departmentIds = (array) $request->department_id;
            
            // Also fetch child teams of these departments
            $childTeamIds = \Workdo\Hrm\Entities\Department::whereIn('parent_id', $departmentIds)
                ->where('type', 'team')
                ->where('workspace', getActiveWorkSpace())
                ->pluck('id')
                ->toArray();
            
            $allDeptAndTeamIds = array_merge($departmentIds, $childTeamIds);

            $deptUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptAndTeamIds)
                ->where('workspace', getActiveWorkSpace())
                ->pluck('user_id')
                ->toArray();

            $query->where(function ($q) use ($deptUserIds) {
                $q->whereIn('leads.user_id', $deptUserIds)
                    ->orWhereExists(function ($subQ) use ($deptUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $deptUserIds);
                    });
            });
        }

        if (($request->has('designation_id') && !empty($request->designation_id)) || ($request->has('team_id') && !empty($request->team_id))) {
            $designationIds = (array) ($request->designation_id ?? $request->team_id);
            $desigUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $designationIds)
                ->where('workspace', getActiveWorkSpace())
                ->pluck('user_id')
                ->toArray();

            $query->where(function ($q) use ($desigUserIds) {
                $q->whereIn('leads.user_id', $desigUserIds)
                    ->orWhereExists(function ($subQ) use ($desigUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $desigUserIds);
                    });
            });
        }

        // Duplicates Filter (Optimized)
        if ($request->has('duplicates') && $request->duplicates == 1) {
            $wsId = getActiveWorkSpace();
            $query->where(function ($q) use ($wsId) {
                // Combine duplicate checks into more efficient subqueries
                $q->whereRaw("leads.email IN (SELECT email FROM (SELECT email FROM leads WHERE workspace_id = ? AND email IS NOT NULL AND email != '' GROUP BY email HAVING COUNT(email) > 1) as temp_email)", [$wsId])
                    ->orWhereRaw("leads.phone IN (SELECT phone FROM (SELECT phone FROM leads WHERE workspace_id = ? AND phone IS NOT NULL AND phone != '' GROUP BY phone HAVING COUNT(phone) > 1) as temp_phone)", [$wsId])
                    ->orWhereRaw("leads.name IN (SELECT name FROM (SELECT name FROM leads WHERE workspace_id = ? AND name IS NOT NULL AND name != '' GROUP BY name HAVING COUNT(name) > 1) as temp_name)", [$wsId]);
            });
        }


        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            if (is_array($search)) {
                $search = isset($search['value']) ? $search['value'] : null;
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leads.name', 'like', "%$search%")
                        ->orWhere('leads.subject', 'like', "%$search%");
                });
            }
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('leads-table')
            ->columns($this->getColumns())
            ->ajax([
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token()
                ],
                'data' => 'function(d) {
                    try {
                        // Ensure selectedLeads is properly initialized
                        if (typeof window.selectedLeads === "undefined") {
                            window.selectedLeads = [];
                        }
                        
                        if (window.selectedLeads && window.selectedLeads.length > 0) {
                            d.export_selected_ids = window.selectedLeads.join(",");
                        }
                        
                        // Pass all custom filters from URL with proper array handling
                        var urlParams = new URL(window.location.href).searchParams;
                        
                        // Initialize arrays for multi-select filters
                        var arrayKeys = ["responsible_person", "stage_id", "source_id", "created_by", "modified_by", "department_id", "designation_id", "team_id"];
                        
                        arrayKeys.forEach(function(key) {
                            d[key] = [];
                        });
                        
                        urlParams.forEach(function(value, key) {
                            if (key.endsWith("[]")) {
                                var cleanKey = key.replace("[]", "");
                                if (arrayKeys.includes(cleanKey)) {
                                    if (!d[cleanKey].includes(value)) {
                                        d[cleanKey].push(value);
                                    }
                                }
                            } else if (!arrayKeys.includes(key) && key !== "default_pipeline_id") {
                                // Skip default_pipeline_id from URL — managed by server-resolved hidden field below
                                d[key] = value;
                            }
                        });
                        
                        // Ensure arrays are properly formatted for server
                        arrayKeys.forEach(function(key) {
                            if (d[key] && d[key].length === 0) {
                                delete d[key];
                            }
                        });

                        // Set pipeline LAST so it always overrides any stale URL param
                        var pipeline = $("#list_pipeline_id").val() || $("select[name=default_pipeline_id]").val();
                        if (pipeline) d.default_pipeline_id = pipeline;
                    } catch (error) {
                        console.error(\'DataTable AJAX Error:\', error);
                        // Return empty data on error to prevent further issues
                        return [];
                    }
                }',
            ])
            ->orderBy(1) // Sort by the first data column, not checkboxes
            ->pageLength(10)
            ->lengthMenu([10, 25, 50, 100, 500])
            ->dom('rtip')
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_",
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                
                // CRITICAL: Reset any saved length=-1 (Show All) to safe default
                // This prevents the browser from permanently requesting all 173k records
                var currentLen = table.api().page.len();
                if (currentLen <= 0 || currentLen > 500) {
                    table.api().page.len(10).draw();
                }
                
                // Set initial value of our custom selector
                $("#entries_per_page").val(table.api().page.len());

                // Dynamically show/hide the Modified Date column based on URL parameters on page load
                var urlParams = new URL(window.location.href).searchParams;
                var hasModFilter = urlParams.has("modified_start_date") || urlParams.has("modified_end_date");
                var api = table.api();
                var columns = api.settings()[0].aoColumns;
                var modColIndex = -1;
                columns.forEach(function(col, idx) {
                    if (col.name === "updated_at" || col.data === "updated_at") {
                        modColIndex = idx;
                    }
                });
                if (modColIndex !== -1) {
                    api.column(modColIndex).visible(hasModFilter);
                }

                $("body").on("change", "#change-pipeline", function() {
                    $("#leads-table").DataTable().draw();
                });
                
                $(document).on("change", "#entries_per_page", function() {
                    var val = $(this).val();
                    table.api().page.len(val).draw();
                });
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'action' => 'function(e, dt, node, config) { if (typeof window.exportLeadsDataTable === "function") { window.exportLeadsDataTable("print"); } else { $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, config); } }'
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'action' => 'function(e, dt, node, config) { if (typeof window.exportLeadsDataTable === "function") { window.exportLeadsDataTable("csv"); } else { $.fn.dataTable.ext.buttons.csv.action.call(this, e, dt, node, config); } }'
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'action' => 'function(e, dt, node, config) { if (typeof window.exportLeadsDataTable === "function") { window.exportLeadsDataTable("excel"); } else { $.fn.dataTable.ext.buttons.excel.action.call(this, e, dt, node, config); } }'
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "stateSave" => false,
            "lengthMenu" => [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
            "dom" => "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $user = \Auth::user();
        $hasBulkPermission = $user->isAbleTo('lead edit') || $user->isAbleTo('lead delete');
        
        $visibleColumns = request('visible_columns');
        $isExport = in_array(request('action'), ['excel', 'csv', 'print']);
        
        $columns = [
            Column::make('batch')
                ->title($hasBulkPermission ? '<div class="form-check custom-checkbox"><input type="checkbox" class="form-check-input" id="checkAll"><label class="form-check-label" for="checkAll"></label></div>' : '')
                ->data('batch')
                ->name('batch')
                ->searchable(false)
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->width(20),
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name'))->visible($isExport && is_array($visibleColumns) ? in_array('name', $visibleColumns) : true),
            Column::make('subject')->title(__('Subject'))->visible($isExport && is_array($visibleColumns) ? in_array('subject', $visibleColumns) : true),
            Column::make('stage_id')->title(__('Stages'))->visible($isExport && is_array($visibleColumns) ? in_array('stage_id', $visibleColumns) : true),
            Column::make('tasks')->title(__('Tasks'))->searchable(false)->orderable(false)->visible($isExport && is_array($visibleColumns) ? in_array('tasks', $visibleColumns) : true),
            Column::make('reminders')->title(__('Reminders'))->searchable(false)->orderable(false)->visible($isExport && is_array($visibleColumns) ? in_array('reminders', $visibleColumns) : true),
            Column::make('created_at')->title(__('Created Date'))->visible($isExport && is_array($visibleColumns) ? in_array('created_at', $visibleColumns) : true),
            Column::make('updated_at')->title(__('Modified Date'))->visible($isExport && is_array($visibleColumns) ? in_array('updated_at', $visibleColumns) : (request()->has('modified_start_date') || request()->has('modified_end_date'))),
            Column::make('follow_up_date')->title(__('Follow Up Date'))->visible($isExport && is_array($visibleColumns) ? in_array('follow_up_date', $visibleColumns) : false),
            Column::make('user_id')->title(__('Users'))->searchable(false)->exportable(true)->orderable(false)->visible($isExport && is_array($visibleColumns) ? in_array('user_id', $visibleColumns) : true),
            Column::make('created_by')->title(__('Created By'))->visible($isExport && is_array($visibleColumns) ? in_array('created_by', $visibleColumns) : false),
            Column::make('updated_by')->title(__('Modified By'))->visible($isExport && is_array($visibleColumns) ? in_array('updated_by', $visibleColumns) : false),
            Column::make('phone')->title(__('Phone No'))->visible($isExport && is_array($visibleColumns) ? in_array('phone', $visibleColumns) : false),
            Column::make('email')->title(__('Email'))->visible($isExport && is_array($visibleColumns) ? in_array('email', $visibleColumns) : false),
            Column::make('team')->title(__('Team'))->exportable(true)->visible($isExport && is_array($visibleColumns) ? in_array('team', $visibleColumns) : false),
        ];

        // Fetch custom fields and append them dynamically
        try {
            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
            foreach ($customFields as $field) {
                $colName = 'custom_' . $field->id;
                $isVisible = $isExport && is_array($visibleColumns) ? in_array($colName, $visibleColumns) : false;
                $columns[] = Column::make($colName)
                    ->title($field->name)
                    ->data($colName)
                    ->name($colName)
                    ->searchable(false)
                    ->orderable(false)
                    ->exportable(true)
                    ->visible($isVisible);
            }
        } catch (\Exception $e) {
            \Log::error('LeadDataTable getColumns customFields error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        if (
            !\Auth::user()->hasRole('client') &&
            (\Laratrust::hasPermission('lead show') ||
                \Laratrust::hasPermission('product&lead edit') ||
                \Laratrust::hasPermission('lead delete'))
        ) {
            $columns[] = Column::computed('action')
                ->title(__('Action'))
                ->searchable(false)
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->width(60)

                ->searchable(false);
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Leads_' . date('YmdHis');
    }
}
