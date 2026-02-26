@extends('layouts.main')
@section('page-title')
{{__('Roles')}}
@endsection
@section('page-breadcrumb')
{{ __('Users') }}
@endsection
@section('page-action')
    <a href="#" class="btn btn-sm btn-primary" data-url="{{ route('roles.create') }}" data-ajax-popup="true" data-title="{{__('Create New Role')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
    </a>
@endsection
@section('content')
<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-header">
            <h5>{{__('Roles')}}</h5>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-flush" id="dataTable">
                  <thead>
                     <tr>
                        <th> {{__('Name')}}</th>
                        <th> {{__('Display Name')}}</th>
                        <th> {{__('Module')}}</th>
                        <th class="text-right" width="200px"> {{__('Action')}}</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($roles as $role)
                     <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->display_name }}</td>
                        <td>{{ $role->module }}</td>
                        <td class="action">
                           <div class="action-btn btn-primary ms-2">
                              <a data-url="{{ route('roles.edit',$role->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Update role')}}" class="btn btn-outline btn-xs blue-madison" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                 <i class="ti ti-pencil text-white"></i>
                              </a>
                           </div>
                           <div class="action-btn bg-danger ms-2">
                              {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id]]) !!}
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
