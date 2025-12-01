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
- **Countries**
- **Regions**
- **Cities**
- **Districts**
- **Property Types**
- **Unit Types**
- **Amenities**
- **Developers**
