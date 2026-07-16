import type { BaseLayoutProps } from 'fumadocs-ui/layouts/shared';
import { BugAnt } from '@/components/icons';

export function baseOptions(): BaseLayoutProps {
  return {
    nav: {
      title: (
        <>
          <BugAnt className="size-5 text-fd-primary" />
          <span className="font-semibold">Filament Bug Reports</span>
        </>
      ),
    },
    githubUrl: 'https://github.com/CerealKiller97/filament-bug-reports',
    links: [
      {
        text: 'Documentation',
        url: '/docs',
        active: 'nested-url',
      },
      {
        text: 'Packagist',
        url: 'https://packagist.org/packages/cerealkiller97/filament-bug-reports',
        external: true,
      },
    ],
  };
}
