<?php

namespace Tests\Feature\GraphQL;

use App\Models\MediaFile;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MediaGraphqlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that project media can be queried via GraphQL.
     */
    public function test_can_query_project_media()
    {
        // 1. Create a Project
        $project = Project::factory()->create([
            'name' => 'Test Project',
            'slug' => 'test-project',
        ]);

        // 2. Create Media Files
        $featured = MediaFile::factory()->create([
            'type' => 'image',
            'alt_text' => 'Featured Image',
            'path' => 'projects/featured.jpg',
            'disk' => 's3_media_local'
        ]);

        $gallery1 = MediaFile::factory()->create(['type' => 'image', 'path' => 'projects/g1.jpg', 'disk' => 's3_media_local']);
        $gallery2 = MediaFile::factory()->create(['type' => 'image', 'path' => 'projects/g2.jpg', 'disk' => 's3_media_local']);

        $brochure = MediaFile::factory()->create([
            'type' => 'pdf',
            'path' => 'projects/brochure.pdf',
            'disk' => 's3_media_local'
        ]);

        // 3. Attach Media
        $project->attachMedia($featured, 'featured');
        $project->attachMedia($gallery1, 'gallery', 0);
        $project->attachMedia($gallery2, 'gallery', 1);
        $project->attachMedia($brochure, 'brochure');

        // 4. Query GraphQL
        // Updated query structure to use MediaLink
        $query = '
            query ProjectMedia($id: ID!) {
                project(id: $id) {
                    name
                    featuredMediaLink {
                        mediaFile {
                            url
                            altText
                        }
                    }
                    galleryMediaLinks {
                        mediaFile {
                            url
                        }
                    }
                    brochureMediaLink {
                        mediaFile {
                            url
                            type
                        }
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => ['id' => $project->id],
        ]);

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertJsonPath('data.project.featuredMediaLink.mediaFile.altText', 'Featured Image');
        $response->assertJsonCount(2, 'data.project.galleryMediaLinks');
        $response->assertJsonPath('data.project.brochureMediaLink.mediaFile.type', 'pdf');
    }

    /**
     * Test Media Library Query.
     */
    public function test_can_query_media_library()
    {
        MediaFile::factory()->count(3)->create(['type' => 'image']);

        $query = '
            query {
                mediaLibrary(page: 1, perPage: 10) {
                    data {
                        id
                        url
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.mediaLibrary.paginatorInfo.total', 3);
    }
}
