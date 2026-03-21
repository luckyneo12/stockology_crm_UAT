<script>
    $(document).ready(function () {
        // Embed the current user's extensions
        var ext1 = "{{ Auth::user() ? Auth::user()->extension_1 : '' }}";
        var ext2 = "{{ Auth::user() ? Auth::user()->extension_2 : '' }}";
        var activeIdx = "{{ Auth::user() ? Auth::user()->active_extension : 1 }}";
        var currentUserExtension = (activeIdx == 2) ? ext2 : ext1;
        
        @php
            $user = Auth::user();
            $settings = $user ? getCompanyAllSetting($user->id, $user->workspace_id) : [];
            
            // Collect Available APIs based on priority (User -> Dept -> Global)
            $availableApis = [];
            
            // 1. Check Individual User Overrides
            for($i=1; $i<=2; $i++) {
                if(!empty($settings['user_api_'.$i.'_url_'.$user->id])) {
                    $availableApis[] = [
                        'id' => 'user_'.$i,
                        'name' => $settings['user_api_'.$i.'_name_'.$user->id] ?: 'User API '.$i,
                    ];
                }
            }
            
            // 2. Check Dept Overrides (if User has no specific APIs)
            if(empty($availableApis) && module_is_active('Hrm', $user->workspace_id)) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                if($employee && $employee->department_id) {
                    for($i=1; $i<=2; $i++) {
                        if(!empty($settings['dept_api_'.$i.'_url_'.$employee->department_id])) {
                            $availableApis[] = [
                                'id' => 'dept_'.$i,
                                'name' => $settings['dept_api_'.$i.'_name_'.$employee->department_id] ?: 'Dept API '.$i,
                            ];
                        }
                    }
                }
            }
            
            // 3. Fallback to Global APIs
            if(empty($availableApis)) {
                for($i=1; $i<=3; $i++) {
                    if(!empty($settings['global_calling_api_'.$i.'_url'])) {
                        $availableApis[] = [
                            'id' => 'global_'.$i,
                            'name' => $settings['global_calling_api_'.$i.'_name'] ?: 'Global API '.$i,
                        ];
                    }
                }
            }

            $ext1Api = $settings['user_ext_1_api_id_'.$user->id] ?? '';
            $ext2Api = $settings['user_ext_2_api_id_'.$user->id] ?? '';
        @endphp
        
        var availableApis = @json($availableApis);
        var ext1Api = "{{ $ext1Api }}";
        var ext2Api = "{{ $ext2Api }}";

        $(document).on('click', '.switch-extension-btn', function (e) {
            e.preventDefault();
            var index = $(this).data('index');
            $.ajax({
                url: '{{ route('lead.call.switch_extension') }}',
                type: 'POST',
                data: { active_index: index, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.status === 'success') {
                        show_toastr('Success', res.message, 'success');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    show_toastr('Error', (xhr.responseJSON ? xhr.responseJSON.message : 'Error'), 'error');
                }
            });
        });
        
        $(document).on('click', '.switch-api-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: '{{ route('lead.call.switch_api') }}',
                type: 'POST',
                data: { active_api_id: id, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.status === 'success') {
                        show_toastr('Success', res.message, 'success');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    show_toastr('Error', (xhr.responseJSON ? xhr.responseJSON.message : 'Error'), 'error');
                }
            });
        });

        $(document).on('click', '#manualExtensionPrompt, #manualExtensionPrompt2', function (e) {
            e.preventDefault();
            promptForExtension('{{ __("Update your Call Settings") }}');
        });

        $(document).on('click', '.click-to-call', function (e) {
            e.preventDefault();
            var phoneNumber = $(this).attr('data-phone');
            var btn = $(this);

            if (!phoneNumber) {
                show_toastr('Error', '{{ __("No phone number available.") }}', 'error');
                return;
            }

            // Optional loading state
            var originalIcon = btn.html();

            function makeCallRequest() {
                btn.html('<i class="ti ti-loader rotate-icon"></i>');
                $.ajax({
                    url: '{{ route('lead.call.make') }}',
                    type: 'POST',
                    data: {
                        phone_number: phoneNumber,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        btn.html(originalIcon);
                        if (response.status === 'copy') {
                            // No API URL configured — copy number to clipboard
                            var numToCopy = response.phone_number || phoneNumber;
                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(numToCopy).then(function () {
                                    show_toastr('Info', '{{ __("Number copied to clipboard: ") }}' + numToCopy, 'success');
                                }).catch(function () {
                                    show_toastr('Info', '{{ __("Number: ") }}' + numToCopy, 'info');
                                });
                            } else {
                                // Fallback for non-secure contexts
                                var $temp = $('<input>');
                                $('body').append($temp);
                                $temp.val(numToCopy).select();
                                document.execCommand("copy");
                                $temp.remove();
                                show_toastr('Info', '{{ __("Number copied to clipboard: ") }}' + numToCopy, 'success');
                            }
                        } else if (response.status === 'success' && response.url) {
                            var callUrl = response.url;

                            // Any URL not starting with http is treated as a protocol handler (zoiper:, tel:, etc.)
                            if (callUrl.indexOf('http') !== 0) {
                                window.location.href = callUrl;
                                show_toastr('Success', '{{ __("Initiating call via application...") }}', 'success');
                            } else {
                                var iframe = $('<iframe>', {
                                    src: callUrl,
                                    id: 'call_frame',
                                    frameborder: 0,
                                    width: 1,
                                    height: 1,
                                    style: 'display:none;'
                                }).appendTo('body');

                                setTimeout(function () {
                                    iframe.remove();
                                }, 5000);

                                show_toastr('Success', '{{ __("Call initiated via gateway.") }}', 'success');
                            }
                        } else {
                            show_toastr('Error', response.message || 'Error occurred.', 'error');
                        }
                    },
                    error: function (xhr) {
                        btn.html(originalIcon);
                        var errorMsg = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        if (xhr.responseJSON && xhr.responseJSON.code === 'MISSING_EXTENSION') {
                            promptForExtension(errorMsg);
                        } else {
                            show_toastr('Error', errorMsg, 'error');
                        }
                    }
                });
            }

            // Check immediately on the frontend FIRST before doing any API calls
            if (!currentUserExtension || currentUserExtension.trim() === '') {
                promptForExtension('{{ __("Please set your extension before making a call.") }}');
            } else {
                makeCallRequest();
            }
        });

        function promptForExtension(message) {
            Swal.fire({
                title: '{{ __("Call Settings") }}',
                html: `
                    <div style="max-height: 60vh; overflow-y: auto; padding-right: 5px;">
                        <p class="text-muted small mb-3">${message}</p>
                        
                        <h6 class="text-start border-bottom pb-2 fw-bold text-primary">{{ __('Extensions') }}</h6>
                        <div class="form-group text-start">
                            <label class="form-label fw-bold">{{ __('Extension 1 (Primary)') }}</label>
                            <input type="text" id="swal_ext_1" class="form-control" value="${ext1}" placeholder="e.g. 101">
                        </div>
                        <div class="form-group text-start mt-3">
                            <label class="form-label fw-bold">{{ __('Extension 2 (Secondary)') }}</label>
                            <input type="text" id="swal_ext_2" class="form-control" value="${ext2}" placeholder="e.g. 102">
                        </div>
                        
                        <h6 class="text-start border-bottom pb-2 mt-4 fw-bold text-success">{{ __('Extension API Mappings') }}</h6>
                        <div class="form-group text-start">
                            <label class="form-label fw-bold">{{ __('API for Extension 1') }}</label>
                            <select id="swal_api_ext_1" class="form-control">
                                <option value="">{{ __('Default API') }}</option>
                                ${availableApis.map(api => `<option value="${api.id}" ${ext1Api == api.id ? 'selected' : ''}>${api.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="form-group text-start mt-3">
                            <label class="form-label fw-bold">{{ __('API for Extension 2') }}</label>
                            <select id="swal_api_ext_2" class="form-control">
                                <option value="">{{ __('Default API') }}</option>
                                ${availableApis.map(api => `<option value="${api.id}" ${ext2Api == api.id ? 'selected' : ''}>${api.name}</option>`).join('')}
                            </select>
                        </div>

                        <small class="text-info d-block mt-3 text-start"><i class="ti ti-info-circle me-1"></i>{{ __("Extensions can only be saved once every 24 hours.") }}</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '{{ __("Save Settings") }}',
                cancelButtonText: '{{ __("Cancel") }}',
                width: '500px',
                preConfirm: () => {
                    const e1 = document.getElementById('swal_ext_1').value;
                    const e2 = document.getElementById('swal_ext_2').value;
                    const api1 = document.getElementById('swal_api_ext_1').value;
                    const api2 = document.getElementById('swal_api_ext_2').value;
                    
                    if (!e1) {
                        Swal.showValidationMessage('{{ __("Extension 1 is required!") }}');
                    }
                    return { 
                        extension_1: e1, 
                        extension_2: e2,
                        api_ext_1: api1,
                        api_ext_2: api2
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('lead.call.save_extension') }}',
                        type: 'POST',
                        data: {
                            extension_1: result.value.extension_1,
                            extension_2: result.value.extension_2,
                            api_ext_1: result.value.api_ext_1,
                            api_ext_2: result.value.api_ext_2,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                show_toastr('Success', res.message, 'success');
                                location.reload();
                            }
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to save.';
                            show_toastr('Error', msg, 'error');
                        }
                    });
                }
            });
        }
    });
</script>
<style>
    .rotate-icon {
        animation: spin 1s linear infinite;
        display: inline-block;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }
</style>