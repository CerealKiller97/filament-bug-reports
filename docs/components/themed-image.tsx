import { withBasePath } from '@/lib/base-path';

const frame = 'rounded-lg border border-fd-border shadow-sm';

/**
 * Swaps a screenshot with the site's light/dark toggle.
 *
 * Both variants are rendered and toggled with CSS rather than picked in JS:
 * the theme isn't known while the server renders, so a JS choice would either
 * flash the wrong screenshot on load or force the image to wait for hydration.
 * The hidden one is `display: none` and lazy, so it isn't fetched.
 */
export function ThemedImage({
  light,
  dark,
  alt,
  className,
}: {
  light: string;
  dark: string;
  alt: string;
  className?: string;
}) {
  return (
    <>
      <img
        src={withBasePath(light)}
        alt={alt}
        loading="lazy"
        className={`${frame} block dark:hidden ${className ?? ''}`}
      />
      <img
        src={withBasePath(dark)}
        alt={alt}
        loading="lazy"
        className={`${frame} hidden dark:block ${className ?? ''}`}
      />
    </>
  );
}
