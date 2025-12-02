<?php

namespace Tests\Feature\Admin;

use App\Models\AmenityCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmenityCategoryEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_amenity_category_edit_page_with_locale_prefix(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $category = AmenityCategory::create([
            'name_en' => 'Swimming Pool',
            'name_ar' => 'حمام سباحة',
            'slug' => 'swimming-pool',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.amenity-categories.edit', [
                'locale' => 'en',
                'amenityCategory' => $category->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertViewIs('admin.amenity_categories.edit');
        $response->assertViewHas('amenityCategory', function (AmenityCategory $boundCategory) use ($category) {
            return $boundCategory->is($category);
        });
    }
}
