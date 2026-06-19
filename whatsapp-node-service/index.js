require('dotenv').config();
const express = require('express');
const http = require('http');
const fs = require('fs');
const path = require('path');
const { Server } = require('socket.io');
const axios = require('axios');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const QRCode = require('qrcode');

const app = express();
app.use(express.json());

// ─── CORS Middleware ──────────────────────────────────────────────────────────
app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-Node-Secret');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS');
    if (req.method === 'OPTIONS') return res.sendStatus(200);
    next();
});

const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: '*', methods: ['GET', 'POST'] }
});

// ─── Config ───────────────────────────────────────────────────────────────────
const PORT            = process.env.PORT || 3001;
const LARAVEL_API_URL = process.env.LARAVEL_API_URL || 'http://localhost';
const NODE_SECRET     = process.env.NODE_SECRET || 'whatsapp_node_secret_key';

// ─── Multi-Session Store ──────────────────────────────────────────────────────
// Map<configId, { client, status, qrDataUrl, phone }>
const sessions = new Map();

// ─── QR Pending Resolvers ─────────────────────────────────────────────────────
// When browser polls for QR, if not ready yet, we hold the request briefly
const qrWaiters = new Map(); // Map<configId, [resolve, reject][]>

// ─────────────────────────────────────────────────────────────────────────────
// Helper: Notify Laravel of session status change
// ─────────────────────────────────────────────────────────────────────────────
async function notifyLaravel(configId, status, phoneNumber = null) {
    try {
        await axios.post(`${LARAVEL_API_URL}/api/whatsapp/session-status`, {
            config_id: configId,
            status,
            phone_number: phoneNumber,
            secret: NODE_SECRET,
        }, { timeout: 5000 });
        console.log(`[Config ${configId}] Notified Laravel: status=${status}`);
    } catch (err) {
        console.error(`[Config ${configId}] Failed to notify Laravel:`, err.message);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Helper: Broadcast via Socket.io room
// ─────────────────────────────────────────────────────────────────────────────
function broadcastToRoom(workspaceId, event, data) {
    const room = `workspace_${workspaceId}`;
    io.to(room).emit(event, data);
    console.log(`[Socket] Broadcast '${event}' to room: ${room}`);
}

// ─────────────────────────────────────────────────────────────────────────────
// Core: Initialize (or reinitialize) a WhatsApp session for a configId
// ─────────────────────────────────────────────────────────────────────────────
function initSession(configId) {
    if (sessions.has(configId)) {
        const existing = sessions.get(configId);
        if (existing.status === 'connected' || existing.status === 'connecting') {
            console.log(`[Config ${configId}] Session already active (${existing.status}). Skipping.`);
            return;
        }
        // Destroy old client before reinitializing
        try { existing.client.destroy(); } catch (e) { /* ignore */ }
    }

    console.log(`[Config ${configId}] Initializing WhatsApp session...`);

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: `config_${configId}` }),
        webVersionCache: {
            type: 'remote',
            remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html'
        },
        puppeteer: {
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu'
            ],
        },
    });

    const sessionData = { client, status: 'connecting', qrDataUrl: null, phone: null };
    sessions.set(configId, sessionData);
    notifyLaravel(configId, 'connecting');

    // ── QR Received ──────────────────────────────────────────────────────────
    client.on('qr', async (qr) => {
        console.log(`[Config ${configId}] QR received.`);
        try {
            const qrDataUrl = await QRCode.toDataURL(qr, { width: 280, margin: 2 });
            sessionData.qrDataUrl = qrDataUrl;
            sessionData.status = 'qr_pending';

            // Resolve any waiting HTTP requests
            if (qrWaiters.has(configId)) {
                qrWaiters.get(configId).forEach(([resolve]) => resolve(qrDataUrl));
                qrWaiters.delete(configId);
            }

            // Broadcast QR to browser via Socket.io room (config-specific)
            io.to(`config_${configId}`).emit('whatsapp_qr', {
                config_id: configId,
                qr_data_url: qrDataUrl,
            });
        } catch (err) {
            console.error(`[Config ${configId}] QR generation failed:`, err.message);
        }
    });

    // ── Authenticated ─────────────────────────────────────────────────────────
    client.on('authenticated', () => {
        console.log(`[Config ${configId}] WhatsApp authenticated.`);
        sessionData.status = 'authenticated';
        sessionData.qrDataUrl = null;
    });

    // ── Loading Screen ────────────────────────────────────────────────────────
    client.on('loading_screen', (percent, message) => {
        console.log(`[Config ${configId}] Loading: ${percent}% - ${message}`);
    });

    // ── Change State ──────────────────────────────────────────────────────────
    client.on('change_state', (state) => {
        console.log(`[Config ${configId}] State changed to: ${state}`);
    });

    // ── Ready (Fully Connected) ───────────────────────────────────────────────
    client.on('ready', async () => {
        const info = client.info;
        const phone = info?.wid?.user || null;
        sessionData.status = 'connected';
        sessionData.phone  = phone;
        console.log(`[Config ${configId}] WhatsApp READY! Phone: ${phone}`);
        await notifyLaravel(configId, 'connected', phone);

        // Notify all workspace rooms that config is live
        io.emit('whatsapp_session_ready', { config_id: configId, phone });
    });

    // ── Message Received ──────────────────────────────────────────────────────
    client.on('message', async (msg) => {
        if (msg.isStatus) return; // Ignore status updates

        console.log(`[Config ${configId}] Inbound message from ${msg.from}: ${msg.body}`);

        const payload = {
            config_id: configId,
            from: msg.from.replace('@c.us', ''), // strip WhatsApp suffix
            body: msg.body,
            type: msg.type,
            timestamp: msg.timestamp,
            has_media: msg.hasMedia,
            message_id: msg.id._serialized,
        };

        // Handle media messages (images, documents, etc.)
        if (msg.hasMedia) {
            try {
                const media = await msg.downloadMedia();
                if (media) {
                    payload.media_base64 = media.data;
                    payload.media_mimetype = media.mimetype;
                    payload.media_filename = media.filename || null;
                }
            } catch (mediaErr) {
                console.warn(`[Config ${configId}] Media download failed:`, mediaErr.message);
            }
        }

        // Forward to Laravel
        try {
            const response = await axios.post(
                `${LARAVEL_API_URL}/api/whatsapp/incoming-webhook`,
                { ...payload, secret: NODE_SECRET },
                { headers: { 'Content-Type': 'application/json' }, timeout: 10000 }
            );

            if (response.data?.status === 'success') {
                const { chat, message, workspace_id } = response.data;
                broadcastToRoom(workspace_id, 'whatsapp_message', { chat, message });
            }
        } catch (err) {
            console.error(`[Config ${configId}] Failed to forward to Laravel:`, err.message);
        }
    });

    // ── Message ACK (delivery status updates) ────────────────────────────────
    client.on('message_ack', async (msg, ack) => {
        // ack: 1=sent, 2=received, 3=read, -1=error
        const statusMap = { 1: 'sent', 2: 'delivered', 3: 'read', '-1': 'failed' };
        const status = statusMap[ack] || 'sent';
        try {
            await axios.post(`${LARAVEL_API_URL}/api/whatsapp/message-ack`, {
                message_sid: msg.id._serialized,
                status,
                secret: NODE_SECRET,
            }, { timeout: 5000 });
        } catch (e) { /* non-critical */ }
    });

    // ── Disconnected ──────────────────────────────────────────────────────────
    client.on('disconnected', async (reason) => {
        console.log(`[Config ${configId}] Disconnected: ${reason}`);
        sessionData.status = 'disconnected';
        sessionData.qrDataUrl = null;
        await notifyLaravel(configId, 'disconnected');
        io.emit('whatsapp_session_disconnected', { config_id: configId, reason });
    });

    // ── Auth Failure ──────────────────────────────────────────────────────────
    client.on('auth_failure', async (msg) => {
        console.error(`[Config ${configId}] Auth failure: ${msg}`);
        sessionData.status = 'disconnected';
        await notifyLaravel(configId, 'disconnected');
    });

    // ── Initialize ────────────────────────────────────────────────────────────
    client.initialize().catch(err => {
        console.error(`[Config ${configId}] Initialization error:`, err.message);
        sessionData.status = 'disconnected';
        notifyLaravel(configId, 'disconnected');
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// HTTP API Routes
// ─────────────────────────────────────────────────────────────────────────────

// ── GET /api/whatsapp/qr/:configId — Generate or return QR for a session ────
app.get('/api/whatsapp/qr/:configId', async (req, res) => {
    const configId = req.params.configId;

    const session = sessions.get(configId);

    if (session?.status === 'connected') {
        return res.json({ status: 'connected', message: 'Already connected.' });
    }

    if (session?.status === 'qr_pending' && session.qrDataUrl) {
        return res.json({ status: 'qr_pending', qr_data_url: session.qrDataUrl });
    }

    // Start session if not already initializing
    if (!session || session.status === 'disconnected') {
        initSession(configId);
    }

    // Wait up to 30s for QR to be generated
    const qrDataUrl = await new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('QR timeout')), 30000);

        // If QR is generated before timeout, resolve immediately
        const check = setInterval(() => {
            const s = sessions.get(configId);
            if (s?.qrDataUrl) {
                clearInterval(check);
                clearTimeout(timeout);
                resolve(s.qrDataUrl);
            }
            if (s?.status === 'connected') {
                clearInterval(check);
                clearTimeout(timeout);
                resolve(null);
            }
        }, 500);
    }).catch(() => null);

    if (!qrDataUrl) {
        const s = sessions.get(configId);
        if (s?.status === 'connected') {
            return res.json({ status: 'connected' });
        }
        return res.status(504).json({ status: 'error', message: 'QR generation timed out. Please try again.' });
    }

    return res.json({ status: 'qr_pending', qr_data_url: qrDataUrl });
});

