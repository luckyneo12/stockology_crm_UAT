
<div class="modal-body">
    @if(isset($discussions) && $discussions->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="mb-3 text-muted fw-bold text-uppercase ls-1" style="font-size: 0.75rem;">{{ __('Existing Comments') }}</h6>
                <div class="p-3 border rounded-3 bg-light-subtle" style="max-height: 250px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @foreach($discussions as $discussion)
                            <li class="list-group-item px-0 py-2 border-0 border-bottom bg-transparent d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-start">
                                    @php
                                        $avatar = 'uploads/users-avatar/avatar.png';
                                        if(!empty($discussion->user->avatar) && check_file($discussion->user->avatar)) {
                                            $avatar = $discussion->user->avatar;
                                        }
                                    @endphp
                                    <img src="{{ get_file($avatar) }}" 
                                         class="rounded-circle me-3" style="width: 32px; height: 32px;" alt="avatar">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="text-xs mb-0 me-2">{{ $discussion->user->name }}</strong>
                                            <small class="text-muted text-xs">{{ $discussion->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="text-sm mb-0 text-dark">{{ $discussion->comment }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if(\Auth::user()->type == 'company' || $discussion->created_by == \Auth::user()->id || \Auth::user()->isAbleTo('lead delete'))
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.discussion.destroy', $lead->id, $discussion->id], 'id' => 'delete-form-' . $discussion->id, 'class' => 'd-inline']) !!}
                                            <a href="#!" class="action-btn btn btn-sm btn-light-danger bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                        {!! Form::close() !!}
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <hr class="my-4 opacity-0.1">
    @endif

    {{ Form::model($lead, array('route' => array('leads.discussion.store', $lead->id), 'method' => 'POST')) }}
        <div class="row">
            {{ Form::hidden('is_kyc', $is_kyc ?? 0) }}
            <div class="col-12 form-group">
                {{ Form::label('comment', __('New Message'),['class'=>'col-form-label']) }}
                {{ Form::textarea('comment', null, array('class' => 'form-control','required'=>'required','rows'=> 3,'placeholder'=> __('Enter Message'))) }}
            </div>
        </div>
        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
            <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
        </div>
    {{ Form::close() }}
</div>

