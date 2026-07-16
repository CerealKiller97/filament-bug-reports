import { cp, mkdir } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

// The screenshots belong to the package (the README uses them too). Copy rather
// than commit a second set that can drift out of sync with the first.
const here = dirname(fileURLToPath(import.meta.url));
const from = resolve(here, '../../art');
const to = resolve(here, '../public/art');

await mkdir(to, { recursive: true });
await cp(from, to, { recursive: true });

console.log(`copied ${from} -> ${to}`);
