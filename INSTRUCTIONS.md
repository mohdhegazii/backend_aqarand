# Media Management System Setup

To enable the new Media Management system, please follow these steps:

1.  **Install Image Processing Library**:
    This system requires `intervention/image` for image manipulation. Run:
    ```bash
    composer require intervention/image
    ```
    *Note: Depending on your PHP version and Intervention Image version, you might need to publish configuration. For v3, it usually works out of the box.*

2.  **Run Migrations**:
    New tables (`media_files`, `blog_posts`, `property_models`) and columns have been added. Also includes a fix for `AmenityCategory` sort order. Run:
    ```bash
    php artisan migrate
    ```

3.  **Link Storage**:
    Ensure the public disk is linked to the `public` directory:
    ```bash
    php artisan storage:link
    ```

4.  **Clear Caches**:
    To ensure new routes and config are loaded:
    ```bash
    php artisan optimize:clear
    ```
