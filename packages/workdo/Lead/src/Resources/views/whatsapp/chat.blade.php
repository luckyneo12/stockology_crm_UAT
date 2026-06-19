@extends('layouts.main')

@section('page-title')
    {{ __('WhatsApp Live Chat') }}
@endsection

@section('page-action')
    <div class="float-end d-flex gap-2">
        <a href="{{ route('whatsapp-teams.index') }}" class="btn btn-sm btn-outline-secondary btn-icon" title="{{ __('Manage Teams') }}">
            <i class="ti ti-users-group"></i> {{ __('Teams') }}
        </a>
        <a href="{{ route('whatsapp-config.index') }}" class="btn btn-sm btn-outline-secondary btn-icon" title="{{ __('WhatsApp Settings') }}">
            <i class="ti ti-settings"></i>
        </a>
    </div>
@endsection

@section('content')

{{-- Pass Laravel server-side data to React via a global JS object --}}
<script>
    window.__WA_CONFIG__ = {
        socket_url:       "{{ env('WHATSAPP_NODE_PUBLIC_URL', 'http://localhost:3001') }}",
        workspace_id:     {{ getActiveWorkSpace() }},
        csrf_token:       "{{ csrf_token() }}",
        current_user: {
            id:   {{ Auth::user()->id }},
            name: "{{ addslashes(Auth::user()->name) }}",
            type: "{{ Auth::user()->type }}"
        },
        preloaded_chat_id: {{ $preloadedChat ? $preloadedChat->id : 'null' }},
        initial_chats: {!! json_encode($chats->map(function($c) {
            return [
                'id'                  => $c->id,
                'whatsapp_config_id'  => $c->whatsapp_config_id,
                'customer_phone'      => $c->customer_phone,
                'customer_name'       => $c->customer_name,
                'last_message_at'     => $c->last_message_at,
                'lead_id'             => $c->lead_id,
                'assigned_user_id'    => $c->assigned_user_id,
                'lead'                => $c->lead ? ['id' => $c->lead->id, 'name' => $c->lead->name] : null,
                'assignee'            => $c->assignee ? ['id' => $c->assignee->id, 'name' => $c->assignee->name] : null,
                'config'              => $c->config ? ['id' => $c->config->id, 'name' => $c->config->name] : null,
                'messages'            => $c->messages->take(-1)->map(fn($m) => ['body' => $m->body, 'direction' => $m->direction])->values()->toArray(),
            ];
        })->values()) !!},
        configs: {!! json_encode($configs->map(function($c) {
            return [
                'id'             => $c->id,
                'name'           => $c->name,
                'phone_number'   => $c->phone_number,
                'session_status' => $c->session_status,
                'is_connected'   => $c->isConnected(),
            ];
        })->values()) !!}
    };
</script>

{{-- React Mount Point --}}
<div id="whatsapp-chat-root"></div>

{{-- Load React WhatsApp Chat App (built by Vite) --}}
@viteReactRefresh
@vite(['resources/js/whatsapp-chat/WhatsAppChatApp.jsx'])

@endsection
