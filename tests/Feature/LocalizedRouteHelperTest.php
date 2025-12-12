<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocalizedRouteHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Define test routes
        Route::get('/test-route', function () {
            return 'ok';
        })->name('test.route');

        Route::get('/test-route-param/{id}', function ($id) {
            return $id;
        })->name('test.route.param');

        Route::group(['prefix' => '{locale}', 'as' => 'localized.'], function () {
            Route::get('/test-route', function () {
                return 'localized ok';
            })->name('test.route');
        });
    }

    public function test_localized_route_accepts_array_parameters()
    {
        $url = localized_route('test.route', ['foo' => 'bar']);
        $this->assertStringContainsString('test-route', $url);
        $this->assertStringContainsString('foo=bar', $url);
    }

    public function test_localized_route_accepts_string_query_parameters()
    {
        $url = localized_route('test.route', 'a=1&b=2');
        $this->assertStringContainsString('test-route', $url);
        $this->assertStringContainsString('a=1', $url);
        $this->assertStringContainsString('b=2', $url);
    }

    public function test_localized_route_accepts_null_parameters()
    {
        $url = localized_route('test.route', null);
        $this->assertStringContainsString('test-route', $url);
    }

    public function test_localized_route_handles_invalid_types_gracefully()
    {
        // Should not throw TypeError
        $url = localized_route('test.route', 123);
        $this->assertStringContainsString('test-route', $url);

        // Should treat as empty array, so no ID param (might throw UrlGenerationException if param required)
        // But for route without params, it works
    }

    public function test_localized_route_with_required_param_passed_as_array()
    {
        $url = localized_route('test.route.param', ['id' => 123]);
        $this->assertStringContainsString('/test-route-param/123', $url);
    }

    public function test_localized_route_localization_logic()
    {
        app()->setLocale('ar');
        $url = localized_route('test.route');
        // ar is default, so no prefix
        $this->assertStringNotContainsString('/ar/', $url);
        $this->assertEquals(route('test.route'), $url);

        app()->setLocale('en');
        $url = localized_route('test.route');
        // en should use localized prefix
        $this->assertStringContainsString('/en/test-route', $url);
    }
}
