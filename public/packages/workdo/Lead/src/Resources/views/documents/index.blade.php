@extends('layouts.main')

@section('page-title')
    {{ __('Manage Lead Documents') }}
@endsection

@section('page-breadcrumb')
    {{ __('Lead Documents') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create Document') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Document') }}" data-url="{{ route('lead-documents.create') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="documents">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Stage Visibility') }}</th>
                                    <th>{{ __('Required') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr>
                                        <td>{{ $document->name }}</td>
                                        <td>{{ $document->stage ? $document->stage->name : __('All Stages') }}</td>
                                        <td>{{ $document->is_required ? __('Yes') : __('No') }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('lead-documents.edit', $document->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Document') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['lead-documents.destroy', $document->id], 'id' => 'delete-form-' . $document->id]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
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
