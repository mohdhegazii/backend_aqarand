# backend_aqarAnd

Skeleton repository for the **Aqar-and Platform** backend (Phase 1: Real Estate Listing Website / Web App).

> ⚠️ This is **not** a full Laravel install.  
> You will create a fresh Laravel project on your machine, then copy/merge this folder structure and import the SQL schema.

---

## 1. Goal of this repo

- Backend name on XAMPP: `backend_aqarAnd`
- Same name will be used as GitHub repository.
- Contains:
  - `database/sql/schema.sql` → **core MySQL schema** for: locations, developers, projects, property models, unit types, units, listings, amenities.
  - This schema is designed to be:
    - Clean, normalized
    - Ready for Laravel Eloquent models
    - Scalable to SaaS later (offices, agents, CRM, etc.)
    - Optimized for search/filter performance
    - AI- and SEO-friendly

Phase 1 focuses ONLY on:

- Projects
- Property models
- Units
- Listings
- Locations
- Amenities (basic)
- No CRM / Offices / Teams / Commissions yet (they'll be added in Phase 2+).

---

## 2. How to use this with Laravel (step-by-step)

### Step 1 — Create Laravel project

```bash
cd C:\xampp\htdocs
composer create-project laravel/laravel backend_aqarAnd
```

> Make sure the folder name is exactly: `backend_aqarAnd`

### Step 2 — Configure `.env`

Inside `backend_aqarAnd/.env`:

```env
APP_NAME="Aqar-and Backend"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost/backend_aqarAnd/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=backend_aqarand
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Then create the database:

```sql
CREATE DATABASE backend_aqarand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3 — Import the schema

Use phpMyAdmin or MySQL CLI to import:

- `database/sql/schema.sql`

Example (CLI):

```bash
mysql -u your_mysql_user -p backend_aqarand < database/sql/schema.sql
```

### Step 4 — Init Git & push to GitHub

From inside `backend_aqarAnd`:

```bash
git init
git add .
git commit -m "chore: init Aqar-and backend schema"
git branch -M main
git remote add origin git@github.com:YOUR_USERNAME/backend_aqarAnd.git
git push -u origin main
```

---

## 3. Next steps (for your AI Agent workflow)

Once this repo is on GitHub and linked to your AI Agent:

1. **Generate Laravel Eloquent models** for:
   - Country, Region, City, District
   - Developer
   - PropertyType, UnitType
   - Amenity, ProjectAmenity
   - Project
   - PropertyModel
   - Unit
   - Listing

2. **Generate admin CRUD (dashboard) controllers + Blade views** for:
   - Developers
   - Projects
   - Property Models
   - Units
   - Listings
   - Amenities
   - Locations

3. **Add authentication** (Laravel Breeze or Laravel UI).

4. **Later:** expose a **GraphQL API** (via Lighthouse) for:
   - `projects`
   - `propertyModels`
   - `units`
   - `listings`
   - `searchListings`

I will give you precise prompts for AI Agents in the next steps, one task at a time.

---

## 4. Notes

- This schema is “Phase 1 only” → ready to extend later with:
  - offices, teams, agents
  - leads, CRM
  - deals, commissions
  - notifications
  - contracts & invoices
- All tables are InnoDB, UTF8MB4, and optimized for MySQL on XAMPP.

## 5. Phase 2 Implementation (Admin & Localization)

### Setup Instructions
1.  **Install Dependencies**:
    ```bash
    composer install
    npm install
    npm run build
    ```
2.  **Install Breeze**:
    ```bash
    php artisan breeze:install blade
    ```
    *Note: This generates the authentication views and controllers.*
3.  **Run Migrations**:
    ```bash
    php artisan migrate
    ```
4.  **Seed Database**:
    ```bash
    php artisan db:seed
    ```
    *This creates the admin user.*

### Admin Access
- **URL**: `/admin`
- **Email**: `admin@aqarand.test`
- **Password**: `password`

### Localization
- The admin panel supports **English (LTR)** and **Arabic (RTL)**.
- Use the language switcher in the top navigation bar to toggle languages.
- The interface automatically adjusts text direction and alignment.

### implemented Modules (CRUD)
- **Locations**:
    - Countries
    - Regions
    - Cities
    - Districts
- **Lookups**:
    - Property Types
    - Unit Types
    - Amenities

### Admin Routes
- `/admin` (Dashboard)
- `/admin/countries`
- `/admin/regions`
- `/admin/cities`
- `/admin/districts`
- `/admin/property-types`
- `/admin/unit-types`
- `/admin/amenities`
