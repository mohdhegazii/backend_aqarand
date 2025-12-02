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

- **Run Migrations:** `php artisan migrate`
- **Seed Database:** `php artisan db:seed` (Creates the admin user)

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
