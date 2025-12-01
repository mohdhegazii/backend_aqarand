<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Listing;
use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ModelsRelationshipsTest extends TestCase
{
    // We cannot use RefreshDatabase because we shouldn't migrate/reset the DB structure.
    // Instead we will rely on manual cleanup or non-destructive reads if data existed.
    // Since the DB might be empty, we can try to insert dummy data and remove it.

    public function test_project_relationships()
    {
        // Check if we can instantiate and access relations (even if empty)
        $project = new Project();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $project->units);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $project->amenities);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $project->propertyModels);

        // We can't easily test DB queries without a running DB connection and data,
        // but this verifies the method definitions exist and return relations.
    }

    public function test_listing_relationships()
    {
        $listing = new Listing();
        // Since belongsTo returns a Relation object, we can check that.
        // However, accessing $listing->unit triggers a query if not loaded.
        // We can check the method instead.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $listing->unit());
    }

    public function test_amenity_relationships()
    {
        $amenity = new Amenity();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $amenity->projects);
    }
}
