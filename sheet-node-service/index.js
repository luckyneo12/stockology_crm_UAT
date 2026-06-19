const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
require('dotenv').config();

const app = express();
const server = http.createServer(app);

// CORS configuration for Socket.io
const io = new Server(server, {
    cors: {
        origin: '*', // Allow connections from CRM
        methods: ['GET', 'POST']
    }
});

const PORT = process.env.PORT || 3001;

// Keep track of active users in each sheet room
// Structure: { sheet_id: { socket_id: { user_id, username } } }
const activeUsers = {};

// Health check endpoint
app.get('/health', (req, res) => {
    res.status(200).json({ 
        status: 'OK', 
        activeSheets: Object.keys(activeUsers).length,
        connectedSockets: io.engine.clientsCount 
    });
});

io.on('connection', (socket) => {
    console.log(`Socket connected: ${socket.id}`);

    // Join a collaborative sheet session
    socket.on('join_sheet', ({ sheet_id, user_id, username }) => {
        if (!sheet_id || !user_id) return;

        const room = `sheet_${sheet_id}`;
        socket.join(room);

        // Store user metadata inside socket instance
        socket.sheet_id = sheet_id;
        socket.user_id = user_id;
        socket.username = username;

        // Add to active users tracking
        if (!activeUsers[sheet_id]) {
            activeUsers[sheet_id] = {};
        }
        activeUsers[sheet_id][socket.id] = { user_id, username };

        console.log(`User ${username} (ID: ${user_id}) joined Room ${room}`);

        // Broadcast updated active users list to all participants in the room
        io.to(room).emit('active_users', Object.values(activeUsers[sheet_id]));
    });

    // Handle real-time cell editing event
    socket.on('cell_edit', (data) => {
        const room = `sheet_${data.sheet_id}`;
        // Broadcast the edit event to all other collaborators in the room
        socket.to(room).emit('cell_edit', data);
    });

    // Handle real-time cursor highlight movement
    socket.on('cursor_move', (data) => {
        const room = `sheet_${data.sheet_id}`;
        // Broadcast cursor position to all other collaborators in the room
        socket.to(room).emit('cursor_move', data);
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        console.log(`Socket disconnected: ${socket.id}`);
        const sheet_id = socket.sheet_id;
        const user_id = socket.user_id;

        if (sheet_id && activeUsers[sheet_id]) {
            // Remove user from tracking list
            delete activeUsers[sheet_id][socket.id];

            // If no users left in sheet, clean up the sheet tracking key
            if (Object.keys(activeUsers[sheet_id]).length === 0) {
                delete activeUsers[sheet_id];
            } else {
                const room = `sheet_${sheet_id}`;
                // Notify remaining participants of client exit
                socket.to(room).emit('user_disconnected', user_id);
                // Broadcast updated active users list
                io.to(room).emit('active_users', Object.values(activeUsers[sheet_id]));
            }
        }
    });
});

server.listen(PORT, () => {
    console.log(`Collaborative Sheets Node.js service running on port ${PORT}`);
});
