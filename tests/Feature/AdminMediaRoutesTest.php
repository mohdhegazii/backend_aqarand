<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminMediaRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user if not exists or mock auth
        // Since I cannot run seeders easily, I'll rely on route existence and minimal response check if possible
    }

    public function test_media_manager_ui_route_exists()
    {
        // Check if admin.media-manager.index exists in route list
        $this->assertTrue(Route::has('admin.media-manager.index'));
        $this->assertTrue(Route::has('localized.admin.media-manager.index'));
    }

    public function test_media_api_routes_exist()
    {
        // Check API routes
        $this->assertTrue(Route::has('admin.media.index'));
        $this->assertTrue(Route::has('admin.media.upload'));
        $this->assertTrue(Route::has('admin.media.destroy'));
    }
}
