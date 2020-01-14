<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\Assert;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejemplo de como crear un método dentro de TestResponse
        // que actua como viewData($key)
        TestResponse::macro('data', function ($key) {
            return $this->viewData($key);
        });

        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value),
                'Failed asserting that the collection has the specified value.');
        });
        Collection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value),
                'Failed asserting that the collection doesnt have the specified value.');
        });

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }
}
