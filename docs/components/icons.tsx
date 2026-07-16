import type { SVGProps } from 'react';
import { BUG_PATH_D } from '@/lib/bug-path';

/** Heroicons `heart` (solid). */
export function Heart(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {...props}>
      <path d="M11.645 20.91a.75.75 0 0 1-1.29 0C7.4 18.24 2.25 13.605 2.25 8.813 2.25 6.153 4.404 4 7.06 4a4.8 4.8 0 0 1 4.94 3.05A4.8 4.8 0 0 1 16.94 4c2.656 0 4.81 2.153 4.81 4.813 0 4.792-5.15 9.427-8.105 12.097Z" />
    </svg>
  );
}

/** Heroicons `bug-ant` (outline) — the same icon the panel's topbar button uses. */
export function BugAnt(props: SVGProps<SVGSVGElement>) {
  return (
    <svg
      fill="none"
      viewBox="0 0 24 24"
      strokeWidth={1.5}
      stroke="currentColor"
      aria-hidden="true"
      {...props}
    >
      <path strokeLinecap="round" strokeLinejoin="round" d={BUG_PATH_D} />
    </svg>
  );
}
