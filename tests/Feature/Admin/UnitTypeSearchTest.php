<?php

namespace Tests\Feature\Admin;

use App\Models\PropertyType;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTypeSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_keeps_default_active_filter()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $propertyType = PropertyType::create([
            'name_en' => 'Apartment',
            'slug' => 'apartment',
            'is_active' => true,
        ]);

        $active = UnitType::create([
            'property_type_id' => $propertyType->id,
            'name' => 'Studio',
            'code' => 'STU',
            'is_active' => true,
        ]);

        $inactive = UnitType::create([
            'property_type_id' => $propertyType->id,
            'name' => 'Dormant',
            'code' => 'STU2',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-types.index', [
            'locale' => 'en',
            'search' => 'STU',
        ]));

        $response->assertStatus(200);
        $unitTypes = $response->viewData('unitTypes');

        $this->assertTrue($unitTypes->contains($active));
        $this->assertFalse($unitTypes->contains($inactive));
        $this->assertEquals(1, $unitTypes->total());
    }
}
