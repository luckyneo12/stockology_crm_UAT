import React, { useState, useEffect, useCallback } from 'react';
import socket from '../socket';

// Toast notification types
const TYPE_CONFIG = {
  info:    { bg: 'bg-blue-500',   icon: 'ℹ️' },
  success: { bg: 'bg-green-500',  icon: '✅' },
  warning: { bg: 'bg-yellow-500', icon: '⚠️' },
  error:   { bg: 'bg-red-500',    icon: '🚨' },
  lead:    { bg: 'bg-purple-600', icon: '👤' },
};

let toastId = 0;

export default function LiveNotifications() {
  const [toasts, setToasts]         = useState([]);
  const [unread,  setUnread]        = useState(0);
  const [panelOpen, setPanelOpen]   = useState(false);
  const [history,   setHistory]     = useState([]);

  const addToast = useCallback((msg) => {
    const id = ++toastId;
    setToasts(prev => [...prev, { id, ...msg }]);
    setHistory(prev => [{ id, ...msg, time: new Date().toLocaleTimeString() }, ...prev].slice(0, 50));
    setUnread(n => n + 1);
    setTimeout(() => setToasts(prev => prev.filter(t => t.id !== id)), 5000);
  }, []);

  useEffect(() => {
    // Server-side general notifications
    socket.on('notification', (data) => addToast({ ...data, type: data.type || 'info' }));

    // New lead created
    socket.on('leads:new', (lead) => addToast({
      type:  'lead',
      title: 'New Lead!',
      body:  `${lead.name} was added (${lead.lead_stage})`,
    }));

    return () => {
      socket.off('notification');
      socket.off('leads:new');
    };
  }, [addToast]);

  return (
    <>
      {/* ── Toast Stack ──────────────────────────────────────────────── */}
      <div className="crm-toast-stack">
        {toasts.map(toast => {
          const cfg = TYPE_CONFIG[toast.type] || TYPE_CONFIG.info;
          return (
            <div key={toast.id} className={`crm-toast ${cfg.bg}`}>
              <span className="crm-toast-icon">{cfg.icon}</span>
              <div className="crm-toast-body">
                <p className="crm-toast-title">{toast.title}</p>
                <p className="crm-toast-msg">{toast.body}</p>
              </div>
              <button
                onClick={() => setToasts(p => p.filter(t => t.id !== toast.id))}
                className="crm-toast-close"
              >✕</button>
            </div>
          );
        })}
      </div>

      {/* ── Bell Button ──────────────────────────────────────────────── */}
      <button
        id="crm-notif-bell"
        className="crm-bell-btn"
        onClick={() => { setPanelOpen(p => !p); setUnread(0); }}
        title="Notifications"
      >
        🔔
        {unread > 0 && <span className="crm-bell-badge">{unread > 9 ? '9+' : unread}</span>}
      </button>

      {/* ── History Panel ─────────────────────────────────────────────── */}
      {panelOpen && (
        <div className="crm-notif-panel">
          <div className="crm-notif-panel-header">
            <span>Notifications</span>
            <button onClick={() => setHistory([])}>Clear</button>
          </div>
          {history.length === 0
            ? <p className="crm-notif-empty">No notifications yet</p>
            : history.map(h => (
              <div key={h.id} className="crm-notif-item">
                <span>{TYPE_CONFIG[h.type]?.icon || 'ℹ️'}</span>
                <div>
                  <p className="crm-notif-item-title">{h.title}</p>
                  <p className="crm-notif-item-body">{h.body}</p>
                  <p className="crm-notif-item-time">{h.time}</p>
                </div>
              </div>
            ))
          }
        </div>
      )}
    </>
  );
}
