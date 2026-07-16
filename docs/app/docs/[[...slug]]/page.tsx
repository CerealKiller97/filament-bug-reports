import {
  DocsBody,
  DocsDescription,
  DocsPage,
  DocsTitle,
} from 'fumadocs-ui/layouts/docs/page';
import { createRelativeLink } from 'fumadocs-ui/mdx';
import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { getMDXComponents } from '@/components/mdx';
import { OG_IMAGES, SITE_DESCRIPTION, SITE_NAME, SITE_URL } from '@/lib/site';
import { source } from '@/lib/source';

export default async function Page(props: {
  params: Promise<{ slug?: string[] }>;
}) {
  const params = await props.params;
  const page = source.getPage(params.slug);
  if (!page) notFound();

  const MDX = page.data.body;

  return (
    <DocsPage toc={page.data.toc} full={page.data.full}>
      <DocsTitle>{page.data.title}</DocsTitle>
      <DocsDescription>{page.data.description}</DocsDescription>
      <DocsBody>
        <MDX
          components={getMDXComponents({
            a: createRelativeLink(source, page),
          })}
        />
      </DocsBody>
    </DocsPage>
  );
}

export function generateStaticParams() {
  return source.generateParams();
}

export async function generateMetadata(props: {
  params: Promise<{ slug?: string[] }>;
}): Promise<Metadata> {
  const params = await props.params;
  const page = source.getPage(params.slug);
  if (!page) notFound();

  const description = page.data.description ?? SITE_DESCRIPTION;

  return {
    title: page.data.title,
    description,
    // Without this, sharing a deep link shows the site's generic title. The
    // images have to be repeated — see the note on OG_IMAGES.
    openGraph: {
      type: 'article',
      siteName: SITE_NAME,
      url: `${SITE_URL}${page.url}`,
      title: `${page.data.title} — ${SITE_NAME}`,
      description,
      images: OG_IMAGES,
    },
    twitter: {
      card: 'summary_large_image',
      title: `${page.data.title} — ${SITE_NAME}`,
      description,
      images: OG_IMAGES.map((image) => image.url),
    },
  };
}
