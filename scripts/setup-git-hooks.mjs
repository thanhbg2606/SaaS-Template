import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const hooksPath = '.githooks';
const repoRoot = process.cwd();

if (process.env.CI) {
    process.exit(0);
}

if (!existsSync(path.join(repoRoot, '.git'))) {
    process.exit(0);
}

try {
    execFileSync('git', ['config', 'core.hooksPath', hooksPath], {
        cwd: repoRoot,
        stdio: 'ignore',
    });
    process.stdout.write(`Configured Git hooks path to ${hooksPath}.\n`);
} catch {
    process.stdout.write('Skipping Git hooks installation.\n');
}
