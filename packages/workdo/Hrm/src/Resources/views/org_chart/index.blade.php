@extends('layouts.main')
@section('page-title')
    {{ __('Company Org Chart') }}
@endsection

@push('css')
<style>
    /* Container Spacing */
    .org-chart-container {
        padding: 50px; /* Uniform padding */
        padding-top: 80px; /* Extra top space */
    }

    .org-tree ul {
        padding-top: 40px; 
        position: relative;
        transition: all 0.5s;
        -webkit-transition: all 0.5s;
        -moz-transition: all 0.5s;
        
        display: flex; /* Flexbox execution */
        flex-direction: row; /* Force Horizontal */
        flex-wrap: nowrap; /* Prevent Wrapping */
        justify-content: center; /* Center align the group */
    }

    .org-tree li {
        text-align: center; /* Removed float: left */
        list-style-type: none;
        position: relative;
        padding: 40px 20px 0 20px;
        transition: all 0.5s;
        -webkit-transition: all 0.5s;
        -moz-transition: all 0.5s;
        
        flex-shrink: 0; /* Prevent squishing */
    }

    /* Connectors - Styled like Inspiration (Blue-ish) */
    .org-tree li::before, .org-tree li::after{
        content: '';
        position: absolute; top: 0; right: 50%;
        border-top: 2px solid #5aa5ff; /* Blue color */
        width: 50%; height: 40px;
    }
    .org-tree li::after{
        right: auto; left: 50%;
        border-left: 2px solid #5aa5ff; /* Blue color */
    }

    .org-tree li:only-child::after, .org-tree li:only-child::before {
        display: none;
    }

    .org-tree li:only-child{ padding-top: 0;}

    .org-tree li:first-child::before, .org-tree li:last-child::after{
        border: 0 none;
    }

    .org-tree li:last-child::before{
        border-right: 2px solid #5aa5ff; /* Blue color */
        border-radius: 0 5px 0 0;
        -webkit-border-radius: 0 5px 0 0;
        -moz-border-radius: 0 5px 0 0;
    }
    .org-tree li:first-child::after{
        border-radius: 5px 0 0 0;
        -webkit-border-radius: 5px 0 0 0;
        -moz-border-radius: 5px 0 0 0;
    }

    .org-tree ul ul::before{
        content: '';
        position: absolute; top: 0; left: 50%;
        border-left: 2px solid #5aa5ff; /* Blue color */
        width: 0; height: 40px;
    }

    /* Card Styling */
    .oc-node .oc-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px; /* Softer corners */
        padding: 15px;
        min-width: 180px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        display: inline-block; /* Ensure it wraps content properly */
    }

    .oc-node .oc-card:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.5);
    }
    
    .oc-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.2);
        object-fit: cover;
        margin-bottom: 10px;
        background: #333; /* Fallback for transparent images */
    }

    .oc-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #fff;
    }

    .oc-role {
        font-size: 13px;
        color: rgba(255,255,255,0.7);
        margin-bottom: 10px;
    }

    .oc-btn-view {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 30px; 
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    .oc-btn-view:hover {
        background: #6fd943;
        color: white;
    }
    
    /* Drag & Drop Visuals */
    /* Drag & Drop Visuals */
    .droppable-area {
        padding: 10px; /* Increase hit area */
        border-radius: 12px;
        transition: 0.2s;
        border: 2px solid transparent; /* Placeholder for hover state */
    }
    .draggable-node[draggable="true"] {
        cursor: grab;
    }
    .draggable-node[draggable="true"]:active {
        cursor: grabbing;
    }
    
    .droppable-area.drag-over {
        border-color: #6fd943;
        background: rgba(111, 217, 67, 0.2);
    }
    
    .droppable-area.drag-over .oc-card {
        transform: scale(1.05); /* Slight zoom on hover */
    }
        border-color: #6fd943;
        box-shadow: 0 0 15px rgba(111, 217, 67, 0.5);
        background: rgba(111, 217, 67, 0.1);
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card glass-card modern-shadow" style="background: #1e1e24; border: 1px solid #333; min-height: 80vh;">
                <div class="card-body p-0" style="position: relative; overflow: hidden;">
                    
                    <!-- Toolbar -->
                    <div class="d-flex justify-content-between align-items-center p-4" style="background: rgba(0,0,0,0.2); border-bottom: 1px solid #333; z-index: 10; position: relative;">
                        <h4 class="text-white mb-0">{{ __('Interactive Organization Chart') }}</h4>
                        <div>
                             <span class="text-muted text-xs me-2"><i class="ti ti-info-circle"></i> {{ __('Drag cards to reassign managers') }}</span>
                            <button class="btn btn-sm btn-primary btn-icon m-1" onclick="window.location.reload()">
                                <i class="ti ti-refresh"></i> {{ __('Refresh') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Grid Background -->
                    <div class="org-grid-bg" style="
                        position: absolute; top:0; left:0; right:0; bottom:0;
                        background-size: 40px 40px;
                        background-image:
                            linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                            linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
                    "></div>

                    <!-- Chart Container -->
                    <div class="org-chart-container position-relative" style="overflow: auto; height: 75vh; padding: 50px;">
                        <div class="org-tree text-center">
                            <ul>
                                <!-- Admin/Root Node -->
                                <li>
                                    <div class="oc-node droppable-area" data-id="root">
                                        <div class="oc-card glass" style="border-color: #6fd943; box-shadow: 0 0 15px rgba(111, 217, 67, 0.3);">
                                            <div class="oc-img-container">
                                                <img src="{{ !empty(Auth::user()->avatar) ? check_file(Auth::user()->avatar) : asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}" 
                                                     alt="Admin" 
                                                     class="oc-img"
                                                     onerror="this.onerror=null;this.src='{{ asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}';">
                                            </div>
                                            <div class="oc-content">
                                                <h4 class="oc-name text-white">{{ Auth::user()->name }}</h4>
                                                <p class="oc-role text-white-50">{{ __('Administrator') }}</p>
                                                <div class="oc-actions">
                                                    <span class="badge bg-success">{{ __('Root') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Children (Top Level Employees) -->
                                    @if($employees->count() > 0)
                                        <ul>
                                            @foreach($employees as $employee)
                                                 @include('hrm::org_chart.tree_node', ['employee' => $employee])
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Drag & Drop Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const draggables = document.querySelectorAll('.draggable-node');
            const droppables = document.querySelectorAll('.droppable-area');

            let draggedItem = null;

            draggables.forEach(user => {
                user.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    setTimeout(() => {
                        this.style.opacity = '0.5';
                    }, 0);
                    e.dataTransfer.effectAllowed = "move"; 
                    e.dataTransfer.setData('employee-id', this.getAttribute('data-id'));
                });

                user.addEventListener('dragend', function() {
                    draggedItem = null;
                    this.style.opacity = '1';
                });
            });

            droppables.forEach(zone => {
                zone.addEventListener('dragover', function(e) {
                     e.preventDefault(); 
                     e.stopPropagation(); // Stop bubbling
                     this.classList.add('drag-over');
                });
                
                zone.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                });

                zone.addEventListener('dragleave', function(e) {
                    e.preventDefault(); // Good practice
                    this.classList.remove('drag-over');
                });

                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Stop bubbling
                    this.classList.remove('drag-over');
                    
                    const employeeId = e.dataTransfer.getData('employee-id');
                    const newParentId = this.getAttribute('data-id'); // 'root' or ID or empty

                    // Allow newParentId to be 'root' or valid ID or even 'null' string if that happens
                    // Logic: Must have employeeId. newParentId can be anything as long as it exists (even empty string if we didn't fix that earlier, but we did)
                    // We fixed Admin to have data-id="root"
                    
                    if(employeeId && newParentId && employeeId != newParentId) {
                         // Debugging Feedback
                         // Use toastrs based on codebase common usage
                         if(typeof toastrs === 'function') {
                            toastrs('Info', 'Updating... Emp: ' + employeeId + ' -> Parent: ' + newParentId, 'info');
                         }
                         updateHierarchy(employeeId, newParentId);
                    } else {
                        // Fallback/Debug if drop logic is rejected
                        if (employeeId == newParentId) {
                             if(typeof toastrs === 'function') {
                                toastrs('Error', 'Cannot assign manager to self.', 'error');
                             } else {
                                alert('Cannot assign manager to self.');
                             }
                        }
                    }
                });
            });

            function updateHierarchy(empId, parentId) {
                const formData = new FormData();
                formData.append('employee_id', empId);
                formData.append('parent_id', parentId);
                formData.append('_token', '{{ csrf_token() }}');

                if(typeof toastrs === 'function') {
                    toastrs('Info', 'Updating Hierarchy...', 'info');
                }

                fetch('{{ route('hrm.org_chart.update_hierarchy') }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        if(typeof toastrs === 'function') {
                            toastrs('Success', data.message, 'success');
                        }
                        setTimeout(() => location.reload(), 1000); // Reload to reflect structure
                    } else {
                         if(typeof toastrs === 'function') {
                            toastrs('Error', data.message, 'error');
                         } else {
                            alert(data.message);
                         }
                    }
                })
                .catch(err => {
                    console.error(err);
                     if(typeof toastrs === 'function') {
                        toastrs('Error', 'Something went wrong.', 'error');
                     }
                });
            }

            // Remove from Hierarchy Function
            window.removeFromHierarchy = function(empId) {
                if (!confirm('{{ __("Are you sure you want to remove this employee from the hierarchy? They will become a root-level employee.") }}')) {
                    return;
                }

                const formData = new FormData();
                formData.append('employee_id', empId);
                formData.append('_token', '{{ csrf_token() }}');

                if(typeof toastrs === 'function') {
                    toastrs('Info', 'Removing from hierarchy...', 'info');
                }

                fetch('{{ route('hrm.org_chart.remove_from_hierarchy') }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        if(typeof toastrs === 'function') {
                            toastrs('Success', data.message, 'success');
                        } else {
                            alert(data.message);
                        }
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if(typeof toastrs === 'function') {
                            toastrs('Error', data.message, 'error');
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if(typeof toastrs === 'function') {
                        toastrs('Error', 'Something went wrong.', 'error');
                    }
                });
            };
        });
    </script>
@endsection
