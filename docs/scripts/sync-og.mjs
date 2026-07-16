import { copyFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

// The README needs a committed image — GitHub only renders files in the repo,
// and the real og.png is generated into the gitignored `out/`. So the README's
// copy is a snapshot, and this keeps it honest.
//
// Deliberately not wired into `build`: CI builds must not write to tracked
// files, or every deploy would leave a dirty tree.
const here = dirname(fileURLToPath(import.meta.url));
const from = resolve(here, '../out/og.png');
const to = resolve(here, '../../art/og.png');

await copyFile(from, to);

console.log(`copied ${from} -> ${to}`);
