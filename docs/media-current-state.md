# Existing Media & File Features

## Upload Logo
*   **Controller**: `App\Http\Controllers\Admin\DeveloperController`
*   **Methods**: `store`, `update`
*   **Route**: `admin.developers.store`, `admin.developers.update` (Resource routes)
*   **Storage Path**: `storage/app/public/developers/` (via `public` disk)
*   **Database Column**: `logo_path` (Also checks `logo` and `logo_url` for legacy/fallback).
*   **Validation**: `mimes:jpeg,png,jpg,webp,gif,svg|max:2048`
*   **Logic**:
    *   Uses `Request::file('logo')->store('developers', 'public')`.
    *   Manually deletes old file if exists using `Storage::disk('public')->delete($oldLogo)`.
    *   Unsets `logo` key before saving to model to avoid column errors.

## Project Media Upload
*   **Controller**: `App\Http\Controllers\Admin\ProjectController`
*   **Method**: `uploadMedia` (Route: `projects.media.store`)
*   **Route**: `POST /admin/projects/{project}/media`
*   **Storage Path**: Likely `projects/{id}/gallery` (Need to confirm in ProjectController code).
*   **Database Columns**:
    *   `gallery`: JSON array of objects (`{path, name, alt, is_hero_candidate}`).
    *   `hero_image_url`: Relative path.
    *   `brochure_url`: Relative path.
    *   `map_polygon`: JSON.
    *   `video_urls`: JSON.
*   **Validation**: Custom per-method.

## Admin File Manager (/admin/file-manager)
*   **Route**: `/admin/file-manager` -> `admin.file_manager`
*   **Controller**: `App\Http\Controllers\Admin\MediaController@index`
*   **View**: `resources/views/admin/media/index.blade.php`
*   **Current Capabilities**:
    *   Lists `MediaFile` records (from `media_files` table).
    *   Filters by `search`, `context_type`, `variant_role`.
    *   Displays image preview, details (size, dim), context (Project/Unit), SEO Alt text.
    *   Actions: View (show), Delete (destroy).
    *   **Note**: This seems to be a read-only view of a "MediaFile" entity, which suggests there is already a `MediaFile` model and table, possibly part of a previous incomplete implementation or the one we are refactoring.
    *   It does *not* seem to provide a general file system browser (like a traditional file manager), but rather a database-driven list of media assets.

## Media-Related Dependencies
*   **intervention/image**: `^2.7` (Added to `composer.json` for later installation)
*   **league/flysystem-aws-s3-v3**: `^3.0` (Added to `composer.json` for later installation)

## Known Problems / Limitations
*   **Fragmentation**: Developer logos use raw file paths in `developers` table. Projects use `gallery` JSON column. A separate `media_files` table exists but isn't used everywhere (e.g. Developer logo).
*   **Manual Handling**: Controllers manually handle `store`, `delete` of files (e.g. `DeveloperController`).
*   **No Central Config**: Storage paths are hardcoded strings ('developers', 'projects').
*   **Dual Storage**: Need to support S3 migration without breaking local dev.

## Phase 0 Summary
*   **Dual Storage Concept**: Configured `local_work` (app/public) and `s3_media_local` (app/s3_media_local) disks in `config/filesystems.php`.
*   **Analysis Completed**: Existing media flows (Developer Logo, Project Media, Admin File Manager) have been documented.
*   **Environment Ready**: `composer.json` updated with `intervention/image` and `flysystem-s3` dependencies. `.env.example` updated with S3 placeholders and instructions for local simulation.
*   **Documentation**: Created `docs/media-current-state.md` to serve as the baseline for the upcoming Media Manager refactoring.

## Phase 1 – Media Schema & Models
*   **New Database Tables**:
    *   `media_files` (Augmented): Added `type`, `alt_text`, `title`, `caption`, `seo_slug`, `uploaded_by_id`, `is_system_asset`, `deleted_at`.
    *   `media_conversions`: Stores info about file variants (thumbs, resized).
    *   `media_links`: Replaces/Standardizes polymorphic associations (`model_type`, `model_id`, `role`).
    *   `media_tags` & `media_file_tag`: For categorizing media.
    *   `media_settings`: Single-row config table for defaults (disk, quality, sizes).
*   **New Models**: `MediaFile`, `MediaConversion`, `MediaLink`, `MediaTag`, `MediaSetting`.
*   **Traits**: `HasMedia` trait introduced for future domain model integration (e.g., Projects, Units).
*   **Status**:
    *   Database schema migrations created.
    *   Models implemented.
    *   Seeder for default settings created.
    *   **Existing functionality (Logo upload, Project gallery, etc.) REMAINS UNCHANGED and utilizes the old logic for now.** The new tables are currently parallel structures waiting for Phase 2 integration.

## Phase 2 – Core Media Services & Processing
*   **Services Implemented**:
    *   `MediaDiskResolver`: Resolves storage disks (Default, System, Tmp) based on `MediaSetting`.
    *   `MediaPathGenerator`: Generates consistent, SEO-friendly paths (e.g., `projects/egypt/cairo/slug/file.jpg`) for files and conversions.
    *   `MediaProcessingService`: Handles image resizing/optimization (using Intervention Image) and PDF processing (placeholder). It writes processed files to `media_tmp` disk first.
    *   `MediaStorageService`: Persists processed variants from `media_tmp` to the final disk (e.g., `s3_media_local`) and updates the database (`MediaFile` and `MediaConversion`).
*   **Jobs**:
    *   `ProcessMediaFileJob`: Queued job to orchestrate the pipeline (Load -> Process -> Store -> Update).
*   **Infrastructure**:
    *   Added `media_tmp` disk to `config/filesystems.php` for safe temporary processing.
*   **Status**:
    *   Core pipeline logic is ready.
    *   **Integration Pending**: These services are not yet wired into the Admin Upload UI. Existing upload forms (Developer Logo, Project Media) continue to work using legacy code.
