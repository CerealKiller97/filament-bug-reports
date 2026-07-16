import { defineConfig, defineDocs } from 'fumadocs-mdx/config';

export const docs = defineDocs({
  dir: 'content/docs',
});

export default defineConfig({
  mdxOptions: {
    remarkImageOptions: {
      // Default `true` rewrites `![](/art/x.png)` into a bundler import object
      // for next/image. The image optimizer can't run on a static export, so
      // that indirection buys nothing and leaves a plain <img> with an object
      // for a src. Keep the string; components/mdx.tsx adds the base path.
      useImport: false,
      publicDir: './public',
    },
  },
});
