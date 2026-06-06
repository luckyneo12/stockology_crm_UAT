@extends('layouts.main')

@section('page-title')
    {{ __('Leads') }}
@endsection

@php
    $lead_stages = [];
    $json = [];
    if ($pipeline) {
        $lead_stages = $pipeline->leadStages;
        $active_stages = (array) request('stage_id');
        if (!empty($active_stages)) {
            $lead_stages = $lead_stages->whereIn('id', $active_stages);
        }
        foreach ($lead_stages as $lead_stage) {
            if ($lead_stage->permissions()->can_view) {
                $json[] = 'task-list-' . $lead_stage->id;
            }
        }
    }
@endphp



@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    @permission('lead move')
    @if ($pipeline)
        <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script>
            (function ($) {
                'use strict';

                // Collect all visible kanban column containers
                var containerIds = {!! json_encode($json ?? []) !!};

                // Robust Fallback: if $json is empty, try reading from the wrapper attribute
                if (!containerIds || containerIds.length === 0) {
                    var $wrapper = $('[data-plugin="dragula"]');
                    if ($wrapper.length && $wrapper.attr('data-containers')) {
                        try {
                            containerIds = JSON.parse($wrapper.attr('data-containers'));
                        } catch (e) { console.error("Kanban containers parse error", e); }
                    }
                }

                var dragulaContainers = (containerIds || []).map(function (id) {
                    return document.getElementById(id);
                }).filter(Boolean);

                if (dragulaContainers.length === 0) {
                    console.warn("Kanban: No containers found for Dragula");
                    return;
                }

                var drake = dragula(dragulaContainers, {
                    moves: function (el, source, handle, sibling) {
                        if ($(el).attr('data-locked') === '1') {
                            return false;
                        }
                        if ($(source).parent().hasClass('locked-stage')) {
                            return false;
                        }
                        return true;
                    },
                    accepts: function (el, target, source, sibling) {
                        if ($(target).parent().hasClass('locked-stage')) {
                            return false;
                        }
                        return true;
                    }
                });

                // --- Horizontal Auto-scrolling Logic ---
                var scrollInterval;
                var $kanbanWrapper = $('.kanban-wrapper');
                var scrollSpeed = 0;

                function startAutoScroll(direction) {
                    stopAutoScroll();
                    scrollSpeed = (direction === 'right') ? 15 : -15;
                    scrollInterval = setInterval(function () {
                        var curScroll = $kanbanWrapper.scrollLeft();
                        $kanbanWrapper.scrollLeft(curScroll + scrollSpeed);
                    }, 20);
                }

                function stopAutoScroll() {
                    if (scrollInterval) {
                        clearInterval(scrollInterval);
                        scrollInterval = null;
                    }
                }

                drake.on('cloned', function (el, target, source) {
                    $(document).on('mousemove.kanbanDrag', function (e) {
                        var edgeSize = 100; // Pixels from edge to trigger scroll
                        var winWidth = $(window).width();
                        var mouseX = e.clientX;

                        if (mouseX > winWidth - edgeSize) {
                            startAutoScroll('right');
                        } else if (mouseX < edgeSize) {
                            startAutoScroll('left');
                        } else {
                            stopAutoScroll();
                        }
                    });
                });

                drake.on('dragend', function (el) {
                    $(document).off('mousemove.kanbanDrag');
                    stopAutoScroll();
                });

                drake.on('drop', function (el, target, source, sibling) {
                    stopAutoScroll();
                    if (!target) return;

                    // Build ordered array of lead IDs from target column (skip sentinel)
                    var order = [];
                    $('#' + target.id + ' > [data-id]').each(function (i) {
                        order[i] = $(this).attr('data-id');
                    });

                    var id = $(el).attr('data-id');
                    var old_status = $('#' + source.id).attr('data-status');
                    var new_status = $('#' + target.id).attr('data-status');
                    var stage_id = $(target).attr('data-id');
                    var pipeline_id = '{{ $pipeline->id }}';
                    var sameColumn = (source.id === target.id);

                    // Optimistic count (server corrects it after AJAX)
                    if (!sameColumn) {
                        var srcCount = parseInt($('#' + source.id).parent().find('.count').text()) || 0;
                        var tgtCount = parseInt($('#' + target.id).parent().find('.count').text()) || 0;
                        $('#' + source.id).parent().find('.count').text(Math.max(0, srcCount - 1));
                        $('#' + target.id).parent().find('.count').text(tgtCount + 1);

                        // Brief highlight on dropped card
                        $(el).css({ outline: '2px solid #198754', background: 'rgba(25,135,84,0.06)' });
                        setTimeout(function () { $(el).css({ outline: '', background: '' }); }, 2500);
                    }

                    // Forward active URL filter params so server returns filtered count
                    var urlParams = new URLSearchParams(window.location.search);
                    var filterData = {
                        lead_id: id,
                        stage_id: stage_id,
                        order: order,
                        new_status: new_status,
                        old_status: old_status,
                        pipeline_id: pipeline_id,
                        _token: '{{ csrf_token() }}'
                    };
                    ['responsible_person', 'source_id', 'start_date', 'end_date',
                        'search', 'created_by', 'modified_by', 'department_id', 'designation_id'
                    ].forEach(function (key) {
                        var vals = urlParams.getAll(key + '[]');
                        if (vals.length > 0) { filterData[key] = vals; }
                        else { var v = urlParams.get(key); if (v) filterData[key] = v; }
                    });

                    $.ajax({
                        url: '{{ route('leads.order') }}',
                        type: 'POST',
                        traditional: true,
                        data: filterData,
                        success: function (data) {
                            if (typeof window.kanbanMarkSelfMoved === 'function') {
                                window.kanbanMarkSelfMoved(id);
                            }
                            // Update counts using explicit IDs (robust)
                            if (data.old_stage_count !== undefined) {
                                $('#count-' + old_status).text(data.old_stage_count);
                            }
                            if (data.new_stage_count !== undefined) {
                                $('#count-' + stage_id).text(data.new_stage_count);
                            }
                            if (data.success) {
                                show_toastr('Success', data.success, 'success');
                            } else if (data.error) {
                                show_toastr('Error', data.error, 'error');
                                drake.cancel(true);
                            }
                        }
                    });
                });

            }(window.jQuery));
        </script>
    @endif
    @endpermission

    <script>
        // Real-time Kanban polling — shows other users' stage moves
        (function ($) {
            var POLL_INTERVAL = 40000; // Increased to 40s to reduce server load
            var IDLE_TIMEOUT = 300000; // 5 minutes
            var pipelineId = '{{ $pipeline ? $pipeline->id : 0 }}';
            if (!pipelineId || pipelineId === '0') return;

            var lastTs = Math.floor(Date.now() / 1000);
            var selfMovedIds = {};
            var pollingActive = true;
            var lastActivity = Date.now();

            // Track activity to detect idle state
            $(document).on('mousemove keydown scroll click', function () {
                lastActivity = Date.now();
                if (!pollingActive && !document.hidden) {
                    pollingActive = true;
                    pollChanges();
                }
            });

            window.kanbanMarkSelfMoved = function (leadId) {
                selfMovedIds[String(leadId)] = true;
                setTimeout(function () { delete selfMovedIds[String(leadId)]; }, 10000);
            };

            function flashCard($el) {
                $el.css({ outline: '2px solid #198754', background: 'rgba(25,135,84,0.07)', transition: 'all 0.3s' });
                setTimeout(function () { $el.css({ outline: '', background: '' }); }, 2500);
            }

            function applyServerCounts(counts) {
                if (!counts) return;
                Object.keys(counts).forEach(function (sid) { $('#count-' + sid).text(counts[sid]); });
            }

            function pollChanges() {
                if (!pollingActive || document.hidden) return;

                // Idle Detection
                if (Date.now() - lastActivity > IDLE_TIMEOUT) {
                    pollingActive = false;
                    console.log("Kanban: Polling paused due to inactivity.");
                    return;
                }

                var checkTs = lastTs;
                lastTs = Math.floor(Date.now() / 1000);

                $.ajax({
                    url: '{{ route('leads.changes.since') }}',
                    data: (function () {
                        var params = new URLSearchParams(window.location.search);
                        var data = { ts: checkTs, pipeline_id: pipelineId };
                        params.forEach(function (value, key) {
                            if (key !== 'ts' && key !== 'pipeline_id') {
                                data[key] = value;
                            }
                        });
                        return data;
                    })(),
                    success: function (resp) {
                        if (!resp) return;
                        if (resp.now_ts) { lastTs = resp.now_ts; }
                        if (!resp.changes || !resp.changes.length) return;
                        applyServerCounts(resp.counts);

                        resp.changes.forEach(function (change) {
                            var leadId = String(change.id);
                            var newStage = String(change.stage_id);
                            var leadName = change.name;

                            if (selfMovedIds[leadId]) return;

                            var $card = $('[data-id="' + leadId + '"].image-matched-card');
                            var $targetBox = $('#task-list-' + newStage);

                            if ($card.length) {
                                var curStage = $card.closest('.kanban-box').attr('data-id');

                                if (curStage === newStage) {
                                    // RE-SORT: Move to top of SAME column if new activity occurred
                                    var $targetBox = $('#task-list-' + newStage);
                                    if ($targetBox.length) {
                                        $targetBox.find('.image-matched-card').first().before($card);
                                        flashCard($card);
                                    }
                                    return;
                                }

                                $card.fadeOut(200, function () {
                                    if ($targetBox.length) {
                                        var $s = $targetBox.find('.loading-sentinel');
                                        $s.length ? $s.before($card) : $targetBox.prepend($card);
                                        $card.fadeIn(280, function () { flashCard($card); });
                                        if (typeof show_toastr === 'function') {
                                            show_toastr('{{ __("Stage Changed") }}', leadName + ' {{ __("moved") }}', 'info');
                                        }
                                    } else { $card.remove(); }
                                });
                            }
                            else if ($targetBox.length) {
                                var $hint = $('<div class="text-center py-1 realtime-hint"><small class="text-muted" style="font-size:0.7rem;"><i class="ti ti-refresh me-1"></i>{{ __("Refresh to see new activity") }}</small></div>');
                                $targetBox.find('.realtime-hint').remove();
                                $targetBox.prepend($hint);
                                setTimeout(function () { $hint.fadeOut(400, function () { $(this).remove(); }); }, 4000);
                            }
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 401 || xhr.status === 419) {
                            pollingActive = false;
                            console.error("Kanban Polling: Session expired.");
                            if (typeof show_toastr === 'function') {
                                show_toastr('Session Expired', 'Please refresh or log in again to continue seeing updates.', 'warning');
                            }
                        }
                    }
                });
            }

            setTimeout(function () { pollChanges(); setInterval(pollChanges, POLL_INTERVAL); }, 3000);

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    pollingActive = true;
                    lastActivity = Date.now();
                    lastTs = Math.floor(Date.now() / 1000) - 25;
                    pollChanges();
                }
            });
        }(window.jQuery));
    </script>

    <script>
        $(document).on('change', '#change-pipeline select[name=default_pipeline_id]', function () {
            $('#change-pipeline').submit();
        });
    </script>
