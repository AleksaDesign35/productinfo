const path = require('path');
const fs = require('fs');
const { Readable } = require('stream');
const { Client } = require('basic-ftp');
const chokidar = require('chokidar');
const CleanCSS = require('clean-css');
const { getConfig } = require('./ftp-config');

const root = path.join(__dirname, '..');
const excludeDirs = ['node_modules', '.git', 'sass', 'scripts', 'source', '.cursor', '.vscode', '.idea'];
const excludeFiles = ['.env', '.gitignore', 'package.json', 'package-lock.json', 'composer.json', 'composer.lock', 'phpcs.xml.dist'];

function shouldExclude(relativePath) {
    const parts = relativePath.split(path.sep);
    if (parts.some(p => excludeDirs.includes(p))) return true;
    if (excludeFiles.includes(path.basename(relativePath))) return true;
    if (/\.(map|log)$/i.test(relativePath)) return true;
    return false;
}

async function getFiles(dir, files = []) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const e of entries) {
        const full = path.join(dir, e.name);
        const rel = path.relative(root, full);
        if (e.isDirectory()) {
            if (!excludeDirs.includes(e.name)) getFiles(full, files);
        } else if (!shouldExclude(rel)) files.push(full);
    }
    return files;
}

async function uploadFile(client, localPath, remoteBase) {
    const rel = path.relative(root, localPath).replace(/\\/g, '/');
    const remotePath = `${remoteBase.replace(/\\/g, '/').replace(/\/$/, '')}/${rel}`;
    const remoteDir = path.dirname(remotePath).replace(/\\/g, '/');
    if (remoteDir !== '.' && remoteDir !== remoteBase) await client.ensureDir(remoteDir);
    if (/\.css$/i.test(localPath) && !/\.min\.css$/i.test(localPath)) {
        const css = fs.readFileSync(localPath, 'utf8');
        const minified = new CleanCSS({ level: 2 }).minify(css);
        const payload = minified.styles || css;
        await client.uploadFrom(Readable.from([payload]), remotePath);
    } else {
        await client.uploadFrom(localPath, remotePath);
    }
    console.log('Uploaded:', rel);
}

async function deleteRemote(client, localPath, remoteBase) {
    const rel = path.relative(root, localPath).replace(/\\/g, '/');
    const full = `${remoteBase.replace(/\\/g, '/').replace(/\/$/, '')}/${rel}`;
    try {
        await client.remove(full, true);
        console.log('Obrisano na serveru:', rel);
    } catch (_) {}
}

async function deleteRemoteDir(client, localPath, remoteBase) {
    const rel = path.relative(root, localPath).replace(/\\/g, '/');
    const full = `${remoteBase.replace(/\\/g, '/').replace(/\/$/, '')}/${rel}`;
    try {
        await client.removeDir(full);
        console.log('Obrisan folder na serveru:', rel);
    } catch (_) {}
}

async function sync() {
    const config = getConfig();
    const client = new Client();

    try {
        await client.access({
            host: config.host,
            port: config.port,
            user: config.user,
            password: config.password,
            secure: config.secure
        });
        await client.ensureDir(config.remotePath);

        const files = await getFiles(root);
        for (const f of files) {
            await uploadFile(client, f, config.remotePath);
        }
        console.log('Sync done');
    } catch (err) {
        console.error('Error:', err.message);
        process.exit(1);
    } finally {
        client.close();
    }
}

async function watch() {
    const config = getConfig();
    let client = null;
    const uploadQueue = [];
    const deleteQueue = [];
    const deleteDirQueue = [];
    let debounce = null;
    let flushing = false;
    const usePolling = process.platform === 'darwin' || /^(1|true|yes)$/i.test(process.env.CHOKIDAR_USEPOLLING || '');

    async function connect() {
        if (client && !client.closed) return client;
        client = new Client();
        await client.access({
            host: config.host,
            port: config.port,
            user: config.user,
            password: config.password,
            secure: config.secure
        });
        await client.ensureDir(config.remotePath);
        return client;
    }

    async function flush() {
        if (flushing) return;
        if (uploadQueue.length === 0 && deleteQueue.length === 0 && deleteDirQueue.length === 0) return;
        flushing = true;
        const toUpload = [...new Set(uploadQueue)];
        const toDelete = [...new Set(deleteQueue)];
        const toDeleteDirs = [...new Set(deleteDirQueue)];
        uploadQueue.length = 0;
        deleteQueue.length = 0;
        deleteDirQueue.length = 0;
        try {
            const c = await connect();
            for (const f of toDeleteDirs.sort((a, b) => b.length - a.length)) {
                await deleteRemoteDir(c, f, config.remotePath);
            }
            for (const f of toDelete) {
                await deleteRemote(c, f, config.remotePath);
            }
            for (const f of toUpload) {
                if (fs.existsSync(f) && fs.statSync(f).isFile()) {
                    await uploadFile(c, f, config.remotePath);
                }
            }
        } catch (err) {
            console.error('FTP greška:', err.message);
            client?.close();
            client = null;
        } finally {
            flushing = false;
            if (uploadQueue.length || deleteQueue.length || deleteDirQueue.length) {
                debounce = setTimeout(flush, 300);
            }
        }
    }

    function scheduleUpload(filePath) {
        const rel = path.relative(root, filePath);
        if (shouldExclude(rel)) return;
        try {
            if (!fs.statSync(filePath).isFile()) return;
        } catch (_) { return; }
        uploadQueue.push(filePath);
        clearTimeout(debounce);
        debounce = setTimeout(flush, 300);
    }

    function scheduleDelete(filePath) {
        const rel = path.relative(root, filePath);
        if (shouldExclude(rel)) return;
        deleteQueue.push(filePath);
        clearTimeout(debounce);
        debounce = setTimeout(flush, 300);
    }

    function scheduleDeleteDir(dirPath) {
        const rel = path.relative(root, dirPath);
        if (shouldExclude(rel)) return;
        if (excludeDirs.some(d => rel.startsWith(d + '/') || rel === d)) return;
        deleteDirQueue.push(dirPath);
        clearTimeout(debounce);
        debounce = setTimeout(flush, 300);
    }

    console.log(`Watching for changes${usePolling ? ' (polling mode)' : ''}...`);
    const watcher = chokidar.watch(root, {
        ignored: filePath => {
            const rel = path.relative(root, filePath);
            if (!rel || rel === '') return false;
            if (rel.startsWith('..')) return true;
            return shouldExclude(rel);
        },
        ignoreInitial: true,
        usePolling,
        interval: usePolling ? 350 : undefined,
        binaryInterval: usePolling ? 700 : undefined,
        awaitWriteFinish: {
            stabilityThreshold: 400,
            pollInterval: 100
        }
    });

    watcher.on('add', scheduleUpload).on('change', scheduleUpload);
    watcher.on('unlink', scheduleDelete);
    watcher.on('unlinkDir', scheduleDeleteDir);
    watcher.on('error', err => {
        console.error('Watcher error:', err.message);
    });
}

const cmd = process.argv[2];
if (cmd === 'sync') sync();
else if (cmd === 'watch') watch();
else {
    console.error('Usage: node ftp-watcher.js [sync|watch]');
    process.exit(1);
}
