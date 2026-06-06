import { io } from 'socket.io-client';

// Single shared Socket.IO connection to the Node server
// During Vite dev → Vite proxies /socket.io to localhost:3000
// In production (via Apache) → direct connection to Node server
const NODE_SERVER = import.meta.env.DEV
  ? window.location.origin          // Vite proxy handles it
  : 'http://localhost:3000';        // direct for XAMPP prod

const socket = io(NODE_SERVER, {
  transports: ['websocket', 'polling'],
  autoConnect: true,
  reconnectionAttempts: 5,
  reconnectionDelay: 2000,
});

socket.on('connect', () => {
  console.log('[socket] Connected ✓', socket.id);
});

socket.on('connect_error', (err) => {
  console.warn('[socket] Connection error:', err.message);
});

export default socket;
