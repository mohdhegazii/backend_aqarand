<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectBoundaryNormalizationTest extends TestCase
{
    // Note: We cannot run migrations in this environment, but we can test the model logic
    // assuming the columns exist or mocking attributes if possible.
    // Actually, Unit tests for Models are better here since we don't have a DB.
    // But let's try to mock the model behavior or just use what we have.
    // If we can't migrate, we can't really save to DB.
    // But we can test the accessor and mutator logic on an instance.

    public function test_boundary_geojson_accessor_prefers_geojson_column()
    {
        $project = new Project();
        $geojson = ['type' => 'Polygon', 'coordinates' => []];
        $project->project_boundary_geojson = $geojson;
        $project->map_polygon = ['type' => 'OldPolygon', 'coordinates' => []];

        $this->assertEquals($geojson, $project->boundary_geojson);
    }

    public function test_boundary_geojson_accessor_fallbacks_to_map_polygon()
    {
        $project = new Project();
        $project->project_boundary_geojson = null;
        $mapPolygon = ['type' => 'OldPolygon', 'coordinates' => []];
        $project->map_polygon = $mapPolygon;

        $this->assertEquals($mapPolygon, $project->boundary_geojson);
    }

    public function test_boundary_geojson_mutator_syncs_both_columns()
    {
        $project = new Project();
        $geojson = ['type' => 'Polygon', 'coordinates' => [[[0,0],[0,1],[1,1],[0,0]]]];

        $project->boundary_geojson = $geojson;

        // Access raw attributes to verify they are set as JSON strings (since mutator does encoding)
        // Note: In memory, before save, attributes might be stored as set.
        // The mutator sets:
        // $this->attributes['project_boundary_geojson'] = json_encode($value);
        // $this->attributes['map_polygon'] = json_encode($value);

        $this->assertEquals(json_encode($geojson), $project->getAttributes()['project_boundary_geojson']);
        $this->assertEquals(json_encode($geojson), $project->getAttributes()['map_polygon']);

        // Also verify that accessing them back (via casts) works if we were to reload or simulate
        // Accessing the casted attributes directly on a new model instance without saving is tricky
        // because casts usually apply when retrieving from DB.
        // But let's check the raw attributes.
    }

    public function test_boundary_geojson_mutator_handles_null()
    {
        $project = new Project();
        $project->boundary_geojson = null;

        $this->assertNull($project->getAttributes()['project_boundary_geojson']);
        $this->assertNull($project->getAttributes()['map_polygon']);
    }
}
