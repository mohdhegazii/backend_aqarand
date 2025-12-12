# Media Module - Current State

## Overview
The Media Module is being implemented in phases to replace legacy file handling.

## Phase 1: Database Schema
- Tables: `media_files`, `media_links`, `media_conversions`, `media_tags`, `media_settings`.
- Models: `MediaFile`, `MediaLink`, `MediaConversion`, `MediaTag`, `MediaSetting`.
- Trait: `HasMedia` (for attachable models).

## Phase 2: Core Services
- Disk Resolver: `MediaDiskResolver` (handles `local_work`, `s3_media_local`, etc.).
- Path Generator: `MediaPathGenerator` (SEO-friendly paths).
- Processing: `MediaProcessingService` (image optimization, resizing).
- Jobs: `ProcessMediaFileJob`.

## Phase 3: Admin APIs
- Endpoints: `POST /admin/media/upload`, `GET /admin/media`, `GET /admin/media/{id}`, `DELETE /admin/media/{id}`.
- Controller: `Admin\MediaApiController`.

## Phase 4: Admin Media Manager UI & Project Integration
- **New Admin Page**: `/admin/media-manager` (List, Filter, Upload, Delete).
- **Reusable Modal**: `<x-admin.media-manager-modal>` for selecting media in forms.
- **Form Component**: `<x-admin.media-picker>` for easy integration.
- **Project Integration**: Added "Featured Image (New)" field to Project Wizard (Step 1).

## Phase 5: Legacy Migration & Extended Integration
- **Project Gallery & Brochure**:
  - Implemented Project Wizard Step 4 (Media).
  - Supports multiple image selection for Gallery (role: `gallery`) via Media Manager.
  - Supports PDF selection for Brochure (role: `brochure`).
- **Logo Migration**:
  - `Developer` model now supports `HasMedia` (role: `logo`).
  - Admin Developer Form updated with Media Picker for Logo.
  - Legacy file input retained (hidden) for backward compatibility.
  - `Developer::resolveLogo()` prioritizes Media Manager links.
- **File Manager Refactor**:
  - Legacy `/admin/file-manager` marked as deprecated with banner.
  - Now strictly reads from `media_files` table (read-only view for legacy references).
- **Default Enforcement**:
  - Media Manager is now the primary upload mechanism for Projects and Developers.

### Current Integration Status
- **Projects**: Fully integrated (Featured, Gallery, Brochure) with Media Manager.
- **Developers**: Logo integrated with Media Manager (Legacy fallback available).
- **Legacy Flows**:
  - `/admin/file-manager`: Deprecated.

## Phase 6 – GraphQL & Next.js Plan
- **GraphQL Library**: `nuwave/lighthouse` is used to expose the API.
- **Schema Location**: `graphql/schema.graphql`.
- **Media Types**: `MediaFile` and `MediaVariant` exposed with `url` resolving to signed/public URLs.
- **Project Integration**: `featuredMedia`, `galleryMedia`, `brochure` fields added to `Project` type.

## Next Steps (Future Phases)
- SEO + GEO structured data (Frontend implementation).
