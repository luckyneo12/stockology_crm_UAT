@extends('layouts.main')

@section('page-title')
    {{__('Manage Custom Fields')}}
@endsection

@section('page-action')
    <div class="row align-items-center m-1">
        <div class="col-auto pe-0">
            @permission('ekyc manage')
                <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create')}}" data-ajax-popup="true" data-size="md" data-title="{{__('Create Custom Field')}}" data-url="{{route('ekyc.custom-fields.create')}}"><i class="ti ti-plus text-white"></i></a>
            @endpermission
        </div>
    </div>
@endsection

@section('page-breadcrumb')
    {{__('Setup')}},
    {{__('Custom Fields')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="custom-fields">
                            <thead>
                                <tr>
                                    <th>{{__('Field Name')}}</th>
                                    <th>{{__('Type')}}</th>
                                    <th width="250px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customFields as $field)
                                    <tr>
                                        <td>{{ $field->name }}</td>
                                        <td>{{ \Workdo\Ekyc\Entities\EkycCustomField::$fieldTypes[$field->type] ?? $field->type }}</td>
                                        <td class="Action">
                                            <span>
                                            @permission('ekyc manage')
                                                <div class="action-btn me-2 mt-1">
                                                    <a href="#" data-url="{{ route('ekyc.custom-fields.edit', $field->id) }}" data-ajax-popup="true" data-title="{{__('Edit Custom Field')}}" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit')}}" ><i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                            @endpermission
                                            @permission('ekyc manage')
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['ekyc.custom-fields.destroy', $field->id]]) !!}
                                                    <a href="#!" class="btn btn-sm align-items-center show_confirm bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Delete')}}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endpermission
                                            </span>
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
