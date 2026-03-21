<div class="row">
    <div class="col-md-7">
        <h6>{{ __('Current Users') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ (!empty($emp->user) && check_file($emp->user->avatar)) ? get_file($emp->user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="wid-20 rounded-circle me-2">
                                    <span>{{ $emp->user->name ?? ($emp->name ?? __('Unknown')) }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-light-primary text-primary">{{ ucfirst($emp->user->type ?? '-') }}</span></td>
                            <td class="text-end">
                                @permission('department edit')
                                    <button class="btn btn-sm btn-danger btn-remove-user" data-emp-id="{{ $emp->id }}" data-dept-id="{{ $department->id }}" data-bs-toggle="tooltip" title="{{ __('Remove from Department') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                @endpermission
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">{{ __('No users in this department.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-5 border-start">
        <h6>{{ __('Add User to Department') }}</h6>
        <div class="form-group mb-3">
            <select class="form-control" id="user_to_add">
                <option value="">{{ __('Select User') }}</option>
                @foreach($availableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary btn-sm w-100" id="btn_add_user_to_dept" data-dept-id="{{ $department->id }}">{{ __('Add User') }}</button>
    </div>
</div>

<script>
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
