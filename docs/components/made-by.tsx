import { Heart } from '@/components/icons';

export function MadeBy({ className }: { className?: string }) {
  return (
    <p className={className}>
      <Heart className="mr-1.5 inline size-[1em] align-[-0.1em] text-rose-500" />
      Proudly made by{' '}
      <a
        href="https://stefanbogdanovic.dev"
        target="_blank"
        rel="noreferrer"
        className="whitespace-nowrap font-medium text-fd-foreground underline underline-offset-4 hover:text-fd-primary"
      >
        Stefan Bogdanović
      </a>
    </p>
  );
}
