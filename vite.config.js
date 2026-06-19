import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app-react.jsx',
                'resources/js/whatsapp-chat/WhatsAppChatApp.jsx',
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1600,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-react':   ['react', 'react-dom'],
                    'vendor-antd':    ['antd'],
                    'vendor-anticons': ['@ant-design/icons'],
                },
            },
        },
    },
    server: {
        proxy: {
            // All /api/node/* requests → Node.js Express on :3000
            '/api/node': {
                target: 'http://localhost:3000',
                changeOrigin: true,
                secure: false,
            },
            // Socket.IO websocket proxy
            '/socket.io': {
                target: 'http://localhost:3001',
                changeOrigin: true,
                ws: true,
            },
        },
    },
});

