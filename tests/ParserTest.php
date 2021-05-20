<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Parser;

class ParserTest extends TestCase
{
    public function test_parse_ach()
    {
        $contents = file_get_contents(__DIR__.'/testdata/sample.ach');

        $parser = new Parser($contents);

        $file = $parser->parse();

        dd($file->generateFile());
    }
}
