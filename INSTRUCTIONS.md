# Database Setup Instructions

 It seems the `segments` and `categories` tables are missing in your database, which causes the Internal Server Error.

 To fix this, please run the following command in your terminal:

 ```bash
 php artisan migrate
 ```

 This will execute the migration `database/migrations/2025_12_02_000000_phase_2_5_enhancements.php` which creates the missing tables.

 ## Caching Issues

 If you see incorrect translations (e.g. `admin.segments`), clear the cache:

 ```bash
 php artisan optimize:clear
 ```
