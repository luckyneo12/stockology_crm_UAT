import React, { useState, useEffect, useRef, useCallback } from 'react';
import ReactDOM from 'react-dom/client';
import { io } from 'socket.io-client';

// ─── Components ────────────────────────────────────────────────────────────
import ChatList      from './components/ChatList';
import ChatWindow    from './components/ChatWindow';
import LeadInfoPanel from './components/LeadInfoPanel';
import QrConnectModal from './components/QrConnectModal';

// ─── Styles ────────────────────────────────────────────────────────────────
import './styles.css';

// ─── Global Config (injected by Blade) ────────────────────────────────────
const WA = window.__WA_CONFIG__ || {};
const SOCKET_URL    = WA.socket_url    || 'http://localhost:3001';
const WORKSPACE_ID  = WA.workspace_id  || 1;
const CURRENT_USER  = WA.current_user  || {};
const CSRF_TOKEN    = WA.csrf_token    || document.querySelector('meta[name="csrf-token"]')?.content;
const PRELOADED_CHAT_ID = WA.preloaded_chat_id || null;

// ─── API Helper ────────────────────────────────────────────────────────────
async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            ...(options.headers || {}),
        },
        ...options,
    });
    if (!res.ok) {
        const err = await res.json().catch(() => ({ error: 'Request failed' }));
        throw new Error(err.error || err.message || 'Request failed');
    }
    return res.json();
}

