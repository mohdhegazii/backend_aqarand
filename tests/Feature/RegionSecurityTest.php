<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_injection_in_boundary_geojson()
    {
        // Arrange: Create an admin user and a country
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create([
            'name_en' => 'Test Country',
            'name_local' => 'Test Country Local',
            'is_active' => true,
            'code' => 'TC',
            'phone_code' => '123'
        ]);

        // This JSON is valid, but contains a SQL injection payload if not escaped.
        // It tries to close the string passed to ST_GeomFromGeoJSON and add a tautology.
        // Or simply cause a syntax error to prove the injection.
        // Payload: {"key": "') --"}
        // Query becomes: ... ST_GeomFromGeoJSON('{"key": "') --"}') ...
        // Result: ST_GeomFromGeoJSON('{"key": "') is the function call. -- comments out the rest.
        // This might fail because ST_GeomFromGeoJSON expects valid GeoJSON, but the fact that we can manipulate the SQL structure is the vulnerability.

        // A better proof is causing a syntax error by just injecting a single quote.
        $maliciousJson = '{"type":"Point","coordinates":[1,1],"name":"test\'ing"}';

        // Act: Make a POST request to create a region
        try {
            $response = $this->actingAs($admin)->post(route('admin.regions.store', ['locale' => 'en']), [
                'country_id' => $country->id,
                'name_en' => 'Malicious Region',
                'name_local' => 'Malicious Region Local',
                'is_active' => 1,
                'boundary_geojson' => $maliciousJson,
            ]);

            // If the code is vulnerable, the above might throw a QueryException (500 error) due to syntax error in SQL.
            // If it's fixed (escaped), it should likely fail DB level validation for "Invalid GeoJSON" inside the SQL function,
            // OR successfully insert if the DB handles escaped quotes correctly in the JSON string.
            // But crucially, it shouldn't be a SQL syntax error caused by the quote.

            // However, MySQL's ST_GeomFromGeoJSON might complain about the content, but that's a data error, not a SQL injection syntax error.
            // The vulnerability is that we broke the SQL string literal.

            // We expect a 500 error if vulnerable (due to SQL syntax error).
            // We expect a redirect (success) or a validation error if fixed and the DB accepts the escaped JSON.
             $response->assertStatus(302); // Redirects on success

        } catch (\Illuminate\Database\QueryException $e) {
            // If we catch a QueryException with a syntax error related to the quote, it confirms the bug.
            $this->fail("SQL Injection vulnerability detected: " . $e->getMessage());
        }
    }
}
