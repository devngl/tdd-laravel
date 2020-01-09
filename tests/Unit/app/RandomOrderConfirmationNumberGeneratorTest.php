<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\RandomOrderConfirmationNumberGenerator;
use Tests\TestCase;

final class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    // Can only contain uppercase letters and numbers
    // Cannot contain ambiguous characters (o0i1)
    // ABCDEFGHJKLMNPQRSTUVWXYZ
    // 23456789
    // Length: 24
    // All order confirmation numbers must be unique

    /** @test */
    public function must_be_24_characters_long(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumber = $generator->generate();

        $this->assertEquals(24, strlen($confirmationNumber));
    }

    /** @test */
    public function can_only_contain_uppercase_letters_and_numbers(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumber = $generator->generate();

        $this->assertRegExp('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    /** @test */
    public function cannot_contain_ambiguous_characters(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumber = $generator->generate();

        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
    }

    /** @test */
    public function confirmation_numbers_must_be_unique(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumbers = array_map(function ($i) use ($generator) {
            return $generator->generate();
        }, range(1, 100));

        $this->assertCount(100, array_unique($confirmationNumbers));
    }
}
