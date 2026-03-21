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

class LeadDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['batch', 'name', 'subject', 'email', 'phone', 'stage_id', 'tasks', 'reminders', 'created_at', 'user_id', 'team'];
        $isExport = in_array(request('action'), ['excel', 'csv']);

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('batch', function (Lead $lead) {
                return '<div class="form-check custom-checkbox">
                            <input type="checkbox" class="form-check-input lead-checkbox" id="lead_checkbox_' . $lead->id . '" value="' . $lead->id . '">
                            <label class="form-check-label" for="lead_checkbox_' . $lead->id . '"></label>
                        </div>';
            })
            ->editColumn('name', function (Lead $lead) use ($isExport) {
                return LeadUtility::getFieldDisplay($lead->id, 'name', $lead->name, $isExport);
            })
            ->editColumn('subject', function (Lead $lead) use ($isExport) {
                return LeadUtility::getFieldDisplay($lead->id, 'subject', $lead->subject, $isExport);
            })
            ->editColumn('email', function (Lead $lead) use ($isExport) {
                return LeadUtility::getFieldDisplay($lead->id, 'email', $lead->email, $isExport);
            })
            ->editColumn('phone', function (Lead $lead) use ($isExport) {
                $phone = LeadUtility::getFieldDisplay($lead->id, 'phone', $lead->phone, $isExport);
                if ($isExport) {
                    return $phone;
                }
                if (!empty($lead->phone)) {
                    return $phone . ' <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="' . $lead->phone . '" data-bs-toggle="tooltip" title="' . __('Call') . '"><i class="ti ti-phone-call"></i></a>';
                }
                return $phone;
            })
            ->editColumn('stage_id', function (Lead $lead) {
                return $lead->stage->name;
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
                return count($lead->tasks) . '/' . count($lead->complete_tasks);
            })
            ->addColumn('reminders', function (Lead $lead) use ($isExport) {
                $todayRemindersCount = $lead->getTodayRemindersCount();
                $totalRemindersCount = $lead->getFilteredReminders()->count();

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
            ->editColumn('follow_up_date', function (Lead $lead) {
                return $lead->follow_up_date ? company_date_formate($lead->follow_up_date) : '-';
            })
            ->editColumn('user_id', function (Lead $lead) use ($isExport) {
                if ($isExport) {
                    return $lead->owner ? $lead->owner->name : '-';
                }
                $html = '-';
                if ($lead->owner) {
                    $user = $lead->owner;
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
                static $employeeDeptCache = [];
                if (module_is_active('Hrm') && $lead->user_id) {
                    if (isset($employeeDeptCache[$lead->user_id])) {
                        return $employeeDeptCache[$lead->user_id];
                    }
                    $departmentName = '-';
                    $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $lead->user_id)->first();
                    if ($employee && $employee->department_id) {
                        $department = \Workdo\Hrm\Entities\Department::find($employee->department_id);
                        if ($department)
                            $departmentName = $department->name;
                    }
                    $employeeDeptCache[$lead->user_id] = $departmentName;
                    return $departmentName;
                }
                return '-';
            });
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
        $user = \Auth::user();
        $pipeline_id = $request->default_pipeline_id ?? $user->default_pipeline;
        if (!$pipeline_id) {
            $firstPipeline = Pipeline::where('workspace_id', getActiveWorkSpace())->first();
            $pipeline_id = $firstPipeline ? $firstPipeline->id : 0;
        }

        $query = $model->where('leads.pipeline_id', '=', $pipeline_id)
            ->where('leads.workspace_id', '=', getActiveWorkSpace())
            ->with(['users', 'tasks', 'complete_tasks', 'stage', 'reminders']);

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
                    ->orWhereHas(
                        'users',
                        function ($subQ) use ($accessibleUserIds) {
                            $subQ->whereIn('users.id', $accessibleUserIds);
                        }
                    );
            });
        }

        // Apply Stage-based visibility (Restrict leads from hidden stages)
        // If user is not 'company', they only see leads from stages where they have 'can_view' permission
        if ($user->type != 'company') {
            $hiddenStageIds = [];
            $allStagesInPipeline = LeadStage::where('pipeline_id', $pipeline_id)->where('workspace_id', getActiveWorkSpace())->get();
            foreach ($allStagesInPipeline as $s) {
                if (!$s->permissions($user)->can_view) {
                    $hiddenStageIds[] = $s->id;
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
                    ->orWhereHas(
                        'users',
                        function ($subQ) use ($respIds) {
                            $subQ->whereIn('users.id', $respIds);
                        }
                    );
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

        if (($request->has('modified_start_date') && !empty($request->modified_start_date)) || ($request->has('modified_end_date') && !empty($request->modified_end_date))) {
            $query->where(function ($q) use ($request) {
                $m_start = $request->modified_start_date;
                $m_end = $request->modified_end_date;

                // Check Lead Core Record
                $q->where(
                    function ($sub) use ($m_start, $m_end) {
                        if (!empty($m_start)) {
                            $sub->where('leads.updated_at', '>=', $m_start . ' 00:00:00');
                        }
                        if (!empty($m_end)) {
                            $sub->where('leads.updated_at', '<=', $m_end . ' 23:59:59');
                        }
                    }
                );

                // Check Related Activities
                $relations = ['tasks', 'discussions', 'calls', 'emails', 'files', 'activities'];
                foreach ($relations as $rel) {
                    $q->orWhereHas(
                        $rel,
                        function ($sub) use ($m_start, $m_end) {
                            if (!empty($m_start)) {
                                $sub->where('updated_at', '>=', $m_start . ' 00:00:00');
                            }
                            if (!empty($m_end)) {
                                $sub->where('updated_at', '<=', $m_end . ' 23:59:59');
                            }
                        }
                    );
                }
            });
        }

        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->whereIn('leads.created_by', (array) $request->created_by);
        }

        if ($request->has('modified_by') && !empty($request->modified_by)) {
            $query->whereIn('leads.updated_by', (array) $request->modified_by);
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $departmentIds = (array) $request->department_id;
            $deptUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $departmentIds)
                ->where('workspace', getActiveWorkSpace())
                ->pluck('user_id')
                ->toArray();

            $query->where(function ($q) use ($deptUserIds) {
                $q->whereIn('leads.user_id', $deptUserIds)
                    ->orWhereHas(
                        'users',
                        function ($subQ) use ($deptUserIds) {
                            $subQ->whereIn('users.id', $deptUserIds);
                        }
                    );
            });
        }

        if ($request->has('designation_id') && !empty($request->designation_id)) {
            $designationIds = (array) $request->designation_id;
            $desigUserIds = \Workdo\Hrm\Entities\Employee::whereIn('designation_id', $designationIds)
                ->where('workspace', getActiveWorkSpace())
                ->pluck('user_id')
                ->toArray();

            $query->where(function ($q) use ($desigUserIds) {
                $q->whereIn('leads.user_id', $desigUserIds)
                    ->orWhereHas(
                        'users',
                        function ($subQ) use ($desigUserIds) {
                            $subQ->whereIn('users.id', $desigUserIds);
                        }
                    );
            });
        }

        // Duplicates Filter
        if ($request->has('duplicates') && $request->duplicates == 1) {
            $wsId = getActiveWorkSpace();
            $query->where(function ($q) use ($wsId) {
                $q->where(
                    function ($inner) use ($wsId) {
                        $inner->whereNotNull('leads.email')->where('leads.email', '!=', '')->whereIn(
                            'leads.email',
                            function ($sub) use ($wsId) {
                                $sub->select('email')->from('leads')->where('workspace_id', $wsId)->whereNotNull('email')->where('email', '!=', '')->groupBy('email')->havingRaw('COUNT(email) > 1');
                            }
                        );
                    }
                )
                    ->orWhere(
                        function ($inner) use ($wsId) {
                            $inner->whereNotNull('leads.phone')->where('leads.phone', '!=', '')->whereIn(
                                'leads.phone',
                                function ($sub) use ($wsId) {
                                    $sub->select('phone')->from('leads')->where('workspace_id', $wsId)->whereNotNull('phone')->where('phone', '!=', '')->groupBy('phone')->havingRaw('COUNT(phone) > 1');
                                }
                            );
                        }
                    )
                    ->orWhere(
                        function ($inner) use ($wsId) {
                            $inner->whereNotNull('leads.name')->where('leads.name', '!=', '')->whereIn(
                                'leads.name',
                                function ($sub) use ($wsId) {
                                    $sub->select('name')->from('leads')->where('workspace_id', $wsId)->whereNotNull('name')->where('name', '!=', '')->groupBy('name')->havingRaw('COUNT(name) > 1');
                                }
                            );
                        }
                    );
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
                'data' => 'function(d) {
                    try {
                        var pipeline = $("select[name=default_pipeline_id]").val();
                        d.default_pipeline_id = pipeline;
                        
                        // Ensure selectedLeads is properly initialized
                        if (typeof window.selectedLeads === "undefined") {
                            window.selectedLeads = [];
                        }
                        
                        if (window.selectedLeads && window.selectedLeads.length > 0) {
                            d.export_selected_ids = window.selectedLeads.join(",");
                        }
                        
                        // Pass all custom filters from URL
                        var urlParams = new URL(window.location.href).searchParams;
                        urlParams.forEach(function(value, key) {
                            if (key.endsWith("[]")) {
                                var cleanKey = key.replace("[]", "");
                                if (!d[cleanKey]) d[cleanKey] = [];
                                if (!d[cleanKey].includes(value)) d[cleanKey].push(value);
                            } else {
                                d[key] = value;
                            }
                        });
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
                
                // Set initial value of our custom selector
                $("#entries_per_page").val(table.api().page.len());

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
                    'exportOptions' => ['columns' => ':visible'],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => ':visible'],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => ':visible'],
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
            "stateSave" => true,
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
        $columns = [
            Column::make('batch')
                ->title('<div class="form-check custom-checkbox"><input type="checkbox" class="form-check-input" id="checkAll"><label class="form-check-label" for="checkAll"></label></div>')
                ->data('batch')
                ->name('batch')
                ->searchable(false)
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->width(20),
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('subject')->title(__('Subject')),
            Column::make('stage_id')->title(__('Stages')),
            Column::make('tasks')->title(__('Tasks'))->searchable(false)->orderable(false),
            Column::make('reminders')->title(__('Reminders'))->searchable(false)->orderable(false),
            Column::make('created_at')->title(__('Created Date')),
            Column::make('follow_up_date')->title(__('Follow Up Date'))->visible(false),
            Column::make('user_id')->title(__('Users'))->searchable(false)->exportable(true)->orderable(false),
            Column::make('created_by')->title(__('Created By'))->visible(false),
            Column::make('updated_by')->title(__('Modified By'))->visible(false),
            Column::make('phone')->title(__('Phone No'))->visible(false),
            Column::make('email')->title(__('Email'))->visible(false),
            Column::make('team')->title(__('Team'))->exportable(true)->visible(false),
        ];

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
