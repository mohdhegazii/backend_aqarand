-- Aqar-and Platform - Core Real Estate Schema (Phase 1)
-- MySQL 8+ recommended
-- This schema covers: locations, developers, projects, property models, unit types, units, listings, amenities.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- 1) Locations (Global-ready, Egypt-first)
-- =========================

CREATE TABLE countries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE, -- ISO alpha-2 or alpha-3
    name_en VARCHAR(100) NOT NULL,
    name_local VARCHAR(100) NOT NULL,
    boundary_polygon JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE regions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_local VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    boundary_polygon JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_regions_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    UNIQUE KEY uq_regions_country_slug (country_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_local VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    boundary_polygon JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_cities_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cities_region_slug (region_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE districts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_local VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    boundary_polygon JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_districts_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    UNIQUE KEY uq_districts_city_slug (city_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 2) Developers
-- =========================

CREATE TABLE developers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    description TEXT NULL,
    logo_url VARCHAR(255) NULL,
    website_url VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_developers_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 3) Property Types & Unit Types
-- =========================

CREATE TABLE property_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_local VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    category ENUM('residential','commercial','administrative','medical','mixed','other') NOT NULL DEFAULT 'residential',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_property_types_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE unit_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_type_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(150) NULL,
    name_local VARCHAR(150) NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NULL,
    description TEXT NULL,
    is_core_type TINYINT(1) NOT NULL DEFAULT 0,
    requires_land_area TINYINT(1) NOT NULL DEFAULT 0,
    requires_built_up_area TINYINT(1) NOT NULL DEFAULT 1,
    requires_garden_area TINYINT(1) NOT NULL DEFAULT 0,
    requires_roof_area TINYINT(1) NOT NULL DEFAULT 0,
    requires_indoor_area TINYINT(1) NOT NULL DEFAULT 0,
    requires_outdoor_area TINYINT(1) NOT NULL DEFAULT 0,
    additional_rules JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_unit_types_property_type FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4) Amenities
-- =========================

CREATE TABLE amenities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(120) NOT NULL,
    name_local VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    icon_class VARCHAR(120) NULL,
    amenity_type ENUM('project','unit','both') NOT NULL DEFAULT 'project',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_amenities_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- NOTE: 'projects' table is defined below; this FK will work if run in an engine that ignores order, 
-- otherwise you can move this block after projects creation.

-- =========================
-- 5) Projects
-- =========================

CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    developer_id BIGINT UNSIGNED NULL,
    country_id BIGINT UNSIGNED NOT NULL,
    region_id BIGINT UNSIGNED NOT NULL,
    city_id BIGINT UNSIGNED NOT NULL,
    district_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    tagline VARCHAR(255) NULL,
    description_long TEXT NULL,
    status ENUM('planned','under_construction','delivered') NOT NULL DEFAULT 'planned',
    delivery_year SMALLINT NULL,
    total_area DECIMAL(12,2) NULL,
    built_up_ratio DECIMAL(5,2) NULL,
    total_units INT NULL,
    min_price DECIMAL(14,2) NULL,
    max_price DECIMAL(14,2) NULL,
    min_bua DECIMAL(10,2) NULL,
    max_bua DECIMAL(10,2) NULL,
    lat DECIMAL(10,7) NULL,
    lng DECIMAL(10,7) NULL,
    address_text VARCHAR(255) NULL,
    brochure_url VARCHAR(255) NULL,
    hero_image_url VARCHAR(255) NULL,
    gallery JSON NULL,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    boundary_polygon JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_projects_developer FOREIGN KEY (developer_id) REFERENCES developers(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projects_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projects_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projects_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_projects_slug (slug),
    KEY idx_projects_location (country_id, region_id, city_id, district_id),
    KEY idx_projects_status (status, delivery_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-create project_amenity now that projects exists (if needed)
DROP TABLE IF EXISTS project_amenity;
CREATE TABLE project_amenity (
    project_id BIGINT UNSIGNED NOT NULL,
    amenity_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, amenity_id),
    CONSTRAINT fk_project_amenity_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_amenity_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 6) Property Models (Unit Models inside Projects)
-- =========================

CREATE TABLE property_models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    unit_type_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NULL,
    description TEXT NULL,
    bedrooms TINYINT UNSIGNED NULL,
    bathrooms TINYINT UNSIGNED NULL,
    min_bua DECIMAL(10,2) NULL,
    max_bua DECIMAL(10,2) NULL,
    min_land_area DECIMAL(10,2) NULL,
    max_land_area DECIMAL(10,2) NULL,
    min_price DECIMAL(14,2) NULL,
    max_price DECIMAL(14,2) NULL,
    floorplan_2d_url VARCHAR(255) NULL,
    floorplan_3d_url VARCHAR(255) NULL,
    gallery JSON NULL,
    seo_slug VARCHAR(220) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_property_models_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_property_models_unit_type FOREIGN KEY (unit_type_id) REFERENCES unit_types(id) ON DELETE RESTRICT,
    KEY idx_property_models_project (project_id),
    KEY idx_property_models_type (unit_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 7) Units (physical units)
-- =========================

CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    property_model_id BIGINT UNSIGNED NULL,
    unit_type_id BIGINT UNSIGNED NOT NULL,
    unit_number VARCHAR(100) NULL,
    floor_label VARCHAR(50) NULL,
    delivery_year SMALLINT NULL,
    unit_status ENUM('available','reserved','sold','rented') NOT NULL DEFAULT 'available',
    bedrooms TINYINT UNSIGNED NULL,
    bathrooms TINYINT UNSIGNED NULL,
    finishing_type VARCHAR(100) NULL,
    orientation VARCHAR(50) NULL,
    view_label VARCHAR(100) NULL,
    is_corner TINYINT(1) NOT NULL DEFAULT 0,
    is_furnished TINYINT(1) NOT NULL DEFAULT 0,
    equipment JSON NULL,
    built_up_area DECIMAL(10,2) NULL,
    land_area DECIMAL(10,2) NULL,
    garden_area DECIMAL(10,2) NULL,
    outdoor_area DECIMAL(10,2) NULL,
    roof_area DECIMAL(10,2) NULL,
    price DECIMAL(14,2) NOT NULL,
    currency_code CHAR(3) NOT NULL DEFAULT 'EGP',
    price_per_sqm DECIMAL(14,2) NULL,
    payment_type ENUM('cash','installments','both') NOT NULL DEFAULT 'cash',
    payment_summary TEXT NULL,
    media JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_units_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_units_property_model FOREIGN KEY (property_model_id) REFERENCES property_models(id) ON DELETE SET NULL,
    CONSTRAINT fk_units_unit_type FOREIGN KEY (unit_type_id) REFERENCES unit_types(id) ON DELETE RESTRICT,
    KEY idx_units_project (project_id),
    KEY idx_units_model (property_model_id),
    KEY idx_units_status (unit_status),
    KEY idx_units_price (price),
    KEY idx_units_bua (built_up_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 8) Listings (what appears on the website)
-- =========================

CREATE TABLE listings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    listing_type ENUM('primary','resale','rental') NOT NULL DEFAULT 'primary',
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description VARCHAR(500) NULL,
    status ENUM('draft','pending','published','hidden','sold','rented','expired') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL DEFAULT NULL,
    seo_title VARCHAR(255) NULL,
    seo_description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_listings_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    UNIQUE KEY uq_listings_slug (slug),
    KEY idx_listings_status (status),
    KEY idx_listings_type (listing_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