// ── GET /api/whatsapp/status/:configId — Check session status ────────────────
app.get('/api/whatsapp/status/:configId', (req, res) => {
    const configId = req.params.configId;
    const session  = sessions.get(configId);

    if (!session) {
        return res.json({ status: 'disconnected', connected: false });
    }

    return res.json({
        status:    session.status,
        connected: session.status === 'connected',
        phone:     session.phone,
    });
});

// ── GET /api/whatsapp/profile-pic/:configId — Fetch profile picture ──────────
app.get('/api/whatsapp/profile-pic/:configId', async (req, res) => {
    const configId = req.params.configId;
    const { to } = req.query;

    if (!to) {
        return res.status(400).json({ status: 'error', message: 'to phone number required.' });
    }

    const session = sessions.get(configId);
    if (!session || session.status !== 'connected') {
        return res.status(400).json({ status: 'error', message: 'Session not connected.' });
    }

    try {
        let chatId = null;

        // 1. Try to find the chat in the active chats list (covers existing JID/LID chats)
        try {
            const chats = await session.client.getChats();
            const foundChat = chats.find(c => c.id.user === to);
            if (foundChat) {
                chatId = foundChat.id._serialized;
            }
        } catch (e) {}

        // 2. If not found in active chats, try getNumberId
        if (!chatId) {
            try {
                const numberId = await session.client.getNumberId(to);
                if (numberId) {
                    chatId = numberId._serialized;
                }
            } catch (e) {}
        }

        if (!chatId) {
            chatId = `${to}@c.us`;
        }

        const profilePicUrl = await session.client.getProfilePicUrl(chatId);
        return res.json({
            status: 'success',
            profile_pic_url: profilePicUrl || null
        });
    } catch (err) {
        console.error(`[Config ${configId}] Get profile pic error:`, err.message);
        return res.status(500).json({ status: 'error', message: err.message });
    }
});

