<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Parser;
use Illuminate\Support\Facades\File;

class ParserTest extends TestCase
{
    public function test_generate_ach_file()
    {
        foreach (File::files(__DIR__.'/testdata') as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $contents = file_get_contents($file->getRealPath());

            $parser = new Parser($contents);

            $file = $parser->parse();

            $output = $file->generateFile();

            $this->assertEquals($output, $contents);
        }
    }
}
