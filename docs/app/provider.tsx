'use client';

import { RootProvider } from 'fumadocs-ui/provider/next';
import type { ReactNode } from 'react';
import StaticSearchDialog from '@/components/search';

/**
 * `RootProvider` is given a component, which a server component can't pass —
 * hence the client boundary.
 */
export function Provider({ children }: { children: ReactNode }) {
  return (
    <RootProvider search={{ SearchDialog: StaticSearchDialog }}>
      {children}
    </RootProvider>
  );
}