// ── POST /api/whatsapp/send/:configId — Send message ────────────────────────
app.post('/api/whatsapp/send/:configId', async (req, res) => {
    const configId = req.params.configId;
    const { to, message, media_base64, media_mimetype, media_filename } = req.body;

    const session = sessions.get(configId);
    if (!session || session.status !== 'connected') {
        return res.status(400).json({ status: 'error', message: 'Session not connected.' });
    }

    try {
        let chatId = null;

        // 1. Try to find the chat in the active chats list (covers existing JID/LID chats)
        try {
            const chats = await session.client.getChats();
            const foundChat = chats.find(c => c.id.user === to);
            if (foundChat) {
                chatId = foundChat.id._serialized;
                console.log(`[Config ${configId}] Found active chat for ${to}: ${chatId}`);
            }
        } catch (chatErr) {
            console.warn(`[Config ${configId}] Failed to fetch active chats:`, chatErr.message);
        }

        // 2. If not found in active chats, try to resolve via getNumberId (covers new phone numbers)
        if (!chatId) {
            try {
                const numberId = await session.client.getNumberId(to);
                if (numberId) {
                    chatId = numberId._serialized;
                    console.log(`[Config ${configId}] Resolved number ID for ${to}: ${chatId}`);
                }
            } catch (numErr) {
                console.warn(`[Config ${configId}] Failed to resolve number ID:`, numErr.message);
            }
        }

        // 3. Fallback to standard c.us format
        if (!chatId) {
            chatId = `${to}@c.us`;
            console.log(`[Config ${configId}] Fallback JID for ${to}: ${chatId}`);
        }

        let sentMsg;

        if (media_base64 && media_mimetype) {
            const media = new MessageMedia(media_mimetype, media_base64, media_filename || 'file');
            sentMsg = await session.client.sendMessage(chatId, media, { caption: message || '' });
        } else {
            sentMsg = await session.client.sendMessage(chatId, message);
        }

        return res.json({
            status:     'success',
            message_id: sentMsg.id._serialized,
        });
    } catch (err) {
        console.error(`[Config ${configId}] Send message error:`, err.message);
        return res.status(500).json({ status: 'error', message: err.message });
    }
});

