import { execFileSync } from 'node:child_process';
import { existsSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { build } from 'esbuild';
import { compileScript, parse } from '@vue/compiler-sfc';

const root = fileURLToPath(new URL('..', import.meta.url));
const chrome = process.env.CHROME_BIN || [
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome',
].find(existsSync);
if (!chrome) throw new Error('Set CHROME_BIN to a local Chrome or Chromium executable.');
const directory = mkdtempSync(path.join(tmpdir(), 'clink-player-test-'));
try {
    const result = await build({
        entryPoints: [path.join(root, 'tests/Browser/display-player.test.js')],
        bundle: true,
        format: 'esm',
        write: false,
        define: { 'process.env.NODE_ENV': '"production"' },
        plugins: [{ name: 'vue-test', setup(builder) {
            builder.onLoad({ filter: /\.vue$/ }, args => {
                const { descriptor } = parse(readFileSync(args.path, 'utf8'));
                return {
                    contents: compileScript(descriptor, { id: args.path, inlineTemplate: true }).content,
                    resolveDir: path.dirname(args.path),
                    loader: 'js',
                };
            });
        } }],
    });
    const fixture = path.join(directory, 'index.html');
    writeFileSync(fixture, `<!doctype html><meta charset="utf-8"><script type="module">${result.outputFiles[0].text}</script>`);
    const html = execFileSync(chrome, [
        '--headless', '--no-first-run', '--no-default-browser-check', '--disable-background-networking',
        `--user-data-dir=${path.join(directory, 'profile')}`, '--virtual-time-budget=1000',
        '--dump-dom', pathToFileURL(fixture).href,
    ], { encoding: 'utf8', timeout: 30000, maxBuffer: 5 * 1024 * 1024, stdio: ['ignore', 'pipe', 'pipe'] });
    const output = html.match(/<pre id="test-results">(.*?)<\/pre>/s);
    if (!output) throw new Error('Browser did not complete the player checks.');
    const results = JSON.parse(output[1]);
    for (const result of results) console.log(`${result.passed ? 'PASS' : 'FAIL'} ${result.name}${result.error ? `: ${result.error}` : ''}`);
    if (results.some(result => !result.passed)) process.exitCode = 1;
} finally {
    rmSync(directory, { recursive: true, force: true });
}
