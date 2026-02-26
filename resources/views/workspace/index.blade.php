@extends('layouts.main')
@section('page-title')
{{__('Workspaces')}}
@endsection
@section('page-breadcrumb')
{{ __('Workspace') }}
@endsection
@section('page-action')
    <a href="#" class="btn btn-sm btn-primary" data-url="{{ route('workspace.create') }}" data-ajax-popup="true" data-title="{{__('Create New Workspace')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
    </a>
@endsection
@section('content')
<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-header">
            <h5>{{__('Workspaces')}}</h5>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-flush" id="dataTable">
                  <thead>
                     <tr>
                        <th> {{__('Name')}}</th>
                        <th> {{__('Created By')}}</th>
                        <th> {{__('Created At')}}</th>
                        <th class="text-right" width="200px"> {{__('Action')}}</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($workspaces as $workspace)
                     <tr>
                        <td>{{ $workspace->name }}</td>
                        <td>{{ $workspace->creator->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($workspace->created_at)->format('d M Y') }}</td>
                        <td class="action">
                           <div class="action-btn btn-primary ms-2">
                              <a data-url="{{ route('workspace.edit',$workspace->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Update workspace')}}" class="btn btn-outline btn-xs blue-madison" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                 <i class="ti ti-pencil text-white"></i>
                              </a>
                           </div>
                           <div class="action-btn bg-info ms-2">
                              <a href="{{ route('workspace.change',$workspace->id) }}" class="btn btn-outline btn-xs info text-white" data-bs-toggle="tooltip" data-original-title="{{__('Switch')}}">
                                 <i class="ti ti-exchange"></i>
                              </a>
                           </div>
                           <div class="action-btn bg-danger ms-2">
                              {!! Form::open(['method' => 'DELETE', 'route' => ['workspace.destroy', $workspace->id]]) !!}
                              <a href="#" class="btn btn-outline btn-xs danger text-white" data-confirm="{{__('Are You Sure?')}}" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}">
                                 <i class="ti ti-trash"></i>
                              </a>
                              {!! Form::close() !!}
                           </div>
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