// ── POST /api/whatsapp/disconnect/:configId — Disconnect session ─────────────
app.post('/api/whatsapp/disconnect/:configId', async (req, res) => {
    const configId = req.params.configId;
    const session  = sessions.get(configId);

    if (!session) {
        return res.json({ status: 'ok', message: 'No active session.' });
    }

    try {
        await session.client.destroy();
    } catch (e) { /* ignore */ }

    sessions.delete(configId);
    await notifyLaravel(configId, 'disconnected');
    return res.json({ status: 'ok', message: 'Session disconnected.' });
});

// ── POST /api/broadcast-message — Laravel → Socket.io broadcast ──────────────
app.post('/api/broadcast-message', (req, res) => {
    const { chat, message, workspace_id } = req.body;

    if (!workspace_id) {
        return res.status(400).json({ status: 'error', message: 'workspace_id required.' });
    }

    broadcastToRoom(workspace_id, 'whatsapp_message', { chat, message });
    return res.json({ status: 'success' });
});

// ── POST /api/whatsapp/restore-sessions — Laravel asks Node to restore ───────
app.post('/api/whatsapp/restore-sessions', (req, res) => {
    const { config_ids } = req.body;
    if (!Array.isArray(config_ids)) {
        return res.status(400).json({ status: 'error', message: 'config_ids array required.' });
    }

    config_ids.forEach(id => {
        const strId = String(id);
        const session = sessions.get(strId);
        if (!session || session.status === 'disconnected') {
            console.log(`[Restore] Reinitializing session for config ${strId}`);
            initSession(strId);
        }
    });

    return res.json({ status: 'ok', message: `Restoring ${config_ids.length} sessions.` });
});

