import { createFromSource } from 'fumadocs-core/search/server';
import { source } from '@/lib/source';

// `staticGET` writes the index to a file at build time instead of answering
// requests — there is no server on GitHub Pages to answer them.
export const revalidate = false;
export const { staticGET: GET } = createFromSource(source, {
  language: 'english',
});
