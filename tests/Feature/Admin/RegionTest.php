<?php

namespace Tests\Feature\Admin;

use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_region_edit_page()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::factory()->create();
        $region = Region::factory()->create([
            'country_id' => $country->id,
            'name_en' => 'Cairo',
            'name_local' => 'القاهرة',
            'slug' => 'cairo',
        ]);

        $response = $this->actingAs($admin)
                         ->get("/en/admin/regions/{$region->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.regions.edit');
        $response->assertSee('Cairo');
    }
}
