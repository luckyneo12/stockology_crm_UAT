@extends('layouts.main')

@section('page-title')
    {{ $lead->name }}
@endsection
@push('css')
    <style>
        .nav-tabs .nav-link-tabs.active {
            background: none;
        }
        /* Modern UI Enhancements */
        :root {
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-bg: rgba(255, 255, 255, 0.1);
        }
        
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        .hero-gradient {
            background: linear-gradient(135deg, #054734 0%, #157e3f 100%) !important; /* Deep Green Gradient */
            position: relative;
            overflow: hidden;
        }
        
        /* Modern Green Theme Overrides */
        .text-primary, .text-info { color: #198754 !important; }
        .bg-primary, .bg-info { background-color: #198754 !important; }
        .btn-primary { background-color: #198754 !important; border-color: #198754 !important; }
        .btn-info { background-color: #0d6efd !important; border-color: #0d6efd !important; } /* Keep info distinct or make teal? Let's make it Teal */
        .bg-info-subtle { background-color: rgba(32, 201, 151, 0.1) !important; color: #20c997 !important; }
        .text-info { color: #20c997 !important; }
        
        .badge.bg-primary { background-color: #198754 !important; }
        .badge.bg-info { background-color: #20c997 !important; }

        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
        
        /* Task & Section Styles */
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #198754;
        }
        
        .task-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .task-item:hover {
            background-color: #f8fdf9;
            border-left-color: #198754;
        }
        .task-checkbox {
            width: 1.25em;
            height: 1.25em;
            border-radius: 50%; /* Rounded checkbox */
        }
        
        .hero-pattern::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .card-modern {
            border: 1px solid rgba(0,0,0,0.05);
            background: #fff;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
            border-color: rgba(var(--bs-primary-rgb), 0.2);
        }

        .icon-shape-lg {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 28px;
            transition: all 0.3s ease;
        }
        
        .card-modern:hover .icon-shape-lg {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-label {
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            opacity: 0.6;
        }
        
        .progress-modern {
            height: 12px;
            background-color: #edf2f7;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .progress-bar-modern {
            height: 100%;
            background: linear-gradient(90deg, #198754 0%, #20c997 100%);
            border-radius: 10px;
            position: relative;
            box-shadow: 0 2px 5px rgba(25, 135, 84, 0.2);
        }
        .progress-bar-modern::after {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            opacity: 0.3;
        }
        
        .list-group-item-action {
            border-radius: 8px !important;
            margin-bottom: 4px;
            border: 1px solid transparent;
        }
        .list-group-item-action.active {
            background: linear-gradient(90deg, rgba(var(--bs-primary-rgb), 0.1), transparent) !important;
            border-left: 4px solid var(--bs-primary) !important;
            color: var(--bs-primary) !important;
            font-weight: 700;
        }
        /* Timeline CSS */
        .timeline-vertical {
            position: relative;
            padding-left: 2rem;
            border-left: 2px solid #e9ecef;
            margin-left: 10px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        .timeline-dot {
            position: relative; /* Fixed from absolute to relative if needed, but absolute is correct for timeline. Keeping absolute as per previous */
            position: absolute;
            left: -33px;
            top: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
        }
        
        /* Sidebar Styling */
        #useradd-sidenav .list-group-item {
            border-radius: 0.5rem !important;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease-in-out;
            color: #526477;
            font-weight: 500;
            border: 1px solid transparent;
        }
        #useradd-sidenav .list-group-item:hover {
            background-color: rgba(25, 135, 84, 0.08);
            color: #198754;
            transform: translateX(5px);
        }
        #useradd-sidenav .list-group-item.active {
            background-color: #198754 !important;
            color: #fff !important;
            border-color: #198754;
            box-shadow: 0 4px 6px rgba(25, 135, 84, 0.2);
        }
        #useradd-sidenav .list-group-item.active .ti {
            color: #fff !important;
        }
        .bg-success { background-color: #198754 !important; }
        .bg-danger { background-color: #dc3545 !important; }
        .bg-warning { background-color: #ffc107 !important; }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/workdo/Lead/src/Resources/assets/css/dropzone.min.css') }}">
@endpush

@php
    $lead->activities = $lead->activities->load('user');
    $lead->discussions = $lead->discussions->load('user');
    $lead->calls = $lead->calls->load('getLeadCallUser');
@endphp

@push('scripts')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
    <script src="{{ asset('packages/workdo/Lead/src/Resources/assets/js/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

    <script>
        @if (!Auth::user()->hasRole('client'))
            Dropzone.autoDiscover = false;


            if ($("#dropzonewidget2").length > 0) {
                myDropzone2 = new Dropzone("#dropzonewidget2", {
                    maxFiles: 20,
                    maxFilesize: 20,
                    parallelUploads: 1,
                    acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
                    url: "{{ route('leads.file.upload', $lead->id) }}",
                    success: function(file, response) {
                        if (response.is_success) {
                            dropzoneBtn(file, response);
                        } else {
                            myDropzone2.removeFile(file);
                            toastrs('Error', response.error, 'error');
                        }
                    },
                    error: function(file, response) {
                        myDropzone2.removeFile(file);
                        if (response.error) {
                            toastrs('Error', response.error, 'error');
                        } else {
                            toastrs('Error', response, 'error');
                        }
                    }
                });
                myDropzone2.on("sending", function(file, xhr, formData) {
                    formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
                    formData.append("lead_id", {{ $lead->id }});
                });
            }

            function dropzoneBtn(file, response) {
                var download = document.createElement('a');
                download.setAttribute('href', response.download);
                download.setAttribute('class', "btn btn-sm btn-primary m-1");
                download.setAttribute('data-toggle', "tooltip");
                download.setAttribute('download', file.name);
                download.setAttribute('data-original-title', "{{ __('Download') }}");
                download.innerHTML = "<i class='ti ti-download'></i>";

                var del = document.createElement('a');
                del.setAttribute('href', response.delete);
                del.setAttribute('class', "btn btn-sm btn-danger mx-1");
                del.setAttribute('data-toggle', "tooltip");
                del.setAttribute('data-original-title', "{{ __('Delete') }}");
                del.innerHTML = "<i class='ti ti-trash'></i>";

                del.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm("Are you sure ?")) {
                        var btn = $(this);
                        $.ajax({
                            url: btn.attr('href'),
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'DELETE',
                            success: function(response) {
                                if (response.is_success) {
                                    btn.closest('.dz-image-preview').remove();
                                    btn.closest('.dz-file-preview').remove();
                                    toastrs('Success', response.success, 'success');
                                } else {
                                    toastrs('Error', response.error, 'error');
                                }
                            },
                            error: function(response) {
                                response = response.responseJSON;
                                if (response.error) {
                                    toastrs('Error', response.error, 'error');
                                } else {
                                    toastrs('Error', response, 'error');
                                }
                            }
                        })
                    }
                });

                var html = document.createElement('div');
                html.appendChild(download);
                @if (!Auth::user()->hasRole('client'))
                    @permission('lead edit')
                        html.appendChild(del);
                    @endpermission
                @endif

                file.previewTemplate.appendChild(html);
            }

            if (typeof myDropzone2 !== 'undefined') {
                @foreach ($lead->files as $file)

                    // Create the mock file:
                    var mockFile2 = {
                        name: "{{ $file->file_name }}",
                        size: "{{ get_size(get_file($file->file_path)) }}"
                    };
                    // Call the default addedfile event handler
                    myDropzone2.emit("addedfile", mockFile2);
                    // And optionally show the thumbnail of the file:
                    myDropzone2.emit("thumbnail", mockFile2, "{{ get_file($file->file_path) }}");
                    myDropzone2.emit("complete", mockFile2);

                    dropzoneBtn(mockFile2, {
                        download: "{{ get_file($file->file_path) }}",
                        delete: "{{ route('leads.file.delete', [$lead->id, $file->id]) }}"
                    });
                @endforeach
            }
        @endif

        @permission('lead task edit')
            $(document).on("click", ".task-checkbox", function() {
                var chbox = $(this);
                var lbl = chbox.parent().parent().find('label');

                $.ajax({
                    url: chbox.attr('data-url'),
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: chbox.val()
                    },
                    type: 'PUT',
                    success: function(response) {
                        if (response.is_success) {
                            chbox.val(response.status);
                            if (response.status == 'done') {
                                lbl.addClass('strike');
                                lbl.find('.badge').removeClass('bg-warning bg-danger').addClass('bg-success');
                            } else if (response.status == 'overdue') {
                                lbl.removeClass('strike');
                                lbl.find('.badge').removeClass('bg-success bg-warning').addClass('bg-danger');
                            } else {
                                lbl.removeClass('strike');
                                lbl.find('.badge').removeClass('bg-success bg-danger').addClass('bg-warning');
                            }
                            lbl.find('.badge').html(response.status_label);

                            toastrs('Success', response.success, 'success');
                        } else {
                            toastrs('Error', response.error, 'error');
                        }
                    },
                    error: function(response) {
                        response = response.responseJSON;
                        if (response.is_success) {
                            toastrs('Error', response.error, 'error');
                        } else {
                            toastrs('Error', response, 'error');
                        }
                    }
                })
            });
        @endpermission

        $(document).ready(function() {
            var tab = 'general';
            @if ($tab = Session::get('status'))
                var tab = '{{ $tab }}';
            @endif
            $("#myTab2 .nav-link-tabs[href='#" + tab + "']").trigger("click");
        });
    </script>

    @if (Laratrust::hasPermission('lead edit'))
        <script>
            $(document).ready(function() {
                $('.summernote').on('summernote.blur', function() {
                    $.ajax({
                        url: "{{ route('leads.note.store', $lead->id) }}",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            notes: $(this).val()
                        },
                        type: 'POST',
                        success: function(response) {
                            if (response.is_success) {} else {
                                toastrs('Error', response.error, 'error');
                            }
                        },
                        error: function(response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                toastrs('Error', response.error, 'error');
                            } else {
                                toastrs('Error', response, 'error');
                            }
                        }
                    })
                });
            });
        </script>
    @else
        <script>
            $('.summernote').hide('disable');
        </script>
    @endif
    <script>
        if ($(".summernote").length > 0) {
            $('.summernote').summernote({
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                    ['list', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'unlink']],
                ],
                height: 230,
            });
        }
    </script>
    {{-- Custom field description --}}
    <script>
        document.querySelectorAll('.description-container').forEach(function(container) {
            container.addEventListener('click', function() {
                var shortDescription = container.querySelector('.shortDescription');
                var fullDescription = container.querySelector('.fullDescription');

                if (shortDescription.style.display === 'block' || shortDescription.style.display === '') {
                    shortDescription.style.display = 'none';
                    fullDescription.style.display = 'block';
                } else {
                    shortDescription.style.display = 'block';
                    fullDescription.style.display = 'none';
                }
            });
        });

        // REVEAL FIELD SCRIPT
        $(document).on('click', '.reveal-link', function(e) {
            e.preventDefault();
            var btn = $(this);
            var url = btn.data('url');
            var targetId = btn.data('target');
            var target = $(targetId);
            
            $.ajax({
                url: url,
                success: function(res) {
                        if(res.is_success) {
                            target.text(res.value);
                            target.removeClass('masked-value');
                            btn.remove();
                        } else {
                            toastrs('Error', res.error, 'error');
                        }
                },
                error: function() {
                    toastrs('Error', 'Permission Denied', 'error');
                }
            });
        });
    </script>

@endpush

@section('page-breadcrumb')
    {{ __('Leads') }},
    {{ $lead->name }}
@endsection


@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('lead edit')
            <a class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Labels') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Label') }}"
                data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}"><i class="ti ti-tag text-white"></i></a>
            <a class="btn btn-sm btn-info btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Edit') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Lead') }}"
                data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}"><i class="ti ti-pencil text-white"></i></a>
        @endpermission

        @permission('lead to deal convert')
            @if (!empty($deal))
                <a href="@permission('deal show') @if ($deal->is_active) {{ route('deals.show', $deal->id) }} @else # @endif @else # @endpermission"
                    class="btn btn-sm btn-primary btn-icon " data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Already Converted To Deal') }}"><i class="ti ti-exchange text-white"></i></a>
            @else
                <a class="btn btn-sm btn-primary btn-icon " data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Convert To Deal') }}" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Convert [') . $lead->subject . '] To Deal' }}"
                    data-url="{{ URL::to('leads/' . $lead->id . '/show_convert') }}"><i class="ti ti-exchange text-white"></i></a>
            @endif
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top border-0 shadow-sm" style="top:30px">
                        <div class="list-group list-group-flush rounded-3 p-3" id="useradd-sidenav">
                            <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2 active" href="#general">
                                <span class="d-flex align-items-center"><i class="ti ti-info-circle me-2"></i> {{ __('General') }}</span>
                                <i class="ti ti-chevron-right text-white" style="font-size: 0.8rem;"></i>
                            </a>

                            @if(isset($leadSections))
                                @foreach($leadSections as $section)
                                    <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                        href="#section-{{ $section->id }}">
                                        <span class="d-flex align-items-center"><i class="ti ti-folder me-2"></i> {{ $section->name }}</span>
                                        <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                    </a>
                                @endforeach
                            @endif

                            @if (!Auth::user()->hasRole('client'))
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#tasks">
                                    <span class="d-flex align-items-center"><i class="ti ti-list-check me-2"></i> {{ __('Tasks') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#reminders">
                                    <span class="d-flex align-items-center"><i class="ti ti-bell me-2"></i> {{ __('Reminders') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#calls">
                                    <span class="d-flex align-items-center"><i class="ti ti-phone-call me-2"></i> {{ __('Calls') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#activity">
                                    <span class="d-flex align-items-center"><i class="ti ti-activity me-2"></i> {{ __('Activity') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                            @endif
                            @stack('indiamart_tab')

                        </div>
                    </div>
                </div>

                <div class="col-9">
                    @if($overdueTasksCount > 0 || $todayRemindersCount > 0)
                        <div class="row">
                            <div class="col-12">
                                @if($overdueTasksCount > 0)
                                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-2" role="alert">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        <div>
                                            <strong>{{ __('Overdue Tasks!') }}</strong> {{ __('You have') }} {{ $overdueTasksCount }} {{ __('overdue task(s) for this lead.') }}
                                            <a href="#tasks" class="alert-link ms-2">{{ __('View Tasks') }}</a>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                @if($todayRemindersCount > 0)
                                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-2" role="alert">
                                        <i class="ti ti-bell me-2"></i>
                                        <div>
                                            <strong>{{ __('Upcoming Reminders!') }}</strong> {{ __('You have') }} {{ $todayRemindersCount }} {{ __('reminder(s) scheduled for today.') }}
                                            <a href="#reminders" class="alert-link ms-2">{{ __('View Reminders') }}</a>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div id="general">
                        <?php
                        // Use $tasks passed from controller to respect visibility scope
                        $products = $lead->products();
                        $sources = $lead->sources();
                        $calls = $lead->calls;
                        $emails = $lead->emails;
                        ?>
                        
                        {{-- Lead Summary Header --}}
                        <!-- Hero Header -->
                        <div class="card hero-gradient text-white mb-4 shadow-lg border-0 fade-in-up">
                            <div class="card-body hero-pattern p-4">
                                <div class="row align-items-center position-relative" style="z-index: 1;">
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="badge bg-white-10 border border-white-50 rounded-pill px-3 py-1 me-2 backdrop-blur">
                                                <i class="ti ti-timeline me-1"></i> {{ $lead->pipeline->name ?? __('Unknown Pipeline') }}
                                            </div>
                                            <span class="text-white-50 text-xs text-uppercase fw-bold ls-1">{{ __('Lead Profile') }}</span>
                                        </div>
                                        <h2 class="text-white mb-2 fw-bold display-6">{{ $lead->name }}</h2>
                                        <p class="mb-0 text-white-50">
                                            <i class="ti ti-calendar-event me-2"></i>{{ __('Created on') }} <span class="text-white">{{ company_date_formate($lead->created_at) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-5 text-end mt-3 mt-md-0">
                                         <div class="d-inline-block bg-white-10 p-3 rounded-3 border border-white-20 backdrop-blur text-start">
                                            <label class="text-white-50 text-xs text-uppercase fw-bold d-block mb-1">{{ __('Current Stage') }}</label>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-shape bg-warning text-white rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ti ti-target"></i>
                                                </div>
                                                <h5 class="text-white mb-0">{{ $lead->stage->name ?? '-' }}</h5>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="row mb-5">
                            <!-- Contact Card -->
                            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0 fade-in-up delay-100">
                                <div class="card card-modern h-100 border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-shape-lg bg-success-subtle text-success">
                                                <i class="ti ti-mail-forward"></i>
                                            </div>
                                            <div class="ms-3">
                                                <label class="stat-label text-success d-block mb-1">{{ __('Email Address') }}</label>
                                                <span class="h6 mb-0 text-break text-dark">{!! \Workdo\Lead\Entities\LeadUtility::getFieldDisplay($lead, 'email', $lead->email) !!}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape-lg bg-danger-subtle text-danger">
                                                <i class="ti ti-phone-call"></i>
                                            </div>
                                            <div class="ms-3">
                                                <label class="stat-label text-danger d-block mb-1">{{ __('Phone Number') }}</label>
                                                <span class="h6 mb-0 text-dark">{!! \Workdo\Lead\Entities\LeadUtility::getFieldDisplay($lead, 'phone', $lead->phone) !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Card -->
                            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0 fade-in-up delay-200">
                                <div class="card card-modern h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-shape-lg bg-primary-subtle text-primary me-3">
                                                    <i class="ti ti-chart-pie"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold">{{ __('Conversion Probability') }}</h6>
                                            </div>
                                            <span class="h4 mb-0 text-primary fw-bolder">{{ $precentage }}%</span>
                                        </div>
                                        <div class="progress-modern w-100">
                                            <div class="progress-bar-modern" role="progressbar" style="width: {{ $precentage }}%" aria-valuenow="{{ $precentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="mt-3 text-end">
                                            <small class="text-muted">{{ __('Based on completed tasks and stage') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Stats -->
                             <div class="col-md-12 col-lg-4 fade-in-up delay-300">
                                <div class="card card-modern h-100 border-0 shadow-sm">
                                     <div class="card-body p-4">
                                        <div class="row h-100 align-items-center">
                                            <div class="col-6 text-center border-end">
                                                <div class="icon-shape-lg bg-info-subtle text-info mb-3 mx-auto">
                                                    <i class="ti ti-package"></i>
                                                </div>
                                                <h3 class="mb-1 fw-bolder text-dark">{{ count($products) }}</h3>
                                                <span class="stat-label text-muted">{{ __('Products Attached') }}</span>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="icon-shape-lg bg-warning-subtle text-warning mb-3 mx-auto">
                                                    <i class="ti ti-social"></i>
                                                </div>
                                                <h3 class="mb-1 fw-bolder text-dark">{{ count($sources) }}</h3>
                                                <span class="stat-label text-muted">{{ __('Source Channels') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                        
                        @if(isset($leadSections) && $leadSections->count() > 0)
                            @foreach($leadSections as $section)
                                <div class="card card-modern mb-4 shadow-sm border-0 fade-in-up" id="section-{{ $section->id }}">
                                    <div class="card-body p-4">
                                        <h5 class="mb-4 d-flex align-items-center section-title">
                                            <span class="icon-shape bg-success-subtle text-success rounded-circle me-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ti ti-folder"></i>
                                            </span>
                                            {{ $section->name }}
                                        </h5>
                                        <div class="row g-4">
                                            @foreach($section->fields as $field)
                                                @php
                                                    // VISIBILITY CHECKS
                                                    if (!empty($field->visible_stages) && !in_array($lead->stage_id, $field->visible_stages)) { continue; }
                                                    if (!empty($field->visible_roles)) {
                                                        $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                                                        if (empty(array_intersect($userRoleIds, $field->visible_roles))) { continue; }
                                                    }
                                                    $colWidth = 12 / ($section->columns > 0 ? $section->columns : 3);
                                                    $colClass = 'col-md-'.(int)$colWidth . ' col-sm-6';
                                                @endphp
                                                <div class="{{ $colClass }}">
                                                    <div class="p-3 border rounded-3 bg-light-subtle h-100 position-relative hover-shadow-sm transition-300">
                                                        <small class="text-muted d-block mb-1 fw-bold text-xs text-uppercase">{{ $field->name }}</small>
                                                        <div class="d-flex align-items-center">
                                                            @if($field->type == 'file' || $field->type == 'attachment')
                                                                <i class="ti ti-file me-2 text-success"></i>
                                                            @else
                                                                <i class="ti ti-{{ $field->icon ?? 'circle-dot' }} me-2 text-success opacity-50"></i>
                                                            @endif
                                                            
                                                            <span class="fw-bold text-dark text-break">
                                                                @if($field->is_system)
                                                                    @switch($field->system_field_id)
                                                                        @case('email') {{ $lead->email }} @break
                                                                        @case('phone') {{ $lead->phone }} @break
                                                                        @case('pipeline') {{ $lead->pipeline->name ?? '-' }} @break
                                                                        @case('stage') {{ $lead->stage->name ?? '-' }} @break
                                                                        @case('created_at') {{ company_date_formate($lead->created_at) }} @break
                                                                        @case('percentage') {{ $precentage }}% @break
                                                                        @case('pan_number') {{ $lead->pan_number ?? '-' }} @break
                                                                        @case('aadhar_number') {{ $lead->aadhar_number ?? '-' }} @break
                                                                        @default -
                                                                    @endswitch
                                                                @else
                                                                    @php $value = $leadCustomFieldValues[$field->id] ?? '-'; @endphp
                                                                    @if($field->type == 'multi_select' && $value !== '-')
                                                                        @foreach(explode(',', $value) as $item)
                                                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">{{ $item }}</span>
                                                                        @endforeach
                                                                    @elseif($field->type == 'file' && $value !== '-')
                                                                        <a href="{{ asset('storage/uploads/custom_fields/'.$value) }}" download class="btn btn-xs btn-outline-success rounded-pill">
                                                                            <i class="ti ti-download me-1"></i> {{ __('Download') }}
                                                                        </a>
                                                                    @else
                                                                        {{ $value }}
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                         <div class="col-12 text-center">{{ __('No Layout Configured. Please run the seeder or configure the builder.') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-xxl-12">
                            <div class="row">

                            </div>


                    <div class="row">
                        @if (!Auth::user()->hasRole('client'))
                            <div id="tasks">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-list-check me-2"></i> {{ __('Tasks Checklist') }}</h5>
                                        @permission('lead task create')
                                            <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Create') }}"
                                                data-url="{{ route('leads.tasks.create', $lead->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Task') }}"
                                                data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{ __('Add Task') }}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse ($lead->tasks as $task)
                                                <div class="list-group-item px-4 py-3 task-item border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-start">
                                                        @permission('lead task edit')
                                                            <div class="form-check form-switch me-3 mt-1">
                                                                <input type="checkbox" class="form-check-input task-checkbox" role="switch" id="task_{{ $task->id }}" 
                                                                    @if ($task->status == 'done') checked="checked" @endif 
                                                                    value="{{ $task->status }}" 
                                                                    data-url="{{ route('leads.tasks.update.status', [$lead->id, $task->id]) }}"/>
                                                            </div>
                                                        @endpermission
                                                        <div>
                                                            <h6 class="mb-1 fw-bold {{ $task->status == 'done' ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                                                {{ $task->name }}
                                                            </h6>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge rounded-pill bg-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }}-subtle text-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }} border border-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }} border-opacity-25">
                                                                    {{ __(Workdo\Lead\Entities\LeadTask::$status[$task->status]) }}
                                                                </span>
                                                                <small class="text-muted"><i class="ti ti-calendar me-1"></i> {{ company_datetime_formate($task->date . ' ' . $task->time) }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                         @permission('lead task edit')
                                                            <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                                data-url="{{ route('leads.tasks.edit', [$lead->id, $task->id]) }}"
                                                                data-ajax-popup="true" data-title="{{ __('Edit Task') }}">
                                                                <i class="ti ti-pencil"></i>
                                                            </a>
                                                         @endpermission
                                                         @permission('lead task delete')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['leads.tasks.destroy', $lead->id, $task->id], 'id' => 'delete-form-' . $task->id, 'class' => 'd-inline']) !!}
                                                                <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                         @endpermission
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-check-list display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No tasks scheduled') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="reminders">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-bell me-2"></i> {{ __('Reminders') }}</h5>
                                        <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                            data-bs-toggle="tooltip" 
                                            title="{{ __('Create') }}"
                                            data-url="{{ route('leads.reminders.create', $lead->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Create Reminder') }}"
                                            data-size="md">
                                            <i class="ti ti-plus text-white"></i> {{ __('Add Reminder') }}
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse ($lead->reminders as $reminder)
                                                @php
                                                    $remindAt = \Carbon\Carbon::parse($reminder->remind_at);
                                                    $now = now();
                                                    $colorClass = 'bg-success-subtle text-success border border-success border-opacity-25';
                                                    if($remindAt->lt($now)) {
                                                        $colorClass = 'bg-danger-subtle text-danger border border-danger border-opacity-25';
                                                    } elseif($remindAt->diffInHours($now) < 24) {
                                                        $colorClass = 'bg-warning-subtle text-warning border border-warning border-opacity-25';
                                                    }
                                                @endphp
                                                <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 text-center">
                                                            <div class="badge {{ $colorClass }} rounded p-2">
                                                                <span class="d-block fw-bold display-6" style="line-height:1;">{{ $remindAt->format('d') }}</span>
                                                                <span class="text-xs text-uppercase">{{ $remindAt->format('M') }}</span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $reminder->title }}</h6>
                                                            <div class="text-xs text-muted">
                                                                <span class="me-2"><i class="ti ti-clock me-1"></i> {{ $remindAt->format('H:i A') }}</span>
                                                                <span><i class="ti ti-user me-1"></i> {{ $reminder->user->name ?? '-' }}</span>
                                                                <span class="ms-2 badge bg-secondary-subtle text-secondary rounded-pill">{{ __(ucfirst($reminder->type)) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                        <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                            data-url="{{ route('leads.reminders.edit', [$lead->id, $reminder->id]) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Edit Reminder') }}">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.reminders.destroy', $lead->id, $reminder->id], 'id' => 'delete-reminder-form-' . $reminder->id, 'class' => 'd-inline']) !!}
                                                            <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </div>
                                            @empty
                                                 <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-bell-off display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No active reminders') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="calls">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-phone me-2"></i> {{ __('Call Logs') }}</h5>
                                        @permission('lead call create')
                                            <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Create') }}"
                                                data-url="{{ route('leads.calls.create', $lead->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Call') }}"
                                                data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{ __('Log Call') }}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse ($lead->calls as $call)
                                                <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-shape bg-info-subtle text-info rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ti ti-phone-call"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $call->subject }}</h6>
                                                            <div class="text-xs text-muted">
                                                                <span class="me-3"><i class="ti ti-clock me-1"></i> {{ company_datetime_formate($call->duration) }}</span>
                                                                <span class="text-success">{{ $call->call_result }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                         @permission('lead call edit')
                                                            <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                                data-url="{{ route('leads.calls.edit', [$lead->id, $call->id]) }}"
                                                                data-ajax-popup="true" data-title="{{ __('Edit Call') }}">
                                                                <i class="ti ti-pencil"></i>
                                                            </a>
                                                         @endpermission
                                                         @permission('lead call delete')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['leads.calls.destroy', $lead->id, $call->id], 'id' => 'delete-form-' . $call->id, 'class' => 'd-inline']) !!}
                                                                <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                         @endpermission
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-phone-off display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No calls logged') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="activity">
                                <div class="card card-modern border-0 shadow-sm">
                                    <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-activity me-2"></i> {{ __('Activity Timeline') }}</h5>
                                    </div>
                                    <div class="card-body p-4 pt-0">
                                        <div class="timeline-vertical mt-3">
                                            @forelse ($lead->activities as $activity)
                                                <div class="timeline-item">
                                                    <div class="timeline-dot"></div>
                                                    <div class="ps-3">
                                                        <h6 class="mb-1 fw-bold text-dark">{!! $activity->getLeadRemark() !!}</h6>
                                                        <small class="text-muted"><i class="ti ti-clock me-1"></i> {{ $activity->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            @empty
                                                 <div class="text-center text-muted">
                                                    <small>{{ __('No activity logs found') }}</small>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @stack('indiamart_div')
                </div>
            </div>
        </div>
    </div>

@endsection
