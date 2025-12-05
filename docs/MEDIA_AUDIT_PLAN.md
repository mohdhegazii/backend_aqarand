# Media Layer Audit & Technical Plan

## 1. Audit Report

The current media system is in a transitional state. It possesses a solid foundation with the `MediaFile` table and `MediaProcessor` service, which supports location-based storage and WebP conversion. However, adoption is inconsistent across entities, and critical features like document handling (PDFs), video support, and private file security are missing.

### Key Findings
*   **Inconsistent Storage:** Entities use a mix of direct URL columns (`hero_image_url`) and the `media_files` table.
*   **Missing Features:** No support for PDFs, attachments, or sorting.
*   **Security:** Private files (contracts) have no secure storage mechanism.
*   **Validation:** Basic size validation exists, but no dimension or strict mime-type checks.

## 2. Architecture

### Directory Structure
```
storage/app/
├── public/
│   ├── media/
│   │   ├── developers/
│   │   │   └── {slug}/logo/
│   │   ├── projects/
│   │   │   └── {country}/{region}/{city}/{project}/
│   │   │       ├── gallery/
│   │   │       ├── floorplans/
│   │   │       └── brochures/
│   │   ├── units/
│   │       └── {country}/{region}/{city}/{project}/{unit}/gallery/
└── private/ (Secure)
    ├── contracts/
    └── attachments/
```

### Database Enhancements
*   Added `collection_name` to group files (e.g., 'gallery', 'floorplans').
*   Added `sort_order` for gallery management.
*   Added `is_private` flag for secure serving.

## 3. Implementation Plan

### Phase 1: Core Foundation (Completed)
*   [x] Create migration for schema updates (`collection_name`, `sort_order`, `is_private`).
*   [x] Implement `HasMedia` trait for standardized model access.
*   [x] Upgrade `MediaProcessor` to handle Documents (PDFs) and Private storage.

### Phase 2: Entity Integration (Next Steps)
1.  **Developers:**
    *   Migrate `logo_path` to `MediaFile` (Collection: `logo`).
    *   Update Controller to use `MediaProcessor`.
2.  **Projects:**
    *   Deprecate `hero_image_url` and `gallery` JSON.
    *   Use `MediaFile` with collections: `hero`, `gallery`, `floorplans`, `brochures`.
3.  **Units:**
    *   Implement secure upload for Contracts (Private).
4.  **Admin UI:**
    *   Build a reusable Blade component for Media Management (Grid, Sort, Delete).

## 4. Usage Examples

### Uploading a Private Contract
```php
$processor->processUpload(
    $request->file('contract'),
    $unit,
    'contracts' // This triggers 'private' storage logic
);
```

### Retrieving Gallery
```php
$project->getMedia('gallery');
```

### Retrieving Hero Image
```php
$project->getFirstMediaUrl('hero');
```