// ── GET /health — Health check ────────────────────────────────────────────────
app.get('/health', (req, res) => {
    const sessionSummary = {};
    sessions.forEach((v, k) => {
        sessionSummary[k] = { status: v.status, phone: v.phone };
    });
    res.json({
        status:        'OK',
        socketClients: io.engine.clientsCount,
        sessions:      sessionSummary,
        uptime:        process.uptime(),
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Socket.io — Client Connection Logic
// ─────────────────────────────────────────────────────────────────────────────
io.on('connection', (socket) => {
    console.log(`[Socket] Client connected: ${socket.id}`);

    // Join workspace room for tenant-scoped broadcasts
    socket.on('join_workspace', ({ workspace_id }) => {
        if (workspace_id) {
            const room = `workspace_${workspace_id}`;
            socket.join(room);
            console.log(`[Socket] ${socket.id} joined room: ${room}`);
        }
    });

    socket.on('lead_moved', ({ workspace_id, lead_id, stage_id, order }) => {
        if (workspace_id) {
            const room = `workspace_${workspace_id}`;
            socket.to(room).emit('lead_moved', { lead_id, stage_id, order });
            console.log(`[Socket] Lead ${lead_id} moved to stage ${stage_id} in workspace ${workspace_id}`);
        }
    });

    socket.on('lead_created', ({ workspace_id, lead }) => {
        if (workspace_id) {
            const room = `workspace_${workspace_id}`;
            socket.to(room).emit('lead_created', { lead });
            console.log(`[Socket] Lead created in workspace ${workspace_id}`);
        }
    });

    socket.on('lead_updated', ({ workspace_id, lead_id, lead }) => {
        if (workspace_id) {
            const room = `workspace_${workspace_id}`;
            socket.to(room).emit('lead_updated', { lead_id, lead });
            console.log(`[Socket] Lead ${lead_id} updated in workspace ${workspace_id}`);
        }
    });

    // Join config-specific room for QR updates
    socket.on('join_config', ({ config_id }) => {
        if (config_id) {
            const room = `config_${config_id}`;
            socket.join(room);
            console.log(`[Socket] ${socket.id} joined config room: ${room}`);

            // Immediately push current status if session exists
            const session = sessions.get(String(config_id));
            if (session) {
                if (session.status === 'connected') {
                    socket.emit('whatsapp_session_ready', { config_id, phone: session.phone });
                } else if (session.qrDataUrl) {
                    socket.emit('whatsapp_qr', { config_id, qr_data_url: session.qrDataUrl });
                }
            }
        }
    });

    socket.on('disconnect', () => {
        console.log(`[Socket] Client disconnected: ${socket.id}`);
    });
});

// ─── Auto-Restore Sessions on Startup ────────────────────────────────────────
function restoreSessions() {
    const authDir = path.join(__dirname, '.wwebjs_auth');
    if (!fs.existsSync(authDir)) return;

    try {
        const files = fs.readdirSync(authDir);
        files.forEach(file => {
            if (file.startsWith('session-config_')) {
                const configId = file.replace('session-config_', '');
                if (configId && !sessions.has(configId)) {
                    console.log(`[Startup] Restoring saved session for config: ${configId}`);
                    initSession(configId);
                }
            }
        });
    } catch (err) {
        console.error('[Startup] Failed to restore sessions:', err.message);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Start Server
// ─────────────────────────────────────────────────────────────────────────────
server.listen(PORT, () => {
    console.log(`\n🟢 WhatsApp CRM Node.js Service v2.0 running on port ${PORT}`);
    console.log(`   Laravel API target  : ${LARAVEL_API_URL}`);
    console.log(`   Health check        : http://localhost:${PORT}/health`);
    console.log(`   QR endpoint         : GET /api/whatsapp/qr/:configId`);
    console.log(`   Status endpoint     : GET /api/whatsapp/status/:configId\n`);

    // Auto-restore saved sessions on startup
    restoreSessions();
});
