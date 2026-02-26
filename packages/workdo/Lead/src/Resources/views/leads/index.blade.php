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

                                var sourceCount = parseInt($("#" + source.id).parent().find('.count').text());
                                var targetCount = parseInt($("#" + target.id).parent().find('.count').text());
                                $("#" + source.id).parent().find('.count').text(sourceCount - 1);
                                $("#" + target.id).parent().find('.count').text(targetCount + 1);

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
                                        if(data.old_stage_count !== undefined) {
                                            $("#" + source.id).parent().find('.count').text(data.old_stage_count);
                                        }
                                        if(data.new_stage_count !== undefined) {
                                            $("#" + target.id).parent().find('.count').text(data.new_stage_count);
                                        }

                                        if (data.success) {
                                            show_toastr('Success', data.success, 'success');
                                        } else {
                                            show_toastr('Error', data.error, 'error');
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
                                    <h6 class="mb-0">{{ $lead_stage->name }}</h6>
                                </div>
                                <div id="task-list-{{ $lead_stage->id }}" data-id="{{ $lead_stage->id }}" data-status="{{ $lead_stage->id }}"
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
        padding-bottom: 20px;
        align-items: stretch; /* Make all columns equal height */
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f1f5f9;
        gap: 0 !important;
        min-height: calc(100vh - 250px);
    }
    .kanban-wrapper .col {
        flex: 0 0 280px !important;
        min-width: 280px !important;
        margin-right: 0 !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
        display: flex;
        flex-direction: column;
    }
    .card-list {
        background: #f8f9fd !important; 
        border-radius: 12px !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        box-shadow: none !important;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: visible !important; /* MUST be visible for dropdowns */
    }
    .card-list .card-header {
       
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid rgba(0,0,0,0.03) !important;
        
        z-index: 20; /* Higher than lead cards (10-15) */
        border-radius: 12px 12px 0 0 !important;
    }
    .kanban-box {
        flex: 1 1 auto;
        overflow-y: auto !important;
        overflow-x: visible !important; /* CRITICAL: Allow dropdowns to pop out */
        max-height: calc(100vh - 320px);
        min-height: 200px;
        padding: 12px !important;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .grid-card {
        border: none !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    /* Custom Scrollbar */
    .kanban-box::-webkit-scrollbar { width: 6px; } /* Slightly wider for better usability */
    .kanban-box::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; border: 1px solid transparent; background-clip: padding-box; }
    .kanban-box::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
    .kanban-box::-webkit-scrollbar-track { background: transparent; }
    
    .kanban-wrapper::-webkit-scrollbar { height: 8px; }
    .kanban-wrapper::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
    .kanban-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
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

        // --- Real-Time Live Search for Leads Kanban ---
        const $leadSearch = $('#lead_search');
        let searchTimer;

        $leadSearch.on('keyup', function() {
            clearTimeout(searchTimer);
            const value = $(this).val().toLowerCase().trim();
            
            // Small delay to prevent too many cycles 
            searchTimer = setTimeout(() => {
                let totalVisible = 0;
                
                $('.kanban-box').each(function() {
                    const $box = $(this);
                    const stageId = $box.data('id');
                    let stageVisibleCount = 0;
                    
                    $box.find('.image-matched-card').each(function() {
                        const $card = $(this);
                        const cardText = $card.text().toLowerCase();
                        
                        if (cardText.indexOf(value) > -1) {
                            $card.removeClass('d-none').css('opacity', '1');
                            stageVisibleCount++;
                            totalVisible++;
                        } else {
                            $card.addClass('d-none');
                        }
                    });
                    
                    // Update stage count badge dynamically
                    const $countBadge = $('#count-' + stageId);
                    if (value === "") {
                        // Reset to original count if search is empty (or keep it dynamic?)
                        // Usually better to show dynamic count during search.
                        $countBadge.text(stageVisibleCount);
                    } else {
                        $countBadge.text(stageVisibleCount);
                    }
                });

                // Visual feedback if no results found globally
                if (totalVisible === 0 && value !== "") {
                    if ($('#no-leads-found').length === 0) {
                        $('.kanban-wrapper').append('<div id="no-leads-found" class="col-12 text-center p-5"><div class="text-muted"><i class="ti ti-search-off f-30"></i><p class="mt-2">{{ __("No leads matching your search were found in the current view.") }}</p></div></div>');
                    }
                } else {
                    $('#no-leads-found').remove();
                }
            }, 150);
        });
    });
</script>
@endpush
