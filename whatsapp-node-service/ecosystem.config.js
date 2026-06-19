// PM2 Ecosystem Configuration for Hostinger Cloud Professional
// Usage: pm2 start ecosystem.config.js
module.exports = {
    apps: [
        {
            name        : 'whatsapp-crm-service',
            script      : 'index.js',
            instances   : 1,           // Single instance (WhatsApp sessions are in-memory)
            autorestart : true,
            watch       : false,
            max_memory_restart: '512M',
            env: {
                NODE_ENV        : 'production',
                PORT            : 3001,
                LARAVEL_API_URL : 'https://stockologysecurities.in',
                NODE_SECRET     : 'CHANGE_THIS_TO_A_RANDOM_SECRET_KEY',
            },
            env_local: {
                NODE_ENV        : 'local',
                PORT            : 3001,
                LARAVEL_API_URL : 'http://localhost',
                NODE_SECRET     : 'whatsapp_node_secret_key',
            },
            // Logging
            log_date_format : 'YYYY-MM-DD HH:mm:ss',
            error_file      : './logs/pm2-error.log',
            out_file        : './logs/pm2-out.log',
            merge_logs      : true,
        },
    ],
};
