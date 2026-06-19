// PM2 Ecosystem Configuration for Hostinger Cloud Professional
// Usage: pm2 start ecosystem.config.js
module.exports = {
    apps: [
        {
            name        : 'whatsapp-crm-service',
            cwd         : './whatsapp-node-service',
            script      : 'index.js',
            instances   : 1,           // Single instance (WhatsApp sessions are in-memory)
            autorestart : true,
            watch       : false,
            max_memory_restart: '512M',
            env: {
                NODE_ENV        : 'production',
                PORT            : 3001,
                LARAVEL_API_URL : 'https://stockologysecurities.in', // Target production URL
                NODE_SECRET     : 'whatsapp_node_secret_key',       // Change this to a strong secret key
            },
            // Logging
            log_date_format : 'YYYY-MM-DD HH:mm:ss',
            error_file      : './logs/pm2-whatsapp-error.log',
            out_file        : './logs/pm2-whatsapp-out.log',
            merge_logs      : true,
        },
        {
            name        : 'sheets-crm-service',
            cwd         : './sheet-node-service',
            script      : 'index.js',
            instances   : 1,
            autorestart : true,
            watch       : false,
            max_memory_restart: '256M',
            env: {
                NODE_ENV        : 'production',
                PORT            : 3002,
            },
            // Logging
            log_date_format : 'YYYY-MM-DD HH:mm:ss',
            error_file      : './logs/pm2-sheets-error.log',
            out_file        : './logs/pm2-sheets-out.log',
            merge_logs      : true,
        }
    ],
};
