<div class="row g-4 align-items-stretch">
    <div class="col-md-7">
        <div class="d-flex align-items-center mb-3">
            <i class="ti ti-users text-primary me-2 fs-5"></i>
            <h6 class="mb-0 fw-bold">{{ __('Current Users') }}</h6>
            <span class="badge bg-light-primary text-primary ms-2 rounded-pill">{{ count($employees) }}</span>
        </div>
        <div class="table-responsive" style="max-height: 380px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 10px; background: #fff;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th class="ps-3 border-0 py-3">{{ __('Name') }}</th>
                        <th class="border-0 py-3">{{ __('Role') }}</th>
                        <th class="text-end pe-3 border-0 py-3">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ (!empty($emp->user) && check_file($emp->user->avatar)) ? get_file($emp->user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="rounded-circle me-2 border" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <span class="fw-semibold text-dark d-block">{{ $emp->user->name ?? ($emp->name ?? __('Unknown')) }}</span>
                                        <small class="text-muted" style="font-size:0.75rem;">{{ $emp->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light-success text-success px-2.5 py-1.5 rounded-pill text-xs fw-semibold">{{ ucfirst($emp->user->type ?? '-') }}</span>
                            </td>
                            <td class="text-end pe-3 py-3">
                                @permission('department edit')
                                    <button class="btn btn-sm btn-outline-danger btn-remove-user p-2 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px;" data-emp-id="{{ $emp->id }}" data-dept-id="{{ $department->id }}" data-bs-toggle="tooltip" title="{{ __('Remove from Department') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                @endpermission
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="ti ti-users-group fs-2 d-block mb-2 text-muted"></i>
                                <span>{{ __('No users in this department.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-5">
        <div class="h-100 p-4 rounded-3 border bg-light d-flex flex-column justify-content-between" style="border-color: #f1f5f9 !important;">
            <div>
                <div class="d-flex align-items-center mb-3">
                    <i class="ti ti-user-plus text-success me-2 fs-5"></i>
                    <h6 class="mb-0 fw-bold">{{ __('Add User') }}</h6>
                </div>
                <p class="text-muted small mb-3">{{ __('Assign an existing employee to this department.') }}</p>
                <div class="form-group mb-4">
                    <select class="form-control select2-searchable" id="user_to_add" style="width:100%;">
                        <option value="">{{ __('Search user by name or email...') }}</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-success rounded-pill w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2" id="btn_add_user_to_dept" data-dept-id="{{ $department->id }}">
                <i class="ti ti-circle-plus"></i> {{ __('Add User') }}
            </button>
        </div>
    </div>
</div>

<style>
    /* Select2 override for modal */
    .select2-container--default .select2-selection--single {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        height: 42px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155;
        line-height: 42px;
        padding-left: 14px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        z-index: 9999;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.9rem;
        outline: none;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #18bf6b;
        color: #fff;
    }
    .select2-results__option {
        padding: 10px 14px;
        font-size: 0.88rem;
    }
    .select2-container { width: 100% !important; }
</style>

<script>
    // Initialize Select2 on the searchable dropdown
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#user_to_add').select2({
                placeholder: '{{ __("Search user by name or email...") }}',
                allowClear: true,
                dropdownParent: $('#deptModal'),
                width: '100%'
            });
        }
    });

    // Function to refresh modal content with a slight fade effect
    function refreshModal(deptId) {
        var modalBody = $('#deptModalBody');
        modalBody.css('opacity', '0.5');
        $.ajax({
            url: '{{ url("department-users") }}/' + deptId,
            success: function(html) {
                modalBody.html(html);
                modalBody.css('opacity', '1');
            },
            error: function() {
                modalBody.css('opacity', '1');
                show_toastr('Error', '{{ __("Failed to refresh user list.") }}', 'error');
            }
        });
    }

    $('#btn_add_user_to_dept').on('click', function() {
        var btn = $(this);
        var userId = $('#user_to_add').val();
        var deptId = btn.data('dept-id');

        if(!userId) {
            show_toastr('Error', '{{ __("Please select a user.") }}', 'error');
            return;
        }

        // Loading state
        btn.attr('disabled', true);
        var originalHtml = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> {{ __("Adding...") }}');

        $.ajax({
            url: '{{ route('department.user.add') }}',
            type: 'POST',
            data: {
                user_id: userId,
                department_id: deptId,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if(data.success) {
                    show_toastr('Success', data.message, 'success');
                    refreshModal(deptId);
                } else {
                    show_toastr('Error', data.message, 'error');
                    btn.attr('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                show_toastr('Error', '{{ __("Something went wrong.") }}', 'error');
                btn.attr('disabled', false).html(originalHtml);
            }
        });
    });

    // Remove user from department
    $(document).on('click', '.btn-remove-user', function() {
        var btn = $(this);
        var empId = btn.data('emp-id');
        var deptId = btn.data('dept-id');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '{{ __("Are you sure?") }}',
                text: '{{ __("You want to remove this user from the department?") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("Yes, remove it!") }}',
                cancelButtonText: '{{ __("No, cancel") }}',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary ms-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    executeRemove();
                }
            });
        } else {
            if(confirm('{{ __("Are you sure you want to remove this user from the department?") }}')) {
                executeRemove();
            }
        }

        function executeRemove() {
            // Loading state
            btn.attr('disabled', true);
            var originalHtml = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');

            $.ajax({
                url: '{{ route('department.user.remove') }}',
                type: 'POST',
                data: {
                    employee_id: empId,
                    department_id: deptId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    if(data.success) {
                        show_toastr('Success', data.message, 'success');
                        refreshModal(deptId);
                    } else {
                        show_toastr('Error', data.message, 'error');
                        btn.attr('disabled', false).html(originalHtml);
                    }
                },
                error: function() {
                    show_toastr('Error', '{{ __("Something went wrong.") }}', 'error');
                    btn.attr('disabled', false).html(originalHtml);
                }
            });
        }
    });
</script>
