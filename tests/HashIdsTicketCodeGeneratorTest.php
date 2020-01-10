<?php

declare(strict_types = 1);

namespace Tests;

use App\HashIdsTicketCodeGenerator;
use App\Ticket;

final class HashIdsTicketCodeGeneratorTest extends TestCase
{
    /** @test */
    public function ticket_code_are_at_least_6_characters_long(): void
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('testsalt1');
        $code                = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) >= 6);
    }

    /** @test */
    public function ticket_code_can_only_contain_uppercase_letters(): void
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('testsalt1');
        $code                = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    /** @test */
    public function ticket_code_for_the_same_ticket_id_are_the_same(): void
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('testsalt1');
        $codeA               = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $codeB               = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($codeA, $codeB);
    }

    /** @test */
    public function tickets_code_for_different_tickets_ids_are_different(): void
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('testsalt1');
        $codeA               = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $codeB               = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($codeA, $codeB);
    }

    /** @test */
    public function ticket_codes_generated_with_different_salts_are_different(): void
    {
        $ticketCodeGeneratorA = new HashIdsTicketCodeGenerator('testsalt1');
        $ticketCodeGeneratorB = new HashIdsTicketCodeGenerator('testsalt2');

        $codeA = $ticketCodeGeneratorA->generateFor(new Ticket(['id' => 1]));
        $codeB = $ticketCodeGeneratorB->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($codeA, $codeB);
    }
}
