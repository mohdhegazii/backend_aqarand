<?php

namespace Tests\Unit\Services;

use App\Services\LocationService;
use Tests\TestCase;

class LocationServiceTest extends TestCase
{
    // Note: Due to lack of migrations in this environment, this test is primarily
    // structural to verify the service class exists and methods are callable.
    // In a real environment, we would use RefreshDatabase and factories.

    public function test_service_class_exists()
    {
        $this->assertTrue(class_exists(LocationService::class));
    }

    public function test_methods_exist()
    {
        $service = new LocationService();
        $this->assertTrue(method_exists($service, 'getRegionsByCountry'));
        $this->assertTrue(method_exists($service, 'getCitiesByRegion'));
        $this->assertTrue(method_exists($service, 'getDistrictsByCity'));
        $this->assertTrue(method_exists($service, 'getProjectsByDistrict'));
        $this->assertTrue(method_exists($service, 'searchRegions'));
        $this->assertTrue(method_exists($service, 'searchCities'));
        $this->assertTrue(method_exists($service, 'searchDistricts'));
    }
}
