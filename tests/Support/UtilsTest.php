<?php

namespace Clair\Ach\Tests\Support;

use Clair\Ach\Definitions\Entry;
use Clair\Ach\Support\Utils;
use Clair\Ach\Tests\TestCase;

class UtilsTest extends TestCase
{
    // http://www.brainjar.com/js/validation/
    public function test_compute_check_digit()
    {
        $aba_number = '78945612';

        $check_digit = Utils::computeCheckDigit($aba_number);

        $this->assertSame(4, $check_digit);
    }
}
