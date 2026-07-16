import Link from 'next/link';
import { MadeBy } from '@/components/made-by';
import { ThemedImage } from '@/components/themed-image';

const steps = [
  {
    title: 'Anyone in the panel reports a bug',
    body: 'They describe the problem, list the steps that led to it, and optionally attach a screenshot. The report is stamped with the reporter, their role and the running app version — they aren’t asked for any of it.',
  },
  {
    title: 'A manager triages',
    body: 'Reports land in a table only managers can see. Noise gets deleted; the real ones get Mark as real.',
  },
  {
    title: 'A GitHub issue is created',
    body: 'With the steps and screenshot formatted into the body. The issue number and URL are stored on the report.',
  },
  {
    title: 'State syncs back',
    body: 'An hourly command checks each linked issue: closed becomes Resolved, reopened flips back to In progress.',
  },
];

export default function HomePage() {
  return (
    <main className="flex flex-1 flex-col">
      <section className="mx-auto w-full max-w-5xl px-4 py-20 text-center sm:py-28">
        <p className="mb-4 inline-flex items-center rounded-full border border-fd-border bg-fd-card px-3 py-1 text-xs font-medium text-fd-muted-foreground">
          For Filament 5 · Laravel 13 · PHP 8.3+
        </p>
        <h1 className="text-balance text-4xl font-semibold tracking-tight sm:text-6xl">
          Bug reports from inside your panel, straight to GitHub
        </h1>
        <p className="mx-auto mt-6 max-w-2xl text-balance text-lg text-fd-muted-foreground">
          Your users get a <strong className="text-fd-foreground">Report a bug</strong>{' '}
          button and a short, plain-language form — no Markdown, no issue
          templates, no GitHub account. You get a triage table where a single
          click turns a report into a proper GitHub issue.
        </p>
        <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
          <Link
            href="/docs"
            data-umami-event="cta-get-started"
            className="rounded-lg bg-fd-primary px-5 py-2.5 text-sm font-medium text-fd-primary-foreground transition-opacity hover:opacity-90"
          >
            Get started
          </Link>
          <Link
            href="/docs/installation"
            data-umami-event="cta-installation"
            className="rounded-lg border border-fd-border bg-fd-card px-5 py-2.5 text-sm font-medium transition-colors hover:bg-fd-accent"
          >
            Installation
          </Link>
        </div>
        <code className="mt-8 inline-block rounded-lg border border-fd-border bg-fd-card px-4 py-2 text-sm text-fd-muted-foreground">
          composer require cerealkiller97/filament-bug-reports
        </code>
      </section>

      <section className="mx-auto w-full max-w-5xl px-4 pb-20">
        <ThemedImage
          light="/art/triage-table-light.png"
          dark="/art/triage-table-dark.png"
          alt="The triage table, showing bug reports with priority badges and stats above the list"
          className="w-full shadow-lg"
        />
      </section>

      <section className="border-t border-fd-border bg-fd-card/40">
        <div className="mx-auto w-full max-w-5xl px-4 py-20">
          <h2 className="text-2xl font-semibold tracking-tight">How it works</h2>
          <ol className="mt-8 grid gap-6 sm:grid-cols-2">
            {steps.map((step, i) => (
              <li
                key={step.title}
                className="rounded-xl border border-fd-border bg-fd-background p-6"
              >
                <span className="flex size-7 items-center justify-center rounded-full bg-fd-primary/10 text-xs font-semibold text-fd-primary">
                  {i + 1}
                </span>
                <h3 className="mt-4 font-medium">{step.title}</h3>
                <p className="mt-2 text-sm text-fd-muted-foreground">{step.body}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <footer className="border-t border-fd-border">
        <div className="mx-auto flex w-full max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-10 text-sm text-fd-muted-foreground">
          <span>
            Apache License 2.0 ·{' '}
            <a
              href="https://github.com/CerealKiller97/filament-bug-reports"
              className="underline underline-offset-4 hover:text-fd-foreground"
            >
              GitHub
            </a>
          </span>
          <MadeBy />
        </div>
      </footer>
    </main>
  );
}
