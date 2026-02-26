@extends('layouts.main')

@section('page-title')
    {{ __('Lead Layout Builder') }}
@endsection

@section('page-breadcrumb')
    {{ __('Lead Layout Builder') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create Custom Field') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Custom Field') }}" data-url="{{ route('lead-custom-fields.create') }}">
            <i class="ti ti-plus text-white"></i> {{ __('Add Field') }}
        </a>
        <button class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createSectionModal">
            <i class="ti ti-plus"></i> {{ __('Add Section') }}
        </button>
        <button class="btn btn-sm btn-success" id="save-layout">
            <i class="ti ti-device-floppy"></i> {{ __('Save Layout') }}
        </button>
    </div>
@endsection

@section('content')
    <style>
        .section-card {
            border: 2px dashed #ccc;
            background: #f9f9f9;
            cursor: move;
        }
        .field-card {
            background: white;
            border: 1px solid #ddd;
            cursor: move;
        }
        .field-card.sortable-ghost {
            opacity: 0.5;
            background: #ecf0f1;
        }
        /* Highlight specific system fields if needed */
        .badge.bg-secondary {
            background-color: #6c757d !important;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div id="sections-container">
                @foreach($sections as $section)
                    <div class="card mb-3 section-card" data-id="{{ $section->id }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 handle cursor-move"><i class="ti ti-grip-vertical me-2"></i> <span class="section-name">{{ $section->name }}</span> <small class="text-muted">({{ $section->columns }} Columns)</small></h5>
                            <div>
                                <button class="btn btn-sm btn-info edit-section-btn" 
                                    data-id="{{ $section->id }}" 
                                    data-name="{{ $section->name }}" 
                                    data-columns="{{ $section->columns }}">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                @if(!$section->is_system)
                                    <form action="{{ route('lead-builder.section.destroy', $section->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger confirm-action"><i class="ti ti-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="field-list row" data-section-id="{{ $section->id }}">
                                @foreach($section->fields as $field)
                                    <div class="col-md-{{ 12 / ($section->columns > 0 ? $section->columns : 3) }} mb-2" data-id="{{ $field->id }}">
                                        <div class="card field-card p-2 shadow-none mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="ti ti-grip-vertical text-muted"></i> 
                                                    {{ $field->name }}
                                                </span>
                                                <div>
                                                    <small class="badge bg-{{ $field->is_system ? 'secondary' : 'primary' }} me-1">{{ $field->is_system ? 'System' : 'Custom' }}</small>
                                                    @if(!$field->is_system)
                                                        <a href="#" class="action-btn btn-info btn btn-sm d-inline-flex align-items-center" data-url="{{ route('lead-custom-fields.edit', $field->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Custom Field') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                        <form action="{{ route('lead-custom-fields.duplicate', $field->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="action-btn btn-success btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Duplicate') }}">
                                                                <i class="ti ti-copy"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('lead-custom-fields.destroy', $field->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="action-btn btn-danger btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($section->fields->isEmpty())
                                <div class="text-center text-muted p-3 empty-placeholder">{{ __('Drag fields here') }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
             <!-- Unassigned Fields Bin -->
            @php
                $unassignedFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->whereNull('section_id')->get();
            @endphp
            @if($unassignedFields->count() > 0)
                <div class="card mb-3 section-card border-warning" data-id="unassigned">
                    <div class="card-header bg-warning-subtle text-warning-emphasis">
                        <h5 class="mb-0">{{ __('Unassigned Fields') }} <small>({{ __('Drag these into a section') }})</small></h5>
                    </div>
                    <div class="card-body p-3">
                         <div class="field-list row" data-section-id="">
                            @foreach($unassignedFields as $field)
                                <div class="col-md-4 mb-2" data-id="{{ $field->id }}">
                                    <div class="card field-card p-2 shadow-sm mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ti ti-grip-vertical text-muted"></i> {{ $field->name }}</span>
                                                 <div>
                                                    <small class="badge bg-{{ $field->is_system ? 'secondary' : 'primary' }} me-1">{{ $field->is_system ? 'System' : 'Custom' }}</small>
                                                     @if(!$field->is_system)
                                                        <a href="#" class="action-btn btn-info btn btn-sm d-inline-flex align-items-center" data-url="{{ route('lead-custom-fields.edit', $field->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Custom Field') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                        <form action="{{ route('lead-custom-fields.duplicate', $field->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="action-btn btn-success btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Duplicate') }}">
                                                                <i class="ti ti-copy"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('lead-custom-fields.destroy', $field->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="action-btn btn-danger btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            @endforeach
                         </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Create/Edit Section Modal -->
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('lead-builder.section.store') }}" method="POST" id="sectionForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sectionModalLabel">{{ __('Create Section') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" id="sectionName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Columns') }}</label>
                            <select class="form-select" name="columns" id="sectionColumns">
                                <option value="1">1 Column</option>
                                <option value="2">2 Columns</option>
                                <option value="3" selected>3 Columns</option>
                                <option value="4">4 Columns</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Sortable Sections
            var sectionsContainer = document.getElementById('sections-container');
            new Sortable(sectionsContainer, {
                animation: 150,
                handle: '.handle',
                ghostClass: 'sortable-ghost'
            });

            // 2. Sortable Fields (Nested)
            var fieldLists = document.querySelectorAll('.field-list');
            fieldLists.forEach(function (el) {
                new Sortable(el, {
                    group: 'fields', // Allow dragging between lists
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onAdd: function (evt) {
                        // Handle formatting if needed when moved to new column layout
                    }
                });
            });

            // 3. Save Layout
            document.getElementById('save-layout').addEventListener('click', function () {
                var payload = [];
                var sections = document.querySelectorAll('.section-card');
                
                sections.forEach(function (section, index) {
                    var sectionId = section.getAttribute('data-id');
                    var fields = [];
                    section.querySelectorAll('.field-list > div').forEach(function (fieldWrapper, fieldIndex) {
                        var fieldId = fieldWrapper.getAttribute('data-id');
                        if(fieldId) {
                            fields.push({ id: fieldId });
                        }
                    });
                    
                    payload.push({
                        id: sectionId,
                        fields: fields
                    });
                });

                // Post to server
                var btn = this;
                var originalText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Saving...';
                
                fetch('{{ route("lead-builder.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ sections: payload })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        toastrs('Success', data.success, 'success');
                    } else {
                        toastrs('Error', data.error || 'Something went wrong', 'error');
                    }
                    btn.innerHTML = originalText;
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastrs('Error', 'Network error', 'error');
                    btn.innerHTML = originalText;
                });
            });

            // 4. Edit Modal Handler
            var modal = document.getElementById('createSectionModal');
            var form = document.getElementById('sectionForm');
            var modalTitle = document.getElementById('sectionModalLabel');
            var nameInput = document.getElementById('sectionName');
            var colsInput = document.getElementById('sectionColumns');
            var methodInput = document.getElementById('formMethod');

            document.querySelectorAll('.edit-section-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    var cols = this.getAttribute('data-columns');

                    form.action = '{{ url("lead-builder/section") }}/' + id;
                    methodInput.value = 'PUT';
                    modalTitle.innerText = '{{ __("Edit Section") }}';
                    nameInput.value = name;
                    colsInput.value = cols;

                    var bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                });
            });

            // Reset modal on close
            modal.addEventListener('hidden.bs.modal', function () {
                form.action = '{{ route("lead-builder.section.store") }}';
                methodInput.value = 'POST';
                modalTitle.innerText = '{{ __("Create Section") }}';
                nameInput.value = '';
                colsInput.value = 3;
            });
        });
    </script>
@endpush
