<?php

namespace Clair\Ach\Tests\Support;

use Carbon\Carbon;
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

    public function test_create_checksum()
    {
        $aba_number = '789456124';

        $checksum = Utils::createChecksum($aba_number);

        $this->assertSame(160, $checksum);
    }

    public function test_format_date()
    {
        Carbon::setTestNow('2021-05-20');

        $date = Utils::formatDate();

        $this->assertSame('210520', $date);

        $date = Utils::formatDate('2021-06-17');

        $this->assertSame('210617', $date);
    }

    public function test_format_time()
    {
        Carbon::setTestNow('2021-05-20 13:23:00');

        $date = Utils::formatTime();

        $this->assertSame('1323', $date);

        $date = Utils::formatTime('2021-05-20 05:34:00');

        $this->assertSame('0534', $date);
    }
}
