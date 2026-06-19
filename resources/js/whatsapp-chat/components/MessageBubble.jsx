import React, { memo } from 'react';

const STATUS_ICONS = { sent: '✓', delivered: '✓✓', read: '✓✓', failed: '✗', sending: '…', received: '' };
const STATUS_COLORS = { read: '#53bdeb', failed: '#ff6b6b', sending: '#aaa' };

export default memo(function MessageBubble({ message }) {
    const isOutbound = message.direction === 'outbound';
    const isFailed   = message.status === 'failed';
    const isSending  = message.status === 'sending';

    const timeStr = message.created_at
        ? new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';

    return (
        <div className={`wa-bubble-wrapper ${isOutbound ? 'outbound' : 'inbound'}`}>
            <div className={`wa-bubble ${isOutbound ? 'wa-bubble-out' : 'wa-bubble-in'} ${isFailed ? 'failed' : ''}`}>
                {/* Sender name (outbound only) */}
                {isOutbound && message.sender?.name && (
                    <span className="wa-bubble-sender">{message.sender.name}</span>
                )}

                {/* Media */}
                {message.media_url && message.message_type === 'image' && (
                    <img
                        src={message.media_url}
                        alt="media"
                        className="wa-bubble-image"
                        loading="lazy"
                    />
                )}
                {message.media_url && message.message_type !== 'image' && (
                    <a href={message.media_url} target="_blank" rel="noreferrer" className="wa-bubble-file">
                        📎 {message.body || 'File'}
                    </a>
                )}

                {/* Text */}
                {message.body && message.message_type !== 'image' && (
                    <span className="wa-bubble-text">{message.body}</span>
                )}

                {/* Timestamp + Status */}
                <span className="wa-bubble-meta">
                    <span className="wa-bubble-time">{timeStr}</span>
                    {isOutbound && (
                        <span
                            className="wa-bubble-status"
                            style={{ color: STATUS_COLORS[message.status] }}
                        >
                            {STATUS_ICONS[message.status] || ''}
                        </span>
                    )}
                </span>
            </div>
            {isFailed && (
                <span className="wa-bubble-error">Failed to send</span>
            )}
        </div>
    );
});
