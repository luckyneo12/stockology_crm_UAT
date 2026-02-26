<li>
    <div class="oc-node droppable-area" data-id="{{ $employee->id }}">
        <div class="oc-card glass draggable-node" draggable="true" data-id="{{ $employee->id }}" style="cursor: move;">
            <div class="oc-img-container">
                <img src="{{ !empty($employee->user->avatar) ? check_file($employee->user->avatar) : asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}" 
                     alt="{{ $employee->name }}" 
                     class="oc-img"
                     onerror="this.onerror=null;this.src='{{ asset('packages/workdo/Hrm/src/Resources/assets/image/default.png') }}';">
            </div>
            <div class="oc-content">
                <h4 class="oc-name text-white">{{ $employee->name }}</h4>
                <p class="oc-role text-white-50 mb-1">{{ $employee->designation->name ?? '-' }}</p>
                <div class="oc-details text-xs text-white-50">
                    <span class="d-block">{{ $employee->department->name ?? '' }}</span>
                    <span class="d-block">{{ $employee->branch->name ?? '' }}</span>
                </div>
                <div class="oc-actions mt-2">
                     @if(Auth::user()->isAbleTo('employee show'))
                    <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->user_id)) }}" class="oc-btn-view" data-bs-toggle="tooltip" title="View Details">
                        <i class="ti ti-eye"></i>
                    </a>
                    @endif
                    @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || Auth::user()->isAbleTo('orgchart edit'))
                    <button onclick="removeFromHierarchy({{ $employee->id }})" class="oc-btn-view ms-1" data-bs-toggle="tooltip" title="Remove from Hierarchy" style="background: rgba(255,0,0,0.2);">
                        <i class="ti ti-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($employee->subordinates->count() > 0)
        <ul>
            @foreach($employee->subordinates as $child)
                @include('hrm::org_chart.tree_node', ['employee' => $child])
            @endforeach
        </ul>
    @endif
</li>
