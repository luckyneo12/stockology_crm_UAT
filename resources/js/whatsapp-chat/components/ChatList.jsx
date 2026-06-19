import React, { memo } from 'react';

// ─── Helpers ──────────────────────────────────────────────────────────────
function formatTimeAgo(ts) {
    if (!ts) return '';
    const seconds = Math.floor((Date.now() - new Date(ts)) / 1000);
    if (seconds < 60)  return 'now';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
    if (seconds < 604800) return Math.floor(seconds / 86400) + 'd';
    return new Date(ts).toLocaleDateString();
}

function getInitials(name = '') {
    return (name || '').substring(0, 2).toUpperCase() || '??';
}

// ─── Single Chat Item ──────────────────────────────────────────────────────
const ChatItem = memo(({ chat, isActive, onSelect, profilePicUrl }) => {
    const lastMsg = chat.messages?.[chat.messages.length - 1];
    const snippet = lastMsg?.body || 'No messages yet';

    return (
        <div
            className={`wa-chat-item ${isActive ? 'active' : ''}`}
            onClick={() => onSelect(chat.id)}
        >
            <div className="wa-chat-avatar">
                {profilePicUrl ? (
                    <img src={profilePicUrl} alt={chat.customer_name} style={{ width: '100%', height: '100%', borderRadius: '50%', objectFit: 'cover' }} />
                ) : (
                    getInitials(chat.customer_name)
                )}
            </div>
            <div className="wa-chat-item-body">
                <div className="wa-chat-item-header">
                    <span className="wa-chat-name">{chat.customer_name || chat.customer_phone}</span>
                    <span className="wa-chat-time">{formatTimeAgo(chat.last_message_at)}</span>
                </div>
                <div className="wa-chat-item-footer">
                    <span className="wa-chat-snippet">{snippet.length > 40 ? snippet.slice(0, 40) + '…' : snippet}</span>
                    {chat.assignee && (
                        <span className="wa-chat-assignee-badge">{chat.assignee.name}</span>
                    )}
                </div>
            </div>
        </div>
    );
});

// ─── ChatList ─────────────────────────────────────────────────────────────
export default function ChatList({
    chats,
    activeChatId,
    searchQuery,
    configFilter,
    configs,
    socketConnected,
    onSelectChat,
    onSearchChange,
    onConfigFilterChange,
    onOpenQrModal,
    profilePics = {}
}) {
    return (
        <div className="wa-left-panel">
            {/* Header */}
            <div className="wa-panel-header">
                <div className="wa-panel-title-row">
                    <div className="wa-brand">
                        <span className="wa-brand-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <span className="wa-brand-text">WhatsApp</span>
                    </div>
                    <div className="wa-socket-indicator" title={socketConnected ? 'Real-time connected' : 'Connecting...'}>
                        <span className={`wa-dot ${socketConnected ? 'online' : 'offline'}`}></span>
                        <span className="wa-dot-label">{socketConnected ? 'Live' : 'Offline'}</span>
                    </div>
                </div>

                {/* Search */}
                <div className="wa-search-box">
                    <span className="wa-search-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input
                        type="text"
                        className="wa-search-input"
                        placeholder="Search chats…"
                        value={searchQuery}
                        onChange={e => onSearchChange(e.target.value)}
                    />
                </div>

                {/* Config filter + QR Connect */}
                <div className="wa-filter-row">
                    <select
                        className="wa-select"
                        value={configFilter}
                        onChange={e => onConfigFilterChange(e.target.value)}
                    >
                        <option value="">All Numbers</option>
                        {configs.map(c => (
                            <option key={c.id} value={c.id}>
                                {c.name} ({c.phone_number})
                            </option>
                        ))}
                    </select>
                    {configs.length > 0 && (
                        <button
                            className="wa-btn-qr"
                            onClick={() => onOpenQrModal(configFilter || configs[0]?.id)}
                            title="Connect WhatsApp via QR"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: '6px', verticalAlign: 'middle', display: 'inline-block' }}>
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            Connect
                        </button>
                    )}
                </div>
            </div>

            {/* Chat List */}
            <div className="wa-chat-list">
                {chats.length === 0 ? (
                    <div className="wa-empty-state">
                        <div className="wa-empty-icon">💬</div>
                        <p className="wa-empty-title">No chats found</p>
                        <p className="wa-empty-sub">Connect WhatsApp via QR to start receiving messages</p>
                    </div>
                ) : (
                    chats.map(chat => (
                        <ChatItem
                            key={chat.id}
                            chat={chat}
                            isActive={activeChatId === chat.id}
                            onSelect={onSelectChat}
                            profilePicUrl={profilePics[chat.customer_phone]}
                        />
                    ))
                )}
            </div>
        </div>
    );
}
