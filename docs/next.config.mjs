import { createMDX } from 'fumadocs-mdx/next';

// Project pages are served from https://<user>.github.io/<repo>/, so every
// absolute URL needs the repo name in front of it. Overridable for previews
// and for anyone serving the docs from a domain root.
const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? '/filament-bug-reports';

/** @type {import('next').NextConfig} */
const config = {
  // GitHub Pages serves files, not a Node server.
  output: 'export',
  basePath,
  // Emit `/docs/foo/index.html` rather than `/docs/foo.html`, which is what
  // Pages' static file server expects to resolve from a directory URL.
  trailingSlash: true,
  // The image optimizer is a server, and there isn't one.
  images: { unoptimized: true },
  env: {
    NEXT_PUBLIC_BASE_PATH: basePath,
  },
};

const withMDX = createMDX();

export default withMDX(config);
