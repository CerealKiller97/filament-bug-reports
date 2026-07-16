import Script from 'next/script';
import { AnalyticsEvents } from '@/components/analytics-events';

const DEFAULT_SCRIPT_URL = 'https://analytics.stefanbogdanovic.dev/script.js';

/**
 * Umami is cookieless and collects no personal data, so there's no consent
 * banner to gate this behind.
 *
 * Renders nothing unless `NEXT_PUBLIC_UMAMI_WEBSITE_ID` is set at build time,
 * which keeps `npm run dev` and forks' builds out of the dashboard. Both vars
 * are inlined by Next at build time — they are not read at runtime.
 */
export function Analytics() {
  const websiteId = process.env.NEXT_PUBLIC_UMAMI_WEBSITE_ID;

  if (!websiteId) return null;

  return (
    <>
      <Script
        defer
        strategy="afterInteractive"
        src={process.env.NEXT_PUBLIC_UMAMI_SCRIPT_URL || DEFAULT_SCRIPT_URL}
        data-website-id={websiteId}
      />
      <AnalyticsEvents />
    </>
  );
}
