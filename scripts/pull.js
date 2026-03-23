const path = require('path');
const fs = require('fs');
const { Client } = require('basic-ftp');
const { getConfig } = require('./ftp-config');

const root = path.join(__dirname, '..');

async function pull() {
    const config = getConfig();
    const client = new Client();
    const hasEnv = fs.existsSync(path.join(root, '.env'));

    try {
        await client.access({
            host: config.host,
            port: config.port,
            user: config.user,
            password: config.password,
            secure: config.secure
        });

        const remotePath = config.remotePath.replace(/\\/g, '/').replace(/\/$/, '');
        console.log('Preuzimam sa servera...');
        const envBackup = hasEnv ? fs.readFileSync(path.join(root, '.env'), 'utf8') : null;

        await client.downloadToDir(root, remotePath);

        if (envBackup) fs.writeFileSync(path.join(root, '.env'), envBackup);
        console.log('Preuzimanje završeno.');
    } catch (err) {
        console.error('Greška:', err.message);
        process.exit(1);
    } finally {
        client.close();
    }
}

pull();
