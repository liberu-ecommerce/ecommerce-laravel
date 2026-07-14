<?php

namespace Tests\Unit;

use App\Support\EuVat;
use PHPUnit\Framework\TestCase;

class EuVatTest extends TestCase
{
    public function test_covers_all_27_member_states(): void
    {
        $this->assertCount(27, EuVat::memberStates());
    }

    public function test_is_member_state_is_case_insensitive_and_excludes_non_eu(): void
    {
        $this->assertTrue(EuVat::isMemberState('DE'));
        $this->assertTrue(EuVat::isMemberState('de'));
        $this->assertFalse(EuVat::isMemberState('US'));
        $this->assertFalse(EuVat::isMemberState('GB')); // post-Brexit
        $this->assertFalse(EuVat::isMemberState(null));
    }

    public function test_standard_rate_lookup(): void
    {
        $this->assertSame(19.0, EuVat::standardRate('DE'));
        $this->assertSame(27.0, EuVat::standardRate('HU')); // highest in the EU
        $this->assertNull(EuVat::standardRate('US'));
    }
}
