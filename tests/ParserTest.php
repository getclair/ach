<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Parser;

class ParserTest extends TestCase
{
    public function test_generate_ach_file()
    {
        $contents = file_get_contents(__DIR__.'/testdata/sample.ach');

        $parser = new Parser($contents);

        $file = $parser->parse();

        $output = $file->generateFile();

        $this->assertSame($output, $contents);
    }
}
