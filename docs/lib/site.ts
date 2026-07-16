export const SITE_NAME = 'Filament Bug Reports';

export const SITE_DESCRIPTION =
  'Collect bug reports from inside your Filament panel, and push the ones you confirm are real straight to GitHub as issues.';

/**
 * Scrapers don't resolve relative URLs and never see the dev server, so the
 * deployed origin (base path included) is hardcoded rather than inferred.
 */
export const SITE_URL = 'https://filament-bug-reports.stefanbogdanovic.dev/';

/**
 * Next merges metadata shallowly: a page that declares `openGraph` replaces the
 * parent's whole object, images included. Any page setting its own OG fields
 * has to spread these back in.
 */
export const OG_IMAGES = [
  {
    url: `${SITE_URL}/og.png`,
    width: 1200,
    height: 630,
    alt: `${SITE_NAME} — in-app bug reports, triaged in your panel and pushed to GitHub as issues.`,
  },
];
