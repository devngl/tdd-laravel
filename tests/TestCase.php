<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

        EloquentCollection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value),
                'Failed asserting that the collection has the specified value.');
        });

        EloquentCollection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value),
                'Failed asserting that the collection doesnt have the specified value.');
        });

        EloquentCollection::macro('assertEquals', function (array $items) {
            Assert::assertCount(count($this), $items);
            $this->zip($items)->each(static function ($pair) {
                [$a, $b] = $pair;
                Assert::assertTrue($a->is($b));
            });
        });

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }
}
