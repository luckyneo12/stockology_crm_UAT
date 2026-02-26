{{ Form::open(['route' => 'lead_tasks.store', 'method' => 'post', 'id' => 'bulk_task_form', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body p-4">
    
    <!-- Section 1: Target Leads -->
    <div class="card shadow-none border mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
             <h6 class="mb-0"><i class="ti ti-target me-2 text-primary"></i>{{ __('Target Audience') }}</h6>
             <div class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="target_type" id="target_single" value="single" autocomplete="off">
                <label class="btn btn-outline-primary" for="target_single">{{ __('Single Lead') }}</label>

                <input type="radio" class="btn-check" name="target_type" id="target_filter" value="filter" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="target_filter">{{ __('Bulk Filter') }}</label>
            </div>
        </div>
        <div class="card-body">
            <!-- Single Lead Selector -->
            <div class="row d-none" id="section_single_lead">
                 <div class="col-md-12">
                    <div class="form-group mb-0">
                        {{ Form::label('lead_id', __('Select Lead'), ['class' => 'form-label fw-bold']) }}
                        {{ Form::select('lead_id', $leads, null, ['class' => 'form-control choices', 'placeholder' => __('Search and select a lead...'), 'data-trigger']) }}
                    </div>
                </div>
            </div>

            <!-- Bulk Filter Options -->
            <div class="row g-4" id="section_bulk_filter">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        {{ Form::label('filter_stage_id', __('Filter by Stage'), ['class' => 'form-label fw-bold']) }}
                        {{ Form::select('filter_stage_id[]', $stages, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'modal_filter_stage_id', 'data-placeholder' => __('Select Stages'), 'data-trigger', 'searchEnabled' => 'true']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        {{ Form::label('filter_user_id', __('Filter by Owner'), ['class' => 'form-label fw-bold']) }}
                        {{ Form::select('filter_user_id[]', $users, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'modal_filter_user_id', 'data-placeholder' => __('Select Owners'), 'data-trigger', 'searchEnabled' => 'true']) }}
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="form-group mb-0">
                        {{ Form::label('filter_date_start', __('Created From'), ['class' => 'form-label fw-bold']) }}
                        <div class="input-group">
                             <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                            {{ Form::date('filter_date_start', null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="form-group mb-0">
                        {{ Form::label('filter_date_end', __('Created To'), ['class' => 'form-label fw-bold']) }}
                         <div class="input-group">
                             <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                            {{ Form::date('filter_date_end', null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Section 2: Actions -->
     <h6 class="mb-3 text-uppercase text-muted fs-6 fw-bold small">{{ __('Action Details') }}</h6>
     
     <div class="row g-4">
        <!-- Task Card -->
        <div class="col-12">
            <div class="card shadow-sm border mb-0 task-card">
                 <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-between cursor-pointer" onclick="document.getElementById('create_task').click()">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="create_task" name="create_task" value="1" checked onclick="event.stopPropagation()">
                        <label class="form-check-label fw-bold lead-option-label" for="create_task">
                            <i class="ti ti-list-check me-2 text-success"></i>{{ __('Create Task') }}
                        </label>
                    </div>
                     <i class="ti ti-chevron-down text-muted transition-icon" id="task_chevron"></i>
                </div>
                <div class="card-body bg-light border-top" id="section_task_fields">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                {{ Form::label('task_subject', __('Subject'), ['class' => 'form-label']) }}
                                {{ Form::text('task_subject', null, ['class' => 'form-control', 'placeholder' => __('Enter task subject...')]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group mb-0">
                                {{ Form::label('task_date', __('Task Date'), ['class' => 'form-label']) }}
                                {{ Form::date('task_date', date('Y-m-d'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                {{ Form::label('task_priority', __('Priority'), ['class' => 'form-label']) }}
                                <select name="task_priority" class="form-control choices" id="modal_task_priority" data-trigger searchEnabled="true">
                                    <option value="1">{{ __('Low') }}</option>
                                    <option value="2">{{ __('Medium') }}</option>
                                    <option value="3">{{ __('High') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reminder Card -->
         <div class="col-12">
            <div class="card shadow-sm border mb-0 reminder-card">
                 <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-between cursor-pointer" onclick="document.getElementById('create_reminder').click()">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="create_reminder" name="create_reminder" value="1" onclick="event.stopPropagation()">
                        <label class="form-check-label fw-bold lead-option-label" for="create_reminder">
                            <i class="ti ti-bell me-2 text-warning"></i>{{ __('Create Reminder') }}
                        </label>
                    </div>
                      <i class="ti ti-chevron-down text-muted transition-icon" id="reminder_chevron" style="transform: rotate(-90deg);"></i>
                </div>
                <div class="card-body bg-light border-top d-none" id="section_reminder_fields">
                    <div class="row g-3">
                         <div class="col-md-6">
                             <div class="form-group mb-0">
                                {{ Form::label('reminder_date', __('Date'), ['class' => 'form-label']) }}
                                {{ Form::date('reminder_date', date('Y-m-d'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                         <div class="col-md-6">
                             <div class="form-group mb-0">
                                {{ Form::label('reminder_time', __('Time'), ['class' => 'form-label']) }}
                                {{ Form::time('reminder_time', '09:00', ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-12">
                             <div class="form-group mb-0">
                                {{ Form::label('reminder_description', __('Description'), ['class' => 'form-label']) }}
                                {{ Form::textarea('reminder_description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter reminder details...')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Assignment -->
    <div class="mt-4 pt-3 border-top">
        <h6 class="mb-3 text-uppercase text-muted fs-6 fw-bold small">{{ __('Assignment') }}</h6>
        <div class="card bg-warning bg-opacity-10 border-warning border-start border-4">
             <div class="card-body py-2">
                <div class="form-check custom-checkbox d-flex align-items-center">
                    <input class="form-check-input me-2" type="checkbox" name="assign_to_owner" id="assign_to_owner" value="1" checked>
                    <div>
                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="assign_to_owner">{{ __('Assign to Lead Owner') }}</label>
                        <small class="d-block text-muted text-xs">{{ __('Tasks will be assigned to the user responsible for each lead.') }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3 d-none transition-all" id="section_assign_user">
              <div class="form-group">
                {{ Form::label('assigned_user_id', __('Assign To (Override)'), ['class' => 'form-label fw-bold']) }}
                {{ Form::select('assigned_user_id', $users, null, ['class' => 'form-control choices', 'placeholder' => __('Select User to assign all tasks...'), 'data-trigger']) }}
            </div>
        </div>
    </div>

</div>
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary shadow">{{ __('Create Actions') }}</button>
</div>
{{ Form::close() }}

<style>
    .transition-icon {
        transition: transform 0.3s ease;
    }
    .lead-option-label {
        cursor: pointer;
        user-select: none;
    }
    .card.shadow-none {
        box-shadow: none !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    $(document).ready(function() {
        // Target Type Toggle
        $('input[name="target_type"]').change(function() {
            if ($(this).val() == 'single') {
                $('#section_bulk_filter').addClass('d-none');
                $('#section_single_lead').removeClass('d-none');
            } else {
                 $('#section_bulk_filter').removeClass('d-none');
                $('#section_single_lead').addClass('d-none');
            }
        });
        
        // Task Toggle and Chevron Animation
        $('#create_task').change(function() {
            if($(this).is(':checked')) {
                 $('#section_task_fields').slideDown();
                 $('#task_chevron').css('transform', 'rotate(0deg)');
            } else {
                 $('#section_task_fields').slideUp();
                 $('#task_chevron').css('transform', 'rotate(-90deg)');
            }
        });

        // Reminder Toggle and Chevron Animation
         $('#create_reminder').change(function() {
            if($(this).is(':checked')) {
                 $('#section_reminder_fields').removeClass('d-none').slideDown();
                 $('#reminder_chevron').css('transform', 'rotate(0deg)');
            } else {
                 $('#section_reminder_fields').slideUp();
                 $('#reminder_chevron').css('transform', 'rotate(-90deg)');
            }
        });

        // Assignment Toggle
         $('#assign_to_owner').change(function() {
            if($(this).is(':checked')) {
                 $('#section_assign_user').addClass('d-none');
            } else {
                 $('#section_assign_user').removeClass('d-none');
            }
        });


    });
</script>
