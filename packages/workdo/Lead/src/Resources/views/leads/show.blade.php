@extends('layouts.main')

@section('page-title')
    {{ $lead->name }}
@endsection
@push('css')
    <style>
        .nav-tabs .nav-link-tabs.active {
            background: none;
        }
        /* Bento Grid Layout Styles */
        .bento-card {
            border: 1px solid rgba(24, 191, 107, 0.12) !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 253, 249, 0.9) 100%) !important;
            backdrop-filter: blur(10px);
            border-radius: 12px !important;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            position: relative;
            overflow: hidden;
            border-left: 4px solid #18bf6b !important;
            min-height: 90px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important;
        }
        .bento-card:hover {
            transform: translateY(-4px) scale(1.01) !important;
            border-left-color: #20c997 !important;
            box-shadow: 0 10px 25px rgba(24, 191, 107, 0.1) !important;
            border-color: rgba(24, 191, 107, 0.25) !important;
        }
        .bento-card-large {
            background: linear-gradient(135deg, rgba(240, 249, 244, 0.9) 0%, rgba(255, 255, 255, 0.9) 100%) !important;
            border-left: 4px solid #157e3f !important;
        }
        .bento-card-large:hover {
            box-shadow: 0 12px 30px rgba(21, 126, 63, 0.12) !important;
        }
        .bento-icon-container {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(24, 191, 107, 0.08);
            color: #18bf6b;
            transition: all 0.3s ease;
        }
        .bento-card:hover .bento-icon-container {
            transform: scale(1.15) rotate(8deg);
            background: #18bf6b;
            color: #fff;
        }
        .editable-field {
            cursor: pointer;
            border-bottom: 1px dashed rgba(24, 191, 107, 0.4);
            padding-bottom: 2px;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .editable-field:hover {
            color: #18bf6b !important;
            border-bottom-color: #18bf6b;
        }
        .editable-field::after {
            content: " ✎";
            font-size: 0.75rem;
            opacity: 0.3;
            transition: opacity 0.2s ease;
            color: #18bf6b;
            margin-left: 4px;
        }
        .editable-field:hover::after {
            opacity: 1;
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
            filter: brightness(1.1);
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
            animation: progress-bar-stripes 2s linear infinite;
            opacity: 0.3;
        }
        
        @keyframes progress-bar-stripes {
            from { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }

        .hover-glow:hover {
            box-shadow: 0 10px 40px rgba(25, 135, 84, 0.15) !important;
        }

        .responsible-glow {
            animation: border-glow 4s ease-in-out infinite;
        }

        @keyframes border-glow {
            0%, 100% { border-color: #ffc107; box-shadow: 0 5px 15px rgba(255, 193, 7, 0.1); }
            50% { border-color: #ffeb3b; box-shadow: 0 8px 25px rgba(255, 235, 59, 0.2); }
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
        #useradd-sidenav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(25, 135, 84, 0.05);
            border-radius: 16px;
        }
        #useradd-sidenav .list-group-item {
            border-radius: 10px !important;
            margin-bottom: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #526477;
            font-weight: 600;
            border: 1px solid transparent;
            background: transparent;
        }
        #useradd-sidenav .list-group-item:hover {
            background-color: rgba(25, 135, 84, 0.08);
            color: #198754;
            transform: translateX(8px);
            border-color: rgba(25, 135, 84, 0.1);
        }
        #useradd-sidenav .list-group-item.active {
            background: linear-gradient(135deg, #198754 0%, #11663e 100%) !important;
            color: #fff !important;
            border-color: transparent;
            box-shadow: 0 8px 15px rgba(25, 135, 84, 0.25);
            transform: translateX(5px);
        }
        #useradd-sidenav .list-group-item.active .ti {
            color: #fff !important;
        }
        #useradd-sidenav .list-group-item .ti {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        #useradd-sidenav .list-group-item:hover .ti {
            transform: scale(1.2);
        }
        .bg-success { background-color: #198754 !important; }
        .bg-danger { background-color: #dc3545 !important; }
        .bg-warning { background-color: #ffc107 !important; }
        
        .stat-card-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
        }

        /* Section Layout Enhancements */
        .section-layout-standard {
            border-left: 4px solid #94a3b8 !important;
        }
        .section-layout-card {
            border-left: 4px solid #3b82f6 !important;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.08) !important;
        }
        .section-layout-bento {
            border-left: 4px solid #18bf6b !important;
            background: radial-gradient(circle, rgba(24, 191, 107, 0.03) 1px, transparent 1px) #fff;
            background-size: 24px 24px;
            box-shadow: 0 10px 25px rgba(24, 191, 107, 0.08) !important;
        }

        /* Premium Card Styles (for fields) */
        .premium-card {
            border: 1px solid rgba(59, 130, 246, 0.15) !important;
            background: linear-gradient(135deg, #ffffff 0%, #f4f7fe 100%) !important;
            border-radius: 14px !important;
            border-top: 4px solid #3b82f6 !important;
            min-height: 90px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative;
            overflow: hidden;
        }
        .premium-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.12) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        .premium-card-large {
            background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%) !important;
            border-top: 4px solid #1d4ed8 !important;
        }
        .premium-card-large:hover {
            box-shadow: 0 15px 35px rgba(29, 78, 216, 0.15) !important;
        }
        .premium-icon-container {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.08);
            color: #3b82f6;
            transition: all 0.3s ease;
        }
        .premium-card:hover .premium-icon-container {
            transform: scale(1.15) rotate(-8deg);
            background: #3b82f6;
            color: #fff;
        }

        /* Standard Card Styles (for fields) */
        .standard-card {
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            border-radius: 8px !important;
            min-height: 80px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .standard-card:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
            background: #f1f5f9 !important;
        }
        .standard-card-large {
            background: #f1f5f9 !important;
            border-left: 3px solid #64748b !important;
        }
        .standard-icon-container {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(148, 163, 184, 0.1);
            color: #64748b;
            transition: all 0.2s ease;
        }
        .standard-card:hover .standard-icon-container {
            background: rgba(148, 163, 184, 0.2);
            color: #1e293b;
        }
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
        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" title="{{__('Back')}}">
            <i class="ti ti-arrow-left text-white"></i>
        </a>
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
                    @php
                        $kycComments = $lead->discussions->where('is_kyc', 1);
                        $latestKyc = $kycComments->first();
                        
                        // Use the model helper to check if I am responsible
                        $isResponsible = $lead->isResponsible();
                        
                        // Was the last comment made by someone other than the owner, creator, or assigned users?
                        // (Meaning it's an external/system alert for the responsible team)
                        $lastCommentByResponsible = $latestKyc ? $lead->isResponsible($latestKyc->user) : false;

                        // Show Alert ONLY if:
                        // 1. There are KYC comments
                        // 2. I am a responsible person (so I should care)
                        // 3. The last comment was NOT by a responsible person (meaning it needs attention)
                        $showAlert = ($kycComments->count() > 0 && $isResponsible && !$lastCommentByResponsible);
                    @endphp

                    @if($showAlert)
                        <div class="alert alert-important alert-warning alert-dismissible fade show mb-3 shadow-sm border-0" role="alert" style="border-left: 5px solid #ffa21d !important;">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-shield-alert me-3 fs-3 text-warning"></i>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">{{ __('KYC Alert!') }}</strong>
                                        <small class="text-muted">{{ $latestKyc->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-dark mb-1">
                                        {{ __('Latest KYC Comment by') }} <strong>{{ $latestKyc->user->name }}</strong>:
                                        <span class="ms-1 italic text-muted">"{{ $latestKyc->comment }}"</span>
                                    </div>
                                    <a href="#kyc-discussions" class="btn btn-xs btn-warning text-white rounded-pill mt-1" style="font-size: 0.75rem; padding: 2px 10px;">{{ __('Review All') }} ({{ $kycComments->count() }})</a>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="px-3 py-1 rounded-pill bg-white-10 border border-white-20 backdrop-blur d-flex align-items-center shadow-sm">
                                                <i class="ti ti-hash text-warning me-2 f-12"></i>
                                                <span class="text-white fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ '#' . $lead->id }}</span>
                                            </div>
                                            <div class="ms-3 h-px-20 border-start border-white-20"></div>
                                            <div class="ms-3 badge rounded-pill bg-white-10 text-white border border-white-10 backdrop-blur" style="font-size: 0.7rem; font-weight: 500; height: 26px; display: inline-flex; align-items: center;">
                                                <i class="ti ti-timeline me-1"></i> {{ $lead->pipeline->name ?? __('Pipeline') }}
                                            </div>
                                        </div>
                                        <h1 class="text-white mb-2 fw-800 display-5" style="letter-spacing: -1px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ $lead->name }}</h1>
                                        <div class="d-flex align-items-center text-white-50">
                                            <div class="d-flex align-items-center me-4">
                                                <i class="ti ti-calendar-event me-2 opacity-50"></i>
                                                <span class="text-xs fw-500">{{ __('Created') }}: <span class="text-white">{{ company_date_formate($lead->created_at) }}</span></span>
                                            </div>
                                            @if($lead->owner)
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-user-check me-2 opacity-50"></i>
                                                    <span class="text-xs fw-500">{{ __('Creator') }}: <span class="text-white">{{ $lead->createdBy->name ?? '-' }}</span></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
                                         <!-- Stage Badge -->
                                         <div class="p-3 rounded-4 bg-white-10 backdrop-blur border border-white-10 shadow-sm text-start" style="min-width: 140px;">
                                            <label class="text-white-50 text-uppercase fw-800 d-block mb-2" style="font-size: 0.65rem; letter-spacing: 1px;">{{ __('Current Stage') }}</label>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-warning text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="ti ti-target f-16"></i>
                                                </div>
                                                <h5 class="text-white mb-0 ms-2 fw-bold">{{ $lead->stage?->name ?? '-' }}</h5>
                                            </div>
                                         </div>

                                         <!-- Responsible Person Highlight -->
                                         <div class="p-3 rounded-4 bg-white shadow-2xl text-start position-relative overflow-hidden responsible-glow" style="min-width: 220px; border: 2px solid #ffc107; transition: all 0.3s ease;">
                                            <div class="position-absolute top-0 end-0 p-1 opacity-5">
                                                <i class="ti ti-crown fs-1" style="transform: rotate(15deg);"></i>
                                            </div>
                                            <label class="text-success text-uppercase fw-800 d-block mb-2" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.8;">{{ __('Responsible Person') }}</label>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-group d-flex align-items-center me-2">
                                                    @if($lead->owner)
                                                        <div class="position-relative" data-bs-toggle="tooltip" title="{{ $lead->owner->name }} ({{ __('Primary Owner') }})">
                                                            <img src="{{ get_file(!empty($lead->owner->avatar) ? $lead->owner->avatar : 'uploads/users-avatar/avatar.png') }}" 
                                                                 class="rounded-circle border border-2 border-warning shadow-sm" 
                                                                 style="width: 36px; height: 36px; z-index: 50; position: relative;">
                                                            <div class="position-absolute bottom-0 end-0 bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 14px; height: 14px; border: 2px solid #fff; z-index: 51;">
                                                                <i class="ti ti-crown text-white" style="font-size: 7px;"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                @if($lead->owner)
                                                    <div class="ms-1 overflow-hidden">
                                                        <span class="text-dark fw-800 d-block text-truncate" style="font-size: 11px; max-width: 80px;">{{ explode(' ', $lead->owner->name)[0] }}</span>
                                                        <span class="text-muted d-block" style="font-size: 9px;">{{ __('Lead Owner') }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs ms-1">{{ __('Unassigned') }}</span>
                                                @endif
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
                                    <div class="card-body p-4 position-relative overflow-hidden">
                                        <div class="stat-card-accent bg-success"></div>
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon-shape-lg bg-success-subtle text-success shadow-sm">
                                                <i class="ti ti-mail-forward"></i>
                                            </div>
                                            <div class="ms-3">
                                                <label class="stat-label text-success d-block mb-1">{{ __('Email Address') }}</label>
                                                <span class="h6 mb-0 text-break text-dark fw-bold">{!! \Workdo\Lead\Entities\LeadUtility::getFieldDisplay($lead, 'email', $lead->email) !!}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape-lg bg-danger-subtle text-danger shadow-sm">
                                                <i class="ti ti-phone-call"></i>
                                            </div>
                                            <div class="ms-3">
                                                <label class="stat-label text-danger d-block mb-1">{{ __('Phone Number') }}</label>
                                                <span class="h6 mb-0 text-dark fw-bold">{!! \Workdo\Lead\Entities\LeadUtility::getFieldDisplay($lead, 'phone', $lead->phone) !!}
                                                    @if($lead->phone)
                                                        <a href="javascript:void(0)" class="ms-2 text-primary hover-scale click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}">
                                                            <i class="ti ti-phone-call"></i>
                                                        </a>
                                                    @endif
                                                </span>
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
                                        </div>
                                        <span class="h4 mb-0 text-primary fw-bolder">{{ $percentage }}%</span>
                                        <div class="progress-modern w-100">
                                             <div class="progress-bar-modern" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
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
                                @php
                                    $layoutType = $section->layout_type ?? 'section';
                                    $sectionClass = 'section-layout-standard';
                                    $iconShapeClass = 'bg-secondary-subtle text-secondary';
                                    $iconClass = 'ti-folder';
                                    if ($layoutType === 'card') {
                                        $sectionClass = 'section-layout-card';
                                        $iconShapeClass = 'bg-primary-subtle text-primary';
                                        $iconClass = 'ti-id-badge';
                                    } elseif ($layoutType === 'bento') {
                                        $sectionClass = 'section-layout-bento';
                                        $iconShapeClass = 'bg-success-subtle text-success';
                                        $iconClass = 'ti-layout-grid';
                                    }

                                    // Dynamic percentage calculation for fields in this section
                                    $totalFields = 0;
                                    $filledFields = 0;
                                    $hasApi = false;
                                    foreach($section->fields as $f) {
                                        if (!empty($f->visible_stages) && !in_array($lead->stage_id, $f->visible_stages)) { continue; }
                                        if (!empty($f->visible_roles)) {
                                            $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                                            if (empty(array_intersect($userRoleIds, $f->visible_roles))) { continue; }
                                        }
                                        $totalFields++;
                                        $val = $f->is_system ? $lead->{$f->system_field_id} : ($leadCustomFieldValues[$f->id] ?? '');
                                        if ($val !== null && $val !== '' && $val !== '-') {
                                            $filledFields++;
                                        }
                                        if (!empty($f->api_url)) {
                                            $hasApi = true;
                                        }
                                    }
                                    $sectionPercentage = $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;
                                @endphp
                                <div class="card card-modern mb-4 shadow-sm border-0 fade-in-up {{ $sectionClass }}" id="section-{{ $section->id }}">
                                    <div class="card-body p-4">
                                        <h5 class="mb-4 d-flex align-items-center section-title w-100">
                                            <span class="icon-shape {{ $iconShapeClass }} rounded-circle me-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $iconClass }}"></i>
                                            </span>
                                            <span class="fw-bold">{{ $section->name }}</span>
                                            @if($hasApi)
                                                <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-success ms-2 sync-section-api-btn" 
                                                   data-section-id="{{ $section->id }}" 
                                                   data-lead-id="{{ $lead->id }}"
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Sync API Data') }}"
                                                   style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0;">
                                                    <i class="ti ti-refresh text-success fs-5"></i>
                                                </a>
                                            @endif
                                            @if($layoutType === 'card')
                                                <i class="ti ti-circle-check-filled text-primary fs-4 ms-2" data-bs-toggle="tooltip" title="Verified"></i>
                                                @if(stripos($section->name, 'basic') !== false)
                                                    <span class="badge bg-light-primary text-primary ms-3 border border-primary border-opacity-25 rounded-pill px-3 py-1 text-capitalize" style="font-size: 0.75rem; font-weight: 500;">Existing and Valid. Aadhaar Seeding is Successful.</span>
                                                @elseif(stripos($section->name, 'address') !== false)
                                                    <span class="badge bg-light-primary text-primary ms-3 border border-primary border-opacity-25 rounded-pill px-3 py-1 text-capitalize" style="font-size: 0.75rem; font-weight: 500;">Address Verification is Successful.</span>
                                                @endif
                                                <span class="badge bg-success ms-auto rounded-pill px-3 py-1" style="font-size: 0.75rem; font-weight: 600;">{{ $sectionPercentage }}%</span>
                                            @endif
                                        </h5>
                                        <div class="row g-3">
                                            @foreach($section->fields as $field)
                                                @php
                                                    // VISIBILITY CHECKS
                                                    if (!empty($field->visible_stages) && !in_array($lead->stage_id, $field->visible_stages)) { continue; }
                                                    if (!empty($field->visible_roles)) {
                                                        $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                                                        if (empty(array_intersect($userRoleIds, $field->visible_roles))) { continue; }
                                                    }
                                                    
                                                    // DYNAMIC BENTO GRID WIDTH CALCULATION
                                                    $sectionCols = $section->columns > 0 ? $section->columns : 3;
                                                    $fieldWidth = $field->width > 0 ? $field->width : 1;
                                                    $calculatedGridCols = min(12, (12 / $sectionCols) * $fieldWidth);
                                                    $colClass = 'col-md-'.(int)$calculatedGridCols . ' col-sm-12';
                                                    
                                                    // Determine bento card style based on size
                                                    $isLargeCard = $calculatedGridCols >= 8;
                                                    
                                                    // Determine layout classes
                                                    if ($layoutType === 'card') {
                                                        $cardClass = 'premium-card' . ($isLargeCard ? ' premium-card-large' : '');
                                                        $iconContainerClass = 'premium-icon-container';
                                                    } elseif ($layoutType === 'bento') {
                                                        $cardClass = 'bento-card' . ($isLargeCard ? ' bento-card-large' : '');
                                                        $iconContainerClass = 'bento-icon-container';
                                                    } else {
                                                        $cardClass = 'standard-card' . ($isLargeCard ? ' standard-card-large' : '');
                                                        $iconContainerClass = 'standard-icon-container';
                                                    }
                                                @endphp
                                                <div class="{{ $colClass }}">
                                                    @if($layoutType === 'card')
                                                        {{-- Clean row layout for premium card mode --}}
                                                        <div class="py-3 px-2 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="border-color: #edf2f7 !important; min-height: 52px;">
                                                            <small class="text-muted fw-bold text-xs text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; min-width: 140px;">{{ $field->name }}</small>
                                                            <div class="d-flex align-items-center text-end flex-grow-1 justify-content-end">
                                                                @php
                                                                    $canEditInline = (!$field->is_system || in_array($field->system_field_id, ['email', 'phone', 'pan_number', 'aadhar_number'])) && Auth::user()->isAbleTo('lead edit');
                                                                    $rawVal = $field->is_system ? $lead->{$field->system_field_id} : ($leadCustomFieldValues[$field->id] ?? '');
                                                                    
                                                                    // Add custom green class for premium style matched fields or values containing X
                                                                    $textClass = 'text-dark';
                                                                    if (strpos(strtoupper($rawVal), 'XXXX') !== false || in_array(strtolower($field->name), ['full name', 'name as per it site', 'name as per esign', 'political relation'])) {
                                                                        $textClass = 'text-success';
                                                                    }
                                                                @endphp
                                                                
                                                                @if($canEditInline)
                                                                    <span class="fs-6 fw-bold {{ $textClass }} text-break editable-field w-100"
                                                                          data-name="{{ $field->is_system ? $field->system_field_id : $field->id }}"
                                                                          data-system="{{ $field->is_system ? 1 : 0 }}"
                                                                          data-type="{{ $field->type }}"
                                                                          data-options="{{ $field->options ?? '' }}"
                                                                          data-value="{{ $rawVal }}">
                                                                @else
                                                                    <span class="fs-6 fw-bold {{ $textClass }} text-break">
                                                                @endif
                                 
                                                                    @if($field->is_system)
                                                                        @switch($field->system_field_id)
                                                                            @case('email') 
                                                                                @if($lead->email)
                                                                                    <a href="mailto:{{ $lead->email }}" class="text-primary hover-underline">{{ $lead->email }}</a>
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('phone') 
                                                                                @if($lead->phone)
                                                                                    {{ $lead->phone }}
                                                                                    <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}">
                                                                                        <i class="ti ti-phone-call"></i>
                                                                                    </a>
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('pipeline') {{ $lead->pipeline->name ?? '-' }} @break
                                                                            @case('stage') {{ $lead->stage?->name ?? '-' }} @break
                                                                            @case('created_at') {{ company_date_formate($lead->created_at) }} @break
                                                                            @case('percentage') {{ $percentage }}% @break
                                                                            @case('pan_number') 
                                                                                @if($lead->pan_number)
                                                                                    {{ $lead->pan_number }}
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('aadhar_number') 
                                                                                @if($lead->aadhar_number)
                                                                                    {{ $lead->aadhar_number }}
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @default -
                                                                        @endswitch
                                                                    @else
                                                                        @php $value = $leadCustomFieldValues[$field->id] ?? ''; @endphp
                                                                        @if($value === '-' || empty($value))
                                                                            <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                        @elseif($field->type == 'multi_select')
                                                                            @foreach(explode(',', $value) as $item)
                                                                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">{{ $item }}</span>
                                                                            @endforeach
                                                                        @elseif($field->type == 'file')
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
                                                    @else
                                                        {{-- Original Card layouts (Bento/Standard) --}}
                                                        <div class="p-3 {{ $cardClass }} d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <small class="text-muted fw-bold text-xs text-uppercase" style="letter-spacing: 0.5px;">{{ $field->name }}</small>
                                                                    <div class="{{ $iconContainerClass }}">
                                                                        @if($field->type == 'file' || $field->type == 'attachment')
                                                                            <i class="ti ti-file fs-6"></i>
                                                                        @else
                                                                            <i class="ti ti-{{ $field->icon ?? 'circle-dot' }} fs-6"></i>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                 <div class="d-flex align-items-center mt-2 w-100">
                                                                    @php
                                                                        $canEditInline = (!$field->is_system || in_array($field->system_field_id, ['email', 'phone', 'pan_number', 'aadhar_number'])) && Auth::user()->isAbleTo('lead edit');
                                                                        $rawVal = $field->is_system ? $lead->{$field->system_field_id} : ($leadCustomFieldValues[$field->id] ?? '');
                                                                    @endphp
                                                                    
                                                                    @if($canEditInline)
                                                                        <span class="{{ $isLargeCard ? 'fs-5' : 'fs-6' }} fw-bold text-dark text-break editable-field w-100"
                                                                              data-name="{{ $field->is_system ? $field->system_field_id : $field->id }}"
                                                                              data-system="{{ $field->is_system ? 1 : 0 }}"
                                                                              data-type="{{ $field->type }}"
                                                                              data-options="{{ $field->options ?? '' }}"
                                                                              data-value="{{ $rawVal }}">
                                                                    @else
                                                                        <span class="{{ $isLargeCard ? 'fs-5' : 'fs-6' }} fw-bold text-dark text-break">
                                                                    @endif
                                     
                                                                        @if($field->is_system)
                                                                            @switch($field->system_field_id)
                                                                                @case('email') 
                                                                                    @if($lead->email)
                                                                                        <a href="mailto:{{ $lead->email }}" class="text-primary hover-underline">{{ $lead->email }}</a>
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('phone') 
                                                                                    @if($lead->phone)
                                                                                        {{ $lead->phone }}
                                                                                        <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}">
                                                                                            <i class="ti ti-phone-call"></i>
                                                                                        </a>
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('pipeline') {{ $lead->pipeline->name ?? '-' }} @break
                                                                                @case('stage') {{ $lead->stage?->name ?? '-' }} @break
                                                                                @case('created_at') {{ company_date_formate($lead->created_at) }} @break
                                                                                @case('percentage') {{ $percentage }}% @break
                                                                                @case('pan_number') 
                                                                                    @if($lead->pan_number)
                                                                                        {{ $lead->pan_number }}
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('aadhar_number') 
                                                                                    @if($lead->aadhar_number)
                                                                                        {{ $lead->aadhar_number }}
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @default -
                                                                            @endswitch
                                                                        @else
                                                                            @php $value = $leadCustomFieldValues[$field->id] ?? ''; @endphp
                                                                            @if($value === '-' || empty($value))
                                                                                <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                            @elseif($field->type == 'multi_select')
                                                                                @foreach(explode(',', $value) as $item)
                                                                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">{{ $item }}</span>
                                                                                @endforeach
                                                                            @elseif($field->type == 'file')
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
                                                    @endif
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

                        @if($lead->isResponsible())
                            <div id="kyc-discussions" class="mb-4 mt-4">
                                <div class="card card-modern border-0 shadow-sm">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-shield-check me-2"></i> {{ __('KYC Comments') }}</h5>
                                        @permission('lead kyc comment')
                                            <a href="#" class="btn btn-sm btn-success rounded-pill shadow-sm" data-url="{{ route('leads.discussions.create', $lead->id) }}?is_kyc=1" data-ajax-popup="true" data-title="{{__('Add KYC Comment')}}" data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{__('Add Comment')}}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-4 pt-0">
                                        <ul class="list-group list-group-flush mt-3">
                                            @forelse ($kycComments as $discussion)
                                                <li class="list-group-item px-0 py-3 border-0 border-bottom">
                                                    <div class="d-flex align-items-start">
                                                        @php
                                                            $avatar = 'uploads/users-avatar/avatar.png';
                                                            if(!empty($discussion->user->avatar) && check_file($discussion->user->avatar)) {
                                                                $avatar = $discussion->user->avatar;
                                                            }
                                                        @endphp
                                                        <img src="{{ get_file($avatar) }}" 
                                                             class="rounded-circle me-3" style="width: 40px; height: 40px;" alt="avatar">
                                                        <div class="w-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <h6 class="mb-0 fw-bold">{{ $discussion->user->name }}</h6>
                                                                <small class="text-muted">{{ $discussion->created_at->diffForHumans() }}</small>
                                                            </div>
                                                            <p class="text-sm text-dark mb-0">{{ $discussion->comment }}</p>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <div class="text-center text-muted py-4">
                                                    <i class="ti ti-message-off display-6 d-block mb-3 opacity-25"></i>
                                                    <small>{{ __('No KYC comments found') }}</small>
                                                </div>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
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
                                        @forelse ($tasks as $task)
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
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0 section-title">
                                            <i class="ti ti-activity me-2"></i> {{ __('Activity Timeline') }}
                                        </h5>
                                        @if($lead->activities->count() > 0)
                                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.7rem; padding: 4px 10px;">
                                                {{ $lead->activities->count() }} {{ __('entries') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-4 pt-2">
                                        @php
                                            $activityIconMap = [
                                                'Lead Created'    => ['icon' => 'ti-circle-plus',       'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Move'            => ['icon' => 'ti-arrows-right-left',  'bg' => '#fd7e14', 'light' => 'rgba(253,126,20,0.1)'],
                                                'Lead Updated'    => ['icon' => 'ti-pencil',              'bg' => '#0d6efd', 'light' => 'rgba(13,110,253,0.1)'],
                                                'Lead Transferred'=> ['icon' => 'ti-switch-horizontal',  'bg' => '#6f42c1', 'light' => 'rgba(111,66,193,0.1)'],
                                                'Lead Imported'   => ['icon' => 'ti-file-upload',         'bg' => '#20c997', 'light' => 'rgba(32,201,151,0.1)'],
                                                'Upload File'     => ['icon' => 'ti-file',                'bg' => '#6c757d', 'light' => 'rgba(108,117,125,0.1)'],
                                                'Add Product'     => ['icon' => 'ti-package',             'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Update Sources'  => ['icon' => 'ti-source-code',         'bg' => '#0dcaf0', 'light' => 'rgba(13,202,240,0.1)'],
                                                'Create Lead Call'=> ['icon' => 'ti-phone',               'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Create Lead Email'=> ['icon' => 'ti-mail',               'bg' => '#0d6efd', 'light' => 'rgba(13,110,253,0.1)'],
                                                'Create Task'     => ['icon' => 'ti-list-check',          'bg' => '#fd7e14', 'light' => 'rgba(253,126,20,0.1)'],
                                                'Create Reminder' => ['icon' => 'ti-bell',                'bg' => '#ffc107', 'light' => 'rgba(255,193,7,0.1)'],
                                            ];
                                        @endphp

                                        @forelse ($lead->activities as $activity)
                                            @php
                                                $ai = $activityIconMap[$activity->log_type] ?? ['icon' => 'ti-point', 'bg' => '#adb5bd', 'light' => 'rgba(173,181,189,0.1)'];
                                                $actUser = $activity->user;
                                                $actAvatar = (!empty($actUser) && !empty($actUser->avatar) && check_file($actUser->avatar))
                                                    ? get_file($actUser->avatar)
                                                    : get_file('uploads/users-avatar/avatar.png');
                                            @endphp
                                            <div class="d-flex align-items-start py-3 px-2 rounded-3 mb-1" style="transition: background 0.2s; border-left: 3px solid {{ $ai['bg'] }}; padding-left: 12px !important;"
                                                 onmouseover="this.style.background='{{ $ai['light'] }}'" onmouseout="this.style.background='transparent'">
                                                {{-- Icon --}}
                                                <div class="flex-shrink-0 me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                     style="width: 36px; height: 36px; background: {{ $ai['light'] }}; border: 2px solid {{ $ai['bg'] }};">
                                                    <i class="ti {{ $ai['icon'] }}" style="color: {{ $ai['bg'] }}; font-size: 14px;"></i>
                                                </div>
                                                {{-- Text --}}
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                        <span class="badge rounded-pill px-2 py-1" style="background: {{ $ai['light'] }}; color: {{ $ai['bg'] }}; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                                                            {{ __($activity->log_type) }}
                                                        </span>
                                                        @if($actUser)
                                                            <span class="d-flex align-items-center ms-1">
                                                                <img src="{{ $actAvatar }}" class="rounded-circle me-1" style="width: 16px; height: 16px; border: 1px solid #dee2e6;">
                                                                <small class="text-muted fw-600" style="font-size: 0.7rem;">{{ $actUser->name }}</small>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-0 text-dark fw-500" style="font-size: 0.85rem; line-height: 1.4;">{!! $activity->getLeadRemark() !!}</p>
                                                    <div class="d-flex align-items-center mt-1 gap-2">
                                                        <small class="text-muted" style="font-size: 0.7rem;">
                                                            <i class="ti ti-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                                                        </small>
                                                        <small class="text-muted opacity-50" style="font-size: 0.65rem;">
                                                            · {{ $activity->created_at->format('d M Y, h:i A') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <div class="d-flex align-items-center justify-content-center mb-3 mx-auto rounded-circle"
                                                     style="width: 64px; height: 64px; background: rgba(25,135,84,0.08);">
                                                    <i class="ti ti-activity-heartbeat text-success" style="font-size: 28px;"></i>
                                                </div>
                                                <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ __('No activity yet') }}</p>
                                                <small class="text-muted opacity-50">{{ __('Actions on this lead will appear here') }}</small>
                                            </div>
                                        @endforelse
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

@push('scripts')
    @include('lead::leads.click_to_call_script')
    
    <script>
        $(document).ready(function() {
            // Click to edit
            $(document).on('click', '.editable-field', function(e) {
                // Ignore if clicked on buttons inside editor or if already editing
                if ($(e.target).closest('.inline-edit-container').length > 0 || $(this).find('.inline-edit-container').length > 0) {
                    return;
                }
                
                var $span = $(this);
                var originalHTML = $span.html();
                var fieldName = $span.data('name');
                var isSystem = $span.data('system');
                var fieldType = $span.data('type');
                var rawValue = $span.attr('data-value') !== undefined ? $span.attr('data-value') : '';
                var optionsStr = $span.data('options') || '';
                
                // Prevent links / click-to-call during edit trigger
                e.preventDefault();
                e.stopPropagation();
                
                var inputHTML = '';
                if (fieldType === 'select' && optionsStr) {
                    var options = optionsStr.split(',');
                    inputHTML = '<select class="form-select form-select-sm inline-edit-input">';
                    options.forEach(function(opt) {
                        opt = opt.trim();
                        var selected = (opt === rawValue) ? 'selected' : '';
                        inputHTML += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    inputHTML += '</select>';
                } else if (fieldType === 'multi_select' && optionsStr) {
                    var options = optionsStr.split(',');
                    var selectedOpts = rawValue ? rawValue.split(',') : [];
                    selectedOpts = selectedOpts.map(function(item) { return item.trim(); });
                    inputHTML += '<div class="w-100 select2-container-inline">';
                    inputHTML += '<select class="form-select form-select-sm inline-edit-input select2-modal-inline" multiple style="min-width: 150px;">';
                    options.forEach(function(opt) {
                        opt = opt.trim();
                        var selected = (selectedOpts.indexOf(opt) !== -1) ? 'selected' : '';
                        inputHTML += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    inputHTML += '</select></div>';
                } else if (fieldType === 'textarea') {
                    inputHTML = '<textarea class="form-control form-control-sm inline-edit-input" rows="2">' + rawValue + '</textarea>';
                } else if (fieldType === 'date') {
                    inputHTML = '<input type="date" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                } else if (fieldType === 'number') {
                    inputHTML = '<input type="number" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                } else if (fieldType === 'file') {
                    inputHTML = '<input type="file" class="form-control form-control-sm inline-edit-input">';
                } else {
                    inputHTML = '<input type="text" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                }
                
                var containerHTML = '<div class="inline-edit-container d-flex align-items-center w-100 mt-1">' +
                    inputHTML +
                    '<button class="btn btn-sm btn-success p-1 ms-2 btn-inline-save" type="button"><i class="ti ti-check text-white fs-6"></i></button>' +
                    '<button class="btn btn-sm btn-danger p-1 ms-1 btn-inline-cancel" type="button"><i class="ti ti-x text-white fs-6"></i></button>' +
                    '</div>';
                    
                $span.data('original-html', originalHTML);
                $span.html(containerHTML);
                $span.find('.inline-edit-input').focus();
            });
            
            // Cancel inline edit
            $(document).on('click', '.btn-inline-cancel', function(e) {
                e.stopPropagation();
                var $span = $(this).closest('.editable-field');
                $span.html($span.data('original-html'));
            });
            
            // Save inline edit
            $(document).on('click', '.btn-inline-save', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                var $span = $btn.closest('.editable-field');
                var fieldName = $span.data('name');
                var isSystem = $span.data('system');
                var fieldType = $span.data('type');
                
                var $input = $span.find('.inline-edit-input');
                var fieldValue = $input.val();
                
                var formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('field_name', fieldName);
                formData.append('is_system', isSystem);
                
                if (fieldType === 'file') {
                    if ($input[0].files.length > 0) {
                        formData.append('field_value', $input[0].files[0]);
                    } else {
                        $span.html($span.data('original-html'));
                        return;
                    }
                } else if (fieldType === 'multi_select') {
                    var selectedVals = $input.val() || [];
                    selectedVals.forEach(function(val) {
                        formData.append('field_value[]', val);
                    });
                } else {
                    formData.append('field_value', fieldValue);
                }
                
                $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm text-white"></i>');
                
                $.ajax({
                    url: "{{ route('leads.inline-update', $lead->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.is_success) {
                            var displayVal = res.value !== undefined ? res.value : fieldValue;
                            $span.attr('data-value', displayVal);
                            
                            if (!displayVal || displayVal === '-') {
                                $span.html('<span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __("Not Provided") }}</span>');
                            } else if (fieldType === 'multi_select') {
                                var items = displayVal.split(',');
                                var badgesHTML = '';
                                items.forEach(function(item) {
                                    badgesHTML += '<span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">' + item.trim() + '</span>';
                                });
                                $span.html(badgesHTML);
                            } else if (fieldType === 'file') {
                                $span.html('<a href="{{ asset("storage/uploads/custom_fields") }}/' + displayVal + '" download class="btn btn-xs btn-outline-success rounded-pill"><i class="ti ti-download me-1"></i> {{ __("Download") }}</a>');
                            } else if (fieldName === 'email' && displayVal) {
                                $span.html('<a href="mailto:' + displayVal + '" class="text-primary hover-underline">' + displayVal + '</a>');
                            } else if (fieldName === 'phone' && displayVal) {
                                $span.html(displayVal + ' <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="' + displayVal + '" data-bs-toggle="tooltip" title="{{ __("Call") }}"><i class="ti ti-phone-call"></i></a>');
                            } else {
                                $span.text(displayVal);
                            }
                            
                            toastrs('Success', res.message || "{{ __('Field updated successfully.') }}", 'success');
                        } else {
                            toastrs('Error', res.error || "{{ __('Failed to update field.') }}", 'error');
                            $span.html($span.data('original-html'));
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.error : "{{ __('Failed to update field.') }}";
                        toastrs('Error', err, 'error');
                        $span.html($span.data('original-html'));
                    }
                });
            });

            // AJAX trigger for Section API Sync
            $(document).on('click', '.sync-section-api-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $icon = $btn.find('i');
                var sectionId = $btn.data('section-id');
                var leadId = $btn.data('lead-id');

                $icon.removeClass('ti-refresh').addClass('ti-loader animate-spin');
                $btn.addClass('disabled');

                $.ajax({
                    url: '{{ route("leads.sync-section-api") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        lead_id: leadId,
                        section_id: sectionId
                    },
                    success: function(response) {
                        if (response.success) {
                            toastrs('Success', response.success, 'success');
                            if (response.values) {
                                $.each(response.values, function(fieldId, value) {
                                    var $fieldSpan = $('[data-name="' + fieldId + '"]');
                                    if ($fieldSpan.length > 0) {
                                        $fieldSpan.attr('data-value', value);
                                        $fieldSpan.text(value);
                                    }
                                });
                                // Reload to update fields properly
                                setTimeout(function() {
                                    location.reload();
                                }, 800);
                            }
                        } else {
                            toastrs('Error', response.error || 'Sync failed', 'error');
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Network error';
                        toastrs('Error', err, 'error');
                    },
                    complete: function() {
                        $icon.removeClass('ti-loader animate-spin').addClass('ti-refresh');
                        $btn.removeClass('disabled');
                    }
                });
            });
        });
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            display: inline-block;
            animation: spin 1.5s linear infinite;
        }
    </style>
@endpush