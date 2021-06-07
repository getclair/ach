<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Parser;
use Illuminate\Support\Facades\File;

class ParserTest extends TestCase
{
    public function test_generate_ach_file()
    {
        foreach (File::files(__DIR__ . '/testdata') as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $name = $file->getFilename();

            if ($name === 'nach-valid-addenda.ach') {
                $parser = new Parser($contents = $file->getContents());

                $file = $parser->parse();

                $output = $file->generateFile();

                $this->assertEquals($output, $contents, $name);
            }
        }
    }
}
