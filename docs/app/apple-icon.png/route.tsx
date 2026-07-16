import { ImageResponse } from 'next/og';
import { BUG_PATH_D } from '@/lib/bug-path';

// Same reason as og.png: the `.png` is in the route name so the file is served
// with an image content type. Linked from metadata.icons.apple, since this
// isn't one of Next's magic filenames.
export const dynamic = 'force-static';

export function GET() {
  return new ImageResponse(
    (
      <div
        style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          // iOS applies its own rounding, so the tile is drawn square.
          background: '#f59e0b',
        }}
      >
        <svg
          width="132"
          height="132"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#18120a"
          strokeWidth={1.6}
          strokeLinecap="round"
          strokeLinejoin="round"
        >
          <path d={BUG_PATH_D} />
        </svg>
      </div>
    ),
    { width: 180, height: 180 },
  );
}
