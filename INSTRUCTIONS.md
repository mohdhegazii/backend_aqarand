# Instructions for User

To resolve the issues with Project Creation (Internal Server Error) and update the Hero Image behavior, please perform the following step:

1.  **Run Migrations:**
    The system has detected that the `projects` table is missing required location columns (`country_id`, `region_id`, etc.), causing the "Column not found" error.

    A fix migration file has been created at:
    `database/migrations/2027_01_06_000000_add_location_columns_to_projects_fix.php`

    Please run the following command in your terminal to apply the fix:
    ```bash
    php artisan migrate
    ```

    *If you are in a production environment, ensure you have a backup before running migrations.*

## Changes Applied:

1.  **Project Creation Error Fix:** Created a migration to add missing location columns to the `projects` table.
2.  **Hero Image Selection:**
    *   **Removed** the mandatory "Hero Image" file upload on Project Creation.
    *   **Logic Change:** The first image uploaded to the Gallery during creation will automatically be set as the Hero Image.
    *   **Edit Screen:** Added a UI to select a Hero Image from the existing Gallery images via radio buttons.
3.  **Language Tabs Fix:** Added Alpine.js to the admin layout (`app.blade.php`) to ensure the English/Arabic tabs switch correctly.
