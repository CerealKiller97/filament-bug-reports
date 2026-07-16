import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import { ImageResponse } from 'next/og';
import { AMBER, BUG_PATH_D } from '@/lib/bug-path';

// next/og only bundles Geist Regular, so `fontWeight: 700` alone renders at
// regular weight. Read the real bold from the `geist` package — a build-time
// dependency, so no network call and no font binaries committed here.
const fontDir = join(process.cwd(), 'node_modules/geist/dist/fonts/geist-sans');
const geist = (file: string) => readFileSync(join(fontDir, file));

// Rendered once at build time into `out/og.png`. The `.png` is part of the
// route name on purpose: a static host serves by file extension, and the
// metadata convention (`app/opengraph-image.tsx`) emits an extensionless file
// that gets served as application/octet-stream — which scrapers refuse.
export const dynamic = 'force-static';

const SIZE = { width: 1200, height: 630 };

function Chip({ children }: { children: string }) {
  return (
    <div
      style={{
        display: 'flex',
        border: '1px solid #27272a',
        borderRadius: 999,
        padding: '8px 20px',
        fontSize: 22,
        color: '#a1a1aa',
      }}
    >
      {children}
    </div>
  );
}

export function GET() {
  return new ImageResponse(
    (
      <div
        style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'space-between',
          padding: 72,
          fontFamily: 'Geist',
          background: '#09090b',
          // Amber bloom behind the mark, so the flat panel has some depth.
          backgroundImage:
            'radial-gradient(760px circle at 140px 90px, rgba(245,158,11,0.20), rgba(9,9,11,0) 62%)',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: 26 }}>
          <svg
            width="104"
            height="104"
            viewBox="0 0 24 24"
            fill="none"
            stroke={AMBER}
            strokeWidth={1.5}
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <path d={BUG_PATH_D} />
          </svg>
          <div
            style={{
              display: 'flex',
              fontSize: 24,
              letterSpacing: 4,
              color: AMBER,
            }}
          >
            FILAMENT PLUGIN
          </div>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column' }}>
          <div
            style={{
              display: 'flex',
              fontSize: 82,
              fontWeight: 700,
              color: '#fafafa',
              letterSpacing: -2,
            }}
          >
            Filament Bug Reports
          </div>
          <div
            style={{
              display: 'flex',
              marginTop: 20,
              fontSize: 32,
              color: '#a1a1aa',
              maxWidth: 900,
            }}
          >
            In-app bug reports, triaged in your panel and pushed to GitHub as
            issues.
          </div>
        </div>

        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}
        >
          <div style={{ display: 'flex', gap: 12 }}>
            <Chip>PHP 8.3+</Chip>
            <Chip>Laravel 13</Chip>
            <Chip>Filament 5</Chip>
          </div>
          <div style={{ display: 'flex', fontSize: 22, color: '#52525b' }}>
            cerealkiller97/filament-bug-reports
          </div>
        </div>
      </div>
    ),
    {
      ...SIZE,
      fonts: [
        { name: 'Geist', data: geist('Geist-Regular.ttf'), weight: 400, style: 'normal' },
        { name: 'Geist', data: geist('Geist-Bold.ttf'), weight: 700, style: 'normal' },
      ],
    },
  );
}
