import { Card, Cards } from 'fumadocs-ui/components/card';
import { Step, Steps } from 'fumadocs-ui/components/steps';
import defaultMdxComponents from 'fumadocs-ui/mdx';
import type { MDXComponents } from 'mdx/types';
import type { ImgHTMLAttributes } from 'react';
import { ThemedImage } from '@/components/themed-image';
import { withBasePath } from '@/lib/base-path';

/**
 * Screenshots are written as `![alt](/art/x.png)` in MDX, which reaches the
 * browser as a literal string — nothing in the Next pipeline prefixes it with
 * the Pages base path, so do it here.
 */
function Img({ src, alt, ...props }: ImgHTMLAttributes<HTMLImageElement>) {
  return (
    // eslint-disable-next-line @next/next/no-img-element
    <img
      src={typeof src === 'string' ? withBasePath(src) : src}
      alt={alt ?? ''}
      loading="lazy"
      className="rounded-lg border border-fd-border shadow-sm"
      {...props}
    />
  );
}

export function getMDXComponents(components?: MDXComponents) {
  return {
    ...defaultMdxComponents,
    img: Img,
    Card,
    Cards,
    Step,
    Steps,
    ThemedImage,
    ...components,
  } satisfies MDXComponents;
}

export const useMDXComponents = getMDXComponents;
