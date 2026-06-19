@extends('layouts.main')

@section('page-title')
    {{ __('WhatsApp Teams') }}
@endsection

@section('page-action')
    <div class="float-end d-flex gap-2">
        <a href="{{ route('whatsapp.chat.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-messages"></i> {{ __('Chat Inbox') }}
        </a>
        <a href="{{ route('whatsapp-config.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-settings"></i> {{ __('WhatsApp Settings') }}
        </a>
    </div>
@endsection

@section('content')
<style>
    .team-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e9edef;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s;
    }
    .team-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .team-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .team-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111b21;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .team-icon {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #25d366, #128c7e);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1rem;
    }
    .team-number-badge {
        background: #e7f8f0;
        color: #25d366;
        border: 1px solid #b2ebd0;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .team-number-unassigned {
        background: #f5f5f5;
        color: #999;
        border: 1px dashed #ccc;
    }
    .member-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f0f2f5;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.78rem;
        color: #333;
        margin: 3px;
    }
    .member-chip.head {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    .member-chip .remove-btn {
        cursor: pointer;
        color: #999;
        font-size: 0.7rem;
        margin-left: 2px;
        border: none;
        background: none;
        padding: 0;
        line-height: 1;
    }
    .member-chip .remove-btn:hover { color: #e53e3e; }
    .create-team-card {
        background: linear-gradient(135deg, #f8fff9, #e7f8f0);
        border: 2px dashed #25d366;
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s;
        margin-bottom: 16px;
    }
    .create-team-card:hover { background: #e7f8f0; }
    .wa-status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .wa-status-dot.connected  { background: #25d366; }
    .wa-status-dot.disconnected { background: #c9cacc; }
    .wa-status-dot.qr_pending   { background: #ffc107; }
</style>

<div class="row">
    <div class="col-md-8">

        {{-- Create Team Card --}}
        <div class="create-team-card" onclick="openCreateTeamModal()">
            <i class="ti ti-users-plus" style="font-size: 2rem; color: #25d366;"></i>
            <p class="mt-2 mb-0 fw-bold" style="color: #25d366; font-size: 1rem;">{{ __('Create New Team') }}</p>
            <small class="text-muted">{{ __('Group users together and assign a WhatsApp number to them') }}</small>
        </div>

        {{-- Teams List --}}
        @forelse($teams as $team)
        <div class="team-card" id="team-card-{{ $team->id }}">
            <div class="team-card-header">
                <div class="team-title">
                    <div class="team-icon"><i class="ti ti-users"></i></div>
                    <div>
                        <span>{{ $team->name }}</span>
                        @if($team->description)
                            <small class="d-block text-muted" style="font-weight: 400; font-size: 0.78rem;">{{ $team->description }}</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($team->config)
                        <span class="team-number-badge">
                            <span class="wa-status-dot {{ $team->config->session_status }}"></span>
                            {{ $team->config->phone_number }}
                        </span>
                    @else
                        <span class="team-number-badge team-number-unassigned">No Number Assigned</span>
                    @endif
                    <button class="btn btn-sm btn-light" onclick="openEditTeamModal({{ $team->id }}, '{{ addslashes($team->name) }}', '{{ addslashes($team->description) }}', {{ $team->whatsapp_config_id ?? 'null' }})" title="Edit Team">
                        <i class="ti ti-pencil text-secondary"></i>
                    </button>
                    <button class="btn btn-sm btn-light" onclick="deleteTeam({{ $team->id }})" title="Delete Team">
                        <i class="ti ti-trash text-danger"></i>
                    </button>
                </div>
            </div>

            {{-- Members --}}
            <div id="members-{{ $team->id }}" class="mb-3">
                @forelse($team->members as $member)
                    <span class="member-chip {{ $member->role === 'head' ? 'head' : '' }}" id="member-chip-{{ $member->id }}">
                        @if($member->role === 'head')<i class="ti ti-crown" style="font-size: 0.7rem;"></i>@endif
                        {{ $member->user?->name ?? 'Unknown' }}
                        <button class="remove-btn" onclick="removeMember({{ $team->id }}, {{ $member->user_id }}, {{ $member->id }})" title="Remove">✕</button>
                    </span>
                @empty
                    <span class="text-muted" style="font-size: 0.83rem;">No members yet. Add members below.</span>
                @endforelse
            </div>

            {{-- Add Member --}}
            <div class="d-flex gap-2 mt-2">
                <select class="form-select form-select-sm" id="user-select-{{ $team->id }}" style="max-width: 200px;">
                    <option value="">{{ __('Select User') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select class="form-select form-select-sm" id="role-select-{{ $team->id }}" style="max-width: 130px;">
                    <option value="member">Member</option>
                    <option value="head">Team Head</option>
                </select>
                <button class="btn btn-sm btn-success" onclick="addMember({{ $team->id }})">
                    <i class="ti ti-plus"></i> Add
                </button>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="ti ti-users" style="font-size: 3rem; opacity: 0.2;"></i>
            <h5 class="mt-3">No Teams Created Yet</h5>
            <p>Click the card above to create your first WhatsApp Team</p>
        </div>
        @endforelse
    </div>

    {{-- Right: Quick Guide --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body">
                <h6 class="fw-bold"><i class="ti ti-info-circle text-primary me-2"></i>How Teams Work</h6>
                <hr>
                <ul class="list-unstyled small text-muted" style="line-height: 2;">
                    <li>🔢 Assign a WhatsApp number to a team</li>
                    <li>👥 All team members share that inbox</li>
                    <li>👑 Team Head sees all member chats</li>
                    <li>👤 Members see only their own chats</li>
                    <li>🏢 Company Owner sees everything</li>
                </ul>
                <hr>
                <h6 class="fw-bold mt-3"><i class="ti ti-brand-whatsapp text-success me-2"></i>WhatsApp Numbers</h6>
                <div class="list-group list-group-flush">
                    @foreach($configs as $config)
                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between border-0 py-2">
                        <div>
                            <div class="fw-semibold small">{{ $config->name }}</div>
                            <small class="text-muted">{{ $config->phone_number }}</small>
                        </div>
                        <span class="badge bg-{{ $config->session_status_color }} text-white" style="font-size: 0.7rem;">
                            {{ $config->session_status_label }}
                        </span>
                    </div>
                    @endforeach
                    @if($configs->isEmpty())
                        <p class="small text-muted">No WhatsApp numbers configured yet. <a href="{{ route('whatsapp-config.index') }}">Add one</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create/Edit Team Modal --}}
<div class="modal fade" id="teamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="teamModalTitle">Create Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-team-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Team Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal-team-name" placeholder="e.g., Sales Team A">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description (Optional)</label>
                    <textarea class="form-control" id="modal-team-desc" rows="2" placeholder="Brief description of this team"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Assign WhatsApp Number</label>
                    <select class="form-select" id="modal-team-config">
                        <option value="">— No Number —</option>
                        @foreach($configs as $config)
                            <option value="{{ $config->id }}">{{ $config->name }} ({{ $config->phone_number }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">All team members will share this WhatsApp number's inbox</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveTeam()" id="btn-save-team" style="background: #25d366; border: none;">
                    <i class="ti ti-check me-1"></i> Save Team
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openCreateTeamModal() {
    document.getElementById('teamModalTitle').innerText = 'Create New Team';
    document.getElementById('modal-team-id').value = '';
    document.getElementById('modal-team-name').value = '';
    document.getElementById('modal-team-desc').value = '';
    document.getElementById('modal-team-config').value = '';
    new bootstrap.Modal(document.getElementById('teamModal')).show();
}

function openEditTeamModal(id, name, desc, configId) {
    document.getElementById('teamModalTitle').innerText = 'Edit Team';
    document.getElementById('modal-team-id').value = id;
    document.getElementById('modal-team-name').value = name;
    document.getElementById('modal-team-desc').value = desc;
    document.getElementById('modal-team-config').value = configId || '';
    new bootstrap.Modal(document.getElementById('teamModal')).show();
}

async function saveTeam() {
    const id     = document.getElementById('modal-team-id').value;
    const name   = document.getElementById('modal-team-name').value.trim();
    const desc   = document.getElementById('modal-team-desc').value.trim();
    const config = document.getElementById('modal-team-config').value;
    if (!name) return alert('Team name is required.');

    const isEdit = !!id;
    const url    = isEdit ? `/whatsapp-teams/${id}` : '/whatsapp-teams';
    const method = isEdit ? 'PUT' : 'POST';

    const res  = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ name, description: desc }),
    });
    const data = await res.json();

    if (data.status === 'success') {
        // Assign config if changed
        if (isEdit && config !== undefined) {
            await fetch(`/whatsapp-teams/${id}/assign-config`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ whatsapp_config_id: config || null }),
            });
        }
        bootstrap.Modal.getInstance(document.getElementById('teamModal')).hide();
        location.reload();
    } else {
        alert(data.error || 'Failed to save team.');
    }
}

async function deleteTeam(id) {
    if (!confirm('Are you sure you want to delete this team? Members will be removed.')) return;
    const res  = await fetch(`/whatsapp-teams/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    });
    const data = await res.json();
    if (data.status === 'success') {
        document.getElementById('team-card-' + id)?.remove();
    } else {
        alert(data.error || 'Failed to delete team.');
    }
}

async function addMember(teamId) {
    const userId = document.getElementById('user-select-' + teamId).value;
    const role   = document.getElementById('role-select-' + teamId).value;
    if (!userId) return alert('Please select a user.');

    const res  = await fetch(`/whatsapp-teams/${teamId}/members`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ user_id: userId, role }),
    });
    const data = await res.json();
    if (data.status === 'success' && data.member) {
        const { member } = data;
        const isHead = member.role === 'head';
        const chip = `<span class="member-chip ${isHead ? 'head' : ''}" id="member-chip-${member.id}">
            ${isHead ? '<i class="ti ti-crown" style="font-size: 0.7rem;"></i>' : ''}
            ${member.user_name}
            <button class="remove-btn" onclick="removeMember(${teamId}, ${member.user_id}, ${member.id})" title="Remove">✕</button>
        </span>`;
        document.getElementById('members-' + teamId).insertAdjacentHTML('beforeend', chip);
        document.getElementById('user-select-' + teamId).value = '';
    } else {
        alert(data.error || data.message || 'Failed to add member.');
    }
}

async function removeMember(teamId, userId, memberId) {
    const res  = await fetch(`/whatsapp-teams/${teamId}/members/${userId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    });
    const data = await res.json();
    if (data.status === 'success') {
        document.getElementById('member-chip-' + memberId)?.remove();
    }
}
</script>
@endsection
