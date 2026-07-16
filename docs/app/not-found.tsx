import { HomeLayout } from 'fumadocs-ui/layouts/home';
import type { Metadata } from 'next';
import Link from 'next/link';
import { BugAnt } from '@/components/icons';
import { baseOptions } from '@/lib/layout.shared';

export const metadata: Metadata = {
  title: 'Page not found',
};

export default function NotFound() {
  return (
    <HomeLayout {...baseOptions()}>
      <main className="flex flex-1 flex-col items-center justify-center px-4 py-20 text-center sm:py-28">
        <span className="flex size-14 items-center justify-center rounded-2xl border border-fd-border bg-fd-card">
          <BugAnt className="size-7 text-fd-primary" />
        </span>

        <p className="mt-8 text-sm font-medium text-fd-muted-foreground">404</p>
        <h1 className="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
          This page doesn’t exist
        </h1>
        <p className="mx-auto mt-4 max-w-md text-balance text-fd-muted-foreground">
          The link is broken, or the page moved. If you followed it from
          somewhere in the docs, that’s a bug — and this seems like the right
          project to report it on.
        </p>

        <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
          <Link
            href="/docs"
            data-umami-event="404-docs"
            className="rounded-lg bg-fd-primary px-5 py-2.5 text-sm font-medium text-fd-primary-foreground transition-opacity hover:opacity-90"
          >
            Read the docs
          </Link>
          <Link
            href="/"
            data-umami-event="404-home"
            className="rounded-lg border border-fd-border bg-fd-card px-5 py-2.5 text-sm font-medium transition-colors hover:bg-fd-accent"
          >
            Go home
          </Link>
        </div>

        <p className="mt-10 text-sm text-fd-muted-foreground">
          Think something should be here?{' '}
          <a
            href="https://github.com/CerealKiller97/filament-bug-reports/issues/new"
            target="_blank"
            rel="noreferrer"
            data-umami-event="404-report"
            className="font-medium text-fd-foreground underline underline-offset-4 hover:text-fd-primary"
          >
            Open an issue
          </a>
          .
        </p>
      </main>
    </HomeLayout>
  );
}
