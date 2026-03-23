const path = require('path');
const fs = require('fs');

const root = path.join(__dirname, '..');
const envPath = path.join(root, '.env');

function parseEnv() {
    const out = {};
    if (!fs.existsSync(envPath)) return out;
    const content = fs.readFileSync(envPath, 'utf8');
    for (const line of content.split('\n')) {
        const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
        if (m) out[m[1].trim()] = m[2].trim().replace(/^["']|["']$/g, '');
    }
    return out;
}

function getConfig() {
    const env = parseEnv();
    let host = (env.FTP_HOST || '').replace(/^ftp:\/\//, '').replace(/\/.*$/, '');
    return {
        host: host || 'localhost',
        port: parseInt(env.FTP_PORT || '21', 10),
        user: env.FTP_USER || '',
        password: env.FTP_PASSWORD || '',
        secure: /^(1|true|yes)$/i.test(env.FTP_SECURE || ''),
        remotePath: (env.FTP_REMOTE_PATH || '/').replace(/\\/g, '/').replace(/\/$/, '') + '/'
    };
}

module.exports = { getConfig };
