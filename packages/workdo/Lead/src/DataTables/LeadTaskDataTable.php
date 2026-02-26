<?php

namespace Workdo\Lead\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadTask;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LeadTaskDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('checkbox', function (LeadTask $task) {
                return '<div class="form-check custom-checkbox">
                            <input type="checkbox" class="form-check-input task-checkbox" id="task_checkbox_'.$task->id.'" value="'.$task->id.'">
                            <label class="form-check-label" for="task_checkbox_'.$task->id.'"></label>
                        </div>';
            })
            ->editColumn('name', function (LeadTask $task) {
                 return $task->name;
            })
            ->editColumn('lead_name', function (LeadTask $task) {
                return $task->lead ? $task->lead->name : '-';
            })
            ->editColumn('priority', function (LeadTask $task) {
                $priority = LeadTask::$priorities[$task->priority] ?? 'Low';
                $class = 'badge-soft-primary';
                if($task->priority == 2) $class = 'badge-soft-warning';
                if($task->priority == 3) $class = 'badge-soft-danger';
                return '<span class="badge '.$class.' p-2 px-3 rounded">'.$priority.'</span>';
            })
            ->editColumn('date', function (LeadTask $task) {
                return company_date_formate($task->date) . ' ' . $task->time;
            })
            ->editColumn('status', function (LeadTask $task) {
                $labels = [
                    'pending' => 'badge-soft-warning',
                    'in_progress' => 'badge-soft-info',
                    'done' => 'badge-soft-success',
                    'overdue' => 'badge-soft-danger',
                ];
                $class = $labels[$task->status] ?? 'badge-soft-secondary';
                $status = LeadTask::$status[$task->status] ?? ucfirst($task->status);
                return '<span class="badge '.$class.' p-2 px-3 rounded">'.$status.'</span>';
            })
            ->editColumn('user_id', function (LeadTask $task) {
                 return $task->user ? $task->user->name : '-';
            })
            ->addColumn('action', function (LeadTask $task) {
                return view('lead::tasks.task_action', compact('task'));
            })
            ->addColumn('test', function (LeadTask $task) {
                return 'RENDERED';
            })
            ->rawColumns(['checkbox', 'priority', 'status', 'action', 'test']);
    }

    public function query(LeadTask $model): QueryBuilder
    {
        $request = request();
        $user = Auth::user();
        
        $query = $model->with(['lead', 'user'])
                       ->where('workspace', getActiveWorkSpace());

        if ($user->type != 'company' && $user->visibility_level != 'all') {
            $accessibleUserIds = $user->getAccessibleUserIds();
             $query->whereIn('user_id', $accessibleUserIds);
        }

        // Filters
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('lead_id') && !empty($request->lead_id)) {
            $query->where('lead_id', $request->lead_id);
        }
         if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
         if ($request->has('priority') && !empty($request->priority)) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('lead-tasks-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(4) // Date column
            ->dom('rtip')
             ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_",
                "searchPlaceholder" => __('Search...'),
                "search" => "",
            ]);
    }

    protected function getColumns(): array
    {
        return [
            Column::computed('checkbox')
                ->title('<div class="form-check custom-checkbox"><input type="checkbox" class="form-check-input" id="checkAllTasks"><label class="form-check-label" for="checkAllTasks"></label></div>')
                ->clickable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(20),
            Column::make('name')->title(__('Task Name')),
            Column::make('lead_name')->title(__('Lead')),
            Column::make('priority')->title(__('Priority')),
            Column::make('date')->title(__('Due Date')),
            Column::make('status')->title(__('Status')),
            Column::make('user_id')->title(__('Assigned To')),
            Column::computed('action')
                ->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
            Column::make('test')->title('DEBUG'),
        ];
    }

    protected function filename(): string
    {
        return 'LeadTasks_' . date('YmdHis');
    }
}
