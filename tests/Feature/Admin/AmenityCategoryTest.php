<?php

namespace Tests\Feature\Admin;

use App\Models\AmenityCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmenityCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming we need to run migrations. Since I cannot run php binary, I rely on RefreshDatabase.
    }

    public function test_admin_can_view_amenity_category_edit_page_with_locale_prefix()
    {
        // 1. Create Admin
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // 2. Create Amenity Category
        $category = AmenityCategory::factory()->create([
            'name_en' => 'Test Category',
            'name_ar' => 'تصنيف تجريبي',
            'slug' => 'test-category',
        ]);

        // 3. Act as Admin and visit edit page
        $response = $this->actingAs($admin)
                         ->get("/en/admin/amenity-categories/{$category->id}/edit");

        // 4. Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.amenity_categories.edit');
        $response->assertSee('Test Category');
    }

    public function test_admin_can_update_amenity_category()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $category = AmenityCategory::factory()->create();

        $response = $this->actingAs($admin)
                         ->put("/en/admin/amenity-categories/{$category->id}", [
                             'name_en' => 'Updated Name',
                             'name_ar' => 'Updated Name AR',
                             'is_active' => '1',
                             'sort_order' => 10,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('amenity_categories', [
            'id' => $category->id,
            'name_en' => 'Updated Name',
        ]);
    }

    public function test_search_defaults_to_active_categories()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $active = AmenityCategory::create([
            'name_en' => 'Active Gym',
            'name_ar' => 'نشط',
            'slug' => 'active-gym',
            'is_active' => true,
        ]);

        $inactive = AmenityCategory::create([
            'name_en' => 'Inactive Gym',
            'name_ar' => 'غير نشط',
            'slug' => 'inactive-gym',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.amenity-categories.index', [
            'locale' => 'en',
            'search' => 'Gym',
        ]));

        $response->assertStatus(200);

        $categories = $response->viewData('categories');

        $this->assertTrue($categories->contains($active));
        $this->assertFalse($categories->contains($inactive));
        $this->assertEquals(1, $categories->total());
    }
}
