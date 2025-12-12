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
  - Uses `HasMedia` trait.
  - Role: `featured`.
  - Legacy image fields (`hero_image_url`, `gallery`) remain untouched for now.

### Current Integration Status
- **Projects**: Now have a **new optional featured image** powered by the Media Manager (Media module).
- **Legacy Flows**:
  - **Logo Upload**: Still uses the existing legacy implementation (direct file upload to storage).
  - **File Manager**: The existing `/admin/file-manager` section is untouched and remains available as a legacy tool.
  - **Project Gallery/Hero**: Existing fields in Step 4 (Media) are still using the legacy logic.

## Next Steps (Future Phases)
- Migrate Developer Logo to use Media Manager.
- Migrate Project Gallery and Hero Image to Media Manager.
- Replace `/admin/file-manager` with the new Media Manager page entirely.
- Expose Media via GraphQL/API for Frontend (Next.js).
