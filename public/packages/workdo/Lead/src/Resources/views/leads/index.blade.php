@extends('layouts.main')

@section('page-title')
    {{ __('Manage Leads') }} @if ($pipeline)
        - {{ $pipeline->name }}
    @endif
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
@endpush

@section('page-breadcrumb')
    {{ __('Leads') }}
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    @permission('lead move')
        @if ($pipeline)
            <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
            <script>
                ! function(a) {
                    "use strict";
                    var t = function() {
                        this.$body = a("body")
                    };
                    t.prototype.init = function() {
                        a('[data-plugin="dragula"]').each(function() {
                            var t = a(this).data("containers"),
                                n = [];
                            if (t)
                                for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                            else n = [a(this)[0]];
                            var r = a(this).data("handleclass");
                            r ? dragula(n, {
                                moves: function(a, t, n) {
                                    return n.classList.contains(r)
                                }
                            }) : dragula(n).on('drop', function(el, target, source, sibling) {

                                var order = [];
                                $("#" + target.id + " > div").each(function() {
                                    order[$(this).index()] = $(this).attr('data-id');
                                });

                                var id = $(el).attr('data-id');

                                var old_status = $("#" + source.id).data('status');
                                var new_status = $("#" + target.id).data('status');
                                var stage_id = $(target).attr('data-id');
                                var pipeline_id = '{{ $pipeline->id }}';

                                $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div")
                                    .length);
                                $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div")
                                    .length);
                                $.ajax({
                                    url: '{{ route('leads.order') }}',
                                    type: 'POST',
                                    data: {
                                        lead_id: id,
                                        stage_id: stage_id,
                                        order: order,
                                        new_status: new_status,
                                        old_status: old_status,
                                        pipeline_id: pipeline_id,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function(data) {
                                        if (data.success) {
                                            toastrs('success', data.success,'success');
                                        } else {
                                            toastrs('error', data.error,'error');
                                        }
                                    }
                                });
                            });
                        })
                    }, a.Dragula = new t, a.Dragula.Constructor = t
                }(window.jQuery),
                function(a) {
                    "use strict";

                    a.Dragula.init()

                }(window.jQuery);
            </script>
        @endif
    @endpermission
    <script>
        $(document).on("change", "#change-pipeline select[name=default_pipeline_id]", function() {
            $('#change-pipeline').submit();
        });
    </script>


@endpush


@section('page-action')
    <div class="d-flex flex-wrap">
        @if ($pipeline)
            <div class="col-auto me-3">
                {{ Form::open(['route' => 'deals.change.pipeline', 'id' => 'change-pipeline']) }}
                {{ Form::select('default_pipeline_id', $pipelines, $pipeline->id, ['class' => 'form-control custom-form-select', 'id' => 'default_pipeline_id']) }}
                {{ Form::close() }}
            </div>
        @endif

        <div class="col-auto pt-2" style="display: inline-table;">
            @stack('addButtonHook')
        </div>
        @permission('lead import')
            <div class="col-auto pt-2">
                <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Lead Import') }}"
                    data-url="{{ route('lead.file.import') }}" data-toggle="tooltip" data-size="md" title="{{ __('Import') }}"><i
                        class="ti ti-file-import"></i>
                </a>
            </div>
        @endpermission

        <div class="col-auto pt-2">
            <a href="{{ route('leads.list') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('List View') }}"
                class="btn btn-sm btn-primary btn-icon me-2"><i class="ti ti-list"></i> </a>
        </div>
        @permission('lead create')
            <div class="col-auto pt-2">
                <a class="btn btn-sm btn-primary btn-icon " data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Create Lead') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Lead') }}"
                    data-url="{{ route('leads.create') }}"><i class="ti ti-plus text-white"></i></a>
            </div>
        @endpermission
    </div>
@endsection

