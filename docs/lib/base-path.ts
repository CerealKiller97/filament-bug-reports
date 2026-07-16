/**
 * The prefix every absolute URL needs when the site is served from a GitHub
 * Pages project path. Next rewrites `<Link>` and `next/image` itself, but not
 * raw `src`/`href` strings that come out of MDX or a hand-written fetch.
 */
export const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? '';

export function withBasePath(path: string): string {
  return path.startsWith('/') ? `${basePath}${path}` : path;
}
