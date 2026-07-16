import './global.css';
import type { Metadata } from 'next';
import NextTopLoader from 'nextjs-toploader';
import type { ReactNode } from 'react';
import { Analytics } from '@/components/analytics';
import { withBasePath } from '@/lib/base-path';
import { OG_IMAGES, SITE_DESCRIPTION, SITE_NAME, SITE_URL } from '@/lib/site';
import { Provider } from './provider';

export const metadata: Metadata = {
  metadataBase: new URL(`${SITE_URL}/`),
  title: {
    default: SITE_NAME,
    template: `%s — ${SITE_NAME}`,
  },
  description: SITE_DESCRIPTION,
  // Declaring `icons` at all opts out of Next's `app/icon.svg` auto-detection,
  // so the favicon has to be listed here too or it silently disappears.
  // Base-path-relative rather than absolute, so these also resolve in dev.
  icons: {
    icon: [{ url: withBasePath('/icon.svg'), type: 'image/svg+xml' }],
    apple: withBasePath('/apple-icon.png'),
  },
  openGraph: {
    type: 'website',
    siteName: SITE_NAME,
    url: SITE_URL,
    title: SITE_NAME,
    description: SITE_DESCRIPTION,
    images: OG_IMAGES,
  },
  twitter: {
    card: 'summary_large_image',
    title: SITE_NAME,
    description: SITE_DESCRIPTION,
    images: OG_IMAGES.map((image) => image.url),
  },
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className="flex min-h-screen flex-col">
        {/* The App Router fires no navigation events, so the loader hooks
            history itself. `color` is the theme variable rather than a literal,
            so the bar follows the light/dark toggle. */}
        <NextTopLoader
          color="var(--color-fd-primary)"
          height={2}
          shadow={false}
          showSpinner={false}
          zIndex={9999}
        />
        <Provider>{children}</Provider>
        <Analytics />
      </body>
    </html>
  );
}