@section('content')
    @include('lead::leads.filter_bar')
    @if ($pipeline)
        <div class="row">
            @php
                $lead_stages = $pipeline->leadStages;
                $active_stages = (array)request('stage_id');
                if(!empty($active_stages)) {
                    $lead_stages = $lead_stages->whereIn('id', $active_stages);
                }
                
                $json = [];
                foreach ($lead_stages as $lead_stage) {
                    if ($lead_stage->permissions()->can_view) {
                        $json[] = 'task-list-' . $lead_stage->id;
                    }
                }
            @endphp

            <div class="col-12">
                <div class="row kanban-wrapper horizontal-scroll-cards pt-3" data-plugin="dragula"
                    data-containers='{!! json_encode($json) !!}'>
                    @foreach ($lead_stages as $lead_stage)
                        @php($permissions = $lead_stage->permissions())
                        @if (!$permissions->can_view)
                            @continue
                        @endif
                        @php($leads = $lead_stage->lead(request(), 10))
                        <div class="col" id="progress">
                            <div class="card card-list {{ !$permissions->can_move ? 'locked-stage' : '' }}">
                                <div class="card-header">
                                    <div class="float-end">
                                        @if (!$permissions->can_move)
                                            <i class="ti ti-lock text-danger" data-bs-toggle="tooltip" title="{{__('Aap is stage par leads move nahi kar sakte')}}"></i>
                                        @endif
                                        <button class="btn btn-sm btn-primary btn-icon count" id="count-{{ $lead_stage->id }}">
                                            {{ $lead_stage->leadCount(request()) }}
                                        </button>
                                    </div>
                                    <h4 class="mb-0">{{ $lead_stage->name }}</h4>
                                </div>
                                <div id="task-list-{{ $lead_stage->id }}" data-id="{{ $lead_stage->id }}"
                                    class="card-body kanban-box kanban-scroll-load" data-offset="10" data-hasmore="{{ $lead_stage->leadCount(request()) > 10 ? 'true' : 'false' }}">
                                    @foreach ($leads as $lead)
                                        @include('lead::leads.card', ['lead' => $lead, 'permissions' => $permissions])
                                    @endforeach
                                    
                                    <div class="loading-sentinel d-flex justify-content-center p-2 d-none">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection

@push('css')
<style>
    .kanban-wrapper {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        padding: 10px 0;
        align-items: flex-start;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f1f5f9;
        gap: 0 !important;
    }
    .kanban-wrapper .col {
        flex: 0 0 340px !important;
        min-width: 340px !important;
        margin-right: 0 !important;
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
    .card-list {
        background: transparent !important;
        border-radius: 8px !important;
        border: none !important;
        box-shadow: none !important;
    }
    .card-list .card-header {
        background: transparent !important;
        padding: 1rem !important;
        border-bottom: none !important;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .kanban-box {
        max-height: calc(100vh - 280px);
        overflow-y: auto !important;
        min-height: 300px;
        padding: 10px !important;
    }
    
    .grid-card {
        border: none !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    /* Custom Scrollbar */
    .kanban-box::-webkit-scrollbar { width: 4px; }
    .kanban-box::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
    .kanban-box::-webkit-scrollbar-track { background: transparent; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var loadingStages = {};

        function loadMoreLeads(stage_id) {
            if (loadingStages[stage_id]) return;
            
            var container = $('#task-list-' + stage_id);
            var hasMore = container.attr('data-hasmore') === 'true';
            
            if (!hasMore) return;

            loadingStages[stage_id] = true;
            var offset = parseInt(container.attr('data-offset'));
            var sentinel = container.find('.loading-sentinel');
            sentinel.removeClass('d-none');

            $.ajax({
                url: "{{ route('leads.kanban.batch') }}",
                type: 'GET',
                data: {
                    stage_id: stage_id,
                    offset: offset,
                    limit: 50,
                    @if(request()->all())
                        @foreach(request()->all() as $key => $value)
                            @if($key != 'stage_id')
                                @if(is_array($value))
                                    @foreach($value as $v)
                                        "{{ $key }}[]": "{{ $v }}",
                                    @endforeach
                                @else
                                    "{{ $key }}": "{{ $value }}",
                                @endif
                            @endif
                        @endforeach
                    @endif
                },
                success: function(data) {
                    if (data.success) {
                        sentinel.before(data.html);
                        container.attr('data-offset', offset + data.count);
                        container.attr('data-hasmore', data.has_more ? 'true' : 'false');
                        
                        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                            var tooltipTriggerList = [].slice.call(container[0].querySelectorAll('[data-bs-toggle="tooltip"]'));
                            tooltipTriggerList.map(function (tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });
                        }
                    }
                    loadingStages[stage_id] = false;
                    sentinel.addClass('d-none');
                    checkAndLoad(stage_id);
                },
                error: function() {
                    loadingStages[stage_id] = false;
                    sentinel.addClass('d-none');
                }
            });
        }

        function checkAndLoad(stage_id) {
            var container = $('#task-list-' + stage_id);
            if (container.attr('data-hasmore') !== 'true') return;

            if (container[0].scrollHeight <= container.innerHeight() + 100) {
                loadMoreLeads(stage_id);
            }
        }

        $('.kanban-scroll-load').each(function() {
            var stage_id = $(this).attr('data-id');
            checkAndLoad(stage_id);
        });

        $('.kanban-box').on('scroll', function() {
            var container = $(this);
            if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 300) {
                loadMoreLeads(container.attr('data-id'));
            }
        });
    });
</script>
@endpush
