import React, { useState, useRef, useEffect, memo } from 'react';
import MessageBubble from './MessageBubble';

// ─── Placeholder when no chat is selected ────────────────────────────────
function ChatPlaceholder() {
    return (
        <div className="wa-placeholder">
            <div className="wa-placeholder-icon">
                <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" width="80" height="80">
                    <circle cx="30" cy="30" r="28" fill="#25d366" opacity="0.12"/>
                    <path d="M30 10C19 10 10 19 10 30c0 3.6 1 7 2.7 9.9L10 50l10.4-2.7C23 49 26.4 50 30 50c11 0 20-9 20-20S41 10 30 10z" fill="#25d366" opacity="0.8"/>
                    <path d="M23 25h14M23 30h10M23 35h12" stroke="white" strokeWidth="2" strokeLinecap="round"/>
                </svg>
            </div>
            <h4 className="wa-placeholder-title">WhatsApp Live Chat</h4>
            <p className="wa-placeholder-sub">Select a conversation from the left panel to start chatting in real-time</p>
        </div>
    );
}

// ─── ChatWindow ────────────────────────────────────────────────────────────
export default function ChatWindow({
    activeChat,
    messages,
    loadingMessages,
    sendingMessage,
    messagesEndRef,
    onSendMessage,
    onBackupChat,
    profilePic,
}) {
    const [inputText, setInputText] = useState('');
    const textareaRef = useRef(null);

    // Auto-resize textarea
    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = Math.min(textareaRef.current.scrollHeight, 120) + 'px';
        }
    }, [inputText]);

    const handleSend = () => {
        const text = inputText.trim();
        if (!text || sendingMessage) return;
        onSendMessage(text);
        setInputText('');
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSend();
        }
    };

    return (
        <div className="wa-middle-panel">
            <div className="wa-chat-window">
                {/* Header */}
                {activeChat ? (
                    <div className="wa-chat-header">
                        <div className="wa-chat-header-avatar">
                            {profilePic ? (
                                <img src={profilePic} alt={activeChat.customer_name} style={{ width: '100%', height: '100%', borderRadius: '50%', objectFit: 'cover' }} />
                            ) : (
                                (activeChat.customer_name || '??').substring(0, 2).toUpperCase()
                            )}
                        </div>
                        <div className="wa-chat-header-info">
                            <span className="wa-chat-header-name">
                                {activeChat.customer_name || activeChat.customer_phone}
                            </span>
                            <span className="wa-chat-header-phone">
                                +{activeChat.customer_phone}
                                {activeChat.config && (
                                    <span className="wa-config-badge">via {activeChat.config.name}</span>
                                )}
                            </span>
                        </div>
                        <div className="wa-chat-header-actions">
                            <button
                                className="wa-icon-btn"
                                title="Backup this chat"
                                onClick={() => onBackupChat(activeChat.id)}
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                    <path d="M3 12A9 3 0 0 0 21 12"></path>
                                </svg>
                            </button>
                            {activeChat.lead && (
                                <a
                                    href={`/leads/${activeChat.lead.id}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="wa-icon-btn"
                                    title="View Lead Profile"
                                >
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </a>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="wa-chat-header wa-chat-header-empty" />
                )}

                {/* Messages Area */}
                <div className="wa-messages-area">
                    {!activeChat && <ChatPlaceholder />}

                    {activeChat && loadingMessages && (
                        <div className="wa-loading">
                            <div className="wa-spinner" />
                            <span>Loading messages…</span>
                        </div>
                    )}

                    {activeChat && !loadingMessages && (
                        <div className="wa-messages-list">
                            {messages.length === 0 && (
                                <div className="wa-no-messages">
                                    <span>No messages yet. Say hello! 👋</span>
                                </div>
                            )}
                            {messages.map((msg, idx) => (
                                <MessageBubble key={msg.id || idx} message={msg} />
                            ))}
                            <div ref={messagesEndRef} />
                        </div>
                    )}
                </div>

                {/* Input Area */}
                {activeChat && (
                    <div className="wa-input-area">
                        <textarea
                            ref={textareaRef}
                            className="wa-input"
                            placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
                            value={inputText}
                            onChange={e => setInputText(e.target.value)}
                            onKeyDown={handleKeyDown}
                            rows={1}
                            disabled={sendingMessage}
                        />
                        <button
                            className={`wa-send-btn ${sendingMessage ? 'sending' : ''}`}
                            onClick={handleSend}
                            disabled={!inputText.trim() || sendingMessage}
                        >
                            {sendingMessage ? (
                                <span className="wa-spinner-sm" />
                            ) : (
                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            )}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
