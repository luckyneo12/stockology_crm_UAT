@extends('layouts.main')

@section('page-title')
    {{ __('Lead Layout Builder') }}
@endsection

@section('page-breadcrumb')
    {{ __('Lead Layout Builder') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create Custom Field') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Custom Field') }}" data-url="{{ route('lead-custom-fields.create', ['pipeline_id' => $selectedPipeline->id]) }}">
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
    @if(isset($pipelines) && $pipelines->count() > 0)
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #fbfcfd 0%, #f1f5f9 100%); border-radius: 12px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <span class="btn btn-light-primary btn-icon me-3" style="cursor: default; pointer-events: none;">
                        <i class="ti ti-git-fork fs-4"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ __('Pipeline Layout Settings') }}</h6>
                        <small class="text-muted">{{ __('Manage layout sections and custom fields for this pipeline') }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <label class="form-label mb-0 me-3 fw-bold text-muted" style="white-space: nowrap;">{{ __('Active Pipeline:') }}</label>
                    <select class="form-select select2" id="builder_pipeline_select" style="min-width: 250px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        @foreach($pipelines as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $selectedPipeline->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif
    <style>
        /* General Overhaul */
        .section-card {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03), 0 2px 4px -1px rgba(0,0,0,0.02) !important;
            transition: all 0.25s ease;
            margin-bottom: 24px !important;
            overflow: hidden;
        }
        .section-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.05) !important;
        }
        .section-card .card-header {
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 16px 20px !important;
        }
        .section-card .handle {
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .section-card .handle i {
            color: #94a3b8;
            cursor: grab;
            transition: color 0.2s;
        }
        .section-card .handle i:hover {
            color: #18bf6b;
        }
        
        /* Field Cards Overhaul */
        .field-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .field-card:hover {
            border-color: #18bf6b;
            box-shadow: 0 4px 12px rgba(24,191,107,0.08);
            transform: translateY(-1px);
        }
        .field-card .action-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.15s ease;
        }
        .field-card .action-btn i {
            font-size: 0.85rem;
        }
        .field-card .btn-info {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #dbeafe !important;
        }
        .field-card .btn-info:hover {
            background-color: #1d4ed8 !important;
            color: #fff !important;
        }
        .field-card .btn-success {
            background-color: #f0fdf4 !important;
            color: #15803d !important;
            border: 1px solid #dcfce7 !important;
        }
        .field-card .btn-success:hover {
            background-color: #15803d !important;
            color: #fff !important;
        }
        .field-card .btn-danger {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fee2e2 !important;
        }
        .field-card .btn-danger:hover {
            background-color: #b91c1c !important;
            color: #fff !important;
        }

        /* Standard Section Style */
        .section-card.layout-section {
            border-left: 4px solid #94a3b8;
        }

        /* Premium Card Layout */
        .section-card.layout-card {
            border-left: 4px solid #3b82f6;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.05), 0 8px 10px -6px rgba(59, 130, 246, 0.05) !important;
        }
        .section-card.layout-card:hover {
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -6px rgba(59, 130, 246, 0.1) !important;
        }

        /* Modern Bento Grid Layout */
        .section-card.layout-bento {
            border-left: 4px solid #18bf6b;
            background: radial-gradient(circle, rgba(24, 191, 107, 0.04) 1px, transparent 1px) #fff;
            background-size: 24px 24px;
            box-shadow: 0 10px 25px -5px rgba(24, 191, 107, 0.05), 0 8px 10px -6px rgba(24, 191, 107, 0.05) !important;
        }
        .section-card.layout-bento:hover {
            box-shadow: 0 20px 25px -5px rgba(24, 191, 107, 0.1), 0 10px 10px -6px rgba(24, 191, 107, 0.1) !important;
        }
        .section-card.layout-bento .field-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            border: 1px solid rgba(24, 191, 107, 0.15);
        }
        .section-card.layout-bento .field-card:hover {
            border-color: #18bf6b;
            box-shadow: 0 12px 20px -8px rgba(24, 191, 107, 0.2);
        }

        .section-card.border-warning {
            border: 1px solid #fef08a !important;
            border-left: 4px solid #eab308 !important;
            background: #fefce8 !important;
        }

        .empty-placeholder {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 20px !important;
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Visual API Mapper Styles */
        .api-key-pill {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 5px 10px;
            margin: 4px;
            font-size: 0.8rem;
            font-family: monospace;
            cursor: grab;
            user-select: none;
            transition: all 0.2s;
        }
        .api-key-pill:hover {
            background: #bae6fd;
            transform: scale(1.02);
        }
        .api-key-pill.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }
        .field-mapping-row {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .field-mapping-dropzone {
            min-width: 140px;
            min-height: 36px;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            font-size: 0.75rem;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .field-mapping-dropzone.drag-over {
            border-color: #18bf6b;
            background: #f0fdf4;
            color: #166534;
        }
        .mapped-pill {
            display: inline-flex;
            align-items: center;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 0.75rem;
            font-family: monospace;
            font-weight: bold;
        }
        .mapped-pill .btn-clear-mapping {
            background: none;
            border: none;
            padding: 0;
            margin-left: 8px;
            color: #ef4444;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div id="sections-container">
                @foreach($sections as $section)
                    <div class="card mb-3 section-card layout-{{ $section->layout_type ?? 'section' }}" data-id="{{ $section->id }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 handle cursor-move">
                                <i class="ti ti-grip-vertical me-2"></i> 
                                <span class="section-name">{{ $section->name }}</span> 
                                <small class="text-muted">({{ $section->columns }} Columns)</small>
                                @if(($section->layout_type ?? 'section') == 'card')
                                    <span class="badge bg-light-info text-info ms-2"><i class="ti ti-id-badge me-1"></i>Premium Card</span>
                                @elseif(($section->layout_type ?? 'section') == 'bento')
                                    <span class="badge bg-light-success text-success ms-2"><i class="ti ti-layout-grid me-1"></i>Bento Grid</span>
                                @else
                                    <span class="badge bg-light-secondary text-secondary ms-2"><i class="ti ti-layout me-1"></i>Standard Section</span>
                                @endif
                            </h5>
                            <div>
                                <button class="btn btn-sm btn-info edit-section-btn" 
                                    data-id="{{ $section->id }}" 
                                    data-name="{{ $section->name }}" 
                                    data-columns="{{ $section->columns }}"
                                    data-layout-type="{{ $section->layout_type ?? 'section' }}"
                                    data-api-url="{{ $section->api_url }}"
                                    data-api-method="{{ $section->api_method }}"
                                    data-api-trigger-stage-id="{{ $section->api_trigger_stage_id }}"
                                    data-api-response-mapping="{{ htmlspecialchars($section->api_response_mapping, ENT_QUOTES, 'UTF-8') }}">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary copy-section-btn" 
                                    data-id="{{ $section->id }}" 
                                    data-name="{{ $section->name }}">
                                    <i class="ti ti-copy" title="{{ __('Copy Section') }}"></i>
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
                $unassignedFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->where('pipeline_id', $selectedPipeline->id)->whereNull('section_id')->get();
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('lead-builder.section.store') }}" method="POST" id="sectionForm">
                    @csrf
                    <input type="hidden" name="pipeline_id" value="{{ $selectedPipeline->id }}">
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
                        <div class="mb-3">
                            <label class="form-label">{{ __('Layout Style') }}</label>
                            <select class="form-select" name="layout_type" id="sectionLayoutType">
                                <option value="section" selected>{{ __('Standard Section') }}</option>
                                <option value="card">{{ __('Premium Card') }}</option>
                                <option value="bento">{{ __('Bento Grid') }}</option>
                            </select>
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="ti ti-api"></i> Section API Integration</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">API Endpoint URL (Optional)</label>
                            <input type="text" class="form-control" name="api_url" id="sectionApiUrl" placeholder="https://api.example.com/get-lead-data">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Request Method</label>
                                <select class="form-select" name="api_method" id="sectionApiMethod">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trigger Stage</label>
                                <select class="form-select" name="api_trigger_stage_id" id="sectionApiTriggerStage">
                                    <option value="">{{ __('Select Stage to Trigger API') }}</option>
                                    @foreach($pipelines as $pipeline)
                                        <optgroup label="{{ $pipeline->name }}">
                                            @foreach($pipeline->leadStages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Hidden Textarea for API Response Mapping (JSON) to submit in form -->
                        <textarea name="api_response_mapping" id="sectionApiResponseMapping" style="display: none;"></textarea>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">{{ __('Visual API Response Mapper') }}</label>
                            <div class="card border border-primary-subtle shadow-none">
                                <div class="card-body p-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted fs-7">{{ __('Step 1: Paste Sample API JSON Response') }}</label>
                                        <textarea class="form-control text-monospace bg-light" id="apiSampleJson" rows="4" placeholder='{
  "status": "success",
  "data": {
    "full_name": "John Doe",
    "phone_number": "+1234567890",
    "email_address": "john@example.com",
    "custom_info": "Some extra data"
  }
}'></textarea>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnParseJson">
                                            <i class="ti ti-terminal-2"></i> {{ __('Parse JSON Response') }}
                                        </button>
                                    </div>
                                    
                                    <div id="visualMappingContainer" style="display: none;">
                                        <hr>
                                        <div class="row">
                                            <!-- Left side: API Keys (Draggable) -->
                                            <div class="col-md-5">
                                                <label class="form-label fw-bold text-secondary mb-2"><i class="ti ti-hand-drag"></i> {{ __('API Keys (Drag)') }}</label>
                                                <div class="bg-light p-2 rounded border" id="apiKeysContainer" style="max-height: 250px; overflow-y: auto; min-height: 150px;">
                                                    <!-- Draggable pills will be rendered here -->
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ __('Drag these pills to the fields on the right') }}</small>
                                            </div>
                                            <!-- Right side: Target Fields (Dropzones) -->
                                            <div class="col-md-7">
                                                <label class="form-label fw-bold text-secondary mb-2"><i class="ti ti-list"></i> {{ __('Target Fields (Drop)') }}</label>
                                                <div class="p-2 rounded border" id="targetFieldsMappingContainer" style="max-height: 250px; overflow-y: auto; background-color: #fafbfc; min-height: 150px;">
                                                    <!-- Field drop zones will be rendered here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

    <!-- Copy Section Modal -->
    <div class="modal fade" id="copySectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="copySectionForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Copy Section to Pipeline') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Select the target pipeline where you want to copy this section and all its custom fields.') }}</p>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Target Pipeline') }}</label>
                            <select class="form-select" name="target_pipeline_id" id="copyTargetPipeline" required>
                                <option value="">{{ __('Select Pipeline') }}</option>
                                @foreach($pipelines as $p)
                                    @if($p->id != $selectedPipeline->id)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Copy Section') }}</button>
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
            if (sectionsContainer) {
                new Sortable(sectionsContainer, {
                    animation: 150,
                    handle: '.handle',
                    ghostClass: 'sortable-ghost'
                });
            }

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
            var layoutInput = document.getElementById('sectionLayoutType');
            var methodInput = document.getElementById('formMethod');
            
            var apiUrlInput = document.getElementById('sectionApiUrl');
            var apiMethodInput = document.getElementById('sectionApiMethod');
            var apiTriggerStageInput = document.getElementById('sectionApiTriggerStage');
            var apiMappingInput = document.getElementById('sectionApiResponseMapping');

            // Drag and Drop State and Functions
            var allFields = [];
            var mappings = {};

            function loadAllFields() {
                allFields = [
                    { id: 'subject', name: 'Subject (System)' },
                    { id: 'name', name: 'Name (System)' },
                    { id: 'email', name: 'Email (System)' },
                    { id: 'phone', name: 'Phone (System)' }
                ];
                
                // Read custom fields from DOM
                document.querySelectorAll('.field-card').forEach(function(el) {
                    var wrapper = el.closest('[data-id]');
                    if (wrapper) {
                        var fid = wrapper.getAttribute('data-id');
                        var span = el.querySelector('span');
                        if (span && fid && fid !== 'unassigned') {
                            var fname = span.textContent.replace(/[\n\r]+/g, '').replace(/\s+/g, ' ').trim();
                            // avoid duplicate entries
                            if (!allFields.some(f => f.id == fid)) {
                                allFields.push({ id: fid, name: fname });
                            }
                        }
                    }
                });
            }

            function renderMappingBoard(existingMappingJson) {
                var container = document.getElementById('targetFieldsMappingContainer');
                container.innerHTML = '';
                
                loadAllFields();

                // Parse existing mapping if valid JSON
                try {
                    mappings = existingMappingJson ? JSON.parse(existingMappingJson) : {};
                } catch(e) {
                    mappings = {};
                }

                // Render each field dropzone
                allFields.forEach(function(field) {
                    var row = document.createElement('div');
                    row.className = 'field-mapping-row';
                    
                    var labelSpan = document.createElement('span');
                    labelSpan.className = 'fw-bold';
                    labelSpan.textContent = field.name;

                    var dropzone = document.createElement('div');
                    dropzone.className = 'field-mapping-dropzone';
                    dropzone.setAttribute('data-field-id', field.id);
                    dropzone.textContent = 'Drop key here';

                    // Register drag & drop events on dropzone
                    dropzone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        this.classList.add('drag-over');
                    });

                    dropzone.addEventListener('dragleave', function(e) {
                        this.classList.remove('drag-over');
                    });

                    dropzone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.classList.remove('drag-over');
                        var key = draggedKey || e.dataTransfer.getData('text/plain');
                        if (key && key !== 'drag') {
                            setFieldMapping(field.id, key);
                        }
                    });

                    // Check if this field is already mapped
                    var mappedKey = null;
                    for (var key in mappings) {
                        if (mappings[key] == field.id) {
                            mappedKey = key;
                            break;
                        }
                    }

                    if (mappedKey) {
                        renderMappedKeyInZone(dropzone, field.id, mappedKey);
                    }

                    row.appendChild(labelSpan);
                    row.appendChild(dropzone);
                    container.appendChild(row);
                });

                // Display visual mapping board container
                document.getElementById('visualMappingContainer').style.display = 'block';

                // Extract and render keys that are already mapped even if no sample JSON is pasted yet
                var initialKeys = [];
                for (var key in mappings) {
                    if (!initialKeys.includes(key)) {
                        initialKeys.push(key);
                    }
                }
                renderDraggablePills(initialKeys);
            }

            function renderDraggablePills(keys) {
                var container = document.getElementById('apiKeysContainer');
                // preserve currently mapped keys or unique list of parsed keys
                var existingPills = Array.from(container.querySelectorAll('.api-key-pill')).map(p => p.getAttribute('data-key'));
                
                keys.forEach(function(key) {
                    if (!existingPills.includes(key)) {
                        existingPills.push(key);
                    }
                });

                container.innerHTML = '';
                existingPills.forEach(function(key) {
                    var pill = document.createElement('div');
                    pill.className = 'api-key-pill';
                    pill.setAttribute('draggable', 'true');
                    pill.setAttribute('data-key', key);
                    pill.textContent = key;

                    pill.addEventListener('dragstart', function(e) {
                        draggedKey = this.getAttribute('data-key');
                        e.dataTransfer.setData('text/plain', draggedKey);
                        this.classList.add('dragging');
                    });

                    pill.addEventListener('dragend', function() {
                        this.classList.remove('dragging');
                    });

                    container.appendChild(pill);
                });
            }

            function setFieldMapping(fieldId, key) {
                // Remove previous mapping value if it was mapped to this key
                for (var k in mappings) {
                    if (mappings[k] == fieldId) {
                        delete mappings[k];
                    }
                }
                
                // Add new mapping
                mappings[key] = fieldId;
                
                // Update hidden mapping input
                apiMappingInput.value = JSON.stringify(mappings, null, 2);

                // Re-render dropzone
                var dropzone = document.querySelector('.field-mapping-dropzone[data-field-id="' + fieldId + '"]');
                if (dropzone) {
                    renderMappedKeyInZone(dropzone, fieldId, key);
                }
            }

            function renderMappedKeyInZone(dropzone, fieldId, key) {
                dropzone.innerHTML = '';
                var pill = document.createElement('div');
                pill.className = 'mapped-pill';
                pill.textContent = key;

                var clearBtn = document.createElement('button');
                clearBtn.className = 'btn-clear-mapping';
                clearBtn.innerHTML = '&times;';
                clearBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    delete mappings[key];
                    apiMappingInput.value = JSON.stringify(mappings, null, 2);
                    dropzone.innerHTML = 'Drop key here';
                });

                pill.appendChild(clearBtn);
                dropzone.appendChild(pill);
            }

            function getFlattenedKeys(obj, prefix = '') {
                let keys = [];
                for (let key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        let path = prefix ? prefix + '.' + key : key;
                        if (typeof obj[key] === 'object' && obj[key] !== null && !Array.isArray(obj[key])) {
                            keys = keys.concat(getFlattenedKeys(obj[key], path));
                        } else if (Array.isArray(obj[key]) && obj[key].length > 0 && typeof obj[key][0] === 'object') {
                            keys = keys.concat(getFlattenedKeys(obj[key][0], path + '[0]'));
                        } else {
                            keys.push(path);
                        }
                    }
                }
                return keys;
            }

            // Parse JSON button click handler
            document.getElementById('btnParseJson').addEventListener('click', function() {
                var jsonText = document.getElementById('apiSampleJson').value.trim();
                if (!jsonText) {
                    toastrs('Error', 'Please paste a sample JSON response.', 'error');
                    return;
                }
                try {
                    var parsed = JSON.parse(jsonText);
                    var keys = getFlattenedKeys(parsed);
                    if (keys.length === 0) {
                        toastrs('Error', 'No keys found in JSON response.', 'error');
                        return;
                    }
                    renderDraggablePills(keys);
                    toastrs('Success', 'JSON response parsed successfully. Drag the keys to target fields.', 'success');
                } catch(e) {
                    toastrs('Error', 'Invalid JSON format. Please correct it.', 'error');
                }
            });

            // Edit section btn click event
            document.querySelectorAll('.edit-section-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    var cols = this.getAttribute('data-columns');
                    var layout = this.getAttribute('data-layout-type');
                    var apiUrl = this.getAttribute('data-api-url');
                    var apiMethod = this.getAttribute('data-api-method');
                    var apiTriggerStage = this.getAttribute('data-api-trigger-stage-id');
                    var apiMapping = this.getAttribute('data-api-response-mapping');

                    form.action = '{{ url("lead-builder/section") }}/' + id;
                    methodInput.value = 'PUT';
                    modalTitle.innerText = '{{ __("Edit Section") }}';
                    nameInput.value = name;
                    colsInput.value = cols;
                    layoutInput.value = layout || 'section';
                    apiUrlInput.value = apiUrl || '';
                    apiMethodInput.value = apiMethod || 'GET';
                    apiTriggerStageInput.value = apiTriggerStage || '';
                    apiMappingInput.value = apiMapping || '';

                    // Reset JSON text area
                    document.getElementById('apiSampleJson').value = '';
                    
                    // Render visual mapping board
                    renderMappingBoard(apiMapping);

                    var bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                });
            });

            // Copy Modal Handler
            document.querySelectorAll('.copy-section-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var copyForm = document.getElementById('copySectionForm');
                    copyForm.action = '{{ url("lead-builder/section") }}/' + id + '/copy';
                    var copyModal = new bootstrap.Modal(document.getElementById('copySectionModal'));
                    copyModal.show();
                });
            });

            // Reset modal on close
            modal.addEventListener('hidden.bs.modal', function () {
                form.action = '{{ route("lead-builder.section.store") }}';
                methodInput.value = 'POST';
                modalTitle.innerText = '{{ __("Create Section") }}';
                nameInput.value = '';
                colsInput.value = 3;
                layoutInput.value = 'section';
                apiUrlInput.value = '';
                apiMethodInput.value = 'GET';
                apiTriggerStageInput.value = '';
                apiMappingInput.value = '';
                document.getElementById('apiSampleJson').value = '';
                document.getElementById('visualMappingContainer').style.display = 'none';
            });

            // Pipeline change listener
            var pipelineSelect = document.getElementById('builder_pipeline_select');
            if (pipelineSelect) {
                pipelineSelect.addEventListener('change', function() {
                    window.location.href = '{{ route("lead-builder.index") }}?pipeline_id=' + this.value;
                });
            }
        });
    </script>
@endpush