@endpush

@section('page-action')
@endsection

@section('content')
@include('lead::leads.filter_bar')
@if ($pipeline)
<div class="row">
    <div class="col-12">
        <div class="row kanban-wrapper horizontal-scroll-cards pt-3" data-plugin="dragula"
            data-containers='{!! json_encode($json) !!}'>
            @foreach ($lead_stages as $lead_stage)
            @php($permissions = $lead_stage->permissions())
            @if (!$permissions->can_view)
                @continue
            @endif
            <div class="col" id="progress">
                <div class="card card-list {{ !$permissions->can_move ? 'locked-stage' : '' }}">
                    <div class="card-header">
                        <div class="float-end">
                            @if (!$permissions->can_move)
                                <i class="ti ti-lock text-danger" data-bs-toggle="tooltip"
                                    title="{{__('Aap is stage par leads move nahi kar sakte')}}"></i>
                            @endif
                            <button class="btn btn-sm btn-primary btn-icon count" id="count-{{ $lead_stage->id }}">
                                {{ $lead_stage->leadCount(request()) }}
                            </button>
                        </div>
                        <h6 class="mb-0">{{ $lead_stage->name }}</h6>
                    </div>
                    <div id="task-list-{{ $lead_stage->id }}" data-id="{{ $lead_stage->id }}"
                        data-status="{{ $lead_stage->id }}" class="card-body kanban-box kanban-scroll-load"
                        data-offset="0" data-hasmore="false">
                        
                        <!-- Initial Loading Spinner -->
                        <div class="initial-kanban-loader d-flex flex-column align-items-center justify-content-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <small style="font-size: 0.75rem;">{{ __('Loading...') }}</small>
                        </div>

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
        .dash-header {
            position: fixed !important;
        }

        .page-header {
            display: none !important;
        }

        .leads-filter-bar-row {
            position: sticky !important;
            top: 124px !important;
            z-index: 1010 !important;
            background: #ffffff !important;
            padding: 10px 20px !important;
            margin-top: 0 !important;
            margin-bottom: 15px !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-bottom-left-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
        }

        .kanban-wrapper {
            display: flex !important;
            flex-flow: row nowrap !important;
            overflow-x: auto !important;
            padding-bottom: 10px;
            align-items: stretch;
            /* Make all columns equal height */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f1f5f9;
            gap: 0 !important;
            height: calc(100vh - 190px) !important;
            min-height: calc(100vh - 190px) !important;
        }

        .kanban-wrapper > div {
            flex: 0 0 310px !important;
            min-width: 310px !important;
            max-width: 310px !important;
            margin-right: 0 !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            display: flex;
            flex-direction: column;
        }

        .card-list {
            background: #f8f9fd !important;
            border-radius: 10px !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            box-shadow: none !important;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: visible !important;
            /* MUST be visible for dropdowns */
        }

        .card-list .card-header {
            padding: 8px 12px !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
            z-index: 20;
            /* Higher than lead cards (10-15) */
            border-radius: 10px 10px 0 0 !important;
        }

        .kanban-box {
            flex: 1 1 auto;
            overflow-y: auto !important;
            overflow-x: visible !important;
            /* CRITICAL: Allow dropdowns to pop out */
            height: calc(100vh - 250px) !important;
            max-height: calc(100vh - 250px) !important;
            min-height: 200px;
            padding: 6px !important;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .grid-card {
            border: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        /* Custom Scrollbar */
        .kanban-box::-webkit-scrollbar {
            width: 6px;
        }

        /* Slightly wider for better usability */
        .kanban-box::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
            border: 1px solid transparent;
            background-clip: padding-box;
        }

        .kanban-box::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .kanban-box::-webkit-scrollbar-track {
            background: transparent;
        }

        .kanban-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .kanban-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .locked-lead {
            opacity: 0.65;
            background-color: #f8fafc !important;
            border: 1px dashed #cbd5e1 !important;
            cursor: not-allowed !important;
            user-select: none;
            box-shadow: none !important;
        }
        .locked-lead:hover {
            box-shadow: none !important;
            transform: none !important;
            outline: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Load initial kanban column data sequentially to avoid overloading the server
            var columns = $('.kanban-box').toArray();
            function loadNextColumn(index) {
                if (index >= columns.length) return;
                var container = $(columns[index]);
                var stageId = container.attr('data-id');
                var loader = container.find('.initial-kanban-loader');
                var requestData = "stage_id=" + encodeURIComponent(stageId) + "&offset=0&limit=10";
                if (window.location.search) {
                    requestData += "&" + window.location.search.substring(1);
                }

                $.ajax({
                    url: "{{ route('leads.kanban.batch') }}",
                    type: 'GET',
                    data: requestData,
                    success: function (data) {
                        loader.remove();
                        if (data.success) {
                            container.find('.loading-sentinel').before(data.html);
                            container.attr('data-offset', data.count);
                            container.attr('data-hasmore', data.has_more ? 'true' : 'false');

                            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                                var tooltipTriggerList = [].slice.call(container[0].querySelectorAll('[data-bs-toggle="tooltip"]'));
                                tooltipTriggerList.map(function (tooltipTriggerEl) {
                                    return new bootstrap.Tooltip(tooltipTriggerEl);
                                });
                            }
                        }
                        loadNextColumn(index + 1);
                    },
                    error: function () {
                        loader.html('<small class="text-danger"><i class="ti ti-alert-circle"></i> Failed to load</small>');
                        loadNextColumn(index + 1);
                    }
                });
            }
            loadNextColumn(0);

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

                var requestData = "stage_id=" + encodeURIComponent(stage_id) + "&offset=" + encodeURIComponent(offset) + "&limit=50";
                if (window.location.search) {
                    requestData += "&" + window.location.search.substring(1);
                }

                $.ajax({
                    url: "{{ route('leads.kanban.batch') }}",
                    type: 'GET',
                    data: requestData,
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

        $('.kanban-scroll-load').each(function () {
            var stage_id = $(this).attr('data-id');
            checkAndLoad(stage_id);
        });

        $('.kanban-box').on('scroll', function () {
            var container = $(this);
            if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 300) {
                loadMoreLeads(container.attr('data-id'));
            }
        });

        // --- Real-Time Live Search for Leads Kanban ---
        const $leadSearch = $('#lead_search');
        let searchTimer;

        $leadSearch.on('keyup', function () {
            clearTimeout(searchTimer);
            const value = $(this).val().toLowerCase().trim();

            // Small delay to prevent too many cycles 
            searchTimer = setTimeout(() => {
                let totalVisible = 0;

                $('.kanban-box').each(function () {
                    const $box = $(this);
                    const stageId = $box.data('id');
                    let stageVisibleCount = 0;

                    $box.find('.image-matched-card').each(function () {
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
    @include('lead::leads.click_to_call_script')
@endpush