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

    public function test_region_search_respects_filters_and_country()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $countryA = Country::factory()->create(['name_en' => 'Country A', 'code' => 'CA']);
        $countryB = Country::factory()->create(['name_en' => 'Country B', 'code' => 'CB']);

        $matchingRegion = Region::factory()->create([
            'country_id' => $countryA->id,
            'name_en' => 'Alpha Region',
            'name_local' => 'منطقة ألفا',
            'slug' => 'alpha-region',
            'is_active' => true,
        ]);

        Region::factory()->create([
            'country_id' => $countryA->id,
            'name_en' => 'Alpha Inactive',
            'name_local' => 'ألفا غير نشطة',
            'slug' => 'alpha-inactive',
            'is_active' => false,
        ]);

        Region::factory()->create([
            'country_id' => $countryB->id,
            'name_en' => 'Alpha Other',
            'name_local' => 'ألفا أخرى',
            'slug' => 'alpha-other',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.regions.index', [
            'locale' => 'en',
            'search' => 'Alpha',
            'country_id' => $countryA->id,
        ]));

        $response->assertStatus(200);
        $regions = $response->viewData('regions');
        $regionIds = collect($regions->items())->pluck('id');

        $this->assertTrue($regionIds->contains($matchingRegion->id));
        $this->assertCount(1, $regionIds);
    }
}