// ─────────────────────────────────────────────────────────────────────────
// Main App Component
// ─────────────────────────────────────────────────────────────────────────
export default function WhatsAppChatApp({ initialChats = [], configs = [] }) {
    const [chats, setChats]               = useState(initialChats);
    const [activeChatId, setActiveChatId] = useState(null);
    const [activeChat, setActiveChat]     = useState(null);
    const [messages, setMessages]         = useState([]);
    const [stages, setStages]             = useState({});
    const [loadingMessages, setLoadingMessages] = useState(false);
    const [sendingMessage, setSendingMessage]   = useState(false);
    const [searchQuery, setSearchQuery]          = useState('');
    const [configFilter, setConfigFilter]        = useState('');
    const [qrModal, setQrModal]                  = useState({ open: false, configId: null });
    const [socketConnected, setSocketConnected]  = useState(false);

    const socketRef = useRef(null);
    const messagesEndRef = useRef(null);

    const [profilePics, setProfilePics] = useState({});
    const fetchedPicsRef = useRef(new Set());

    // ── Fetch Profile Pictures dynamically from Node.js service ───────────
    useEffect(() => {
        chats.forEach(async (chat) => {
            const phone = chat.customer_phone;
            if (phone && !fetchedPicsRef.current.has(phone)) {
                fetchedPicsRef.current.add(phone);
                try {
                    const res = await fetch(`${SOCKET_URL}/api/whatsapp/profile-pic/${chat.whatsapp_config_id}?to=${phone}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'success' && data.profile_pic_url) {
                            setProfilePics(prev => ({
                                ...prev,
                                [phone]: data.profile_pic_url
                            }));
                        }
                    }
                } catch (e) {
                    fetchedPicsRef.current.delete(phone);
                }
            }
        });
    }, [chats]);

    // ── Socket.io Setup ────────────────────────────────────────────────────
    useEffect(() => {
        const socket = io(SOCKET_URL, {
            transports: ['websocket', 'polling'],
            reconnectionAttempts: 5,
        });

        socket.on('connect', () => {
            setSocketConnected(true);
            socket.emit('join_workspace', { workspace_id: WORKSPACE_ID });
        });

        socket.on('disconnect', () => setSocketConnected(false));

        socket.on('whatsapp_message', (data) => {
            const { chat, message } = data;

            // Update messages if this chat is active
            setActiveChatId(currentId => {
                if (currentId && currentId === chat.id) {
                    setMessages(prev => {
                        // Prevent duplicate (outbound already added optimistically)
                        if (prev.some(m => m.id === message.id)) return prev;
                        return [...prev, message];
                    });
                }
                return currentId;
            });

            // Move chat to top of list and update last message
            setChats(prev => {
                const existing = prev.find(c => c.id === chat.id);
                if (existing) {
                    const updated = { ...existing, last_message_at: new Date().toISOString() };
                    return [updated, ...prev.filter(c => c.id !== chat.id)];
                }
                // New chat from real-time
                return [chat, ...prev];
            });
        });

        socket.on('whatsapp_session_ready', ({ config_id }) => {
            // Refresh config status indicators if needed
            window.dispatchEvent(new CustomEvent('wa_session_ready', { detail: { config_id } }));
        });

        socketRef.current = socket;
        return () => socket.disconnect();
    }, []);

    // ── Preloaded Chat ─────────────────────────────────────────────────────
    useEffect(() => {
        if (PRELOADED_CHAT_ID) {
            selectChat(parseInt(PRELOADED_CHAT_ID));
        }
    }, []);

    // ── Scroll to bottom when messages change ──────────────────────────────
    useEffect(() => {
        if (messagesEndRef.current) {
            messagesEndRef.current.scrollIntoView({ behavior: 'smooth' });
        }
    }, [messages]);

    // ── Select Chat ────────────────────────────────────────────────────────
    const selectChat = useCallback(async (chatId) => {
        if (activeChatId === chatId) return;
        setActiveChatId(chatId);
        setMessages([]);
        setLoadingMessages(true);

        try {
            const res = await apiFetch(`/whatsapp-chats/${chatId}/messages`);
            setActiveChat(res.chat);
            setMessages(res.messages || []);
            setStages(res.stages || {});
        } catch (err) {
            console.error('Failed to load messages:', err);
        } finally {
            setLoadingMessages(false);
        }
    }, [activeChatId]);

    // ── Send Message ────────────────────────────────────────────────────────
    const sendMessage = useCallback(async (text) => {
        if (!text.trim() || !activeChatId) return;
        setSendingMessage(true);

        // Optimistic UI
        const tempMsg = {
            id:          Date.now(),
            direction:   'outbound',
            message_type:'text',
            body:         text,
            sender:      { name: CURRENT_USER.name },
            created_at:  new Date().toISOString(),
            status:      'sending',
        };
        setMessages(prev => [...prev, tempMsg]);

        try {
            const res = await apiFetch('/whatsapp-chats/send', {
                method: 'POST',
                body: JSON.stringify({ chat_id: activeChatId, message: text }),
            });

            // Replace temp message with real one
            setMessages(prev =>
                prev.map(m => m.id === tempMsg.id ? res.message : m)
            );

            // Update chat snippet
            setChats(prev => {
                const existing = prev.find(c => c.id === activeChatId);
                if (existing) {
                    return [{ ...existing, last_message_at: new Date().toISOString() },
                            ...prev.filter(c => c.id !== activeChatId)];
                }
                return prev;
            });
        } catch (err) {
            // Mark as failed
            setMessages(prev =>
                prev.map(m => m.id === tempMsg.id ? { ...m, status: 'failed' } : m)
            );
            console.error('Send failed:', err);
        } finally {
            setSendingMessage(false);
        }
    }, [activeChatId]);

    // ── Update Lead Stage ───────────────────────────────────────────────────
    const updateLeadStage = useCallback(async (stageId) => {
        if (!activeChatId || !stageId) return;
        try {
            await apiFetch(`/whatsapp-chats/${activeChatId}/update-lead-stage`, {
                method: 'POST',
                body: JSON.stringify({ stage_id: stageId }),
            });
        } catch (err) {
            console.error('Stage update failed:', err);
        }
    }, [activeChatId]);

    // ── Backup Chat ─────────────────────────────────────────────────────────
    const backupChat = useCallback(async (chatId) => {
        try {
            await apiFetch(`/whatsapp-chats/${chatId}/backup`, { method: 'POST' });
            alert('Chat backed up successfully!');
        } catch (err) {
            console.error('Backup failed:', err);
            alert('Backup failed: ' + err.message);
        }
    }, []);

    // ── Filtered Chats ─────────────────────────────────────────────────────
    const filteredChats = chats.filter(chat => {
        const matchSearch = !searchQuery ||
            (chat.customer_name || '').toLowerCase().includes(searchQuery.toLowerCase()) ||
            (chat.customer_phone || '').includes(searchQuery);
        const matchConfig = !configFilter || chat.whatsapp_config_id == configFilter;
        return matchSearch && matchConfig;
    });

    // ── Render ─────────────────────────────────────────────────────────────
    return (
        <div className="wa-app-container">
            {/* Left Panel */}
            <ChatList
                chats={filteredChats}
                activeChatId={activeChatId}
                searchQuery={searchQuery}
                configFilter={configFilter}
                configs={configs}
                socketConnected={socketConnected}
                onSelectChat={selectChat}
                onSearchChange={setSearchQuery}
                onConfigFilterChange={setConfigFilter}
                onOpenQrModal={(configId) => setQrModal({ open: true, configId })}
                profilePics={profilePics}
            />

            {/* Middle Panel */}
            <ChatWindow
                activeChat={activeChat}
                messages={messages}
                loadingMessages={loadingMessages}
                sendingMessage={sendingMessage}
                messagesEndRef={messagesEndRef}
                onSendMessage={sendMessage}
                onBackupChat={backupChat}
                profilePic={activeChat ? profilePics[activeChat.customer_phone] : null}
            />

            {/* Right Panel — Lead Info */}
            <LeadInfoPanel
                activeChat={activeChat}
                stages={stages}
                onUpdateStage={updateLeadStage}
                profilePic={activeChat ? profilePics[activeChat.customer_phone] : null}
            />

            {/* QR Connect Modal */}
            {qrModal.open && (
                <QrConnectModal
                    configId={qrModal.configId}
                    onClose={() => setQrModal({ open: false, configId: null })}
                />
            )}
        </div>
    );
}

// ─── Mount ────────────────────────────────────────────────────────────────
const rootEl = document.getElementById('whatsapp-chat-root');
if (rootEl) {
    const root = ReactDOM.createRoot(rootEl);
    root.render(
        <WhatsAppChatApp
            initialChats={WA.initial_chats || []}
            configs={WA.configs || []}
        />
    );
}
