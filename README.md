# Aqar-and Backend

## Phase 1 & 2 Completed

This repository contains the backend for the Aqar-and real estate listing platform.

### Phase 1: Database Schema & Models
- Database schema imported (`database/sql/schema.sql`).
- Eloquent Models created in `app/Models`.
- Relationships and fillable fields defined.

### Phase 2: Auth, Admin, Localization
- Authentication via Laravel Breeze (Custom implementation for Admin).
- Admin Dashboard at `/admin`.
- Localization (EN/AR) with RTL support.
- CRUD Operations for:
  - Locations: Countries, Regions, Cities, Districts.
  - Lookups: Property Types, Unit Types, Amenities.
  - Developers.

### How to Access

1. **Login as Admin:**
   - Go to `/login`.
   - Credentials: `admin@aqarand.test` / `password`.
   - You will be redirected to `/admin` upon successful login.

2. **Admin Dashboard:**
   - Access `/admin`.
   - Use the sidebar to manage content.
   - Use the Language Switcher in the top bar to toggle between English and Arabic (RTL).

3. **Switch Language:**
   - Click "العربية" or "English" in the top navigation bar of the admin panel.

### Development

- **Run Migrations:**
    After pulling new code, run:
    ```bash
    php artisan migrate
    ```
    *Note: Do NOT drop or recreate existing tables. This will only add missing columns.*

- **Seed Database:** `php artisan db:seed` (Creates the admin user)

### Configuration

- **Session Lifetime:**
  Admin sessions expire after 24 hours. Ensure your `.env` has:
  ```
  SESSION_LIFETIME=1440
  ```

### Entities Implemented (Phase 2)
- **Countries** (Added optional lat/lng)
- **Regions** (Added optional lat/lng)
- **Cities** (Added optional lat/lng)
- **Districts** (Added optional lat/lng)
- **Property Types** (Added icon_class and image_url)
- **Unit Types** (Added icon_class and image_url)
- **Amenities** (Added image_url)
- **Developers**

### Business Logic Alignment (Phase 1+2 Audit)
- **Lookups:** Slug is no longer required or relied upon for lookup entities (Property Types, Amenities, etc.).
- **Locations:** Hierarchical integrity enforced (Country -> Region -> City -> District).
- **Icons/Images:** Lookup entities now support icon classes and image URLs.
- **Coordinates:** All location entities support latitude and longitude storage.

### Phase 2.5 Enhancements (Completed)

This phase introduced UX improvements and additional features for the admin panel.

#### 1. Locations UX
- **Map Picker:** Integrated Leaflet (OpenStreetMap) for all location entities (Country, Region, City, District).
- **FlyTo Behavior:** When selecting a parent location (e.g., choosing a City for a District), the map automatically flies to the parent's coordinates.
- **ISO Code:** Clarified Country Code input as ISO code (e.g., EG, SA) in UI.

#### 2. Segments & Categories
- Implemented `Segments` and `Categories` taxonomies.
- CRUD screens for managing these entities.
- Amenities now link to Categories.

#### 3. Developer Enhancements
- **Bilingual Support:** Developers now have EN and AR name and description fields.
- **SEO Meta Box:** A Yoast-like SEO meta box for managing Meta Title, Description, and Focus Keyphrase.
- **Logo Upload:** Ability to upload logos locally.

#### 4. Bulk Actions & Soft Deletes
- **Bulk Actions:** All admin lists now support bulk "Activate" and "Deactivate".
- **Soft Delete Behavior:** Delete actions now perform a "Soft Delete" (setting `is_active = 0`) instead of removing the record from the database.

#### 5. Lookups & Validation
- **Images:** Added image upload support for Property Types, Unit Types, Amenities, and Categories.
- **Unit Types:** Enforced validation logic (e.g., "Requires Built-up Area" is mandatory).
- **Bootstrap Icons:** Added clear help text and preview for icon classes.

### Phase 2.6 Updates (Current)

- **Routing & Auth:** Improved login redirection and added public placeholder.
- **Database:** Added missing `is_active` and `image_path` columns via migration.
- **Sessions:** Increased admin session lifetime to 24 hours.
- **Developers:** Split Create/Edit form into English/Arabic tabs with per-language SEO settings.
- **SEO Plugin:** Added traffic-light indicators for SEO rules (Title length, Description length, Keyphrase).
- **Migrations:** Fixed seo_meta migration (create or extend table safely) and added lat/lng columns to countries for location map support. Remember to run php artisan migrate after pulling.
