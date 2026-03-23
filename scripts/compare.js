const path = require('path');
const fs = require('fs');
const { Client } = require('basic-ftp');
const { getConfig } = require('./ftp-config');

const root = path.join(__dirname, '..');
const fullCompare = process.argv.includes('--full');
const targetDir = fullCompare ? '' : 'template-parts';
const localDir = targetDir ? path.join(root, targetDir) : root;

async function listRemoteRecursive(client, remotePath, prefix = '') {
  const entries = await client.list(remotePath);
  const files = [];

  for (const e of entries) {
    const fullPath = prefix ? `${prefix}/${e.name}` : e.name;
    if (e.isDirectory && e.name !== '.' && e.name !== '..') {
      const subPath = remotePath ? `${remotePath}/${e.name}` : e.name;
      const sub = await listRemoteRecursive(client, subPath, fullPath);
      files.push(...sub);
    } else if (e.isFile) {
      files.push({ path: fullPath, size: e.size });
    }
  }

  return files;
}

function listLocalRecursive(dir, prefix = '') {
  const files = [];
  const entries = fs.readdirSync(dir, { withFileTypes: true });

  for (const e of entries) {
    const fullPath = prefix ? `${prefix}/${e.name}` : e.name;
    if (e.isDirectory()) {
      files.push(...listLocalRecursive(path.join(dir, e.name), fullPath));
    } else {
      const stat = fs.statSync(path.join(dir, e.name));
      files.push({ path: fullPath.replace(/\\/g, '/'), size: stat.size });
    }
  }

  return files;
}

async function compare() {
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

    const remoteBase = targetDir
      ? `${config.remotePath.replace(/\\/g, '/').replace(/\/$/, '')}/${targetDir}`
      : config.remotePath.replace(/\\/g, '/').replace(/\/$/, '');

    const excludeDirs = ['node_modules', 'scripts', '.git', '.cursor'];
    const excludeFiles = ['.env', '.env.example', 'package.json', 'package-lock.json'];

    function shouldExclude(p) {
      if (excludeDirs.some(d => p.includes(`/${d}/`) || p.startsWith(`${d}/`) || p === d)) return true;
      if (excludeFiles.includes(path.basename(p))) return true;
      if (/\.(map|log)$/i.test(p)) return true;
      return false;
    }

    let remoteFiles;
    try {
      remoteFiles = (await listRemoteRecursive(client, remoteBase)).filter(f => !shouldExclude(f.path));
    } catch (err) {
      if (err.message.includes('No such file')) {
        console.log('template-parts ili theme folder nije pronađen na serveru.\n');
        try {
          const pwd = await client.pwd();
          console.log('Trenutni direktorij nakon prijave:', pwd);
          const rootList = await client.list('/');
          const rootItems = rootList.filter(f => f.isFile || (f.isDirectory && !/^\.\.?$/.test(f.name)));
          console.log('Root sadržaj:', rootItems.map(f => f.name).join(', '));
          if (config.remotePath !== '/' && config.remotePath !== '') {
            const parts = config.remotePath.replace(/^\/+/, '').split('/');
            let current = '';
            for (const p of parts) {
              current = current ? `${current}/${p}` : `/${p}`;
              try {
                const list = await client.list(current);
                const items = list.filter(f => f.isFile || (f.isDirectory && !/^\.\.?$/.test(f.name)));
                console.log(`  ${current}:`, items.map(f => f.name).join(', ') || '(prazno)');
              } catch (_) {
                console.log(`  ${current}: ne postoji`);
              }
            }
          }
        } catch (e) {
          console.log('Nije moguće listati:', e.message);
        }
        console.log('\nProvjeri FTP_REMOTE_PATH u .env – trebao bi pokazivati na theme folder.');
        return;
      }
      throw err;
    }
    const localFiles = listLocalRecursive(localDir).filter(f => !shouldExclude(f.path));

    const remoteMap = new Map(remoteFiles.map(f => [f.path, f]));
    const localMap = new Map(localFiles.map(f => [f.path, f]));

    const onlyRemote = remoteFiles.filter(f => !localMap.has(f.path));
    const onlyLocal = localFiles.filter(f => !remoteMap.has(f.path));
    const different = localFiles.filter(f => {
      const r = remoteMap.get(f.path);
      return r && r.size !== f.size;
    });
    const same = localFiles.filter(f => {
      const r = remoteMap.get(f.path);
      return r && r.size === f.size;
    });

    const title = fullCompare ? 'cijeli theme' : 'template-parts';
    console.log(`\n=== ${title} usporedba ===\n`);
    if (onlyRemote.length) {
      console.log('Samo na serveru:');
      onlyRemote.forEach(f => console.log('  +', f.path, `(${f.size} B)`));
      console.log('');
    }
    if (onlyLocal.length) {
      console.log('Samo lokalno:');
      onlyLocal.forEach(f => console.log('  -', f.path, `(${f.size} B)`));
      console.log('');
    }
    if (different.length) {
      console.log('Različite veličine (lokalno vs server):');
      different.forEach(f => {
        const r = remoteMap.get(f.path);
        console.log('  ~', f.path, `lokalno: ${f.size} B, server: ${r.size} B`);
      });
      console.log('');
    }
    if (same.length) {
      console.log('Iste datoteke:', same.map(f => f.path).join(', '));
    }
    console.log('\nUkupno: remote', remoteFiles.length, '| lokalno', localFiles.length, '| različite', different.length);
  } catch (err) {
    console.error('Greška:', err.message);
    process.exit(1);
  } finally {
    client.close();
  }
}

compare();
