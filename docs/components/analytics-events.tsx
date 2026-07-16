'use client';

import { useEffect } from 'react';

declare global {
  interface Window {
    umami?: {
      track: (name: string, data?: Record<string, unknown>) => void;
    };
  }
}

/**
 * Umami auto-tracks any `<a>`/`<button>` carrying `data-umami-event`, which
 * covers the elements we render ourselves. Everything below is rendered by
 * Fumadocs — we can't put an attribute on it, so match on the aria-labels it
 * sets and report through `umami.track` instead.
 *
 * Those labels are Fumadocs' English strings; this site ships no other locale.
 * If a label changes upstream, the event quietly stops firing — hence the
 * single list here rather than the checks being scattered around.
 */
const LABELS = {
  copyCode: 'Copy Text',
  copyAnchor: 'Copy Anchor Link',
  openSearch: 'Open Search',
  toggleTheme: 'Toggle Theme',
} as const;

export function AnalyticsEvents() {
  useEffect(() => {
    function handleClick(event: MouseEvent) {
      const target = event.target;
      if (!(target instanceof Element)) return;

      const el = target.closest('a,button');
      if (!el) return;

      // Umami already tracks these; reporting again would double count.
      if (el.hasAttribute('data-umami-event')) return;

      const track = window.umami?.track;
      if (typeof track !== 'function') return;

      const page = window.location.pathname;
      const label = el.getAttribute('aria-label');

      if (label === LABELS.copyCode) {
        // The snippet's title, e.g. "config/bug-reports.php" — tells us which
        // snippets people actually take, which an undifferentiated count can't.
        const title = el
          .closest('figure')
          ?.querySelector('figcaption')
          ?.textContent?.trim();

        track('copy-code', { snippet: title || 'untitled', page });
        return;
      }

      if (label === LABELS.copyAnchor) {
        const heading = el.closest('h1,h2,h3,h4,h5,h6')?.textContent?.trim();
        track('copy-heading-link', { heading: heading || 'unknown', page });
        return;
      }

      if (label === LABELS.toggleTheme) {
        track('toggle-theme', { page });
        return;
      }

      // The collapsed trigger is labelled; the wide one is a button with a
      // "⌘ K" hint inside and no label of its own.
      if (label === LABELS.openSearch || (el.tagName === 'BUTTON' && el.querySelector('kbd'))) {
        track('open-search', { page });
        return;
      }

      if (el instanceof HTMLAnchorElement && el.hostname && el.hostname !== window.location.hostname) {
        track('outbound-link', { url: el.href, page });
      }
    }

    // Capture phase: some of these buttons stop propagation.
    document.addEventListener('click', handleClick, true);
    return () => document.removeEventListener('click', handleClick, true);
  }, []);

  return null;
}
