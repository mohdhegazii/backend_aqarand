<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmenitySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_defaults_to_active_records()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $activeAmenity = Amenity::create([
            'name_en' => 'Gym',
            'name_local' => 'صالة رياضية',
            'slug' => 'gym',
            'amenity_type' => 'unit',
            'is_active' => true,
        ]);

        $inactiveAmenity = Amenity::create([
            'name_en' => 'Closed Gym',
            'name_local' => 'Gym Arabic',
            'slug' => 'closed-gym',
            'amenity_type' => 'unit',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.amenities.index', ['locale' => 'en', 'search' => 'Gym']));

        $response->assertStatus(200);

        $amenities = $response->viewData('amenities');

        $this->assertTrue($amenities->contains($activeAmenity));
        $this->assertFalse($amenities->contains($inactiveAmenity));
        $this->assertEquals(1, $amenities->total());
    }

    public function test_empty_filter_parameter_still_limits_to_active_records()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $activeAmenity = Amenity::create([
            'name_en' => 'Pool',
            'name_local' => 'حمام سباحة',
            'slug' => 'pool',
            'amenity_type' => 'unit',
            'is_active' => true,
        ]);

        $inactiveAmenity = Amenity::create([
            'name_en' => 'Closed Pool',
            'name_local' => 'حمام سباحة مغلق',
            'slug' => 'closed-pool',
            'amenity_type' => 'unit',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.amenities.index', ['locale' => 'en', 'filter' => '', 'search' => 'Pool']));

        $response->assertStatus(200);

        $amenities = $response->viewData('amenities');

        $this->assertTrue($amenities->contains($activeAmenity));
        $this->assertFalse($amenities->contains($inactiveAmenity));
        $this->assertEquals(1, $amenities->total());
    }
}
