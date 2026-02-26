<div class="d-flex align-items-center justify-content-center">
    @if(\Auth::user()->isAbleTo('lead edit') || \Auth::user()->isAbleTo('lead task edit'))
        <div class="action-btn bg-info ms-2">
            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" 
               data-url="{{ route('leads.tasks.edit', [$task->lead_id, $task->id]) }}" 
               data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit Task')}}" 
               data-title="{{__('Edit Task')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endif
    @if(\Auth::user()->isAbleTo('lead edit') || \Auth::user()->isAbleTo('lead task delete'))
        <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['lead_tasks.destroy', $task->id], 'id' => 'delete-form-'.$task->id]) !!}
            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para" 
               data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" 
               data-confirm="{{__('Are You Sure?')}}|{{__('This action cannot be undone. Do you want to continue?')}}" 
               data-confirm-yes="document.getElementById('delete-form-{{$task->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        </div>
    @endif
</div>
