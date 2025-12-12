# Frontend Implementation Instructions for Phase 6

Since the Frontend repository was not accessible in this workspace, the following changes must be applied to the Next.js application manually.

## 1. Update Project GraphQL Queries

Locate the queries for Project Listing and Project Details (e.g., `queries/project.graphql` or `pages/projects/[slug].tsx`).

**Update Fragment/Query:**

Note: The schema exposes `MediaLink` objects to ensure performance (avoiding N+1 queries). You must access the `mediaFile` property within the link.

```graphql
fragment ProjectMediaFields on Project {
  featuredMediaLink {
    mediaFile {
      url
      altText
      title
      width
      height
      variants {
        name
        url
        width
        height
      }
    }
  }
  galleryMediaLinks {
    mediaFile {
      url
      altText
      variants {
        name
        url
        width
        height
      }
    }
  }
  brochureMediaLink {
    mediaFile {
      url
      mimeType
    }
  }
}
```

Include this fragment in your `GetProject` and `GetProjects` queries.

## 2. Implement `<MediaImage />` Component

Create `components/MediaImage.tsx`:

```tsx
import Image, { ImageProps } from 'next/image';

interface MediaVariant {
  name: string;
  url: string;
  width?: number;
  height?: number;
}

interface MediaFile {
  url: string;
  altText?: string;
  title?: string;
  width?: number;
  height?: number;
  variants?: MediaVariant[];
}

interface Props extends Omit<ImageProps, 'src' | 'alt'> {
  media?: MediaFile | null;
  variantPreference?: 'thumb' | 'medium' | 'large' | 'original';
  fallbackSrc?: string;
}

export default function MediaImage({
  media,
  variantPreference = 'original',
  fallbackSrc = '/images/placeholder.jpg',
  className,
  ...props
}: Props) {
  if (!media) {
    return <Image src={fallbackSrc} alt="Placeholder" {...props} className={className} />;
  }

  // Helper to find variant
  const getUrl = () => {
    if (!media.variants || media.variants.length === 0) return media.url;

    const variant = media.variants.find(v => v.name === variantPreference);
    if (variant) return variant.url;

    // Fallback logic
    if (variantPreference === 'thumb') {
      return media.variants.find(v => v.name === 'medium')?.url || media.url;
    }

    return media.url;
  };

  const src = getUrl();
  const alt = media.altText || media.title || 'Project Image';

  return (
    <Image
      src={src}
      alt={alt}
      width={media.width || 800}
      height={media.height || 600}
      className={className}
      {...props}
    />
  );
}
```

## 3. SEO & Structured Data (Project Detail Page)

In your `pages/projects/[slug].tsx` (or generic Metadata generator):

```tsx
import { Metadata } from 'next';

export async function generateMetadata({ params }): Promise<Metadata> {
  const project = await fetchProject(params.slug);
  const featured = project.featuredMediaLink?.mediaFile;

  return {
    title: project.meta_title || project.name,
    description: project.meta_description,
    openGraph: {
      images: featured ? [featured.url] : [],
    },
    twitter: {
      card: 'summary_large_image',
      images: featured ? [featured.url] : [],
    }
  };
}

// JSON-LD
export default function ProjectPage({ project }) {
  const featured = project.featuredMediaLink?.mediaFile;

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Product', // Or Place/RealEstateListing
    name: project.name,
    description: project.description_short,
    image: featured?.url,
    url: \`https://aqarand.com/projects/\${project.slug}\`,
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      {/* ... rest of page */}
    </>
  );
}
```
