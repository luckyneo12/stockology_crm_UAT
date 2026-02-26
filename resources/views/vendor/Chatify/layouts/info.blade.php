{{-- user info and avatar --}}
<div class="info-profile-header">
    @if(!empty(\Auth::user()->avatar))
        <div class="avatar av-l" style="background-image: url('{{ get_file(Auth::user()->avatar) }}');"></div>
    @else
        <div class="avatar av-l" style="background-image: url('{{ get_file('uploads/users-avatar/avatar.png') }}');"></div>
    @endif
    <p class="info-name">{{ config('chatify.name') }}</p>
    <div class="info-details">
        <p class="info-email"><i class="ti ti-mail"></i> <span></span></p>
        <p class="info-phone"><i class="ti ti-phone"></i> <span></span></p>
        <p class="info-role"><i class="ti ti-user-check"></i> <span></span></p>
    </div>
    <span class="info-status">{{__('Active now')}}</span>
</div>

<div class="messenger-infoView-btns">
    <a href="#" class="danger delete-conversation">
        <i class="ti ti-trash"></i>
        <span>{{__('Delete Conversation')}}</span>
    </a>
</div>

{{-- shared photos --}}
<div class="messenger-infoView-shared">
    <p class="messenger-title">{{__('Shared Photos')}}</p>
    <div class="shared-photos-list"></div>
</div>
